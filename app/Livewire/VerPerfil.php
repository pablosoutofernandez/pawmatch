<?php

namespace App\Livewire;

use App\Models\Like;
use App\Models\Perro;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class VerPerfil extends Component
{
    public User  $perfil;           // Usuario cuyo perfil se ve
    public bool  $esMiPerfil = false;
    public bool  $yaLike     = false;
    public bool  $esMatch    = false;
    public ?int  $compatibilidad = null;
    public ?int  $conversacionId = null;

    // Stats calculadas
    public int $totalMatches    = 0;
    public int $likesRecibidos  = 0;
    public int $diasEnPawMatch  = 0;

    public function mount(?int $userId = null): void
    {
        $yo = Auth::user();

        // Sin parámetro → mi propio perfil
        $this->perfil    = $userId ? User::with('perros')->findOrFail($userId) : $yo->load('perros');
        $this->esMiPerfil = $this->perfil->id === $yo->id;

        // Relación con el perfil visitado
        if (!$this->esMiPerfil) {
            $like = Like::where('de_user_id', $yo->id)
                        ->where('a_user_id', $this->perfil->id)
                        ->first();
            $this->yaLike  = (bool) $like;
            $this->esMatch = $like?->match_at !== null;

            // Conversación existente si hay match
            if ($this->esMatch) {
                $conv = $yo->conversaciones()
                    ->whereHas('participantes', fn ($q) => $q->where('users.id', $this->perfil->id))
                    ->first();
                $this->conversacionId = $conv?->id;
            }

            // Compatibilidad entre perros
            $miPerro    = $yo->perros()->first();
            $suPerro    = $this->perfil->perros()->first();
            if ($miPerro && $suPerro) {
                $this->compatibilidad = $miPerro->compatibilidadCon($suPerro);
            }
        }

        // Stats del perfil visitado
        $this->totalMatches   = Like::where('de_user_id', $this->perfil->id)
                                    ->whereNotNull('match_at')->count();
        $this->likesRecibidos = Like::where('a_user_id', $this->perfil->id)->count();
        $this->diasEnPawMatch = (int) $this->perfil->created_at->diffInDays(now());
    }

    public function darLike(): void
    {
        if ($this->esMiPerfil || $this->yaLike) return;

        $yo      = Auth::user();
        $miPerro = $yo->perros()->first();
        $suPerro = $this->perfil->perros()->first();

        $esMatch = Like::darLike(
            $yo->id,
            $this->perfil->id,
            $miPerro?->id,
            $suPerro?->id,
        );

        $this->yaLike  = true;
        $this->esMatch = $esMatch;

        if ($esMatch) {
            session()->flash('success', '🎉 ¡Es un match con '.$this->perfil->name.'!');
            // Buscar conversación recién creada
            $conv = $yo->conversaciones()
                ->whereHas('participantes', fn ($q) => $q->where('users.id', $this->perfil->id))
                ->first();
            $this->conversacionId = $conv?->id;
        }
    }

    public function render()
    {
        $perro = $this->perfil->perros()->first();

        return view('livewire.ver-perfil', [
            'perro'  => $perro,
            'galeria' => $perro?->fotos ?? [],
        ]);
    }
}
