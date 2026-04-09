<?php

namespace App\Models;

use App\Enums\TipoMovimiento;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MovimientoInventario extends Model
{
    protected $fillable = [
        'emisor_id', 'inventario_id', 'producto_id', 'establecimiento_id',
        'tipo', 'cantidad', 'costo_unitario', 'costo_total', 'stock_resultante',
        'referencia_type', 'referencia_id', 'descripcion', 'user_id',
    ];

    protected function casts(): array
    {
        return [
            'tipo' => TipoMovimiento::class,
            'cantidad' => 'decimal:4',
            'costo_unitario' => 'decimal:4',
            'costo_total' => 'decimal:4',
            'stock_resultante' => 'decimal:4',
        ];
    }

    public function emisor(): BelongsTo
    {
        return $this->belongsTo(Emisor::class);
    }

    public function inventario(): BelongsTo
    {
        return $this->belongsTo(Inventario::class);
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    public function establecimiento(): BelongsTo
    {
        return $this->belongsTo(Establecimiento::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function referencia(): MorphTo
    {
        return $this->morphTo();
    }
}
