<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['nombre' => 'ROLE_ADMIN', 'descripcion' => 'Administrador del sistema — acceso total'],
            ['nombre' => 'ROLE_EMISOR_ADMIN', 'descripcion' => 'Administrador del emisor — acceso completo a su emisor'],
            ['nombre' => 'ROLE_EMISOR', 'descripcion' => 'Usuario emisor — solo facturar y ver comprobantes'],
        ];

        foreach ($roles as $rol) {
            DB::table('roles')->updateOrInsert(
                ['nombre' => $rol['nombre']],
                array_merge($rol, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }
}
