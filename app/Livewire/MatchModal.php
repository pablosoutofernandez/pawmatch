<?php

namespace App\Livewire;

use App\Models\Conversacion;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

// Modal que sale cuando se cierra un match. Escucha 'match-cerrado'
// (con el userId del otro) y enseña la pantalla de celebración.
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
                // Si el otro ya no existe, cerramos sin más.
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
