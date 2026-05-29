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
    // Ubicación / distancia
    // ────────────────────────────────────────────────────────

    public function getTieneUbicacionAttribute(): bool
    {
        return $this->latitud !== null && $this->longitud !== null;
    }

    /**
     * Distancia en km (Haversine) entre este usuario y unas coordenadas.
     * Devuelve null si falta alguna coordenada.
     */
    public function distanciaKm(?float $lat, ?float $lng): ?float
    {
        if ($lat === null || $lng === null || $this->latitud === null || $this->longitud === null) {
            return null;
        }

        $r = 6371; // radio Tierra km
        $dLat = deg2rad($lat - (float) $this->latitud);
        $dLng = deg2rad($lng - (float) $this->longitud);

        $a = sin($dLat / 2) ** 2
           + cos(deg2rad((float) $this->latitud)) * cos(deg2rad($lat))
           * sin($dLng / 2) ** 2;

        return round($r * 2 * atan2(sqrt($a), sqrt(1 - $a)), 1);
    }

    // ────────────────────────────────────────────────────────
    // Likes / notificaciones
    // ────────────────────────────────────────────────────────

    /**
     * Likes recibidos que aún no son match (el usuario no ha dado like de vuelta).
     * Son las "notificaciones": «X le ha dado like a tu perfil».
     */
    public function likesPendientes(): HasMany
    {
        // Un like recibido con match_at null significa que aún no he dado like
        // de vuelta (si lo hubiera, darLike habría marcado el match en ambos).
        return $this->likesRecibidos()->whereNull('match_at')->latest();
    }

    public function getNotificacionesCountAttribute(): int
    {
        return $this->likesPendientes()->count();
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
        $url = $this->avatar_url ?: $this->avatar ?: null;
        if (!$url) return null;
        // Si ya es una URL absoluta, la devolvemos tal cual
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }
        // Si empieza por /storage/ usamos asset() para que funcione en cualquier entorno
        if (str_starts_with($url, '/storage/')) {
            return asset(ltrim($url, '/'));
        }
        return asset('storage/' . $url);
    }
}
