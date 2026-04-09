<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ImpuestosDefaultSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedIvas();
        $this->seedIces();
        $this->seedIrbpnrs();
    }

    private function seedIvas(): void
    {
        $ivas = [
            [
                'codigo_porcentaje' => '0',
                'nombre' => 'IVA 0%',
                'tarifa' => 0.00,
                'activo' => true,
                'fecha_vigencia_desde' => null,
                'fecha_vigencia_hasta' => null,
            ],
            [
                'codigo_porcentaje' => '2',
                'nombre' => 'IVA 12% (histórico)',
                'tarifa' => 12.00,
                'activo' => false,
                'fecha_vigencia_desde' => null,
                'fecha_vigencia_hasta' => '2024-04-01',
            ],
            [
                'codigo_porcentaje' => '4',
                'nombre' => 'IVA 15%',
                'tarifa' => 15.00,
                'activo' => true,
                'fecha_vigencia_desde' => '2024-04-01',
                'fecha_vigencia_hasta' => null,
            ],
            [
                'codigo_porcentaje' => '5',
                'nombre' => 'IVA 5%',
                'tarifa' => 5.00,
                'activo' => true,
                'fecha_vigencia_desde' => null,
                'fecha_vigencia_hasta' => null,
            ],
            [
                'codigo_porcentaje' => '6',
                'nombre' => 'No Objeto de Impuesto',
                'tarifa' => 0.00,
                'activo' => true,
                'fecha_vigencia_desde' => null,
                'fecha_vigencia_hasta' => null,
            ],
            [
                'codigo_porcentaje' => '7',
                'nombre' => 'Exento de IVA',
                'tarifa' => 0.00,
                'activo' => true,
                'fecha_vigencia_desde' => null,
                'fecha_vigencia_hasta' => null,
            ],
            [
                'codigo_porcentaje' => '8',
                'nombre' => 'IVA diferenciado 8%',
                'tarifa' => 8.00,
                'activo' => true,
                'fecha_vigencia_desde' => null,
                'fecha_vigencia_hasta' => null,
            ],
            [
                'codigo_porcentaje' => '10',
                'nombre' => 'IVA 13%',
                'tarifa' => 13.00,
                'activo' => true,
                'fecha_vigencia_desde' => null,
                'fecha_vigencia_hasta' => null,
            ],
        ];

        foreach ($ivas as $iva) {
            DB::table('impuesto_ivas')->insert(
                array_merge($iva, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }

    private function seedIces(): void
    {
        $ices = [
            ['codigo_porcentaje' => '3023', 'nombre' => 'Productos del tabaco y sucedáneos del tabaco', 'tarifa' => 150.00],
            ['codigo_porcentaje' => '3610', 'nombre' => 'Perfumes y aguas de tocador', 'tarifa' => 20.00],
            ['codigo_porcentaje' => '3620', 'nombre' => 'Videojuegos', 'tarifa' => 35.00],
            ['codigo_porcentaje' => '3630', 'nombre' => 'Armas de fuego, deportivas y municiones', 'tarifa' => 300.00],
            ['codigo_porcentaje' => '3640', 'nombre' => 'Focos incandescentes', 'tarifa' => 100.00],
        ];

        foreach ($ices as $ice) {
            DB::table('impuesto_ices')->insert(
                array_merge($ice, ['activo' => true, 'created_at' => now(), 'updated_at' => now()])
            );
        }
    }

    private function seedIrbpnrs(): void
    {
        DB::table('impuesto_irbpnrs')->insert([
            'codigo_porcentaje' => '5001',
            'nombre' => 'IRBPNR - Botellas plásticas no retornables',
            'tarifa' => 0.02,
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
