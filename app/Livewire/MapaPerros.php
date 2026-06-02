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
    public bool $mapaVisible     = true;

    // Cache por request: distancias y resultado de Overpass no se recalculan
    // dos veces en el mismo render.
    private ?Collection $perrosMemo = null;
    private ?Collection $parquesMemo = null;

    public function mount(): void
    {
        $this->paseandoAhora = Auth::user()->paseando_ahora;
        $this->mapaVisible   = (bool) (Auth::user()->mapa_visible ?? true);
        // El radio se comparte con Descubrir (vive en el usuario).
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

    // Aparecer u ocultarse del mapa de los demás. Tú sigues viéndolos igual.
    public function toggleVisibilidad(): void
    {
        $usuario = Auth::user();
        $nuevo   = !$usuario->mapa_visible;
        $usuario->update(['mapa_visible' => $nuevo]);
        $this->mapaVisible = $nuevo;

        session()->flash('info', $nuevo
            ? 'Ahora apareces en el mapa de los demás.'
            : 'Te has ocultado del mapa. Nadie verá tu posición (tú sí ves a los demás).');
    }

    // Al mover el slider del radio: persistir y pedir al cliente que recargue
    // la página. Más simple que repintar el mapa en vivo.
    public function updated(string $prop): void
    {
        if ($prop === 'radio_km') {
            $maximo = Auth::user()->radioMaximo();
            $this->radio_km = max(1, min($maximo, (int) $this->radio_km));
            Auth::user()->update(['radio_busqueda_km' => $this->radio_km]);

            $this->dispatch('paw-recargar-pagina');
        }
    }

    private function claveParques(): ?string
    {
        $yo = Auth::user();
        if (!$yo->tiene_ubicacion) {
            return null;
        }
        $kmAprox = max(2, $this->radio_km);
        return sprintf('parques:%.2f,%.2f,r%d', (float) $yo->latitud, (float) $yo->longitud, $kmAprox);
    }

    private function olvidarCacheParques(): void
    {
        if ($clave = $this->claveParques()) {
            Cache::forget($clave);
        }
    }

    // Perros cercanos con distancia y compatibilidad reales.
    private function perrosCercanos(): Collection
    {
        if ($this->perrosMemo !== null) {
            return $this->perrosMemo;
        }

        $yo      = Auth::user();
        $miPerro = $yo->perros()->first();

        return $this->perrosMemo = Perro::with('dueno')
            ->excluyendoUsuario($yo->id)
            ->whereHas('dueno', fn ($q) => $q->whereNotNull('latitud')->whereNotNull('longitud')->where('mapa_visible', true))
            ->get()
            ->map(function (Perro $p) use ($yo, $miPerro) {
                $dueno = $p->dueno;
                $dist  = $dueno->distanciaKm((float) $yo->latitud, (float) $yo->longitud);

                return (object) [
                    'id'         => $p->id,
                    'nombre'     => $p->nombre,
                    'raza'       => $p->raza ?? 'Mestizo',
                    'foto'       => $p->foto_url,
                    'placeholder'=> $p->placeholder_url,
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

    // Parques caninos cerca, sacados de OpenStreetMap (API Overpass, gratis).
    // Cacheado 12h por celda; si falla devolvemos colección vacía.
    private function parques(): Collection
    {
        if ($this->parquesMemo !== null) {
            return $this->parquesMemo;
        }

        $yo = Auth::user();

        if (!$yo->tiene_ubicacion) {
            return $this->parquesMemo = collect();
        }

        $lat = (float) $yo->latitud;
        $lng = (float) $yo->longitud;

        // Bounding box ≈ radio del usuario (1° lat ≈ 111 km).
        $kmAprox = max(2, $this->radio_km);
        $dLat    = $kmAprox / 111.0;
        $dLng    = $kmAprox / max(0.01, 111.0 * cos(deg2rad($lat)));

        $clave = $this->claveParques();

        // ?fresh=1 fuerza saltar la caché (lo usa "Refrescar mapa").
        if (request()->boolean('fresh')) {
            Cache::forget($clave);
        }

        $fetchOverpass = function () use ($lat, $lng, $dLat, $dLng) {
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
        };

        // No cacheamos resultados vacíos: si Overpass falla, reintentamos la
        // próxima vez en lugar de quedarnos 12h sin parques.
        $cached = Cache::get($clave);
        if (is_array($cached) && !empty($cached)) {
            $parques = $cached;
        } else {
            $parques = $fetchOverpass();
            if (!empty($parques)) {
                Cache::put($clave, $parques, now()->addHours(12));
            }
        }

        return $this->parquesMemo = collect($parques)
            ->map(function ($p) use ($yo) {
                $d = $yo->distanciaKm($p['lat'], $p['lng']);
                $p['dist']     = $d !== null ? $d.' km' : 's/d';
                $p['dist_num'] = $d ?? 9999;
                return (object) $p;
            })
            ->sortBy('dist_num')
            ->values();
    }

    // Lo que consume el script de Leaflet.
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
            // ->values()->all() para que sea JSON array, no objeto.
            'perros'  => $this->perrosCercanos()->values()->all(),
            'parques' => $this->parques()->values()->all(),
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
            'mapaVisible'    => $this->mapaVisible,
        ]);
    }
}
