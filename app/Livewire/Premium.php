<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Premium extends Component
{
    // Demo sin pasarela: en producción aquí iría Stripe/PayPal.
    public function activar(): void
    {
        $usuario = Auth::user();

        $usuario->update([
            'plan'           => 'premium',
            'plan_expira_at' => now()->addMonth(),
        ]);

        if (!$usuario->hasRole('premium') && !$usuario->hasRole('admin')) {
            $usuario->syncRoles(['premium']);
        }

        session()->flash('success', '✨ ¡Bienvenido a PawMatch Premium! Ya tienes matches ilimitados.');
    }

    public function cancelar(): void
    {
        $usuario = Auth::user();

        $usuario->update([
            'plan'           => 'free',
            'plan_expira_at' => null,
            // El radio guardado puede ser mayor que el tope del plan free, lo recortamos.
            'radio_busqueda_km' => min((int) $usuario->radio_busqueda_km, \App\Models\User::RADIO_MAX_GRATIS),
        ]);

        if ($usuario->hasRole('premium')) {
            $usuario->syncRoles(['user']);
        }

        session()->flash('info', 'Tu plan ha vuelto a ser gratuito.');
    }

    public function render()
    {
        $usuario = Auth::user();

        return view('livewire.premium', [
            'esPremium'       => $usuario->es_premium,
            'matchesActivos'  => $usuario->matchesActivos(),
            'matchesRestantes'=> $usuario->matchesRestantes(),
            'limite'          => \App\Models\User::LIMITE_MATCHES_GRATIS,
            'expira'          => $usuario->plan_expira_at,
        ]);
    }
}
