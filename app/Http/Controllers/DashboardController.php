<?php

namespace App\Http\Controllers;

use App\Models\Perro;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user  = Auth::user()->load('perros');
        $miPerro = $user->perros->first();

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
                $perro->distancia = $dist !== null ? $dist.' km' : '— km';
                return $perro;
            });

        // Stats globales
        $stats = [
            'perros_total'   => Perro::count(),
            'activos_ahora'  => User::where('walk_now_until', '>', now())
                                    ->where('id', '!=', $user->id)
                                    ->count(),
            'mis_puntos'     => $user->puntos ?? 0,
            'mis_matches'    => $user->likesEnviados()->whereNotNull('match_at')->count(),
        ];

        return view('dashboard', compact('user', 'miPerro', 'sugerencias', 'stats'));
    }
}
