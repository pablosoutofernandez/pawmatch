<?php

namespace App\Livewire;

use App\Models\Perro;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class MapaPerros extends Component
{
    public bool $paseandoAhora = false;
    public int  $radio_km = 5;
    public bool $mostrarPerros = true;
    public bool $mostrarParques = true;

    public function mount(): void
    {
        $this->paseandoAhora = Auth::user()->paseando_ahora;
    }

    public function togglePaseoAhora(): void
    {
        $usuario = Auth::user();

        if ($this->paseandoAhora) {
            $usuario->update(['walk_now_until' => null]);
            $this->paseandoAhora = false;
            session()->flash('info', 'Modo paseo desactivado');
        } else {
            $usuario->update(['walk_now_until' => now()->addMinutes(60)]);
            $this->paseandoAhora = true;
            session()->flash('success', 'Modo paseo activado durante 1 hora');
        }
    }

    public function render()
    {
        $perrosCercanos = Perro::with('dueno')
            ->excluyendoUsuario(Auth::id())
            ->inRandomOrder()
            ->limit(5)
            ->get()
            ->map(fn (Perro $p) => (object) [
                'id'         => $p->id,
                'nombre'     => $p->nombre,
                'raza'       => $p->raza,
                'compat'     => rand(65, 95),
                'distancia'  => round(rand(3, 40) / 10, 1).' km',
                'activo'     => $p->dueno->paseando_ahora,
            ]);

        // Parques (datos estáticos para demo)
        $parques = [
            (object) ['nombre' => 'Parque del Retiro',     'tipo' => 'Vallado · Grande',      'dist' => '0.5 km'],
            (object) ['nombre' => 'Parque Juan Carlos I',  'tipo' => 'Vallado · Muy grande',  'dist' => '1.8 km'],
            (object) ['nombre' => 'El Capricho',           'tipo' => 'Sin vallar · Grande',   'dist' => '3.1 km'],
        ];

        return view('livewire.mapa-perros', [
            'perrosCercanos' => $perrosCercanos,
            'parques'        => $parques,
        ]);
    }
}
