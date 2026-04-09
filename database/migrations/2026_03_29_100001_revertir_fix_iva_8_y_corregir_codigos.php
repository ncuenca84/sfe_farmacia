<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Revertir: el codigo 8 SI es correcto para IVA 8%
        // La migracion anterior lo cambio a 10 incorrectamente
        DB::table('impuesto_ivas')
            ->where('codigo_porcentaje', '10')
            ->where('tarifa', 8.00)
            ->update(['codigo_porcentaje' => '8']);

        // Corregir comprobantes afectados por la migracion anterior
        DB::table('impuestos')
            ->where('codigo', '2')
            ->where('codigo_porcentaje', '10')
            ->where('tarifa', 8.00)
            ->update(['codigo_porcentaje' => '8']);

        // Asegurar que IVA 13% tenga codigo_porcentaje = 10
        DB::table('impuesto_ivas')
            ->where('tarifa', 13.00)
            ->where('nombre', 'like', '%13%')
            ->update(['codigo_porcentaje' => '10']);
    }

    public function down(): void
    {
        // No revertir
    }
};
