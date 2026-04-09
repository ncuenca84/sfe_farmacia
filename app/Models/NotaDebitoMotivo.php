<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotaDebitoMotivo extends Model
{
    protected $table = 'nota_debito_motivos';

    protected $fillable = ['nota_debito_id', 'razon', 'valor', 'impuesto_iva_id'];

    protected function casts(): array
    {
        return ['valor' => 'decimal:2'];
    }

    public function notaDebito(): BelongsTo
    {
        return $this->belongsTo(NotaDebito::class);
    }

    public function impuestoIva(): BelongsTo
    {
        return $this->belongsTo(ImpuestoIva::class);
    }
}
