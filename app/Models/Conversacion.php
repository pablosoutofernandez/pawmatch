<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;

class Conversacion extends Model
{
    use HasFactory;

    protected $table = 'conversaciones';

    protected $fillable = ['match_at'];

    protected $casts = [
        'match_at' => 'datetime',
    ];

    public function participantes(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'conversacion_user')
                    ->withPivot('last_read_at')
                    ->withTimestamps();
    }

    public function mensajes(): HasMany
    {
        return $this->hasMany(Mensaje::class)->orderBy('created_at');
    }

    public function ultimoMensaje(): HasOne
    {
        return $this->hasOne(Mensaje::class)->latestOfMany();
    }

    /**
     * Devuelve el otro participante (el que no es el user dado).
     */
    public function otroParticipante(int $userId): ?User
    {
        return $this->participantes->firstWhere('id', '!=', $userId);
    }

    /**
     * Perros del OTRO participante con los que $userId ha hecho match.
     *
     * Los matches son por perro: si has hecho match con varios perros del mismo
     * dueño, aquí aparecen todos para mostrarlos en la misma ventana de chat.
     *
     * @return \Illuminate\Support\Collection<int, \App\Models\Perro>
     */
    public function perrosMatcheados(int $userId): Collection
    {
        $otro = $this->otroParticipante($userId);
        if (!$otro) {
            return collect();
        }

        // Perros del otro usuario que son objetivo (a_perro_id) de un like mío con match
        $ids = Like::query()
            ->where('de_user_id', $userId)
            ->where('a_user_id', $otro->id)
            ->whereNotNull('match_at')
            ->whereNotNull('a_perro_id')
            ->pluck('a_perro_id')
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            // Respaldo: si por algún motivo no hay like direccional registrado,
            // mostramos los perros del otro usuario.
            return $otro->perros()->orderBy('id')->get();
        }

        return Perro::whereIn('id', $ids)->orderBy('id')->get();
    }
}
