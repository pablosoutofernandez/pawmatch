<?php

namespace App\Http\Controllers;

use App\Models\Perro;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user    = Auth::user()->load('perros');
        $miPerro = $user->perros->first();
        $misPerros = $user->perros;

        // Perros sugeridos (excluyendo los míos)
        $sugerencias = Perro::with('dueno')
            ->excluyendoUsuario($user->id)
            ->inRandomOrder()
            ->limit(4)
            ->get()
            ->map(function (Perro $perro) use ($miPerro, $user) {
                $perro->compatibilidad = $miPerro
                    ? $miPerro->compatibilidadCon($perro)
                    : rand(65, 95);
                $dist = $perro->dueno?->distanciaKm((float) $user->latitud, (float) $user->longitud);
                $perro->distancia = $dist !== null ? $dist.' km' : '-- km';
                return $perro;
            });

        // Stats globales
        $stats = [
            'perros_total'  => Perro::count(),
            'activos_ahora' => User::where('walk_now_until', '>', now())
                                   ->where('id', '!=', $user->id)
                                   ->count(),
            'mis_puntos'    => $user->puntos ?? 0,
            'mis_matches'   => $user->likesEnviados()->whereNotNull('match_at')->count(),
        ];

        // Mini-mapa del dashboard
        $miniMapData = $this->miniMapData($user);

        return view('dashboard', compact('user', 'miPerro', 'misPerros', 'sugerencias', 'stats', 'miniMapData'));
    }

    private function miniMapData(User $user): array
    {
        $lat = $user->latitud ? (float) $user->latitud : null;
        $lng = $user->longitud ? (float) $user->longitud : null;

        // Perros de otros usuarios que tengan coordenadas, a <= 15 km (max 30)
        $perros = Perro::with('dueno')
            ->excluyendoUsuario($user->id)
            ->whereHas('dueno', fn ($q) => $q->whereNotNull('latitud')->whereNotNull('longitud'))
            ->get()
            ->filter(function (Perro $perro) use ($lat, $lng) {
                if (!$lat || !$lng) return true; // sin ubicacion propia -> todos
                $dist = $perro->dueno->distanciaKm($lat, $lng);
                return $dist !== null && $dist <= 15;
            })
            ->take(30)
            ->map(function (Perro $perro) {
                // Nunca exponemos las coordenadas reales de otros usuarios:
                // se difuminan igual que en el mapa principal.
                $coords = $perro->dueno->coordenadasFuzzificadas();
                return [
                    'nombre' => $perro->nombre,
                    'raza'   => $perro->raza ?? 'Mestizo',
                    'lat'    => $coords['lat'],
                    'lng'    => $coords['lng'],
                    'activo' => $perro->dueno->paseando_ahora,
                ];
            })
            ->values()
            ->toArray();

        return [
            'centro_lat'      => $lat ?? 40.4168,
            'centro_lng'      => $lng ?? -3.7038,
            'tiene_ubicacion' => $lat !== null,
            'perros'          => $perros,
        ];
    }
}
