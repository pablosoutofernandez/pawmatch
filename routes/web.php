<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard (controller — vista simple)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // PawMatch — componentes Livewire como rutas (estilo mi-app-sail)
    Route::get('/discover', \App\Livewire\DiscoverPerros::class)->name('discover');
    Route::get('/chat',     \App\Livewire\ChatPerros::class)->name('chat');
    Route::get('/mapa',     \App\Livewire\MapaPerros::class)->name('mapa');
    Route::get('/notificaciones', \App\Livewire\Notificaciones::class)->name('notificaciones');
    Route::get('/perfil',          \App\Livewire\EditarPerfil::class)->name('perfil');
    Route::get('/mi-perfil',       \App\Livewire\VerPerfil::class)->name('mi-perfil');
    Route::get('/perfil/{userId}', \App\Livewire\VerPerfil::class)->name('ver-perfil');

    // Ubicación (geolocalización en tiempo real)
    Route::post('/ubicacion', [\App\Http\Controllers\UbicacionController::class, 'actualizar'])
        ->name('ubicacion.actualizar');

    // Profile (de Breeze)
    Route::get('/profile',    [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',  [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [\App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
