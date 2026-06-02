<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminRole
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()?->hasRole('admin')) {
            abort(403, 'Solo administradores.');
        }

        return $next($request);
    }
}
