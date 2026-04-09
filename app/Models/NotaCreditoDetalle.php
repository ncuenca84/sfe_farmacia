<?php

namespace App\Models;

use App\Models\Traits\TieneImpuestosDetalle;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotaCreditoDetalle extends Model
{
    use TieneImpuestosDetalle;

    protected $table = 'nota_credito_detalles';

    protected $fillable = [
        'nota_credito_id', 'codigo_principal', 'codigo_auxiliar',
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

    public function notaCredito(): BelongsTo
    {
        return $this->belongsTo(NotaCredito::class);
    }
}
