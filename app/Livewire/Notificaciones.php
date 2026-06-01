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
    /**
     * Dar like de vuelta a quien me dio like → genera match + conversación.
     * Solo disponible para usuarios premium (los gratuitos no ven quién les
     * ha dado like; ver render()).
     */
    public function corresponder(int $likeId): void
    {
        $usuario = Auth::user();

        // Ver quién te ha dado like y corresponder es una ventaja premium.
        if (!$usuario->es_premium) {
            session()->flash('premium', 'Ver quién te ha dado like y corresponder al instante es una ventaja Premium.');
            return;
        }

        if ($usuario->cannot('crear')) {
            abort(403, 'No tienes permiso para dar like');
        }

        $like = Like::where('id', $likeId)
            ->where('a_user_id', $usuario->id)   // seguridad: el like es para mí
            ->first();

        if (!$like) {
            return;
        }

        // Límite del plan gratuito (por coherencia; premium no lo alcanza).
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
            session()->flash('match', '🎉 ¡Es un match con '.$like->deUsuario->name.'! Ya podéis chatear.');
        } else {
            session()->flash('match', 'Le diste like a '.$like->deUsuario->name);
        }
    }

    /** Rechazar / ignorar un like recibido (solo premium ve los likes). */
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

        // Mensajes nuevos (no leídos): se muestran a TODOS los usuarios.
        $mensajesNuevos = $usuario->conversacionesConMensajesNuevos();

        // Likes recibidos pendientes: solo se detallan a usuarios premium.
        // Para los gratuitos solo se cuenta cuántos hay (gancho de conversión).
        $pendientes = $esPremium
            ? $usuario->likesPendientes()->with(['deUsuario', 'dePerro', 'aPerro'])->get()
            : collect();

        $numLikesOcultos = $esPremium ? 0 : $usuario->likesPendientes()->count();

        // "Tus matches" = los PERROS con los que has hecho match (per-perro).
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
