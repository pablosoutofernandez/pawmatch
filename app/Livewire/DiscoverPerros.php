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
            session()->flash('success', '🎉 ¡Nuevo match con '.$perro->nombre.'!');
        } else {
            session()->flash('success', 'Le diste like a '.$perro->nombre);
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

        $query = Perro::query()
            ->with('dueno')
            ->excluyendoUsuario($usuario->id)
            ->porNombre($this->f_nombre)
            ->porRaza($this->f_raza)
            ->porTamano($this->f_tamano)
            ->porEnergia($this->f_energia_min);

        if ($this->solo_disponibles) {
            $query->whereHas('dueno', fn ($q) => $q->where('walk_now_until', '>', now()));
        }

        $perros = $query->paginate(4)->withQueryString();

        // Inyectar compatibilidad y distancia simulada en cada perro
        $perros->getCollection()->transform(function (Perro $perro) use ($miPerro) {
            $perro->compatibilidad = $miPerro
                ? $miPerro->compatibilidadCon($perro)
                : rand(60, 95);
            $perro->distancia = round(rand(3, 40) / 10, 1).' km';
            $perro->disponible_ahora = $perro->dueno->paseando_ahora ?? false;
            return $perro;
        });

        return view('livewire.discover-perros', [
            'perros'  => $perros,
            'miPerro' => $miPerro,
        ]);
    }
}
