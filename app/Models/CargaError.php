<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CargaError extends Model
{
    protected $table = 'carga_errors';

    protected $fillable = ['carga_archivo_id', 'fila', 'mensaje'];

    public function cargaArchivo(): BelongsTo
    {
        return $this->belongsTo(CargaArchivo::class);
    }
}
