<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Fix IVA diferenciado 8%: codigo_porcentaje '8' -> '10'
        // Segun Tabla 18 del SRI:
        //   codigo 8 = IVA presuntivo (retencion)
        //   codigo 10 = Tarifa diferenciada >= 8% y < 12% (turismo)
        DB::table('impuesto_ivas')
            ->where('codigo_porcentaje', '8')
            ->where('tarifa', 8.00)
            ->update(['codigo_porcentaje' => '10']);

        // Corregir comprobantes ya emitidos con codigo incorrecto
        DB::table('impuestos')
            ->where('codigo', '2')
            ->where('codigo_porcentaje', '8')
            ->where('tarifa', 8.00)
            ->update(['codigo_porcentaje' => '10']);
    }

    public function down(): void
    {
        DB::table('impuesto_ivas')
            ->where('codigo_porcentaje', '10')
            ->where('tarifa', 8.00)
            ->update(['codigo_porcentaje' => '8']);

        DB::table('impuestos')
            ->where('codigo', '2')
            ->where('codigo_porcentaje', '10')
            ->where('tarifa', 8.00)
            ->update(['codigo_porcentaje' => '8']);
    }
};
