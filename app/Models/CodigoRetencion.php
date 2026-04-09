<?php

namespace App\Models;

use App\Enums\TipoRetencion;
use Illuminate\Database\Eloquent\Model;

class CodigoRetencion extends Model
{
    protected $table = 'codigos_retencion';

    protected $fillable = ['tipo', 'codigo', 'descripcion', 'porcentaje', 'activo'];

    protected function casts(): array
    {
        return [
            'tipo' => TipoRetencion::class,
            'porcentaje' => 'decimal:2',
            'activo' => 'boolean',
        ];
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopeRenta($query)
    {
        return $query->where('tipo', 'RENTA');
    }

    public function scopeIva($query)
    {
        return $query->where('tipo', 'IVA');
    }
}
