<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('nota_debitos')) {
            return;
        }

        Schema::table('nota_debitos', function (Blueprint $table) {
            if (!Schema::hasColumn('nota_debitos', 'total_iva')) {
                $table->decimal('total_iva', 14, 2)->default(0)->after('total_descuento');
            }

            if (!Schema::hasColumn('nota_debitos', 'total_ice')) {
                $table->decimal('total_ice', 14, 2)->default(0)->after('total_iva');
            }

            if (!Schema::hasColumn('nota_debitos', 'importe_total')) {
                $table->decimal('importe_total', 14, 2)->default(0)->after('total_ice');
            }

            if (!Schema::hasColumn('nota_debitos', 'observaciones')) {
                $table->text('observaciones')->nullable()->after('fecha_emision_doc_sustento');
            }

            if (!Schema::hasColumn('nota_debitos', 'clave_acceso')) {
                $table->string('clave_acceso', 49)->nullable()->after('importe_total');
            }

            if (!Schema::hasColumn('nota_debitos', 'numero_autorizacion')) {
                $table->string('numero_autorizacion', 49)->nullable()->after('clave_acceso');
            }

            if (!Schema::hasColumn('nota_debitos', 'fecha_autorizacion')) {
                $table->dateTime('fecha_autorizacion')->nullable()->after('numero_autorizacion');
            }

            if (!Schema::hasColumn('nota_debitos', 'motivo_rechazo')) {
                $table->text('motivo_rechazo')->nullable()->after('estado');
            }

            if (!Schema::hasColumn('nota_debitos', 'xml_path')) {
                $table->string('xml_path', 255)->nullable()->after('motivo_rechazo');
            }
        });
    }

    public function down(): void
    {
        $columns = [
            'total_iva', 'total_ice', 'importe_total', 'observaciones',
            'clave_acceso', 'numero_autorizacion', 'fecha_autorizacion',
            'motivo_rechazo', 'xml_path',
        ];

        Schema::table('nota_debitos', function (Blueprint $table) use ($columns) {
            $toDrop = [];
            foreach ($columns as $column) {
                if (Schema::hasColumn('nota_debitos', $column)) {
                    $toDrop[] = $column;
                }
            }
            if (!empty($toDrop)) {
                $table->dropColumn($toDrop);
            }
        });
    }
};
