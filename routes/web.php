<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : view('welcome');
});

// Servir imágenes del disco "public" sin depender del symlink storage:link.
// Acepta cualquier subruta (perros/x.jpg, avatars/y.png, etc.).
Route::get('/img/{ruta}', [\App\Http\Controllers\ImagenController::class, 'show'])
    ->where('ruta', '.*')
    ->name('imagen.show');

Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard (controller — vista simple)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // PawMatch — componentes Livewire como rutas (estilo mi-app-sail)
    Route::get('/discover', \App\Livewire\DiscoverPerros::class)->name('discover');
    Route::get('/chat',     \App\Livewire\ChatPerros::class)->name('chat');
    Route::get('/mapa',     \App\Livewire\MapaPerros::class)->name('mapa');
    Route::get('/notificaciones', \App\Livewire\Notificaciones::class)->name('notificaciones');
    Route::get('/premium',        \App\Livewire\Premium::class)->name('premium');
    Route::get('/perfil',          \App\Livewire\EditarPerfil::class)->name('perfil');
    Route::get('/mi-perfil',       \App\Livewire\VerPerfil::class)->name('mi-perfil');
    Route::get('/perfil/{userId}', \App\Livewire\VerPerfil::class)->name('ver-perfil');
    Route::get('/perro/{perroId}', \App\Livewire\VerPerfil::class)->name('ver-perro');

    // Ubicación (geolocalización en tiempo real)
    Route::post('/ubicacion', [\App\Http\Controllers\UbicacionController::class, 'actualizar'])
        ->name('ubicacion.actualizar');

    // Profile (de Breeze)
    Route::get('/profile',    [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',  [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [\App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');

    // Zona de administración (solo rol 'admin').
    Route::middleware(function ($request, $next) {
            if (!$request->user()?->hasRole('admin')) {
                abort(403, 'Solo administradores.');
            }
            return $next($request);
        })->prefix('admin')->group(function () {
            Route::get('/usuarios', \App\Livewire\AdminUsuarios::class)->name('admin.usuarios');
        });
});

require __DIR__.'/auth.php';
