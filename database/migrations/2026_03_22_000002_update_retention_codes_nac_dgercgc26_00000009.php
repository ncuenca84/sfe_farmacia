<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Actualiza porcentajes de retención según resolución NAC-DGERCGC26-00000009
     * vigente desde 1 de marzo de 2026.
     */
    public function up(): void
    {
        $updates = [
            // Códigos que pasan de 8% a 10%
            ['codigo' => '304', 'porcentaje' => 10.00],
            ['codigo' => '304A', 'porcentaje' => 10.00],
            ['codigo' => '304B', 'porcentaje' => 10.00],
            ['codigo' => '304C', 'porcentaje' => 10.00],
            ['codigo' => '304D', 'porcentaje' => 10.00],
            ['codigo' => '304E', 'porcentaje' => 10.00],
            ['codigo' => '314A', 'porcentaje' => 10.00],
            ['codigo' => '314B', 'porcentaje' => 10.00],
            ['codigo' => '314C', 'porcentaje' => 10.00],
            ['codigo' => '314D', 'porcentaje' => 10.00],
            ['codigo' => '320', 'porcentaje' => 10.00],
            ['codigo' => '345', 'porcentaje' => 10.00],
            // Códigos que pasan de 2% a 3%
            ['codigo' => '307', 'porcentaje' => 3.00],
            // Códigos que pasan de 1% a 3%
            ['codigo' => '309', 'porcentaje' => 3.00],
            // Códigos que pasan de 1.75% a 2%
            ['codigo' => '312', 'porcentaje' => 2.00],
            ['codigo' => '343B', 'porcentaje' => 2.00],
        ];

        foreach ($updates as $update) {
            DB::table('codigos_retencion')
                ->where('tipo', 'RENTA')
                ->where('codigo', $update['codigo'])
                ->update([
                    'porcentaje' => $update['porcentaje'],
                    'updated_at' => now(),
                ]);
        }

        // Actualizar descripción de 345 (ya no es "8%")
        DB::table('codigos_retencion')
            ->where('tipo', 'RENTA')
            ->where('codigo', '345')
            ->update(['descripcion' => 'Otras retenciones aplicables el 10%']);
    }

    public function down(): void
    {
        $rollbacks = [
            ['codigo' => '304', 'porcentaje' => 8.00],
            ['codigo' => '304A', 'porcentaje' => 8.00],
            ['codigo' => '304B', 'porcentaje' => 8.00],
            ['codigo' => '304C', 'porcentaje' => 8.00],
            ['codigo' => '304D', 'porcentaje' => 8.00],
            ['codigo' => '304E', 'porcentaje' => 8.00],
            ['codigo' => '314A', 'porcentaje' => 8.00],
            ['codigo' => '314B', 'porcentaje' => 8.00],
            ['codigo' => '314C', 'porcentaje' => 8.00],
            ['codigo' => '314D', 'porcentaje' => 8.00],
            ['codigo' => '320', 'porcentaje' => 8.00],
            ['codigo' => '345', 'porcentaje' => 8.00],
            ['codigo' => '307', 'porcentaje' => 2.00],
            ['codigo' => '309', 'porcentaje' => 1.00],
            ['codigo' => '312', 'porcentaje' => 1.75],
            ['codigo' => '343B', 'porcentaje' => 1.75],
        ];

        foreach ($rollbacks as $rb) {
            DB::table('codigos_retencion')
                ->where('tipo', 'RENTA')
                ->where('codigo', $rb['codigo'])
                ->update([
                    'porcentaje' => $rb['porcentaje'],
                    'updated_at' => now(),
                ]);
        }

        DB::table('codigos_retencion')
            ->where('tipo', 'RENTA')
            ->where('codigo', '345')
            ->update(['descripcion' => 'Otras retenciones aplicables el 8%']);
    }
};
