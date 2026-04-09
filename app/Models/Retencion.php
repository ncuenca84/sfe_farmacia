<?php

namespace App\Models;

use App\Models\Traits\TieneComprobanteSri;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;

class Retencion extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use TieneComprobanteSri;

    protected $table = 'retenciones';

    protected $fillable = [
        'emisor_id', 'establecimiento_id', 'pto_emision_id', 'cliente_id',
        'secuencial', 'fecha_emision',
        'cod_doc_sustento', 'num_doc_sustento', 'fecha_emision_doc_sustento',
        'periodo_fiscal',
        'clave_acceso', 'numero_autorizacion', 'fecha_autorizacion',
        'estado', 'ambiente', 'motivo_rechazo', 'xml_path', 'user_id',
    ];

    protected function casts(): array
    {
        return [
            'fecha_emision' => 'date',
            'fecha_emision_doc_sustento' => 'date',
            'fecha_autorizacion' => 'datetime',
        ];
    }

    public function emisor(): BelongsTo { return $this->belongsTo(Emisor::class); }
    public function establecimiento(): BelongsTo { return $this->belongsTo(Establecimiento::class); }
    public function ptoEmision(): BelongsTo { return $this->belongsTo(PtoEmision::class); }
    public function cliente(): BelongsTo { return $this->belongsTo(Cliente::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function impuestosRetencion(): HasMany { return $this->hasMany(RetencionImpuesto::class); }
}
