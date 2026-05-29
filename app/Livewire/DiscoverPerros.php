<?php

namespace App\Livewire;

use App\Models\Perro;
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
    public int     $radio_km = 5;

    // Estado del feed
    public array $likesDados = [];

    public function mount(): void
    {
        if (Auth::user()->cannot('ver')) {
            abort(403, 'No tienes permiso para ver perros');
        }
    }

    public function updated($propertyName): void
    {
        if (str_starts_with($propertyName, 'f_') || $propertyName === 'solo_disponibles') {
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

        $miPerro = $usuario->perros()->first();

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
        $miPerro = $usuario->perros()->first();

        // IDs de usuarios con los que ya hay match (en cualquier dirección)
        $idsConLike = \App\Models\Like::query()
            ->where(fn ($q) => $q
                ->where('de_user_id', $usuario->id)
                ->orWhere('a_user_id', $usuario->id)
            )
            ->get()
            ->map(fn ($l) => $l->de_user_id === $usuario->id ? $l->a_user_id : $l->de_user_id)
            ->unique()
            ->values()
            ->toArray();

        // IDs de perros pasados en esta sesión (likesDados[id] === false)
        $perrosPasados = array_keys(array_filter($this->likesDados, fn ($v) => $v === false));

        $query = Perro::query()
            ->with('dueno')
            ->excluyendoUsuario($usuario->id)
            ->porNombre($this->f_nombre)
            ->porRaza($this->f_raza)
            ->porTamano($this->f_tamano)
            ->porEnergia($this->f_energia_min)
            // Ocultar perros de usuarios con los que ya hay match
            ->when($idsConLike, fn ($q) => $q->whereNotIn('user_id', $idsConLike))
            // Ocultar perros pasados en esta sesión
            ->when($perrosPasados, fn ($q) => $q->whereNotIn('id', $perrosPasados));

        if ($this->solo_disponibles) {
            $query->whereHas('dueno', fn ($q) => $q->where('walk_now_until', '>', now()));
        }

        $perros = $query->paginate(4)->withQueryString();

        // Inyectar compatibilidad y distancia REAL en cada perro
        $yo = $usuario;
        $perros->getCollection()->transform(function (Perro $perro) use ($miPerro, $yo) {
            $perro->compatibilidad = $miPerro
                ? $miPerro->compatibilidadCon($perro)
                : rand(60, 95);

            $dist = $perro->dueno?->distanciaKm((float) $yo->latitud, (float) $yo->longitud);
            $perro->distancia = $dist !== null ? $dist.' km' : '— km';
            $perro->disponible_ahora = $perro->dueno->paseando_ahora ?? false;
            return $perro;
        });

        return view('livewire.discover-perros', [
            'perros'  => $perros,
            'miPerro' => $miPerro,
        ]);
    }
}