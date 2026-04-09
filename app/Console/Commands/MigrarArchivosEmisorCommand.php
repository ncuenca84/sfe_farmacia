<?php

namespace App\Console\Commands;

use App\Models\Emisor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrarArchivosEmisorCommand extends Command
{
    protected $signature = 'emisor:migrar-archivos
        {--dir-viejo= : Ruta base del sistema viejo (ej: /home/exxalin/emisores)}
        {--dir-nuevo= : Ruta base del sistema nuevo (ej: /home/exxalin/documentos)}
        {--ruc= : Migrar solo un emisor específico por RUC}
        {--dry-run : Simular sin mover archivos}';

    protected $description = 'Migra firmas, logos y XMLs del sistema viejo (/home/usuario/emisores) al nuevo (/home/usuario/documentos)';

    public function handle(): int
    {
        $dirViejo = rtrim($this->option('dir-viejo'), '/');
        $dirNuevo = rtrim($this->option('dir-nuevo') ?? config('sri.dir_base'), '/');
        $dryRun = $this->option('dry-run');

        if (!$dirViejo) {
            $this->error('Debe especificar --dir-viejo (ruta del sistema anterior).');
            $this->info('Ejemplo: php artisan emisor:migrar-archivos --dir-viejo=/home/exxalin/emisores');
            return 1;
        }

        if (!is_dir($dirViejo)) {
            $this->error("La ruta del sistema viejo no existe: {$dirViejo}");
            return 1;
        }

        $this->info("Sistema viejo: {$dirViejo}");
        $this->info("Sistema nuevo: {$dirNuevo}");
        if ($dryRun) {
            $this->warn('*** MODO SIMULACIÓN (--dry-run) — no se moverán archivos ***');
        }
        $this->newLine();

        $query = Emisor::query();
        if ($ruc = $this->option('ruc')) {
            $query->where('ruc', $ruc);
        }

        $emisores = $query->get();
        if ($emisores->isEmpty()) {
            $this->warn('No se encontraron emisores para migrar.');
            return 0;
        }

        $this->info("Emisores a procesar: {$emisores->count()}");
        $this->newLine();

        $migrados = 0;
        $errores = 0;

        foreach ($emisores as $emisor) {
            $this->info("--- [{$emisor->ruc}] {$emisor->razon_social} ---");

            $carpetaVieja = $this->buscarCarpetaVieja($dirViejo, $emisor);
            if (!$carpetaVieja) {
                $this->warn("  No se encontró carpeta en sistema viejo. Saltando.");
                continue;
            }

            $carpetaNueva = $dirNuevo . '/' . $emisor->ruc;
            $this->line("  Origen:  {$carpetaVieja}");
            $this->line("  Destino: {$carpetaNueva}");

            if (!$dryRun) {
                $this->crearDirectorio($carpetaNueva);
                $this->crearDirectorio($carpetaNueva . '/firmas');
                $this->crearDirectorio($carpetaNueva . '/logos');
            }

            $updates = [];

            // Migrar firma (.p12)
            $firmaResult = $this->migrarArchivos($carpetaVieja, $carpetaNueva . '/firmas', ['*.p12', '*.P12'], $dryRun);
            if ($firmaResult) {
                $updates['firma_path'] = $firmaResult;
                $this->info("  Firma migrada: {$firmaResult}");
            }

            // Migrar logo
            $logoResult = $this->migrarArchivos($carpetaVieja, $carpetaNueva . '/logos', ['*.png', '*.jpg', '*.jpeg', '*.gif', '*.PNG', '*.JPG'], $dryRun);
            if ($logoResult) {
                $updates['logo_path'] = $logoResult;
                $this->info("  Logo migrado: {$logoResult}");
            }

            // Migrar XMLs autorizados (copiar estructura completa)
            $xmlCount = $this->migrarXmls($carpetaVieja, $carpetaNueva, $dryRun);
            $this->info("  XMLs migrados: {$xmlCount}");

            // Actualizar dir_doc_autorizados y paths en BD
            $updates['dir_doc_autorizados'] = $carpetaNueva;
            $updates['dir_proformas'] = $carpetaNueva . '/proformas';

            if (!$dryRun) {
                $emisor->update($updates);
            }

            $migrados++;
            $this->newLine();
        }

        $this->newLine();
        $this->info("Migración completada: {$migrados} emisores procesados, {$errores} errores.");

        if ($dryRun) {
            $this->warn('Ejecute sin --dry-run para aplicar los cambios.');
        }

        return 0;
    }

    /**
     * Busca la carpeta del emisor en el sistema viejo.
     * El sistema viejo usaba /emisores/RUC/ o /emisores/ID/
     */
    private function buscarCarpetaVieja(string $dirViejo, Emisor $emisor): ?string
    {
        // Primero buscar por RUC
        $porRuc = $dirViejo . '/' . $emisor->ruc;
        if (is_dir($porRuc)) {
            return $porRuc;
        }

        // Buscar por ID del emisor
        $porId = $dirViejo . '/' . $emisor->id;
        if (is_dir($porId)) {
            return $porId;
        }

        // Buscar en la ruta vieja de dir_doc_autorizados si existe
        if ($emisor->dir_doc_autorizados && is_dir($emisor->dir_doc_autorizados)) {
            return $emisor->dir_doc_autorizados;
        }

        return null;
    }

    /**
     * Migra archivos que coincidan con los patrones al destino.
     * Retorna la ruta del último archivo copiado.
     */
    private function migrarArchivos(string $origen, string $destino, array $patrones, bool $dryRun): ?string
    {
        $ultimoArchivo = null;

        foreach ($patrones as $patron) {
            $archivos = glob($origen . '/' . $patron);
            // También buscar en subdirectorios comunes
            foreach (['firmas', 'firma', 'logos', 'logo', 'certificados'] as $subdir) {
                $subArchivos = glob($origen . '/' . $subdir . '/' . $patron);
                $archivos = array_merge($archivos, $subArchivos);
            }

            foreach ($archivos as $archivo) {
                if (!is_file($archivo)) continue;

                $nombreArchivo = basename($archivo);
                $rutaDestino = $destino . '/' . $nombreArchivo;

                if (!$dryRun) {
                    copy($archivo, $rutaDestino);
                    chmod($rutaDestino, 0664);
                }

                $this->line("    Copiado: {$nombreArchivo}");
                $ultimoArchivo = $rutaDestino;
            }
        }

        return $ultimoArchivo;
    }

    /**
     * Migra archivos XML manteniendo la estructura de carpetas.
     */
    private function migrarXmls(string $origen, string $destino, bool $dryRun): int
    {
        $count = 0;

        // Buscar XMLs recursivamente
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($origen, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isDir()) continue;

            $ext = strtolower($item->getExtension());
            if ($ext !== 'xml') continue;

            // Obtener ruta relativa desde la carpeta origen
            $rutaRelativa = str_replace($origen . '/', '', $item->getPathname());
            $rutaDestino = $destino . '/' . $rutaRelativa;

            if (!$dryRun) {
                $dirDestino = dirname($rutaDestino);
                if (!is_dir($dirDestino)) {
                    mkdir($dirDestino, 0775, true);
                }
                copy($item->getPathname(), $rutaDestino);
            }
            $count++;
        }

        return $count;
    }

    private function crearDirectorio(string $ruta): void
    {
        if (!is_dir($ruta)) {
            mkdir($ruta, 0775, true);
        }
    }
}
