<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UbicacionController extends Controller
{
    /**
     * Guarda la ubicación del usuario autenticado (geolocalización del navegador).
     * Se invoca desde el front con fetch() — incluida la ubicación en tiempo real.
     */
    public function actualizar(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'latitud'  => ['required', 'numeric', 'between:-90,90'],
            'longitud' => ['required', 'numeric', 'between:-180,180'],
        ]);

        Auth::user()->update([
            'latitud'  => $datos['latitud'],
            'longitud' => $datos['longitud'],
        ]);

        return response()->json([
            'ok'       => true,
            'latitud'  => (float) $datos['latitud'],
            'longitud' => (float) $datos['longitud'],
        ]);
    }
}
