<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emisor_id')->constrained('emisores');
            $table->string('codigo_principal', 50);
            $table->string('codigo_auxiliar', 50)->nullable();
            $table->string('nombre', 300);
            $table->text('descripcion')->nullable();
            $table->decimal('precio_unitario', 14, 4)->default(0);
            $table->foreignId('impuesto_iva_id')->nullable()->constrained('impuesto_ivas')->nullOnDelete();
            $table->boolean('tiene_ice')->default(false);
            $table->foreignId('impuesto_ice_id')->nullable()->constrained('impuesto_ices')->nullOnDelete();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index(['emisor_id', 'codigo_principal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
