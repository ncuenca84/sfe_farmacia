<?php
/**
 * POST MIGRACIÓN: Ejecutar después del script SQL y migrar_archivos.sh
 *
 * CONFIGURACIÓN: Cambiar estas variables
 */
$SFE_DIR_BASE = '/home/andres/emisores'; // Ruta donde se copiaron los archivos

/**
 * Uso: cd /ruta/al/proyecto && php database/migration_scripts/post_migracion.php
 */

require __DIR__ . '/../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== POST MIGRACIÓN SFE ===\n\n";

// 1. Actualizar rutas de firma y logo
echo "1. Actualizando rutas de firma y logo...\n";
$emisores = DB::table('emisores')
    ->whereNotNull('firma_path')
    ->where('firma_path', '!=', '')
    ->get(['id', 'ruc', 'firma_path']);

$firmas = 0;
foreach ($emisores as $e) {
    $filename = basename($e->firma_path);
    $newPath = "{$SFE_DIR_BASE}/{$e->ruc}/firmas/{$filename}";
    DB::table('emisores')->where('id', $e->id)->update(['firma_path' => $newPath]);
    $firmas++;
}
echo "   Firmas actualizadas: $firmas\n";

$emisores = DB::table('emisores')
    ->whereNotNull('logo_path')
    ->where('logo_path', '!=', '')
    ->get(['id', 'ruc', 'logo_path']);

$logos = 0;
foreach ($emisores as $e) {
    $filename = basename($e->logo_path);
    $newPath = "{$SFE_DIR_BASE}/{$e->ruc}/logos/{$filename}";
    DB::table('emisores')->where('id', $e->id)->update(['logo_path' => $newPath]);
    $logos++;
}
echo "   Logos actualizados: $logos\n\n";

// 2. Cifrar contraseñas de firma (el viejo las tiene en texto plano)
echo "2. Cifrando contraseñas de firma electrónica...\n";
$emisores = DB::table('emisores')
    ->whereNotNull('firma_password')
    ->where('firma_password', '!=', '')
    ->get(['id', 'firma_password']);

$cifradas = 0;
foreach ($emisores as $e) {
    // Si no empieza con 'eyJ' no está cifrada (las encrypted de Laravel empiezan así)
    if (!str_starts_with($e->firma_password, 'eyJ')) {
        DB::table('emisores')->where('id', $e->id)->update([
            'firma_password' => encrypt($e->firma_password)
        ]);
        $cifradas++;
    }
}
echo "   Firmas cifradas: $cifradas\n\n";

// 2b. Cifrar contraseñas de correo SMTP (también en texto plano en el viejo)
echo "2b. Cifrando contraseñas de correo SMTP...\n";
$emisores = DB::table('emisores')
    ->whereNotNull('mail_password')
    ->where('mail_password', '!=', '')
    ->get(['id', 'mail_password']);

$mailCifradas = 0;
foreach ($emisores as $e) {
    if (!str_starts_with($e->mail_password, 'eyJ')) {
        DB::table('emisores')->where('id', $e->id)->update([
            'mail_password' => encrypt($e->mail_password)
        ]);
        $mailCifradas++;
    }
}
echo "   Mail passwords cifrados: $mailCifradas\n\n";

// 3. Resetear passwords de usuarios (password = RUC del emisor)
echo "3. Reseteando passwords de usuarios (password = RUC del emisor)...\n";
$users = DB::table('users')
    ->join('emisores', 'users.emisor_id', '=', 'emisores.id')
    ->whereNotNull('users.emisor_id')
    ->get(['users.id', 'emisores.ruc']);

$passwords = 0;
foreach ($users as $u) {
    DB::table('users')->where('id', $u->id)->update([
        'password' => password_hash($u->ruc, PASSWORD_BCRYPT)
    ]);
    $passwords++;
}
echo "   Passwords reseteados: $passwords\n\n";

// 4. Crear usuario admin si no existe
echo "4. Verificando usuario admin...\n";
$admin = DB::table('users')->where('rol_id', 1)->first();
if (!$admin) {
    DB::table('users')->insert([
        'username' => 'admin',
        'nombre' => 'Administrador',
        'apellido' => 'Sistema',
        'email' => 'admin@sistema.com',
        'password' => password_hash('Admin2026!', PASSWORD_BCRYPT),
        'rol_id' => 1,
        'activo' => true,
        'created_at' => now(),
        'updated_at' => now()
    ]);
    echo "   Admin creado (user: admin, pass: Admin2026!)\n\n";
} else {
    echo "   Admin ya existe (id: {$admin->id}, user: {$admin->username})\n\n";
}

echo "=== MIGRACIÓN COMPLETA ===\n";
echo "Recuerda:\n";
echo "  - Verificar SFE_DIR_BASE={$SFE_DIR_BASE} en .env\n";
echo "  - Ejecutar: php artisan config:clear && php artisan cache:clear && php artisan route:clear\n";
echo "  - Los emisores entran con su RUC como contraseña\n";
