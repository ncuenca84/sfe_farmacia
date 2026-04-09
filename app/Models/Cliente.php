<?php

namespace App\Models;

use App\Enums\TipoIdentificacion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable;

class Cliente extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'emisor_id', 'unidad_negocio_id', 'tipo_identificacion', 'identificacion',
        'razon_social', 'direccion', 'email', 'telefono', 'activo',
    ];

    protected function casts(): array
    {
        return [
            'tipo_identificacion' => TipoIdentificacion::class,
            'activo' => 'boolean',
        ];
    }

    public function emisor(): BelongsTo
    {
        return $this->belongsTo(Emisor::class);
    }

    public function unidadNegocio(): BelongsTo
    {
        return $this->belongsTo(UnidadNegocio::class);
    }
}
