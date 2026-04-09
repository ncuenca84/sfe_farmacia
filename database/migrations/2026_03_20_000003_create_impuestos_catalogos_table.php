<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('impuesto_ivas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_porcentaje', 4);
            $table->string('nombre', 100);
            $table->decimal('tarifa', 5, 2);
            $table->boolean('activo')->default(true);
            $table->date('fecha_vigencia_desde')->nullable();
            $table->date('fecha_vigencia_hasta')->nullable(); // NULL = vigente
            $table->timestamps();
        });

        Schema::create('impuesto_ices', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_porcentaje', 4);
            $table->string('nombre', 255);
            $table->decimal('tarifa', 5, 2);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('impuesto_irbpnrs', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_porcentaje', 4);
            $table->string('nombre', 255);
            $table->decimal('tarifa', 5, 2);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('codigos_retencion', function (Blueprint $table) {
            $table->id();
            $table->string('tipo', 10); // RENTA, IVA, ISD
            $table->string('codigo', 10);
            $table->text('descripcion');
            $table->decimal('porcentaje', 5, 2);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index(['tipo', 'codigo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('codigos_retencion');
        Schema::dropIfExists('impuesto_irbpnrs');
        Schema::dropIfExists('impuesto_ices');
        Schema::dropIfExists('impuesto_ivas');
    }
};
