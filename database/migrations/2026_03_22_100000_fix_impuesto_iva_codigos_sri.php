<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Fix IVA 15%: codigo_porcentaje 2 -> 4 (SRI updated code)
        DB::table('impuesto_ivas')
            ->where('codigo_porcentaje', '2')
            ->where('tarifa', 15.00)
            ->update(['codigo_porcentaje' => '4']);

        // Fix IVA 5%: codigo_porcentaje 3 -> 5 (SRI code)
        DB::table('impuesto_ivas')
            ->where('codigo_porcentaje', '3')
            ->where('tarifa', 5.00)
            ->update(['codigo_porcentaje' => '5']);

        // Add IVA diferenciado 8% if not exists
        if (!DB::table('impuesto_ivas')->where('codigo_porcentaje', '8')->exists()) {
            DB::table('impuesto_ivas')->insert([
                'codigo_porcentaje' => '8',
                'nombre' => 'IVA diferenciado 8%',
                'tarifa' => 8.00,
                'activo' => true,
                'fecha_vigencia_desde' => null,
                'fecha_vigencia_hasta' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Add IVA 13% if not exists
        if (!DB::table('impuesto_ivas')->where('codigo_porcentaje', '10')->exists()) {
            DB::table('impuesto_ivas')->insert([
                'codigo_porcentaje' => '10',
                'nombre' => 'IVA 13%',
                'tarifa' => 13.00,
                'activo' => true,
                'fecha_vigencia_desde' => null,
                'fecha_vigencia_hasta' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Remove old "Sin IVA" (codigo_porcentaje=5 with tarifa=0) if it conflicts
        // The correct IVA 5% now uses codigo_porcentaje=5
        DB::table('impuesto_ivas')
            ->where('codigo_porcentaje', '5')
            ->where('tarifa', 0.00)
            ->where('nombre', 'like', '%Sin IVA%')
            ->delete();

        // Also fix existing impuestos (polymorphic) that reference old codes
        DB::table('impuestos')
            ->where('codigo', '2')
            ->where('codigo_porcentaje', '2')
            ->where('tarifa', 15.00)
            ->update(['codigo_porcentaje' => '4']);

        DB::table('impuestos')
            ->where('codigo', '2')
            ->where('codigo_porcentaje', '3')
            ->where('tarifa', 5.00)
            ->update(['codigo_porcentaje' => '5']);
    }

    public function down(): void
    {
        DB::table('impuesto_ivas')
            ->where('codigo_porcentaje', '4')
            ->where('tarifa', 15.00)
            ->update(['codigo_porcentaje' => '2']);

        DB::table('impuesto_ivas')
            ->where('codigo_porcentaje', '5')
            ->where('tarifa', 5.00)
            ->update(['codigo_porcentaje' => '3']);

        DB::table('impuesto_ivas')->where('codigo_porcentaje', '8')->delete();
        DB::table('impuesto_ivas')->where('codigo_porcentaje', '10')->delete();

        DB::table('impuestos')
            ->where('codigo', '2')
            ->where('codigo_porcentaje', '4')
            ->where('tarifa', 15.00)
            ->update(['codigo_porcentaje' => '2']);

        DB::table('impuestos')
            ->where('codigo', '2')
            ->where('codigo_porcentaje', '5')
            ->where('tarifa', 5.00)
            ->update(['codigo_porcentaje' => '3']);
    }
};
