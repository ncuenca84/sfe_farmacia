<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('retenciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emisor_id')->constrained('emisores');
            $table->foreignId('establecimiento_id')->constrained('establecimientos');
            $table->foreignId('pto_emision_id')->constrained('pto_emisiones');
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->integer('secuencial');
            $table->date('fecha_emision');
            // Documento sustento
            $table->string('cod_doc_sustento', 2)->nullable();
            $table->string('num_doc_sustento', 17)->nullable();
            $table->date('fecha_emision_doc_sustento')->nullable();
            $table->string('periodo_fiscal', 7)->nullable(); // MM/YYYY
            // SRI
            $table->string('clave_acceso', 49)->nullable();
            $table->string('numero_autorizacion', 49)->nullable();
            $table->dateTime('fecha_autorizacion')->nullable();
            $table->string('estado', 20)->default('CREADA');
            $table->text('motivo_rechazo')->nullable();
            $table->string('xml_path', 255)->nullable();

            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['emisor_id', 'estado']);
            $table->unique(['pto_emision_id', 'secuencial']);
        });

        Schema::create('retencion_impuestos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('retencion_id')->constrained('retenciones')->cascadeOnDelete();
            $table->string('codigo_impuesto', 4); // 1=Renta, 2=IVA, 6=ISD
            $table->string('codigo_retencion', 10);
            $table->decimal('base_imponible', 14, 2);
            $table->decimal('porcentaje_retener', 5, 2);
            $table->decimal('valor_retenido', 14, 2);
            $table->string('cod_doc_sustento', 2)->nullable();
            $table->string('num_doc_sustento', 17)->nullable();
            $table->date('fecha_emision_doc_sustento')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('retencion_impuestos');
        Schema::dropIfExists('retenciones');
    }
};
