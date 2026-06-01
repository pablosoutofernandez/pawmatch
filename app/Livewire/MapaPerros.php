<?php

namespace App\Livewire;

use App\Models\Perro;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class MapaPerros extends Component
{
    public bool $paseandoAhora  = false;
    public int  $radio_km       = 10;
    public bool $mostrarPerros  = true;
    public bool $mostrarParques = true;

    public function mount(): void
    {
        $this->paseandoAhora = Auth::user()->paseando_ahora;
        // Radio compartido con Descubrir (persistido en el usuario)
        $this->radio_km = Auth::user()->radioBusqueda();
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

    /** Reemite los datos al mapa cuando el usuario cambia capas o radio. */
    public function updated(string $prop): void
    {
        // Al cambiar el radio: limitar al máximo del plan y persistirlo (sincroniza con Descubrir)
        if ($prop === 'radio_km') {
            $maximo = Auth::user()->radioMaximo();
            $this->radio_km = max(1, min($maximo, (int) $this->radio_km));
            Auth::user()->update(['radio_busqueda_km' => $this->radio_km]);
        }

        if (in_array($prop, ['mostrarPerros', 'mostrarParques', 'radio_km'], true)) {
            $this->dispatch('mapa-datos', data: $this->mapData());
        }
    }

    /** Perros cercanos REALES (distancia Haversine + compatibilidad reales). */
    private function perrosCercanos(): Collection
    {
        $yo      = Auth::user();
        $miPerro = $yo->perros()->first();

        return Perro::with('dueno')
            ->excluyendoUsuario($yo->id)
            ->whereHas('dueno', fn ($q) => $q->whereNotNull('latitud')->whereNotNull('longitud'))
            ->get()
            ->map(function (Perro $p) use ($yo, $miPerro) {
                $dueno = $p->dueno;
                $dist  = $dueno->distanciaKm((float) $yo->latitud, (float) $yo->longitud);

                return (object) [
                    'id'         => $p->id,
                    'nombre'     => $p->nombre,
                    'raza'       => $p->raza ?? 'Mestizo',
                    'compat'     => $miPerro ? $miPerro->compatibilidadCon($p) : 75,
                    'distancia'  => $dist !== null ? $dist.' km' : 's/d',
                    'dist_num'   => $dist ?? 9999,
                    'activo'     => (bool) $dueno->paseando_ahora,
                    ...$dueno->coordenadasFuzzificadas(),
                    'perfil_url' => route('ver-perro', $p->id),
                    'dueno_url'  => route('ver-perfil', $dueno->id),
                ];
            })
            ->filter(fn ($p) => $yo->tiene_ubicacion ? $p->dist_num <= $this->radio_km : true)
            ->sortBy('dist_num')
            ->values();
    }

    private function parques(): Collection
    {
        $yo      = Auth::user();
        $baseLat = (float) ($yo->latitud ?? 40.4168);
        $baseLng = (float) ($yo->longitud ?? -3.7038);

        return collect([
            (object) ['nombre' => 'Parque canino más cercano', 'tipo' => 'Vallado · Grande',     'dist' => '0.5 km', 'lat' => $baseLat + 0.004, 'lng' => $baseLng + 0.003],
            (object) ['nombre' => 'Zona de esparcimiento',      'tipo' => 'Vallado · Muy grande', 'dist' => '1.8 km', 'lat' => $baseLat - 0.006, 'lng' => $baseLng + 0.008],
            (object) ['nombre' => 'Pradera para perros',        'tipo' => 'Sin vallar · Grande',  'dist' => '3.1 km', 'lat' => $baseLat + 0.010, 'lng' => $baseLng - 0.009],
        ]);
    }

    /** Estructura que consume el script de Leaflet. */
    private function mapData(): array
    {
        $yo = Auth::user();

        return [
            'usuario' => [
                'lat'    => (float) ($yo->latitud ?? 40.4168),
                'lng'    => (float) ($yo->longitud ?? -3.7038),
                'tiene'  => (bool) $yo->tiene_ubicacion,
                'nombre' => $yo->name,
            ],
            'perros'  => $this->mostrarPerros ? $this->perrosCercanos()->all() : [],
            'parques' => $this->mostrarParques ? $this->parques()->all() : [],
            'radio'   => $this->radio_km,
        ];
    }

    public function render()
    {
        return view('livewire.mapa-perros', [
            'perrosCercanos' => $this->perrosCercanos(),
            'parques'        => $this->parques(),
            'mapData'        => $this->mapData(),
            'tieneUbicacion' => (bool) Auth::user()?->tiene_ubicacion,
            'radioMax'       => Auth::user()->radioMaximo(),
            'esPremium'      => Auth::user()->es_premium,
        ]);
    }
}