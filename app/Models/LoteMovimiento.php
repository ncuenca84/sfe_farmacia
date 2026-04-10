<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class LoteMovimiento extends Model
{
    protected $table = 'lote_movimientos';

    protected $fillable = [
        'lote_id', 'tipo', 'cantidad', 'cantidad_anterior',
        'cantidad_posterior', 'referencia_type', 'referencia_id',
        'descripcion', 'user_id',
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'decimal:4',
            'cantidad_anterior' => 'decimal:4',
            'cantidad_posterior' => 'decimal:4',
        ];
    }

    public function lote(): BelongsTo
    {
        return $this->belongsTo(Lote::class);
    }

    public function referencia(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
