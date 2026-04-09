<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Agregar columna ambiente a tablas de comprobantes para registrar
     * el ambiente (pruebas/producción) en que fue emitido cada documento.
     */
    public function up(): void
    {
        $tables = [
            'facturas',
            'nota_creditos',
            'nota_debitos',
            'retenciones',
            'retencion_ats',
            'liquidacion_compras',
            'guias',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->enum('ambiente', ['1', '2'])->nullable()->after('estado');
            });
        }

        // Poblar ambiente desde la clave_acceso existente (posición 24, índice 23)
        foreach ($tables as $table) {
            DB::table($table)
                ->whereNotNull('clave_acceso')
                ->where('clave_acceso', '!=', '')
                ->update([
                    'ambiente' => DB::raw("SUBSTRING(clave_acceso, 24, 1)"),
                ]);
        }
    }

    public function down(): void
    {
        $tables = [
            'facturas',
            'nota_creditos',
            'nota_debitos',
            'retenciones',
            'retencion_ats',
            'liquidacion_compras',
            'guias',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropColumn('ambiente');
            });
        }
    }
};
