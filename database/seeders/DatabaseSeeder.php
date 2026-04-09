<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesSeeder::class,
            ImpuestosDefaultSeeder::class,
            CodigosRetencionSeeder::class,
        ]);

        // Crear usuario admin por defecto
        $rolAdmin = Role::where('nombre', 'ROLE_ADMIN')->first();
        User::create([
            'username' => 'admin',
            'nombre' => 'Administrador',
            'apellido' => 'Sistema',
            'email' => 'admin@sistemsfe.com',
            'password' => 'admin2026',
            'rol_id' => $rolAdmin->id,
            'emisor_id' => null,
            'activo' => true,
        ]);
    }
}
