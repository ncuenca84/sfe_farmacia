<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrdenCompraItem extends Model
{
    protected $table = 'orden_compra_items';

    protected $fillable = [
        'orden_compra_id', 'producto_id', 'cantidad_pedida',
        'cantidad_recibida', 'costo_unitario', 'numero_lote', 'fecha_vencimiento',
    ];

    protected function casts(): array
    {
        return [
            'cantidad_pedida' => 'decimal:4',
            'cantidad_recibida' => 'decimal:4',
            'costo_unitario' => 'decimal:4',
            'fecha_vencimiento' => 'date',
        ];
    }

    public function ordenCompra(): BelongsTo { return $this->belongsTo(OrdenCompra::class); }
    public function producto(): BelongsTo { return $this->belongsTo(Producto::class); }

    public function pendiente(): float
    {
        return max(0, (float) $this->cantidad_pedida - (float) $this->cantidad_recibida);
    }
}
