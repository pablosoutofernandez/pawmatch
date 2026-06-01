<?php

namespace App\Livewire;

use App\Models\Perro;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
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

    /**
     * Parques caninos REALES alrededor de la ubicación del usuario, vía la
     * API Overpass de OpenStreetMap (gratuita, sin clave). Resultado cacheado
     * 12 horas por celda geográfica para no sobrecargar el servicio.
     *
     * Si la petición falla o no hay ubicación, devuelve una colección vacía y
     * la app sigue funcionando con normalidad.
     */
    private function parques(): Collection
    {
        $yo = Auth::user();

        if (!$yo->tiene_ubicacion) {
            return collect();
        }

        $lat = (float) $yo->latitud;
        $lng = (float) $yo->longitud;

        // Bounding box dinámico = radio del usuario (1° lat ≈ 111 km)
        $kmAprox = max(2, $this->radio_km);
        $dLat    = $kmAprox / 111.0;
        $dLng    = $kmAprox / max(0.01, 111.0 * cos(deg2rad($lat)));

        // Clave de caché por celda gruesa (redondeo a 2 decimales ≈ 1 km).
        $clave = sprintf('parques:%.2f,%.2f,r%d', $lat, $lng, $kmAprox);

        $parques = Cache::remember($clave, now()->addHours(12), function () use ($lat, $lng, $dLat, $dLng) {
            $minLat = $lat - $dLat; $maxLat = $lat + $dLat;
            $minLng = $lng - $dLng; $maxLng = $lng + $dLng;


            $ql = "[out:json][timeout:8];\n"
                . "(\n"
                . "  node[\"leisure\"=\"dog_park\"]($minLat,$minLng,$maxLat,$maxLng);\n"
                . "  way[\"leisure\"=\"dog_park\"]($minLat,$minLng,$maxLat,$maxLng);\n"
                . "  relation[\"leisure\"=\"dog_park\"]($minLat,$minLng,$maxLat,$maxLng);\n"
                . ");\n"
                . "out center 60;";

            try {
                $resp = Http::timeout(8)
                    ->withHeaders(['User-Agent' => 'PawMatch/1.0 (proyecto académico)'])
                    ->asForm()
                    ->post('https://overpass-api.de/api/interpreter', ['data' => $ql]);

                if (!$resp->ok()) {
                    return [];
                }

                $items = [];
                foreach ($resp->json('elements', []) as $el) {
                    $plat = $el['lat']  ?? $el['center']['lat']  ?? null;
                    $plng = $el['lon']  ?? $el['center']['lon']  ?? null;
                    if ($plat === null || $plng === null) continue;

                    $tags = $el['tags'] ?? [];
                    $nombre = $tags['name'] ?? 'Parque canino';
                    $fenced = $tags['fence'] ?? $tags['barrier'] ?? null;
                    $tipo   = $fenced ? 'Vallado' : 'Sin vallar';

                    $items[] = [
                        'nombre' => $nombre,
                        'tipo'   => $tipo,
                        'lat'    => (float) $plat,
                        'lng'    => (float) $plng,
                    ];
                }
                return $items;
            } catch (\Throwable $e) {
                return [];
            }
        });

        // Calcular distancia respecto al usuario y ordenar
        return collect($parques)
            ->map(function ($p) use ($yo) {
                $d = $yo->distanciaKm($p['lat'], $p['lng']);
                $p['dist']     = $d !== null ? $d.' km' : 's/d';
                $p['dist_num'] = $d ?? 9999;
                return (object) $p;
            })
            ->sortBy('dist_num')
            ->values();
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
            // ->values()->all() garantiza array indexado (JSON array, no objeto).
            'perros'  => $this->mostrarPerros ? $this->perrosCercanos()->values()->all() : [],
            'parques' => $this->mostrarParques ? $this->parques()->values()->all() : [],
            'radio'   => $this->radio_km,
            'capas'   => [
                'perros'  => (bool) $this->mostrarPerros,
                'parques' => (bool) $this->mostrarParques,
            ],
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