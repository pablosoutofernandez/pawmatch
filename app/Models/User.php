<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
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
        'mapa_visible',
        'radio_busqueda_km',
        'avatar',
        'plan',
        'plan_expira_at',
        'walk_now_until',
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
            'mapa_visible'          => 'boolean',
            'radio_busqueda_km'     => 'integer',
        ];
    }

    // Relaciones

    public function perros(): HasMany
    {
        return $this->hasMany(Perro::class);
    }

    // Primer perro del usuario (el "presentador" al dar like).
    public function perroPrincipal(): ?Perro
    {
        return $this->perros()->orderBy('id')->first();
    }

    public const RADIO_MAX_GRATIS = 5;
    public const RADIO_MAX_PREMIUM = 15;

    public function radioMaximo(): int
    {
        return $this->es_premium ? self::RADIO_MAX_PREMIUM : self::RADIO_MAX_GRATIS;
    }

    // Radio actual del usuario, recortado al máximo de su plan.
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

    // Ubicación / distancia

    public function getTieneUbicacionAttribute(): bool
    {
        return $this->latitud !== null && $this->longitud !== null;
    }

    // Distancia Haversine en km. Null si falta alguna coord.
    public function distanciaKm(?float $lat, ?float $lng): ?float
    {
        if ($lat === null || $lng === null || $this->latitud === null || $this->longitud === null) {
            return null;
        }

        $r = 6371;
        $dLat = deg2rad($lat - (float) $this->latitud);
        $dLng = deg2rad($lng - (float) $this->longitud);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad((float) $this->latitud)) * cos(deg2rad($lat))
            * sin($dLng / 2) ** 2;

        return round($r * 2 * atan2(sqrt($a), sqrt(1 - $a)), 1);
    }

    // Coordenadas con ruido (±~440 m) para que en el mapa nadie vea tu casa exacta.
    // El offset es estable por día — el pin no salta al recargar.
    public function coordenadasFuzzificadas(): array
    {
        $h1 = hexdec(substr(md5($this->id.'_lat_'.now()->format('Y-m-d').'_'.config('app.key')), 0, 8));
        $h2 = hexdec(substr(md5($this->id.'_lng_'.now()->format('Y-m-d').'_'.config('app.key')), 0, 8));

        $offsetLat = (($h1 % 10000) / 10000 * 2 - 1) * 0.004;
        $offsetLng = (($h2 % 10000) / 10000 * 2 - 1) * 0.004
            / max(cos(deg2rad((float) $this->latitud)), 0.01);

        return [
            'lat' => round((float) $this->latitud + $offsetLat, 6),
            'lng' => round((float) $this->longitud + $offsetLng, 6),
        ];
    }

    // Likes y notificaciones

    // Likes recibidos que aún no han generado match. Son las notificaciones.
    public function likesPendientes(): HasMany
    {
        // Un like recibido con match_at null significa que aún no he dado like
        // Si hubiera reciprocidad ya sería match, no aparecería aquí.
        return $this->likesRecibidos()->whereNull('match_at')->latest();
    }

    public function retirarLikesPendientes(): void
    {
        $this->likesEnviados()
            ->whereNull('match_at')
            ->delete();
    }

    // Si un usuario gratuito llega a su tope de matches, le borramos los likes
    // sin correspondencia. Así esos perros vuelven a aparecerle en Descubrir
    // y podrá likearlos otra vez si en algún momento se libera un hueco.
    public function limpiarLikesSiLlenoDeMatches(): void
    {
        if (!$this->es_premium && $this->matchesActivos() >= self::LIMITE_MATCHES_GRATIS) {
            $this->retirarLikesPendientes();
        }
    }

    public function likesPendientesCount(): int
    {
        return $this->likesEnviados()->whereNull('match_at')->count();
    }

    public function getNotificacionesCountAttribute(): int
    {
        return $this->likesPendientes()->count();
    }

    // Plan gratuito / límite de matches

    public const LIMITE_MATCHES_GRATIS = 3;

    // Cada match crea una conversación, así que contamos por ahí.
    public function matchesActivos(): int
    {
        return $this->conversaciones()->count();
    }

    public function puedeIniciarMatch(): bool
    {
        return $this->es_premium || $this->matchesActivos() < self::LIMITE_MATCHES_GRATIS;
    }

    // Null si es premium (ilimitados).
    public function matchesRestantes(): ?int
    {
        if ($this->es_premium) {
            return null;
        }

        return max(0, self::LIMITE_MATCHES_GRATIS - $this->matchesActivos());
    }

    // Mensajes no leídos

    // Un mensaje cuenta como no leído si lo envió el otro participante
    // después de mi last_read_at en esa conversación.
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

    // Accessors

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
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://') || str_starts_with($url, 'data:')) {
            return $url;
        }
        // Pasamos por /img/{ruta} para no depender de storage:link.
        $rel = ltrim($url, '/');
        if (str_starts_with($rel, 'storage/')) {
            $rel = substr($rel, strlen('storage/'));
        }
        return url('img/' . $rel);
    }
}