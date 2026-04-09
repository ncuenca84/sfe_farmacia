<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompraDetalle extends Model
{
    protected $table = 'compra_detalles';

    protected $fillable = [
        'compra_id', 'codigo_principal', 'codigo_auxiliar', 'descripcion',
        'cantidad', 'precio_unitario',
        'subtotal', 'iva', 'total',
        'producto_id', 'agregar_inventario',
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'decimal:6',
            'precio_unitario' => 'decimal:6',
            'subtotal' => 'decimal:2',
            'iva' => 'decimal:2',
            'total' => 'decimal:2',
            'agregar_inventario' => 'boolean',
        ];
    }

    public function compra(): BelongsTo
    {
        return $this->belongsTo(Compra::class);
    }

    public function producto(): BelongsTo { return $this->belongsTo(Producto::class); }
}
