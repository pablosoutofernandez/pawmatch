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
        'ubicacion_tiempo_real',
        'radio_busqueda_km',
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
            'latitud'               => 'decimal:7',
            'longitud'              => 'decimal:7',
            'ubicacion_tiempo_real' => 'boolean',
            'radio_busqueda_km'     => 'integer',
        ];
    }

    // ────────────────────────────────────────────────────────
    // Relaciones
    // ────────────────────────────────────────────────────────

    public function perros(): HasMany
    {
        return $this->hasMany(Perro::class);
    }

    /**
     * El perro "principal" del usuario: el primero que registró.
     * Se usa como perro presentador por defecto al dar like.
     */
    public function perroPrincipal(): ?Perro
    {
        return $this->perros()->orderBy('id')->first();
    }

    /**
     * Radio máximo de búsqueda según el plan:
     *  - Gratuito: 15 km
     *  - Premium: 50 km
     */
    public const RADIO_MAX_GRATIS = 5;
    public const RADIO_MAX_PREMIUM = 15;

    public function radioMaximo(): int
    {
        return $this->es_premium ? self::RADIO_MAX_PREMIUM : self::RADIO_MAX_GRATIS;
    }

    /**
     * Radio de búsqueda efectivo (1 km hasta el máximo de su plan).
     * Compartido entre Descubrir y Mapa.
     */
    public function radioBusqueda(): int
    {
        $r = (int) ($this->radio_busqueda_km ?? 10);
        return max(1, min($this->radioMaximo(), $r));
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

    /**
     * Coordenadas fuzzificadas (±~440 m) para mostrar en el mapa a terceros.
     *
     * El offset es determinístico por usuario y cambia cada día, de forma que:
     *  - el pin no salta en cada recarga de página,
     *  - pero no es estático para siempre (dificulta triangulación por observación).
     *
     * Nunca se exponen las coordenadas reales al frontend.
     *
     * @return array{lat: float, lng: float}
     */
    public function coordenadasFuzzificadas(): array
    {
        // Hashes diarios independientes para lat y lng
        $h1 = hexdec(substr(md5($this->id.'_lat_'.now()->format('Y-m-d').'_'.config('app.key')), 0, 8));
        $h2 = hexdec(substr(md5($this->id.'_lng_'.now()->format('Y-m-d').'_'.config('app.key')), 0, 8));

        // Normalizar a [-1, 1] y escalar a ±0.004° (≈ ±444 m en lat)
        $offsetLat = (($h1 % 10000) / 10000 * 2 - 1) * 0.004;
        $offsetLng = (($h2 % 10000) / 10000 * 2 - 1) * 0.004
            / max(cos(deg2rad((float) $this->latitud)), 0.01);

        return [
            'lat' => round((float) $this->latitud + $offsetLat, 6),
            'lng' => round((float) $this->longitud + $offsetLng, 6),
        ];
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
    // Freemium / límite de matches
    // ────────────────────────────────────────────────────────

    /** Nº de matches simultáneos permitidos en el plan gratuito. */
    public const LIMITE_MATCHES_GRATIS = 3;

    /**
     * Matches activos del usuario = número de conversaciones en las que
     * participa (cada match crea exactamente una conversación).
     */
    public function matchesActivos(): int
    {
        return $this->conversaciones()->count();
    }

    /**
     * ¿Puede el usuario cerrar un match nuevo?
     *  - Premium: matches ilimitados.
     *  - Gratuito: sólo si tiene menos de LIMITE_MATCHES_GRATIS activos.
     */
    public function puedeIniciarMatch(): bool
    {
        return $this->es_premium || $this->matchesActivos() < self::LIMITE_MATCHES_GRATIS;
    }

    /**
     * Matches que le quedan en el plan gratuito.
     * Devuelve null si es premium (ilimitados).
     */
    public function matchesRestantes(): ?int
    {
        if ($this->es_premium) {
            return null;
        }

        return max(0, self::LIMITE_MATCHES_GRATIS - $this->matchesActivos());
    }

    // ────────────────────────────────────────────────────────
    // Mensajes no leídos
    // ────────────────────────────────────────────────────────

    /**
     * Nº total de mensajes sin leer en todas mis conversaciones.
     * Un mensaje cuenta como no leído si lo envió el otro participante
     * después de mi last_read_at en esa conversación.
     */
    public function mensajesNoLeidos(): int
    {
        $total = 0;

        $convs = $this->conversaciones()->with('mensajes')->get();
        foreach ($convs as $conv) {
            $lastRead = $conv->pivot->last_read_at;
            $total += $conv->mensajes
                ->where('remitente_id', '!=', $this->id)
                ->when($lastRead, fn ($c) => $c->where('created_at', '>', $lastRead))
                ->count();
        }

        return $total;
    }

    /**
     * Conversaciones con al menos un mensaje sin leer, con los datos mínimos
     * para mostrarlas como notificación de "mensaje nuevo".
     *
     * @return \Illuminate\Support\Collection
     */
    public function conversacionesConMensajesNuevos(): \Illuminate\Support\Collection
    {
        return $this->conversaciones()
            ->with(['participantes', 'mensajes' => fn ($q) => $q->latest()])
            ->get()
            ->map(function (Conversacion $conv) {
                $lastRead = $conv->pivot->last_read_at;
                $noLeidos = $conv->mensajes
                    ->where('remitente_id', '!=', $this->id)
                    ->when($lastRead, fn ($c) => $c->where('created_at', '>', $lastRead))
                    ->count();

                if ($noLeidos === 0) {
                    return null;
                }

                $otro   = $conv->otroParticipante($this->id);
                $ultimo = $conv->mensajes
                    ->where('remitente_id', '!=', $this->id)
                    ->first();

                return (object) [
                    'conversacion_id' => $conv->id,
                    'otro'            => $otro,
                    'no_leidos'       => $noLeidos,
                    'ultimo'          => $ultimo?->cuerpo,
                    'hora'            => $ultimo?->created_at,
                ];
            })
            ->filter()
            ->sortByDesc('hora')
            ->values();
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
        // URL absoluta o data URI: devolver tal cual
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://') || str_starts_with($url, 'data:')) {
            return $url;
        }
        // Servir siempre por /img/{ruta} (sin depender del symlink public/storage).
        $rel = ltrim($url, '/');
        if (str_starts_with($rel, 'storage/')) {
            $rel = substr($rel, strlen('storage/'));
        }
        return url('img/' . $rel);
    }
}