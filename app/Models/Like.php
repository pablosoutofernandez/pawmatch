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

    // Si yo le doy like ahora, ¿se cierra match? (¿el otro ya me lo dio?)
    public static function seriaMatch(int $deUserId, int $aUserId): bool
    {
        if ($deUserId === $aUserId) {
            return false;
        }

        return static::where('de_user_id', $aUserId)
                     ->where('a_user_id', $deUserId)
                     ->exists();
    }

    // Da like a un perro concreto. Si hay reciprocidad cierra el match a nivel
    // de usuario (cubre todos los perros del otro) y crea la conversación.
    // Devuelve true si este like ha cerrado un match nuevo.
    public static function darLike(int $deUserId, int $aUserId, ?int $dePerroId = null, ?int $aPerroId = null): bool
    {
        if ($deUserId === $aUserId) {
            return false;
        }

        $like = static::firstOrCreate(
            ['de_user_id' => $deUserId, 'a_perro_id' => $aPerroId],
            ['a_user_id' => $aUserId, 'de_perro_id' => $dePerroId]
        );

        // Por si el like ya existía con campos a medio rellenar.
        $cambios = [];
        if ($like->a_user_id !== $aUserId)        $cambios['a_user_id']  = $aUserId;
        if ($dePerroId && !$like->de_perro_id)    $cambios['de_perro_id'] = $dePerroId;
        if ($cambios) $like->update($cambios);

        $reciproco = static::where('de_user_id', $aUserId)
                           ->where('a_user_id', $deUserId)
                           ->first();

        $hayMatchNuevo = false;

        if ($reciproco) {
            $now = now();

            if (!$like->match_at) {
                $like->update(['match_at' => $now]);
                $hayMatchNuevo = true;
            }

            // Marcar como match todos los likes pendientes entre ambos.
            static::where('de_user_id', $aUserId)
                  ->where('a_user_id', $deUserId)
                  ->whereNull('match_at')
                  ->update(['match_at' => $now]);

            // El match es a nivel de usuario: rellenamos con likes automáticos
            // los perros del otro que faltaban (y al revés).
            self::completarLikesPara($deUserId, $aUserId, $now);
            self::completarLikesPara($aUserId, $deUserId, $now);

            $yaExiste = Conversacion::whereHas('participantes', fn ($q) => $q->where('users.id', $deUserId))
                ->whereHas('participantes', fn ($q) => $q->where('users.id', $aUserId))
                ->exists();

            if (!$yaExiste) {
                $conv = Conversacion::create(['match_at' => $now]);
                $conv->participantes()->attach([$deUserId, $aUserId]);
            }

            // Si alguno acaba de llenar los 3 matches del plan gratuito,
            // limpiamos sus likes pendientes (ver User::limpiarLikesSiLlenoDeMatches).
            User::find($deUserId)?->limpiarLikesSiLlenoDeMatches();
            User::find($aUserId)?->limpiarLikesSiLlenoDeMatches();
        }

        return $hayMatchNuevo;
    }

    // Crea los likes que faltan de $deUserId a los perros de $aUserId.
    private static function completarLikesPara(int $deUserId, int $aUserId, \Carbon\Carbon $now): void
    {
        $perros = Perro::where('user_id', $aUserId)->pluck('id')->all();
        if (empty($perros)) return;

        $miPerroPresentador = Perro::where('user_id', $deUserId)->orderBy('id')->value('id');

        $yaLikeados = static::where('de_user_id', $deUserId)
            ->whereIn('a_perro_id', $perros)
            ->pluck('a_perro_id')
            ->all();

        $faltan = array_diff($perros, $yaLikeados);
        foreach ($faltan as $aPerroId) {
            static::create([
                'de_user_id'  => $deUserId,
                'a_user_id'   => $aUserId,
                'de_perro_id' => $miPerroPresentador,
                'a_perro_id'  => $aPerroId,
                'match_at'    => $now,
            ]);
        }
    }
}
