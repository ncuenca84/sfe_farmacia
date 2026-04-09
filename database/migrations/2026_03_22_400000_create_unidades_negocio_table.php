<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Crear tabla unidades_negocio
        Schema::create('unidades_negocio', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emisor_id')->constrained('emisores');
            $table->string('nombre', 200);
            $table->string('logo_path', 255)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index('emisor_id');
        });

        // 2. Crear una unidad de negocio por defecto para cada emisor existente
        $emisores = DB::table('emisores')->get();
        foreach ($emisores as $emisor) {
            DB::table('unidades_negocio')->insert([
                'emisor_id' => $emisor->id,
                'nombre' => $emisor->nombre_comercial ?: $emisor->razon_social,
                'logo_path' => $emisor->logo_path,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 3. Agregar unidad_negocio_id a establecimientos
        Schema::table('establecimientos', function (Blueprint $table) {
            $table->foreignId('unidad_negocio_id')->nullable()->after('emisor_id')->constrained('unidades_negocio');
        });

        // Asignar establecimientos existentes a la unidad de negocio de su emisor
        $unidades = DB::table('unidades_negocio')->get();
        foreach ($unidades as $unidad) {
            DB::table('establecimientos')
                ->where('emisor_id', $unidad->emisor_id)
                ->whereNull('unidad_negocio_id')
                ->update(['unidad_negocio_id' => $unidad->id]);
        }

        // 4. Agregar unidad_negocio_id a clientes
        Schema::table('clientes', function (Blueprint $table) {
            $table->foreignId('unidad_negocio_id')->nullable()->after('emisor_id')->constrained('unidades_negocio');
        });

        foreach ($unidades as $unidad) {
            DB::table('clientes')
                ->where('emisor_id', $unidad->emisor_id)
                ->whereNull('unidad_negocio_id')
                ->update(['unidad_negocio_id' => $unidad->id]);
        }

        // 5. Agregar unidad_negocio_id a productos
        Schema::table('productos', function (Blueprint $table) {
            $table->foreignId('unidad_negocio_id')->nullable()->after('emisor_id')->constrained('unidades_negocio');
        });

        foreach ($unidades as $unidad) {
            DB::table('productos')
                ->where('emisor_id', $unidad->emisor_id)
                ->whereNull('unidad_negocio_id')
                ->update(['unidad_negocio_id' => $unidad->id]);
        }

        // 6. Agregar unidad_negocio_id a transportistas
        Schema::table('transportistas', function (Blueprint $table) {
            $table->foreignId('unidad_negocio_id')->nullable()->after('emisor_id')->constrained('unidades_negocio');
        });

        foreach ($unidades as $unidad) {
            DB::table('transportistas')
                ->where('emisor_id', $unidad->emisor_id)
                ->whereNull('unidad_negocio_id')
                ->update(['unidad_negocio_id' => $unidad->id]);
        }

        // 7. Agregar unidad_negocio_id a users (para restringir acceso)
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('unidad_negocio_id')->nullable()->after('emisor_id')->constrained('unidades_negocio')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('unidad_negocio_id');
        });

        Schema::table('transportistas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('unidad_negocio_id');
        });

        Schema::table('productos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('unidad_negocio_id');
        });

        Schema::table('clientes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('unidad_negocio_id');
        });

        Schema::table('establecimientos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('unidad_negocio_id');
        });

        Schema::dropIfExists('unidades_negocio');
    }
};
