<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // IVA 8% solo aplica durante feriados decretados por el presidente.
        // Fuera de feriado el SRI rechaza el codigo 8.
        // Se desactiva por defecto. El admin debe activarlo manualmente
        // durante los dias de feriado desde el panel de impuestos.
        DB::table('impuesto_ivas')
            ->where('codigo_porcentaje', '8')
            ->where('tarifa', 8.00)
            ->update([
                'nombre' => 'IVA 8% (solo feriados turismo)',
                'activo' => false,
            ]);
    }

    public function down(): void
    {
        DB::table('impuesto_ivas')
            ->where('codigo_porcentaje', '8')
            ->where('tarifa', 8.00)
            ->update([
                'nombre' => 'IVA diferenciado 8%',
                'activo' => true,
            ]);
    }
};
