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
     * Crea un like y comprueba si genera match bidireccional.
     * Devuelve true si hay match nuevo.
     */
    public static function darLike(int $deUserId, int $aUserId, ?int $dePerroId = null, ?int $aPerroId = null): bool
    {
        // Crear el like si no existía
        $like = static::firstOrCreate(
            ['de_user_id' => $deUserId, 'a_user_id' => $aUserId],
            ['de_perro_id' => $dePerroId, 'a_perro_id' => $aPerroId]
        );

        // ¿Existe el like recíproco?
        $reciproco = static::where('de_user_id', $aUserId)
                           ->where('a_user_id', $deUserId)
                           ->first();

        if ($reciproco && !$like->match_at) {
            $now = now();
            $like->update(['match_at' => $now]);
            $reciproco->update(['match_at' => $now]);

            // Crear conversación solo si no existe ya una entre ambos
            $yaExiste = Conversacion::whereHas('participantes', fn ($q) => $q->where('users.id', $deUserId))
                ->whereHas('participantes', fn ($q) => $q->where('users.id', $aUserId))
                ->exists();

            if (!$yaExiste) {
                $conv = Conversacion::create(['match_at' => $now]);
                $conv->participantes()->attach([$deUserId, $aUserId]);
            }

            return true;
        }

        return false;
    }
}
