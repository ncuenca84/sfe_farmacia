<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CargaArchivo extends Model
{
    protected $fillable = [
        'emisor_id', 'tipo', 'dir_archivo', 'archivo_nombre', 'estado',
        'total_registros', 'procesados', 'errores', 'user_id',
    ];

    public function emisor(): BelongsTo { return $this->belongsTo(Emisor::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function cargaErrors(): HasMany { return $this->hasMany(CargaError::class); }
    public function erroresDetalle(): HasMany { return $this->hasMany(CargaError::class); }
}
