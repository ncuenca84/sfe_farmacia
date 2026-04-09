<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Impuestos polimórficos — asociados a detalles de cualquier comprobante
        Schema::create('impuestos', function (Blueprint $table) {
            $table->id();
            $table->morphs('detalle'); // detalle_type, detalle_id
            $table->string('codigo', 4);           // 2=IVA, 3=ICE, 5=IRBPNR
            $table->string('codigo_porcentaje', 4); // código de tarifa
            $table->decimal('tarifa', 5, 2);
            $table->decimal('base_imponible', 14, 2);
            $table->decimal('valor', 14, 2);
            $table->timestamps();
        });

        // Campos adicionales polimórficos
        Schema::create('campos_adicionales', function (Blueprint $table) {
            $table->id();
            $table->morphs('comprobante'); // comprobante_type, comprobante_id
            $table->string('nombre', 300);
            $table->text('valor')->nullable();
            $table->timestamps();
        });

        // Mensajes SRI polimórficos
        Schema::create('mensajes', function (Blueprint $table) {
            $table->id();
            $table->morphs('comprobante'); // comprobante_type, comprobante_id
            $table->string('identificador', 10)->nullable();
            $table->text('mensaje')->nullable();
            $table->text('informacion_adicional')->nullable();
            $table->string('tipo', 20)->nullable(); // ERROR, ADVERTENCIA, INFORMATIVO
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mensajes');
        Schema::dropIfExists('campos_adicionales');
        Schema::dropIfExists('impuestos');
    }
};
