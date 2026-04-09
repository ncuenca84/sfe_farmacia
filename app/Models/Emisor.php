<?php

namespace App\Models;

use App\Enums\Ambiente;
use App\Enums\OrigenEmisor;
use App\Enums\RegimenEmisor;
use App\Enums\TipoEmision;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use OwenIt\Auditing\Contracts\Auditable;

class Emisor extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'emisores';

    protected $fillable = [
        'ruc', 'razon_social', 'nombre_comercial', 'direccion_matriz', 'celular',
        'ambiente', 'tipo_emision', 'obligado_contabilidad',
        'contribuyente_especial', 'agente_retencion', 'regimen',
        'codigo_numerico', 'dir_doc_autorizados', 'dir_proformas',
        'dir_plantilla_jasper',
        'firma_path', 'firma_password', 'firma_vigencia',
        'mail_host', 'mail_port', 'mail_username', 'mail_password',
        'mail_encryption', 'mail_from_address', 'mail_from_name',
        'logo_path', 'activo', 'origen', 'whmcs_service_id',
    ];

    protected function casts(): array
    {
        return [
            'ambiente' => Ambiente::class,
            'tipo_emision' => TipoEmision::class,
            'origen' => OrigenEmisor::class,
            'obligado_contabilidad' => 'boolean',
            'regimen' => RegimenEmisor::class,
            'activo' => 'boolean',
            'firma_password' => 'encrypted',
            'mail_password' => 'encrypted',
            'firma_vigencia' => 'date',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function unidadesNegocio(): HasMany
    {
        return $this->hasMany(UnidadNegocio::class);
    }

    public function establecimientos(): HasMany
    {
        return $this->hasMany(Establecimiento::class);
    }

    public function clientes(): HasMany
    {
        return $this->hasMany(Cliente::class);
    }

    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class);
    }

    public function suscripciones(): HasMany
    {
        return $this->hasMany(EmisorSuscripcion::class);
    }

    public function suscripcionActiva(): HasOne
    {
        return $this->hasOne(EmisorSuscripcion::class)->where('estado', 'ACTIVA')->latestOfMany();
    }

    public function facturas(): HasMany
    {
        return $this->hasMany(Factura::class);
    }

    public function notaCreditos(): HasMany
    {
        return $this->hasMany(NotaCredito::class);
    }

    public function notaDebitos(): HasMany
    {
        return $this->hasMany(NotaDebito::class);
    }

    public function retenciones(): HasMany
    {
        return $this->hasMany(Retencion::class);
    }

    public function liquidacionCompras(): HasMany
    {
        return $this->hasMany(LiquidacionCompra::class);
    }

    public function guias(): HasMany
    {
        return $this->hasMany(Guia::class);
    }

    public function proformas(): HasMany
    {
        return $this->hasMany(Proforma::class);
    }

    public function inventarios(): HasMany
    {
        return $this->hasMany(Inventario::class);
    }

    public function categoriasProducto(): HasMany
    {
        return $this->hasMany(CategoriaProducto::class);
    }

    public function proveedores(): HasMany
    {
        return $this->hasMany(Proveedor::class);
    }

    public function crmNotas(): HasMany
    {
        return $this->hasMany(CrmNota::class);
    }

    public function crmHistorialEmails(): HasMany
    {
        return $this->hasMany(CrmHistorialEmail::class);
    }
}
