<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PtoEmision extends Model
{
    protected $table = 'pto_emisiones';

    protected $fillable = [
        'establecimiento_id', 'codigo', 'nombre',
        'sec_factura', 'sec_nota_credito', 'sec_nota_debito',
        'sec_retencion', 'sec_guia', 'sec_liquidacion', 'sec_proforma',
        'activo',
    ];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    public function establecimiento(): BelongsTo
    {
        return $this->belongsTo(Establecimiento::class);
    }

    /**
     * Genera el siguiente secuencial de forma segura para concurrencia.
     * Usa lockForUpdate para evitar que dos requests obtengan el mismo número.
     * DEBE llamarse dentro de una DB::transaction().
     */
    public function siguienteSecuencial(string $tipo): int
    {
        $campo = "sec_{$tipo}";

        // Lock the row to prevent race conditions
        $locked = static::where('id', $this->id)->lockForUpdate()->first();
        $locked->increment($campo);

        // Refresh this instance with the new value
        $this->refresh();

        return $this->$campo;
    }
}
