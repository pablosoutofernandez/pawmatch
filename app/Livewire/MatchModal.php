<?php

namespace App\Livewire;

use App\Models\Conversacion;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Modal que aparece cuando se cierra un match nuevo.
 * Escucha el evento Livewire 'match-cerrado' (userId del otro usuario)
 * y muestra la pantalla de celebración con los perros de ambos lados.
 */
class MatchModal extends Component
{
    public bool $abierto = false;
    public ?int $otroUserId = null;

    #[On('match-cerrado')]
    public function mostrar(int $userId): void
    {
        $this->otroUserId = $userId;
        $this->abierto = true;
    }

    public function cerrar(): void
    {
        $this->abierto = false;
        $this->otroUserId = null;
    }

    public function render()
    {
        $yo = Auth::user();
        $otro = null;
        $misPerros = collect();
        $susPerros = collect();
        $conversacionId = null;

        if ($this->abierto && $this->otroUserId) {
            $otro = User::with('perros')->find($this->otroUserId);
            if ($otro) {
                $misPerros = $yo->perros()->orderBy('id')->get();
                $susPerros = $otro->perros()->orderBy('id')->get();

                $conv = Conversacion::whereHas('participantes', fn ($q) => $q->where('users.id', $yo->id))
                    ->whereHas('participantes', fn ($q) => $q->where('users.id', $otro->id))
                    ->first();
                $conversacionId = $conv?->id;
            } else {
                // El otro usuario ya no existe: cerramos en silencio
                $this->abierto = false;
            }
        }

        return view('livewire.match-modal', [
            'yo'             => $yo,
            'otro'           => $otro,
            'misPerros'      => $misPerros,
            'susPerros'      => $susPerros,
            'conversacionId' => $conversacionId,
        ]);
    }
}
