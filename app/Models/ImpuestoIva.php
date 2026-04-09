<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImpuestoIva extends Model
{
    protected $fillable = [
        'codigo_porcentaje', 'nombre', 'tarifa', 'activo',
        'fecha_vigencia_desde', 'fecha_vigencia_hasta',
    ];

    protected function casts(): array
    {
        return [
            'tarifa' => 'decimal:2',
            'activo' => 'boolean',
            'fecha_vigencia_desde' => 'date',
            'fecha_vigencia_hasta' => 'date',
        ];
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}
