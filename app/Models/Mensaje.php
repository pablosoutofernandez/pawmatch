<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mensaje extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversacion_id',
        'remitente_id',
        'cuerpo',
        'tipo',          // texto | foto | quedada
        'metadata',
        'leido_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'leido_at' => 'datetime',
    ];

    public function remitente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'remitente_id');
    }

    public function conversacion(): BelongsTo
    {
        return $this->belongsTo(Conversacion::class);
    }
}
