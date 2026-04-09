<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Detail tables - foreign key indexes
        Schema::table('factura_detalles', function (Blueprint $table) {
            $table->index('factura_id');
        });

        Schema::table('nota_credito_detalles', function (Blueprint $table) {
            $table->index('nota_credito_id');
        });

        Schema::table('nota_debito_motivos', function (Blueprint $table) {
            $table->index('nota_debito_id');
        });

        Schema::table('retencion_impuestos', function (Blueprint $table) {
            $table->index('retencion_id');
        });

        Schema::table('guia_detalles', function (Blueprint $table) {
            $table->index('guia_id');
        });

        Schema::table('liquidacion_detalles', function (Blueprint $table) {
            $table->index('liquidacion_compra_id');
        });

        Schema::table('proforma_detalles', function (Blueprint $table) {
            $table->index('proforma_id');
        });

        // Doc sustento retenciones
        if (Schema::hasTable('doc_sustento_retenciones')) {
            Schema::table('doc_sustento_retenciones', function (Blueprint $table) {
                $table->index('retencion_ats_id');
            });
        }

        // Transportistas - emisor_id for lookups
        Schema::table('transportistas', function (Blueprint $table) {
            $table->index('emisor_id');
        });

        // Carga archivos
        if (Schema::hasTable('carga_archivos')) {
            Schema::table('carga_archivos', function (Blueprint $table) {
                $table->index('emisor_id');
                $table->index('estado');
            });
        }

        // Compras
        if (Schema::hasTable('compras')) {
            Schema::table('compras', function (Blueprint $table) {
                $table->index('estado');
            });
        }
    }

    public function down(): void
    {
        Schema::table('factura_detalles', function (Blueprint $table) {
            $table->dropIndex(['factura_id']);
        });

        Schema::table('nota_credito_detalles', function (Blueprint $table) {
            $table->dropIndex(['nota_credito_id']);
        });

        Schema::table('nota_debito_motivos', function (Blueprint $table) {
            $table->dropIndex(['nota_debito_id']);
        });

        Schema::table('retencion_impuestos', function (Blueprint $table) {
            $table->dropIndex(['retencion_id']);
        });

        Schema::table('guia_detalles', function (Blueprint $table) {
            $table->dropIndex(['guia_id']);
        });

        Schema::table('liquidacion_detalles', function (Blueprint $table) {
            $table->dropIndex(['liquidacion_compra_id']);
        });

        Schema::table('proforma_detalles', function (Blueprint $table) {
            $table->dropIndex(['proforma_id']);
        });

        if (Schema::hasTable('doc_sustento_retenciones')) {
            Schema::table('doc_sustento_retenciones', function (Blueprint $table) {
                $table->dropIndex(['retencion_ats_id']);
            });
        }

        Schema::table('transportistas', function (Blueprint $table) {
            $table->dropIndex(['emisor_id']);
        });

        if (Schema::hasTable('carga_archivos')) {
            Schema::table('carga_archivos', function (Blueprint $table) {
                $table->dropIndex(['emisor_id']);
                $table->dropIndex(['estado']);
            });
        }

        if (Schema::hasTable('compras')) {
            Schema::table('compras', function (Blueprint $table) {
                $table->dropIndex(['estado']);
            });
        }
    }
};
