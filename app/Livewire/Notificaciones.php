<?php

namespace App\Livewire;

use App\Models\Like;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Notificaciones extends Component
{
    /**
     * Dar like de vuelta a quien me dio like → genera match + conversación.
     */
    public function corresponder(int $likeId): void
    {
        $usuario = Auth::user();

        if ($usuario->cannot('crear')) {
            abort(403, 'No tienes permiso para dar like');
        }

        $like = Like::where('id', $likeId)
            ->where('a_user_id', $usuario->id)   // seguridad: el like es para mí
            ->first();

        if (!$like) {
            return;
        }

        $miPerro = $usuario->perros()->first();

        $esMatch = Like::darLike(
            $usuario->id,
            $like->de_user_id,
            $miPerro?->id,
            $like->de_perro_id,
        );

        if ($esMatch) {
            session()->flash('match', '🎉 ¡Es un match con '.$like->deUsuario->name.'! Ya podéis chatear.');
        } else {
            session()->flash('match', 'Le diste like a '.$like->deUsuario->name);
        }
    }

    /** Rechazar / ignorar un like recibido. */
    public function ignorar(int $likeId): void
    {
        Like::where('id', $likeId)
            ->where('a_user_id', Auth::id())
            ->whereNull('match_at')
            ->delete();
    }

    public function render()
    {
        $usuario = Auth::user();

        // Notificaciones = likes recibidos pendientes (aún no correspondidos)
        $pendientes = $usuario->likesPendientes()
            ->with(['deUsuario', 'dePerro', 'aPerro'])
            ->get();

        // Matches recientes (para contexto)
        $matches = $usuario->likesRecibidos()
            ->whereNotNull('match_at')
            ->with(['deUsuario'])
            ->latest('match_at')
            ->limit(10)
            ->get();

        return view('livewire.notificaciones', [
            'pendientes' => $pendientes,
            'matches'    => $matches,
        ]);
    }
}
