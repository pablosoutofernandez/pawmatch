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

    // Mostrar también los perros que he pasado, para recuperarlos.
    public bool    $mostrarPasados = false;

    // Radio en km. Se comparte con el mapa via usuario.
    public int     $radio_km = 10;

    public array $likesDados = [];

    public function mount(): void
    {
        if (Auth::user()->cannot('ver')) {
            abort(403, 'No tienes permiso para ver perros');
        }

        $this->radio_km = Auth::user()->radioBusqueda();
    }

    public function updated($propertyName): void
    {
        if (str_starts_with($propertyName, 'f_') || $propertyName === 'solo_disponibles' || $propertyName === 'mostrarPasados') {
            $this->resetPage();
        }

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

        // Plan gratuito al tope: modal y fuera.
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
            $this->dispatch('match-cerrado', userId: $perro->user_id);
        } else {
            session()->flash('success', '♥ Le diste like a '.$perro->nombre.'. Se lo notificaremos a su dueño.');
        }
    }

    // Pasar = ocultar del feed (persistente).
    public function pasar(int $perroId): void
    {
        $usuario = Auth::user();
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

    public function quitarPasado(int $perroId): void
    {
        DB::table('perros_pasados')
            ->where('user_id', Auth::id())
            ->where('perro_id', $perroId)
            ->delete();

        unset($this->likesDados[$perroId]);
        $this->resetPage();
        session()->flash('success', 'Perro recuperado. Vuelve a estar en tu feed.');
    }

    public function render()
    {
        $usuario = Auth::user();
        $miPerro = $usuario->perroPrincipal();

        // Perros a los que ya he dado like: fuera del feed.
        $perrosYaLikeados = \App\Models\Like::query()
            ->where('de_user_id', $usuario->id)
            ->whereNotNull('a_perro_id')
            ->pluck('a_perro_id')
            ->all();

        // Usuarios con los que ya hay match: ocultar también sus otros perros
        // por si quedó alguno suelto en datos antiguos.
        $usuariosConMatch = \App\Models\Like::query()
            ->where('de_user_id', $usuario->id)
            ->whereNotNull('match_at')
            ->pluck('a_user_id')
            ->unique()
            ->all();

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
            ->when(!$this->mostrarPasados && $perrosPasados, fn ($q) => $q->whereNotIn('id', $perrosPasados));

        if ($this->solo_disponibles) {
            $query->whereHas('dueno', fn ($q) => $q->where('walk_now_until', '>', now()));
        }

        $tengoUbicacion = $usuario->tiene_ubicacion;

        $todos = $query->get();

        // Compatibilidad y distancia real perro a perro.
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

        // Si tengo ubicación, filtro por radio (recortado al tope del plan).
        if ($tengoUbicacion) {
            $radio = min($usuario->radioMaximo(), $this->radio_km);
            $todos = $todos->filter(fn (Perro $p) => $p->dist_num !== null && $p->dist_num <= $radio);
        }

        // Más cerca primero, los sin distancia al final.
        $todos = $todos->sortBy(fn (Perro $p) => $p->dist_num ?? 99999)->values();

        // Paginación manual sobre la colección ya filtrada.
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
            'perrosPasados'  => array_flip($perrosPasados),
        ]);
    }
}
