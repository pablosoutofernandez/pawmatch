<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sirve imágenes guardadas en el disco "public" (storage/app/public)
 * a través de una ruta interna, de forma que el proyecto funcione
 * incluso si no se ha ejecutado `php artisan storage:link`.
 *
 * Ruta esperada: GET /img/{ruta}  (la {ruta} incluye subdirectorios)
 */
class ImagenController extends Controller
{
    public function show(Request $request, string $ruta): Response
    {
        // Sanea: no permitimos salir del disco con ../, y normalizamos barras.
        $ruta = ltrim(str_replace('\\', '/', $ruta), '/');
        if (str_contains($ruta, '..')) {
            abort(404);
        }

        $disk = Storage::disk('public');

        if (!$disk->exists($ruta)) {
            abort(404);
        }

        // response() de Storage establece Content-Type, Content-Length y
        // permite cacheo en navegador.
        return $disk->response($ruta, headers: [
            'Cache-Control' => 'public, max-age=604800',
        ]);
    }
}
