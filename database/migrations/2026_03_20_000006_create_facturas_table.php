<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facturas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emisor_id')->constrained('emisores');
            $table->foreignId('establecimiento_id')->constrained('establecimientos');
            $table->foreignId('pto_emision_id')->constrained('pto_emisiones');
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->integer('secuencial');
            $table->date('fecha_emision');
            $table->string('guia_remision', 20)->nullable();
            // Totales
            $table->decimal('total_sin_impuestos', 14, 2)->default(0);
            $table->decimal('total_descuento', 14, 2)->default(0);
            $table->decimal('total_iva', 14, 2)->default(0);
            $table->decimal('total_ice', 14, 2)->default(0);
            $table->decimal('total_irbpnr', 14, 2)->default(0);
            $table->decimal('propina', 14, 2)->default(0);
            $table->decimal('importe_total', 14, 2)->default(0);
            $table->string('moneda', 15)->default('DOLAR');
            // Forma de pago
            $table->string('forma_pago', 2)->default('01');
            $table->decimal('forma_pago_valor', 14, 2)->default(0);
            $table->integer('forma_pago_plazo')->default(0);
            $table->string('forma_pago_unidad_tiempo', 20)->default('dias');
            // SRI
            $table->string('clave_acceso', 49)->nullable();
            $table->string('numero_autorizacion', 49)->nullable();
            $table->dateTime('fecha_autorizacion')->nullable();
            $table->string('estado', 20)->default('CREADA');
            $table->text('motivo_rechazo')->nullable();
            // XML
            $table->string('xml_path', 255)->nullable();
            // Reembolso
            $table->boolean('es_reembolso')->default(false);
            $table->string('cod_doc_reembolso', 2)->nullable();
            // Exportación
            $table->boolean('es_exportacion')->default(false);
            $table->string('incoterm_termino', 10)->nullable();
            $table->decimal('incoterm_total', 14, 2)->nullable();
            $table->string('incoterm_lugar', 300)->nullable();
            $table->string('pais_origen', 3)->nullable();
            $table->string('puerto_embarque', 300)->nullable();
            $table->string('puerto_destino', 300)->nullable();
            $table->string('pais_destino', 3)->nullable();
            $table->string('pais_adquisicion', 3)->nullable();

            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['emisor_id', 'estado']);
            $table->index(['emisor_id', 'fecha_emision']);
            $table->unique(['pto_emision_id', 'secuencial']);
        });

        Schema::create('factura_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('factura_id')->constrained('facturas')->cascadeOnDelete();
            $table->string('codigo_principal', 50)->nullable();
            $table->string('codigo_auxiliar', 50)->nullable();
            $table->string('descripcion', 300);
            $table->decimal('cantidad', 14, 4);
            $table->decimal('precio_unitario', 14, 4);
            $table->decimal('descuento', 14, 2)->default(0);
            $table->decimal('precio_total_sin_impuesto', 14, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('factura_reembolsos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('factura_id')->constrained('facturas')->cascadeOnDelete();
            $table->string('tipo_identificacion_proveedor', 2);
            $table->string('identificacion_proveedor', 20);
            $table->string('codigo_pais_pago', 3)->nullable();
            $table->string('tipo_proveedor', 2)->nullable();
            $table->string('cod_doc_reembolso', 2);
            $table->string('estab_doc_reembolso', 3);
            $table->string('pto_emision_doc_reembolso', 3);
            $table->string('secuencial_doc_reembolso', 9);
            $table->date('fecha_emision_doc_reembolso');
            $table->string('numero_autorizacion_doc_reembolso', 49)->nullable();
            $table->decimal('base_imponible', 14, 2)->default(0);
            $table->decimal('impuesto_valor', 14, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('factura_reembolsos');
        Schema::dropIfExists('factura_detalles');
        Schema::dropIfExists('facturas');
    }
};
