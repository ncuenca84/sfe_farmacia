<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('emisores', function (Blueprint $table) {
            $table->string('celular', 20)->nullable()->after('direccion_matriz');
        });
    }

    public function down(): void
    {
        Schema::table('emisores', function (Blueprint $table) {
            $table->dropColumn('celular');
        });
    }
};
