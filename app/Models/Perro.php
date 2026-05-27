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
        'compatible_pequenos',
        'compatible_grandes',
        'notas',
    ];

    protected $casts = [
        'esterilizado'         => 'boolean',
        'vacunado'             => 'boolean',
        'compatible_pequenos'  => 'boolean',
        'compatible_grandes'   => 'boolean',
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

    public function getEdadTextoAttribute(): string
    {
        if ($this->edad_anios > 0) {
            return $this->edad_anios.' '.($this->edad_anios === 1 ? 'año' : 'años');
        }
        return ($this->edad_meses ?? 0).' meses';
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

        // 1) Energía similar (30 pts máximo)
        $diff = abs($this->energia - $otro->energia);
        $score += max(0, 30 - ($diff * 8));

        // 2) Tamaño relativo (25 pts)
        if ($this->peso_kg > 0 && $otro->peso_kg > 0) {
            $ratio = min($this->peso_kg, $otro->peso_kg) / max($this->peso_kg, $otro->peso_kg);
            $score += (int) round((float) $ratio * 25);
        } else {
            $score += 12;
        }

        // 3) Compatibilidad por tamaño declarada (20 pts)
        $otroPequeno = ($otro->peso_kg ?? 15) < 10;
        $otroGrande  = ($otro->peso_kg ?? 15) >= 25;
        if ($otroPequeno && $this->compatible_pequenos)   $score += 20;
        elseif ($otroGrande && $this->compatible_grandes) $score += 20;
        else                                               $score += 10;

        // 4) Ambos esterilizados (15 pts)
        if ($this->esterilizado && $otro->esterilizado)         $score += 15;
        elseif ($this->esterilizado || $otro->esterilizado)     $score += 7;

        // 5) Ambos vacunados (10 pts)
        if ($this->vacunado && $otro->vacunado) $score += 10;

        return min(100, $score);
    }
}
