<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('establecimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emisor_id')->constrained('emisores');
            $table->string('codigo', 3); // 001, 002, etc
            $table->string('nombre', 200)->nullable();
            $table->text('direccion')->nullable();
            $table->string('logo_path', 255)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['emisor_id', 'codigo']);
        });

        Schema::create('pto_emisiones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('establecimiento_id')->constrained('establecimientos');
            $table->string('codigo', 3); // 001, 002, etc
            $table->string('nombre', 200)->nullable();
            // Secuenciales por tipo de comprobante
            $table->integer('sec_factura')->default(0);
            $table->integer('sec_nota_credito')->default(0);
            $table->integer('sec_nota_debito')->default(0);
            $table->integer('sec_retencion')->default(0);
            $table->integer('sec_guia')->default(0);
            $table->integer('sec_liquidacion')->default(0);
            $table->integer('sec_proforma')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['establecimiento_id', 'codigo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pto_emisiones');
        Schema::dropIfExists('establecimientos');
    }
};
