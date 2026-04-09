<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inventario extends Model
{
    protected $fillable = [
        'emisor_id', 'producto_id', 'establecimiento_id',
        'stock_actual', 'stock_minimo', 'costo_promedio',
    ];

    protected function casts(): array
    {
        return [
            'stock_actual' => 'decimal:4',
            'stock_minimo' => 'decimal:4',
            'costo_promedio' => 'decimal:4',
        ];
    }

    public function emisor(): BelongsTo
    {
        return $this->belongsTo(Emisor::class);
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    public function establecimiento(): BelongsTo
    {
        return $this->belongsTo(Establecimiento::class);
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoInventario::class);
    }

    public function stockBajo(): bool
    {
        return $this->stock_minimo > 0 && $this->stock_actual <= $this->stock_minimo;
    }
}
