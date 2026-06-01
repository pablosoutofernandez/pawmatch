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
     * ¿Una llamada a darLike() entre estos dos usuarios cerraría un match?
     * Es decir: ¿el usuario destino ya me ha dado like a alguno de mis perros?
     *
     * Se usa para aplicar el límite de matches del plan gratuito ANTES de
     * crear el match (ver User::puedeIniciarMatch()).
     */
    public static function seriaMatch(int $deUserId, int $aUserId): bool
    {
        if ($deUserId === $aUserId) {
            return false;
        }

        return static::where('de_user_id', $aUserId)
                     ->where('a_user_id', $deUserId)
                     ->exists();
    }

    /**
     * Crea un like de un usuario hacia un PERRO concreto (a_perro_id) y, si hay
     * reciprocidad entre los dos usuarios, genera el match con el USUARIO
     * (no solo con ese perro): crea likes con match en todos los perros del otro
     * y la conversación.
     *
     * Modelo: el match es a nivel de USUARIO. Cuando hay match, los dos
     * usuarios "matchean" con todos los perros del otro, y el chat los muestra
     * todos. Por eso, una vez hay match no se puede dar like a perros del
     * otro usuario por separado: ya están todos matcheados.
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

            // Marcar como match TODOS los likes ya existentes entre ambos
            static::where('de_user_id', $aUserId)
                  ->where('a_user_id', $deUserId)
                  ->whereNull('match_at')
                  ->update(['match_at' => $now]);

            // ── Match a nivel de USUARIO: rellenar con likes "automáticos" los
            //    perros del otro usuario que yo aún no había marcado, y viceversa.
            //    Así el match cubre TODOS los perros de ambos lados.
            self::completarLikesPara($deUserId, $aUserId, $now);
            self::completarLikesPara($aUserId, $deUserId, $now);

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

    /**
     * Crea (o actualiza con match_at) los likes "automáticos" de $deUserId
     * hacia los perros de $aUserId que aún no tuvieran like de $deUserId.
     */
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
