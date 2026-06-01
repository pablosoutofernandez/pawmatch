<?php

namespace App\Livewire;

use App\Models\Perro;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class DiscoverPerros extends Component
{
    use WithPagination;

    // Filtros
    public ?string $f_nombre = null;
    public ?string $f_raza   = null;
    public string  $f_tamano = 'todos';     // todos | pequeno | mediano | grande
    public ?int    $f_energia_min = null;
    public bool    $solo_disponibles = false;

    // Radio de búsqueda (km). Compartido con el Mapa a través del usuario.
    public int     $radio_km = 10;

    // Estado del feed
    public array $likesDados = [];

    public function mount(): void
    {
        if (Auth::user()->cannot('ver')) {
            abort(403, 'No tienes permiso para ver perros');
        }

        // Cargar el radio guardado por el usuario (sincronizado con el Mapa)
        $this->radio_km = Auth::user()->radioBusqueda();
    }

    public function updated($propertyName): void
    {
        if (str_starts_with($propertyName, 'f_') || $propertyName === 'solo_disponibles') {
            $this->resetPage();
        }

        // Al cambiar el radio: limitar al máximo del plan, persistirlo y refrescar
        if ($propertyName === 'radio_km') {
            $maximo = Auth::user()->radioMaximo();
            $this->radio_km = max(1, min($maximo, (int) $this->radio_km));
            Auth::user()->update(['radio_busqueda_km' => $this->radio_km]);
            $this->resetPage();
        }
    }

    public function limpiarFiltros(): void
    {
        $this->reset(['f_nombre', 'f_raza', 'f_tamano', 'f_energia_min', 'solo_disponibles']);
        $this->f_tamano = 'todos';
        $this->resetPage();
    }

    public function setTamano(string $tamano): void
    {
        $this->f_tamano = $tamano;
        $this->resetPage();
    }



    public function darLike(int $perroId): void
    {
        $perro = Perro::findOrFail($perroId);
        $usuario = Auth::user();

        if ($usuario->cannot('crear')) {
            abort(403, 'No tienes permiso para dar like');
        }

        // Límite del plan gratuito: si  ya se ha alcanzado el tope, mostrar modal y bloquear
        if (!$usuario->puedeIniciarMatch()) {
            $this->dispatch('sin-matches-disponibles');
            return;
        }

        $miPerro = $usuario->perroPrincipal();

        $esMatch = \App\Models\Like::darLike(
            $usuario->id,
            $perro->user_id,
            $miPerro?->id,
            $perro->id,
        );

        $this->likesDados[$perroId] = true;
        $this->resetPage();

        if ($esMatch) {
            // Si es un usuario gratuito y ahora tiene 3 matches, retirar todos sus likes pendientes
            if (!$usuario->es_premium && $usuario->matchesActivos() >= \App\Models\User::LIMITE_MATCHES_GRATIS) {
                $usuario->retirarLikesPendientes();
            }

            // Disparar pantalla de match (modal)
            $this->dispatch('match-cerrado', userId: $perro->user_id);
        } else {
            session()->flash('success', '♥ Le diste like a '.$perro->nombre.'. Se lo notificaremos a su dueño.');
        }
    }

    /**
     * "Pasar" un perro: se persiste para que no vuelva a aparecer en el feed.
     */
    public function pasar(int $perroId): void
    {
        $usuario = Auth::user();
        // Evitar duplicados y referencias inválidas
        if (!Perro::whereKey($perroId)->exists()) {
            return;
        }
        DB::table('perros_pasados')->insertOrIgnore([
            'user_id'    => $usuario->id,
            'perro_id'   => $perroId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->likesDados[$perroId] = false;
        $this->resetPage();
    }

    public function render()
    {
        $usuario = Auth::user();
        $miPerro = $usuario->perroPrincipal();

        // Perros a los que ya he dado like (pendientes o con match): se ocultan
        // del feed. Tras un match ya no necesito ver más perros del otro
        // usuario, porque match es a nivel de usuario (cubre todos sus perros).
        $perrosYaLikeados = \App\Models\Like::query()
            ->where('de_user_id', $usuario->id)
            ->whereNotNull('a_perro_id')
            ->pluck('a_perro_id')
            ->all();

        // Usuarios con los que ya hay match: ocultar también todos sus perros.
        // (En la práctica ya están todos en perrosYaLikeados por el cascade,
        // pero lo dejamos por defensividad ante datos heredados.)
        $usuariosConMatch = \App\Models\Like::query()
            ->where('de_user_id', $usuario->id)
            ->whereNotNull('match_at')
            ->pluck('a_user_id')
            ->unique()
            ->all();

        // Perros "pasados" (persistente).
        $perrosPasados = DB::table('perros_pasados')
            ->where('user_id', $usuario->id)
            ->pluck('perro_id')
            ->all();

        $query = Perro::query()
            ->with('dueno')
            ->excluyendoUsuario($usuario->id)
            ->porNombre($this->f_nombre)
            ->porRaza($this->f_raza)
            ->porTamano($this->f_tamano)
            ->porEnergia($this->f_energia_min)
            ->when($usuariosConMatch, fn ($q) => $q->whereNotIn('user_id', $usuariosConMatch))
            ->when($perrosYaLikeados, fn ($q) => $q->whereNotIn('id', $perrosYaLikeados))
            ->when($perrosPasados, fn ($q) => $q->whereNotIn('id', $perrosPasados));

        if ($this->solo_disponibles) {
            $query->whereHas('dueno', fn ($q) => $q->where('walk_now_until', '>', now()));
        }

        // Sólo perros de dueños con ubicación, para poder filtrar por distancia
        $tengoUbicacion = $usuario->tiene_ubicacion;

        $todos = $query->get();

        // Inyectar compatibilidad y distancia REAL, y filtrar por radio
        $todos->transform(function (Perro $perro) use ($miPerro, $usuario) {
            $perro->compatibilidad = $miPerro
                ? $miPerro->compatibilidadCon($perro)
                : rand(60, 95);

            $dist = $perro->dueno?->distanciaKm((float) $usuario->latitud, (float) $usuario->longitud);
            $perro->dist_num = $dist;
            $perro->distancia = $dist !== null ? $dist.' km' : '— km';
            $perro->disponible_ahora = $perro->dueno->paseando_ahora ?? false;
            return $perro;
        });

        // Filtrar por radio (sólo si tengo ubicación). Tope según plan.
        if ($tengoUbicacion) {
            $radio = min($usuario->radioMaximo(), $this->radio_km);
            $todos = $todos->filter(fn (Perro $p) => $p->dist_num !== null && $p->dist_num <= $radio);
        }

        // Ordenar por distancia (los sin distancia al final)
        $todos = $todos->sortBy(fn (Perro $p) => $p->dist_num ?? 99999)->values();

        // Paginación manual sobre la colección filtrada
        $porPagina = 4;
        $pagina    = $this->getPage();
        $items     = $todos->forPage($pagina, $porPagina)->values();

        $perros = new LengthAwarePaginator(
            $items,
            $todos->count(),
            $porPagina,
            $pagina,
            ['path' => request()->url(), 'pageName' => 'page']
        );
        $perros->withQueryString();

        return view('livewire.discover-perros', [
            'perros'         => $perros,
            'miPerro'        => $miPerro,
            'tengoUbicacion' => $tengoUbicacion,
            'radioMax'       => $usuario->radioMaximo(),
            'esPremium'      => $usuario->es_premium,
        ]);
    }
}
