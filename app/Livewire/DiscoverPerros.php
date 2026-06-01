<?php

namespace App\Livewire;

use App\Models\Perro;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
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

        // Límite del plan gratuito: si este like cerraría un match y ya se ha
        // alcanzado el tope, se bloquea y se ofrece premium.
        if (\App\Models\Like::seriaMatch($usuario->id, $perro->user_id) && !$usuario->puedeIniciarMatch()) {
            session()->flash('premium', 'Has alcanzado el límite de '.\App\Models\User::LIMITE_MATCHES_GRATIS.' matches del plan gratuito. Hazte Premium para conseguir matches ilimitados.');
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

        if ($esMatch) {
            session()->flash('success', '🎉 ¡Es un match con '.$perro->nombre.'! Ya podéis hablar en el chat.');
        } else {
            session()->flash('success', '♥ Le diste like a '.$perro->nombre.'. Se lo notificaremos a su dueño.');
        }
    }

    public function pasar(int $perroId): void
    {
        $this->likesDados[$perroId] = false;
    }

    public function render()
    {
        $usuario = Auth::user();
        $miPerro = $usuario->perroPrincipal();

        // Perros que ya son objetivo de un like mío (con match): se ocultan
        // (los pendientes siguen mostrándose por si quiero dar like a otros perros).
        $perrosConMatch = \App\Models\Like::query()
            ->where('de_user_id', $usuario->id)
            ->whereNotNull('match_at')
            ->whereNotNull('a_perro_id')
            ->pluck('a_perro_id')
            ->all();

        // Usuarios con los que ya hay match: ocultar también sus perros del feed
        $usuariosConMatch = \App\Models\Like::query()
            ->where('de_user_id', $usuario->id)
            ->whereNotNull('match_at')
            ->pluck('a_user_id')
            ->unique()
            ->all();

        // Perros pasados en esta sesión
        $perrosPasados = array_keys(array_filter($this->likesDados, fn ($v) => $v === false));

        $query = Perro::query()
            ->with('dueno')
            ->excluyendoUsuario($usuario->id)
            ->porNombre($this->f_nombre)
            ->porRaza($this->f_raza)
            ->porTamano($this->f_tamano)
            ->porEnergia($this->f_energia_min)
            ->when($usuariosConMatch, fn ($q) => $q->whereNotIn('user_id', $usuariosConMatch))
            ->when($perrosConMatch, fn ($q) => $q->whereNotIn('id', $perrosConMatch))
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
