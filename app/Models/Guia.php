<?php

namespace App\Models;

use App\Models\Traits\TieneComprobanteSri;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;

class Guia extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use TieneComprobanteSri;

    protected $fillable = [
        'emisor_id', 'establecimiento_id', 'pto_emision_id',
        'secuencial', 'fecha_emision',
        'ruc_transportista', 'razon_social_transportista',
        'tipo_identificacion_transportista', 'placa',
        'dir_partida', 'dir_llegada', 'fecha_ini_transporte', 'fecha_fin_transporte',
        'clave_acceso', 'numero_autorizacion', 'fecha_autorizacion',
        'estado', 'ambiente', 'motivo_rechazo', 'xml_path', 'user_id', 'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha_emision' => 'date',
            'fecha_ini_transporte' => 'date',
            'fecha_fin_transporte' => 'date',
            'fecha_autorizacion' => 'datetime',
        ];
    }

    public function emisor(): BelongsTo { return $this->belongsTo(Emisor::class); }
    public function establecimiento(): BelongsTo { return $this->belongsTo(Establecimiento::class); }
    public function ptoEmision(): BelongsTo { return $this->belongsTo(PtoEmision::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function detalles(): HasMany { return $this->hasMany(GuiaDetalle::class); }
}
