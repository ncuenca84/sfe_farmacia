<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emisor_id')->constrained('emisores');
            $table->foreignId('establecimiento_id')->constrained('establecimientos');
            $table->foreignId('pto_emision_id')->constrained('pto_emisiones');
            $table->integer('secuencial');
            $table->date('fecha_emision');
            // Transportista
            $table->string('ruc_transportista', 13)->nullable();
            $table->string('razon_social_transportista', 300)->nullable();
            $table->string('tipo_identificacion_transportista', 2)->nullable();
            $table->string('placa', 20)->nullable();
            // Ruta
            $table->string('dir_partida', 300)->nullable();
            $table->string('dir_llegada', 300)->nullable();
            $table->date('fecha_ini_transporte')->nullable();
            $table->date('fecha_fin_transporte')->nullable();
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

        Schema::create('guia_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guia_id')->constrained('guias')->cascadeOnDelete();
            // Destinatario
            $table->string('identificacion_destinatario', 20);
            $table->string('razon_social_destinatario', 300);
            $table->string('dir_destinatario', 300)->nullable();
            $table->string('motivo_traslado', 300)->nullable();
            $table->string('doc_aduanero_unico', 20)->nullable();
            $table->string('cod_establecimiento_destino', 3)->nullable();
            $table->string('ruta', 300)->nullable();
            // Documento sustento
            $table->string('cod_doc_sustento', 2)->nullable();
            $table->string('num_doc_sustento', 17)->nullable();
            $table->string('num_aut_doc_sustento', 49)->nullable();
            $table->date('fecha_emision_doc_sustento')->nullable();
            // Producto
            $table->string('codigo_principal', 50)->nullable();
            $table->string('descripcion', 300)->nullable();
            $table->decimal('cantidad', 14, 4)->nullable();
            $table->timestamps();
        });

        Schema::create('info_guia_remisiones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('factura_id')->constrained('facturas')->cascadeOnDelete();
            $table->string('dir_partida', 300)->nullable();
            $table->string('ruc_transportista', 13)->nullable();
            $table->string('tipo_identificacion_transportista', 2)->nullable();
            $table->string('razon_social_transportista', 300)->nullable();
            $table->string('placa', 20)->nullable();
            $table->string('dir_llegada', 300)->nullable();
            $table->date('fecha_ini_transporte')->nullable();
            $table->date('fecha_fin_transporte')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('info_guia_remisiones');
        Schema::dropIfExists('guia_detalles');
        Schema::dropIfExists('guias');
    }
};
