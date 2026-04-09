<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add maneja_inventario to establecimientos
        Schema::table('establecimientos', function (Blueprint $table) {
            $table->boolean('maneja_inventario')->default(false)->after('activo');
        });

        // Migrate: if the emisor had maneja_inventario=true, enable it on all its establecimientos
        DB::table('establecimientos')
            ->join('emisores', 'establecimientos.emisor_id', '=', 'emisores.id')
            ->where('emisores.maneja_inventario', true)
            ->update(['establecimientos.maneja_inventario' => true]);

        // Remove from emisores
        Schema::table('emisores', function (Blueprint $table) {
            $table->dropColumn('maneja_inventario');
        });
    }

    public function down(): void
    {
        Schema::table('emisores', function (Blueprint $table) {
            $table->boolean('maneja_inventario')->default(false)->after('activo');
        });

        // Reverse: if any establecimiento had it, set it on the emisor
        DB::table('emisores')
            ->whereIn('id', DB::table('establecimientos')->where('maneja_inventario', true)->pluck('emisor_id'))
            ->update(['maneja_inventario' => true]);

        Schema::table('establecimientos', function (Blueprint $table) {
            $table->dropColumn('maneja_inventario');
        });
    }
};
