<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Compra extends Model
{
    protected $fillable = [
        'emisor_id', 'cliente_id', 'establecimiento_id', 'tipo_comprobante', 'numero_comprobante',
        'autorizacion', 'clave_acceso', 'fecha_emision',
        'total_sin_impuestos', 'total_iva', 'importe_total',
        'estado', 'user_id', 'xml_contenido', 'ruc_proveedor', 'razon_social_proveedor',
    ];

    protected function casts(): array
    {
        return [
            'fecha_emision' => 'date',
            'total_sin_impuestos' => 'decimal:2',
            'total_iva' => 'decimal:2',
            'importe_total' => 'decimal:2',
        ];
    }

    public function emisor(): BelongsTo { return $this->belongsTo(Emisor::class); }
    public function cliente(): BelongsTo { return $this->belongsTo(Cliente::class); }
    public function establecimiento(): BelongsTo { return $this->belongsTo(Establecimiento::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function detalles(): HasMany { return $this->hasMany(CompraDetalle::class); }
}
