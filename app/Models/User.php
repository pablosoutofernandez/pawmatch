<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'bio',
        'ciudad',
        'latitud',
        'longitud',
        'avatar',
        'plan',
        'plan_expira_at',
        'walk_now_until',
        'puntos',
        'avatar_url',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'plan_expira_at'    => 'datetime',
            'walk_now_until'    => 'datetime',
            'latitud'           => 'decimal:7',
            'longitud'          => 'decimal:7',
        ];
    }

    // ────────────────────────────────────────────────────────
    // Relaciones
    // ────────────────────────────────────────────────────────

    public function perros(): HasMany
    {
        return $this->hasMany(Perro::class);
    }

    public function likesEnviados(): HasMany
    {
        return $this->hasMany(Like::class, 'de_user_id');
    }

    public function likesRecibidos(): HasMany
    {
        return $this->hasMany(Like::class, 'a_user_id');
    }

    public function conversaciones(): BelongsToMany
    {
        return $this->belongsToMany(Conversacion::class, 'conversacion_user')
                    ->withPivot('last_read_at')
                    ->withTimestamps();
    }

    // ────────────────────────────────────────────────────────
    // Accessors
    // ────────────────────────────────────────────────────────

    public function getEsPremiumAttribute(): bool
    {
        return $this->plan === 'premium' && $this->plan_expira_at?->isFuture();
    }

    public function getPaseandoAhoraAttribute(): bool
    {
        return $this->walk_now_until?->isFuture() ?? false;
    }

    public function getIniciales(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->map(fn (string $name) => Str::of($name)->substr(0, 1))
            ->implode('');
    }

    public function getAvatarPhotoAttribute(): ?string
    {
        return $this->avatar_url ?: $this->avatar ?: null;
    }
}
