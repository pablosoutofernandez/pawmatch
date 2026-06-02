<?php

namespace App\Livewire;

use App\Models\Like;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Notificaciones extends Component
{
    // Dar like de vuelta a quien me dio like → match + conversación.
    // Solo para premium (los free no ven quién les dio like).
    public function corresponder(int $likeId): void
    {
        $usuario = Auth::user();

        if (!$usuario->es_premium) {
            session()->flash('premium', 'Ver quién te ha dado like y corresponder al instante es una ventaja Premium.');
            return;
        }

        if ($usuario->cannot('crear')) {
            abort(403, 'No tienes permiso para dar like');
        }

        $like = Like::where('id', $likeId)
            ->where('a_user_id', $usuario->id)   // que el like sea realmente para mí
            ->first();

        if (!$like) {
            return;
        }

        // Por coherencia: un premium nunca debería estar al tope, pero por si acaso.
        if (!$usuario->puedeIniciarMatch()) {
            session()->flash('premium', 'Has alcanzado el límite de '.User::LIMITE_MATCHES_GRATIS.' matches del plan gratuito. Hazte Premium para conseguir matches ilimitados.');
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
            $this->dispatch('match-cerrado', userId: $like->de_user_id);
        } else {
            session()->flash('match', 'Le diste like a '.$like->deUsuario->name);
        }
    }

    // Ignorar un like recibido (solo premium ve los likes).
    public function ignorar(int $likeId): void
    {
        if (!Auth::user()->es_premium) {
            return;
        }

        Like::where('id', $likeId)
            ->where('a_user_id', Auth::id())
            ->whereNull('match_at')
            ->delete();
    }

    public function render()
    {
        $usuario = Auth::user();
        $esPremium = $usuario->es_premium;

        // Mensajes no leídos: para todos.
        $mensajesNuevos = $usuario->conversacionesConMensajesNuevos();

        // Likes pendientes: solo se muestran a premium. Los free solo ven el contador
        // (gancho de conversión).
        $pendientes = $esPremium
            ? $usuario->likesPendientes()->with(['deUsuario', 'dePerro', 'aPerro'])->get()
            : collect();

        $numLikesOcultos = $esPremium ? 0 : $usuario->likesPendientes()->count();

        // Tus matches: perros con los que has hecho match (por perro, no por usuario).
        $matches = Like::where('de_user_id', $usuario->id)
            ->whereNotNull('match_at')
            ->whereNotNull('a_perro_id')
            ->with(['aPerro.dueno', 'aUsuario'])
            ->latest('match_at')
            ->limit(20)
            ->get()
            ->unique('a_perro_id')
            ->values();

        return view('livewire.notificaciones', [
            'esPremium'       => $esPremium,
            'mensajesNuevos'  => $mensajesNuevos,
            'pendientes'      => $pendientes,
            'numLikesOcultos' => $numLikesOcultos,
            'matches'         => $matches,
        ]);
    }
}
