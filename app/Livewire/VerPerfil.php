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
    public User  $perfil;               // Usuario cuyo perfil se ve
    public bool  $esMiPerfil = false;
    public bool  $esMatch    = false;   // ¿hay match (cualquier perro) con este usuario?
    public ?int  $conversacionId = null;

    // Modo "un solo perro": si está fijado, sólo se muestra ese perro.
    public ?int  $perroFocoId = null;

    // Stats calculadas
    public int $totalMatches    = 0;
    public int $likesRecibidos  = 0;
    public int $diasEnPawMatch  = 0;

    /**
     * Rutas posibles:
     *  - /mi-perfil            → mi propio perfil (todos mis perros)
     *  - /perfil/{userId}      → perfil de un usuario (todos sus perros)
     *  - /perro/{perroId}      → perfil de un perro concreto (oculta los demás)
     */
    public function mount(?int $userId = null, ?int $perroId = null): void
    {
        $yo = Auth::user();

        if ($perroId !== null) {
            // ── Modo PERRO ──────────────────────────────────────
            $perro = Perro::with('dueno')->findOrFail($perroId);
            $this->perroFocoId = $perro->id;
            $this->perfil      = $perro->dueno->load('perros');
        } else {
            // ── Modo USUARIO ────────────────────────────────────
            $this->perfil = $userId
                ? User::with('perros')->findOrFail($userId)
                : $yo->load('perros');
        }

        $this->esMiPerfil = $this->perfil->id === $yo->id;

        // Relación de match con el perfil visitado (a nivel de usuario)
        if (!$this->esMiPerfil) {
            $this->esMatch = Like::where('de_user_id', $yo->id)
                ->where('a_user_id', $this->perfil->id)
                ->whereNotNull('match_at')
                ->exists();

            if ($this->esMatch) {
                $conv = $yo->conversaciones()
                    ->whereHas('participantes', fn ($q) => $q->where('users.id', $this->perfil->id))
                    ->first();
                $this->conversacionId = $conv?->id;
            }
        }

        // Stats del perfil visitado
        $this->totalMatches   = Like::where('de_user_id', $this->perfil->id)
                                    ->whereNotNull('match_at')->count();
        $this->likesRecibidos = Like::where('a_user_id', $this->perfil->id)->count();
        $this->diasEnPawMatch = (int) $this->perfil->created_at->diffInDays(now());
    }

    /**
     * ¿He dado like (pendiente o con match) a un perro concreto?
     */
    public function yaLikePerro(int $perroId): bool
    {
        return Like::where('de_user_id', Auth::id())
            ->where('a_perro_id', $perroId)
            ->exists();
    }

    public function esMatchPerro(int $perroId): bool
    {
        return Like::where('de_user_id', Auth::id())
            ->where('a_perro_id', $perroId)
            ->whereNotNull('match_at')
            ->exists();
    }

    /**
     * Compatibilidad de mi perro principal con un perro concreto.
     */
    public function compatibilidadCon(Perro $otro): ?int
    {
        $miPerro = Auth::user()->perroPrincipal();
        return $miPerro ? $miPerro->compatibilidadCon($otro) : null;
    }

    /**
     * Dar like a un PERRO concreto del perfil visitado.
     */
    public function darLikePerro(int $perroId): void
    {
        if ($this->esMiPerfil) return;

        $yo = Auth::user();

        if ($yo->cannot('crear')) {
            abort(403, 'No tienes permiso para dar like');
        }

        $perro = Perro::findOrFail($perroId);
        if ($perro->user_id === $yo->id) return;

        // Si ya hay match con el dueño, todos sus perros están matcheados
        // automáticamente: no se puede dar like a más perros suyos.
        if ($this->esMatch) {
            return;
        }

        // Límite del plan gratuito: si este like cerraría un match y ya se ha
        // alcanzado el tope, se bloquea y se ofrece premium.
        if (Like::seriaMatch($yo->id, $perro->user_id) && !$yo->puedeIniciarMatch()) {
            session()->flash('premium', 'Has alcanzado el límite de '.User::LIMITE_MATCHES_GRATIS.' matches del plan gratuito. Hazte Premium para conseguir matches ilimitados.');
            return;
        }

        $miPerro = $yo->perroPrincipal();

        $esMatch = Like::darLike(
            $yo->id,
            $perro->user_id,
            $miPerro?->id,
            $perro->id,
        );

        if ($esMatch) {
            $this->esMatch = true;
            $conv = $yo->conversaciones()
                ->whereHas('participantes', fn ($q) => $q->where('users.id', $this->perfil->id))
                ->first();
            $this->conversacionId = $conv?->id;
            // Dispara la pantalla de match
            $this->dispatch('match-cerrado', userId: $this->perfil->id);
        } else {
            session()->flash('success', '♥ Le diste like a '.$perro->nombre.'. Se lo notificaremos a su dueño.');
        }
    }

    /**
     * Activa/desactiva la ubicación en tiempo real desde "Mi perfil".
     * Solo el dueño del perfil puede hacerlo.
     */
    public function toggleUbicacionTiempoReal(): void
    {
        if (!$this->esMiPerfil) {
            return;
        }

        $yo = Auth::user();

        // No se puede activar el tiempo real sin una ubicación de partida.
        if (!$yo->ubicacion_tiempo_real && !$yo->tiene_ubicacion) {
            session()->flash('info', 'Primero fija tu ubicación para poder activar el tiempo real.');
            return;
        }

        $nuevo = !$yo->ubicacion_tiempo_real;
        $yo->update(['ubicacion_tiempo_real' => $nuevo]);
        $this->perfil->refresh();

        session()->flash('success', $nuevo
            ? '📍 Ubicación en tiempo real activada.'
            : 'Ubicación en tiempo real desactivada.');
    }

    public function render()
    {
        // Perros a mostrar: uno solo (modo perro) o todos (modo usuario)
        if ($this->perroFocoId) {
            $perros = Perro::where('id', $this->perroFocoId)->get();
        } else {
            $perros = $this->perfil->perros()->orderBy('id')->get();
        }

        // Perro destacado (héroe) = el primero de la lista
        $perro = $perros->first();

        // Compatibilidad del perro destacado con mi perro principal
        $compatibilidad = (!$this->esMiPerfil && $perro)
            ? $this->compatibilidadCon($perro)
            : null;

        return view('livewire.ver-perfil', [
            'perros'         => $perros,
            'perro'          => $perro,
            'galeria'        => $perro?->fotos ?? [],
            'modoPerro'      => $this->perroFocoId !== null,
            'compatibilidad' => $compatibilidad,
        ]);
    }
}
