<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
}
