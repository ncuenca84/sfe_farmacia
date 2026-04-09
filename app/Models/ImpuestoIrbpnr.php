<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImpuestoIrbpnr extends Model
{
    protected $fillable = ['codigo_porcentaje', 'nombre', 'tarifa', 'activo'];

    protected function casts(): array
    {
        return [
            'tarifa' => 'decimal:2',
            'activo' => 'boolean',
        ];
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}
