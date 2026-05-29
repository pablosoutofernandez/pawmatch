<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Like extends Model
{
    use HasFactory;

    protected $fillable = [
        'de_user_id',
        'a_user_id',
        'de_perro_id',
        'a_perro_id',
        'match_at',
    ];

    protected $casts = [
        'match_at' => 'datetime',
    ];

    public function deUsuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'de_user_id');
    }

    public function aUsuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'a_user_id');
    }

    public function dePerro(): BelongsTo
    {
        return $this->belongsTo(Perro::class, 'de_perro_id');
    }

    public function aPerro(): BelongsTo
    {
        return $this->belongsTo(Perro::class, 'a_perro_id');
    }

    /**
     * Crea un like de un usuario hacia un PERRO concreto (a_perro_id) y, si hay
     * reciprocidad entre los dos usuarios, genera el match y la conversación.
     *
     * El modelo es POR PERRO: un mismo usuario puede dar like a varios perros
     * del mismo dueño (cada uno es un like distinto). El chat sigue siendo por
     * usuario y agrupa todos los perros con los que se ha hecho match.
     *
     * Devuelve true si este like genera un match nuevo.
     */
    public static function darLike(int $deUserId, int $aUserId, ?int $dePerroId = null, ?int $aPerroId = null): bool
    {
        if ($deUserId === $aUserId) {
            return false; // no te das like a ti mismo
        }

        // Crear el like hacia ese perro si no existía (unicidad por de_user + a_perro)
        $like = static::firstOrCreate(
            ['de_user_id' => $deUserId, 'a_perro_id' => $aPerroId],
            ['a_user_id' => $aUserId, 'de_perro_id' => $dePerroId]
        );

        // Mantener coherentes a_user_id / de_perro_id por si el like ya existía
        $cambios = [];
        if ($like->a_user_id !== $aUserId)        $cambios['a_user_id']  = $aUserId;
        if ($dePerroId && !$like->de_perro_id)    $cambios['de_perro_id'] = $dePerroId;
        if ($cambios) $like->update($cambios);

        // ¿El otro usuario ya me ha dado like a alguno de mis perros?
        $reciproco = static::where('de_user_id', $aUserId)
                           ->where('a_user_id', $deUserId)
                           ->first();

        $hayMatchNuevo = false;

        if ($reciproco) {
            $now = now();

            // Marcar como match este like (si no lo estaba ya)
            if (!$like->match_at) {
                $like->update(['match_at' => $now]);
                $hayMatchNuevo = true;
            }

            // Marcar como match TODOS los likes recíprocos pendientes entre ambos
            static::where('de_user_id', $aUserId)
                  ->where('a_user_id', $deUserId)
                  ->whereNull('match_at')
                  ->update(['match_at' => $now]);

            // Crear la conversación entre ambos si no existe
            $yaExiste = Conversacion::whereHas('participantes', fn ($q) => $q->where('users.id', $deUserId))
                ->whereHas('participantes', fn ($q) => $q->where('users.id', $aUserId))
                ->exists();

            if (!$yaExiste) {
                $conv = Conversacion::create(['match_at' => $now]);
                $conv->participantes()->attach([$deUserId, $aUserId]);
            }
        }

        return $hayMatchNuevo;
    }
}
