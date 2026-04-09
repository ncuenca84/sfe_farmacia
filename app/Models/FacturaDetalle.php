<?php

namespace App\Models;

use App\Models\Traits\TieneImpuestosDetalle;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FacturaDetalle extends Model
{
    use TieneImpuestosDetalle;

    protected $table = 'factura_detalles';

    protected $fillable = [
        'factura_id', 'codigo_principal', 'codigo_auxiliar',
        'descripcion', 'cantidad', 'precio_unitario',
        'descuento', 'precio_total_sin_impuesto',
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'decimal:6',
            'precio_unitario' => 'decimal:6',
            'descuento' => 'decimal:2',
            'precio_total_sin_impuesto' => 'decimal:2',
        ];
    }

    public function factura(): BelongsTo
    {
        return $this->belongsTo(Factura::class);
    }
}
