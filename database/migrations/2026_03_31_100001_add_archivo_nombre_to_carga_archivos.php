<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('carga_archivos', function (Blueprint $table) {
            $table->string('archivo_nombre', 255)->nullable()->after('dir_archivo');
        });

        // Make dir_archivo nullable (not needed for client/product uploads)
        Schema::table('carga_archivos', function (Blueprint $table) {
            $table->string('dir_archivo', 255)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('carga_archivos', function (Blueprint $table) {
            $table->dropColumn('archivo_nombre');
            $table->string('dir_archivo', 255)->nullable(false)->change();
        });
    }
};
