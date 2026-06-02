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

    // Relaciones

    public function dueno(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Scopes

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

    // Accessors

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

    // Devuelve la URL pública de una imagen (subida o externa).
    protected function resolverUrl(?string $url): ?string
    {
        if (!$url) return null;
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://') || str_starts_with($url, 'data:')) {
            return $url;
        }
        // Quitamos el prefijo /storage/ heredado y servimos por /img/{ruta}
        // para no depender del symlink public/storage.
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

    // Fallback de color por si fallan las otras imágenes (6 variantes).
    public function getPlaceholderUrlAttribute(): string
    {
        $n = (($this->id ?? 0) % 6) + 1;
        return url('img/perros/ph-'.$n.'.svg');
    }

    // Silueta "perro misterioso" para perros sin foto.
    public function getMysteryUrlAttribute(): string
    {
        return url('img/perros/ph-misterioso.svg');
    }

    // Foto a mostrar: la subida si la hay, si no el "misterioso".
    // (Los perros de demo guardan en BD una URL de placedog, así que pasan por la primera rama.)
    public function getFotoUrlAttribute(): string
    {
        return $this->foto_principal
            ? $this->resolverUrl($this->foto_principal)
            : $this->mystery_url;
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

    // Puntuación 0-100 entre dos perros.
    public function compatibilidadCon(Perro $otro): int
    {
        $score = 0;

        // Energía parecida (hasta 35)
        $diff = abs($this->energia - $otro->energia);
        $score += max(0, 35 - ($diff * 9));

        // Tamaño parecido (hasta 35)
        if ($this->peso_kg > 0 && $otro->peso_kg > 0) {
            $ratio = min($this->peso_kg, $otro->peso_kg) / max($this->peso_kg, $otro->peso_kg);
            $score += (int) round((float) $ratio * 35);
        } else {
            $score += 12;
        }

        // Esterilización (15 / 7)
        if ($this->esterilizado && $otro->esterilizado)         $score += 15;
        elseif ($this->esterilizado || $otro->esterilizado)     $score += 7;

        // Vacunación (15)
        if ($this->vacunado && $otro->vacunado) $score += 15;

        return min(100, $score);
    }
}
