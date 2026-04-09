<?php

namespace App\Models;

use App\Enums\TipoPeriodo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;

class Plan extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'planes';

    protected $fillable = [
        'nombre', 'cant_comprobante', 'tipo_periodo', 'dias',
        'precio', 'observaciones', 'activo', 'whmcs_package_id',
    ];

    protected function casts(): array
    {
        return [
            'tipo_periodo' => TipoPeriodo::class,
            'precio' => 'decimal:2',
            'activo' => 'boolean',
        ];
    }

    public function suscripciones(): HasMany
    {
        return $this->hasMany(EmisorSuscripcion::class);
    }

    public function esIlimitado(): bool
    {
        return $this->cant_comprobante === 0;
    }
}
