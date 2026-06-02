<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class Conversacion extends Model
{
    use HasFactory;

    // Horas sin mensajes tras las que el usuario puede eliminar el match.
    // Los matches no caducan solos.
    public const HORAS_INACTIVIDAD = 48;

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

    public function otroParticipante(int $userId): ?User
    {
        return $this->participantes->firstWhere('id', '!=', $userId);
    }

    // Perros del otro participante con los que $userId ha hecho match.
    // Pueden ser varios (multi-perro del mismo dueño).
    public function perrosMatcheados(int $userId): Collection
    {
        $otro = $this->otroParticipante($userId);
        if (!$otro) {
            return collect();
        }

        $ids = Like::query()
            ->where('de_user_id', $userId)
            ->where('a_user_id', $otro->id)
            ->whereNotNull('match_at')
            ->whereNotNull('a_perro_id')
            ->pluck('a_perro_id')
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            // Datos antiguos sin a_perro_id: caemos a todos los perros del otro.
            return $otro->perros()->orderBy('id')->get();
        }

        return Perro::whereIn('id', $ids)->orderBy('id')->get();
    }

    // Inactividad y borrado del match

    // Última actividad = último mensaje o, si no hay, el momento del match.
    public function getUltimaActividadAttribute(): Carbon
    {
        $ultimo = $this->ultimoMensaje();
        return $ultimo?->created_at ?? $this->match_at ?? $this->created_at ?? now();
    }

    public function estaInactiva(): bool
    {
        return $this->ultima_actividad->lte(now()->subHours(self::HORAS_INACTIVIDAD));
    }

    public function puedeEliminarse(): bool
    {
        return $this->estaInactiva();
    }

    public function horasParaPoderEliminar(): int
    {
        $disponible = $this->ultima_actividad->copy()->addHours(self::HORAS_INACTIVIDAD);
        if ($disponible->isPast()) {
            return 0;
        }
        return max(0, (int) ceil(now()->diffInHours($disponible, false)));
    }

    // Borra el match: conversación + likes recíprocos. Libera el slot.
    // La comprobación de inactividad se hace fuera, antes de llamar.
    public function eliminar(): void
    {
        $ids = $this->participantes()->pluck('users.id')->all();

        if (count($ids) === 2) {
            Like::whereIn('de_user_id', $ids)
                ->whereIn('a_user_id', $ids)
                ->delete();
        }

        $this->delete(); // mensajes y pivote por cascada
    }
}
