<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhmcsServicio extends Model
{
    protected $table = 'whmcs_servicios';

    protected $fillable = [
        'whmcs_service_id', 'whmcs_client_id', 'whmcs_package_id',
        'emisor_id', 'estado',
    ];

    public function emisor(): BelongsTo
    {
        return $this->belongsTo(Emisor::class);
    }
}
