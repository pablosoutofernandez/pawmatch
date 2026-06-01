<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Perro extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'perros';

    protected $fillable = [
        'user_id',
        'nombre',
        'raza',
        'edad_anios',
        'edad_meses',
        'peso_kg',
        'sexo',                  // macho | hembra
        'esterilizado',
        'energia',               // 1-5
        'caracter',              // JSON array: ['juguetón','tranquilo',...]
        'foto_principal',
        'fotos',                 // JSON array de URLs
        'vacunado',
        'notas',
        'descripcion',
    ];

    protected $casts = [
        'esterilizado'         => 'boolean',
        'vacunado'             => 'boolean',
        'caracter'             => 'array',
        'fotos'                => 'array',
        'peso_kg'              => 'decimal:2',
    ];

    // ────────────────────────────────────────────────────────
    // Relaciones
    // ────────────────────────────────────────────────────────

    public function dueno(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // ────────────────────────────────────────────────────────
    // Scopes (estilo Por*)
    // ────────────────────────────────────────────────────────

    public function scopePorNombre(Builder $query, ?string $valor): Builder
    {
        return $valor
            ? $query->where('nombre', 'like', '%'.$valor.'%')
            : $query;
    }

    public function scopePorRaza(Builder $query, ?string $valor): Builder
    {
        return $valor
            ? $query->where('raza', 'like', '%'.$valor.'%')
            : $query;
    }

    public function scopePorTamano(Builder $query, ?string $valor): Builder
    {
        if (!$valor || $valor === 'todos') return $query;

        return match ($valor) {
            'pequeno' => $query->where('peso_kg', '<', 10),
            'mediano' => $query->whereBetween('peso_kg', [10, 25]),
            'grande'  => $query->where('peso_kg', '>=', 25),
            default   => $query,
        };
    }

    public function scopePorEnergia(Builder $query, ?int $minimo): Builder
    {
        return $minimo
            ? $query->where('energia', '>=', $minimo)
            : $query;
    }

    public function scopeExcluyendoUsuario(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', '!=', $userId);
    }

    // ────────────────────────────────────────────────────────
    // Accessors
    // ────────────────────────────────────────────────────────

    public function getTamanoAttribute(): string
    {
        return match (true) {
            $this->peso_kg < 10 => 'Pequeño',
            $this->peso_kg < 25 => 'Mediano',
            default             => 'Grande',
        };
    }

    public function getEnergiaTextoAttribute(): string
    {
        return match ((int) $this->energia) {
            1 => 'Muy baja',
            2 => 'Baja',
            3 => 'Media',
            4 => 'Alta',
            5 => 'Muy alta',
            default => 'Media',
        };
    }

    /**
     * Devuelve la URL pública correcta de una ruta de storage.
     */
    protected function resolverUrl(?string $url): ?string
    {
        if (!$url) return null;
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://') || str_starts_with($url, 'data:')) {
            return $url;
        }
        // Normalizamos: quitamos el prefijo /storage/ si viene así (formato heredado)
        // o la barra inicial, y servimos siempre por /img/{ruta} (sin depender del
        // symlink public/storage).
        $rel = ltrim($url, '/');
        if (str_starts_with($rel, 'storage/')) {
            $rel = substr($rel, strlen('storage/'));
        }
        return url('img/' . $rel);
    }

    public function getFotoPrincipalUrlAttribute(): ?string
    {
        return $this->resolverUrl($this->foto_principal);
    }

    public function getFotoUrlAttribute(): string
    {
        if ($this->foto_principal) {
            return $this->resolverUrl($this->foto_principal);
        }

        // Sin foto subida: placeholder local determinístico (funciona sin
        // conexión, p. ej. en la máquina virtual de entrega). Cada perro tiene
        // siempre el mismo, repartidos entre 6 variantes de color.
        $n = (($this->id ?? 0) % 6) + 1;

        return url('img/perros/ph-'.$n.'.svg');
    }

    public function getEdadTextoAttribute(): string
    {
        $anios = $this->edad_anios ?? 0;
        $meses = $this->edad_meses ?? 0;
        if ($anios > 0 && $meses > 0) {
            return $anios . ' ' . ($anios === 1 ? 'año' : 'años') . ' y ' . $meses . ' ' . ($meses === 1 ? 'mes' : 'meses');
        }
        if ($anios > 0) {
            return $anios . ' ' . ($anios === 1 ? 'año' : 'años');
        }
        return ($meses) . ' meses';
    }

    // ────────────────────────────────────────────────────────
    // Compatibilidad
    // ────────────────────────────────────────────────────────

    /**
     * Calcula la puntuación de compatibilidad (0-100) con otro perro.
     */
    public function compatibilidadCon(Perro $otro): int
    {
        $score = 0;

        // 1) Energía similar (35 pts máximo)
        $diff = abs($this->energia - $otro->energia);
        $score += max(0, 35 - ($diff * 9));

        // 2) Tamaño relativo (35 pts)
        if ($this->peso_kg > 0 && $otro->peso_kg > 0) {
            $ratio = min($this->peso_kg, $otro->peso_kg) / max($this->peso_kg, $otro->peso_kg);
            $score += (int) round((float) $ratio * 35);
        } else {
            $score += 12;
        }

        // 3) Ambos esterilizados (15 pts)
        if ($this->esterilizado && $otro->esterilizado)         $score += 15;
        elseif ($this->esterilizado || $otro->esterilizado)     $score += 7;

        // 4) Ambos vacunados (15 pts)
        if ($this->vacunado && $otro->vacunado) $score += 15;

        return min(100, $score);
    }
}
