<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class ModalSinMatches extends Component
{
    public bool $abierto = false;

    #[On('sin-matches-disponibles')]
    public function mostrar(): void
    {
        $this->abierto = true;
    }

    public function cerrar(): void
    {
        $this->abierto = false;
    }

    public function render()
    {
        $usuario = Auth::user();

        return view('livewire.modal-sin-matches', [
            'matchesActivos' => $usuario->matchesActivos(),
            'limiteMatches' => \App\Models\User::LIMITE_MATCHES_GRATIS,
        ]);
    }
}