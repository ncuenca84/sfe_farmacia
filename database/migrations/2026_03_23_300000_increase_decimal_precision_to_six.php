<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ampliar precisión de cantidad y precio_unitario a 6 decimales.
     * Requisito de la ficha técnica del SRI para comprobantes electrónicos.
     */
    public function up(): void
    {
        $tablesWithBothColumns = [
            'factura_detalles',
            'nota_credito_detalles',
            'liquidacion_detalles',
            'proforma_detalles',
            'compra_detalles',
        ];

        foreach ($tablesWithBothColumns as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->decimal('cantidad', 14, 6)->change();
                $t->decimal('precio_unitario', 14, 6)->change();
            });
        }

        // guia_detalles solo tiene cantidad
        Schema::table('guia_detalles', function (Blueprint $table) {
            $table->decimal('cantidad', 14, 6)->nullable()->change();
        });

        // productos tiene precio_unitario
        Schema::table('productos', function (Blueprint $table) {
            $table->decimal('precio_unitario', 14, 6)->default(0)->change();
        });
    }

    public function down(): void
    {
        $tablesWithBothColumns = [
            'factura_detalles',
            'nota_credito_detalles',
            'liquidacion_detalles',
            'proforma_detalles',
            'compra_detalles',
        ];

        foreach ($tablesWithBothColumns as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->decimal('cantidad', 14, 4)->change();
                $t->decimal('precio_unitario', 14, 4)->change();
            });
        }

        Schema::table('guia_detalles', function (Blueprint $table) {
            $table->decimal('cantidad', 14, 4)->nullable()->change();
        });

        Schema::table('productos', function (Blueprint $table) {
            $table->decimal('precio_unitario', 14, 4)->default(0)->change();
        });
    }
};
