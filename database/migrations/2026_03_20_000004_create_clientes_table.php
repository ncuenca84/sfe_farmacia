<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emisor_id')->constrained('emisores');
            $table->string('tipo_identificacion', 2); // TipoIdentificacion enum
            $table->string('identificacion', 20);
            $table->string('razon_social', 300);
            $table->text('direccion')->nullable();
            $table->string('email', 200)->nullable();
            $table->string('telefono', 50)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index(['emisor_id', 'identificacion']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
