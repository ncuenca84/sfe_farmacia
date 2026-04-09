<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('retencion_ats', function (Blueprint $table) {
            $table->foreignId('establecimiento_id')->nullable()->after('emisor_id')->constrained('establecimientos');
            $table->foreignId('pto_emision_id')->nullable()->after('establecimiento_id')->constrained('pto_emisiones');
            $table->integer('secuencial')->nullable()->after('pto_emision_id');
            $table->date('fecha_emision')->nullable()->after('secuencial');
            $table->string('parte_rel', 2)->default('NO')->after('periodo_fiscal');
            // SRI
            $table->string('clave_acceso', 49)->nullable()->after('parte_rel');
            $table->string('numero_autorizacion', 49)->nullable()->after('clave_acceso');
            $table->dateTime('fecha_autorizacion')->nullable()->after('numero_autorizacion');
            $table->text('motivo_rechazo')->nullable()->after('estado');
            $table->string('xml_path', 255)->nullable()->after('motivo_rechazo');

            $table->index(['emisor_id', 'estado']);
        });

        Schema::table('doc_sustento_retenciones', function (Blueprint $table) {
            $table->string('cod_sustento', 2)->nullable()->after('retencion_ats_id');
            $table->date('fecha_registro_contable')->nullable()->after('fecha_emision_doc_sustento');
            $table->string('pago_loc_ext', 2)->default('01')->after('num_aut_doc_sustento');
            $table->string('forma_pago', 2)->default('20')->after('importe_total');
        });

        Schema::table('pto_emisiones', function (Blueprint $table) {
            $table->integer('sec_retencion_ats')->default(0)->after('sec_retencion');
        });

        Schema::create('impuesto_doc_sustentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doc_sustento_retencion_id')->constrained('doc_sustento_retenciones')->cascadeOnDelete();
            $table->string('codigo_impuesto', 4); // 2=IVA, 3=ICE, 5=IRBPNR
            $table->string('codigo_porcentaje', 10);
            $table->decimal('base_imponible', 14, 2);
            $table->decimal('tarifa', 5, 2)->default(0);
            $table->decimal('valor_impuesto', 14, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('impuesto_doc_sustentos');

        Schema::table('pto_emisiones', function (Blueprint $table) {
            $table->dropColumn('sec_retencion_ats');
        });

        Schema::table('doc_sustento_retenciones', function (Blueprint $table) {
            $table->dropColumn(['cod_sustento', 'fecha_registro_contable', 'pago_loc_ext', 'forma_pago']);
        });

        Schema::table('retencion_ats', function (Blueprint $table) {
            $table->dropForeign(['establecimiento_id']);
            $table->dropForeign(['pto_emision_id']);
            $table->dropIndex(['emisor_id', 'estado']);
            $table->dropColumn([
                'establecimiento_id', 'pto_emision_id', 'secuencial', 'fecha_emision',
                'parte_rel', 'clave_acceso', 'numero_autorizacion', 'fecha_autorizacion',
                'motivo_rechazo', 'xml_path',
            ]);
        });
    }
};
