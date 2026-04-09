<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guias', function (Blueprint $table) {
            if (!Schema::hasColumn('guias', 'dir_llegada')) {
                $table->string('dir_llegada', 300)->nullable()->after('dir_partida');
            }
            if (!Schema::hasColumn('guias', 'observaciones')) {
                $table->text('observaciones')->nullable()->after('xml_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('guias', function (Blueprint $table) {
            $table->dropColumn(['dir_llegada', 'observaciones']);
        });
    }
};
