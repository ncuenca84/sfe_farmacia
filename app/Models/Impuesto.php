<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Impuesto extends Model
{
    protected $fillable = [
        'detalle_type', 'detalle_id',
        'codigo', 'codigo_porcentaje', 'tarifa',
        'base_imponible', 'valor',
    ];

    protected function casts(): array
    {
        return [
            'tarifa' => 'decimal:2',
            'base_imponible' => 'decimal:2',
            'valor' => 'decimal:2',
        ];
    }

    public function detalle(): MorphTo
    {
        return $this->morphTo();
    }
}
