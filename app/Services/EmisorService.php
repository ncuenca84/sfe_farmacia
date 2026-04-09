<?php

namespace App\Services;

use App\Exceptions\PermisosException;
use App\Models\Emisor;
use App\Models\Establecimiento;
use App\Models\Plan;
use App\Models\PtoEmision;
use App\Models\Role;
use App\Models\UnidadNegocio;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EmisorService
{
    public function __construct(
        private FirmaElectronicaService $firmaElectronicaService
    ) {}
    /**
     * Crea un emisor completo con su usuario administrador.
     */
    public function crear(array $data, Request $request): Emisor
    {
        // Auto-generar rutas usando el RUC como carpeta única
        $dirBase = rtrim(config('sri.dir_base'), '/');
        $dirDocAutorizados = $dirBase . '/' . $data['ruc'];

        $emisor = Emisor::create([
            'ruc' => $data['ruc'],
            'razon_social' => $data['razon_social'],
            'nombre_comercial' => $data['nombre_comercial'] ?? null,
            'direccion_matriz' => $data['direccion_matriz'] ?? null,
            'ambiente' => $data['ambiente'],
            'tipo_emision' => $data['tipo_emision'] ?? '1',
            'obligado_contabilidad' => $data['obligado_contabilidad'] ?? false,
            'contribuyente_especial' => $data['contribuyente_especial'] ?? null,
            'agente_retencion' => $data['agente_retencion'] ?? null,
            'regimen' => $data['regimen'] ?? 'GENERAL',
            'codigo_numerico' => $data['codigo_numerico'] ?? '00000001',
            'dir_doc_autorizados' => $dirDocAutorizados,
            'dir_proformas' => $dirDocAutorizados . '/proformas',
            'mail_host' => $data['mail_host'] ?? null,
            'mail_port' => $data['mail_port'] ?? null,
            'mail_username' => $data['mail_username'] ?? null,
            'mail_password' => $data['mail_password'] ?? null,
            'mail_encryption' => $data['mail_encryption'] ?? null,
            'mail_from_address' => $data['mail_from_address'] ?? null,
            'mail_from_name' => $data['mail_from_name'] ?? null,
            'activo' => true,
        ]);

        // Crear directorio base del emisor y subdirectorios
        $this->crearDirectorioEmisor($dirDocAutorizados);
        $this->crearDirectorioEmisor($dirDocAutorizados . '/firmas');
        $this->crearDirectorioEmisor($dirDocAutorizados . '/logos');
        $this->crearDirectorioEmisor($dirDocAutorizados . '/proformas');

        // Firma electrónica — guardar en la carpeta del emisor
        if ($request->hasFile('firma')) {
            $firmaPath = $this->guardarFirma($request, $dirDocAutorizados);
            $emisor->update([
                'firma_path' => $firmaPath,
                'firma_password' => $data['firma_password'] ?? null,
            ]);

            // Auto-registrar en gestión de firmas CRM
            if (!empty($data['firma_password'])) {
                $this->firmaElectronicaService->registrarDesdeP12($firmaPath, $data['firma_password'], $emisor);
            }
        }

        // Logo — guardar en la carpeta del emisor
        if ($request->hasFile('logo')) {
            $logoPath = $this->guardarLogo($request, $dirDocAutorizados);
            $emisor->update(['logo_path' => $logoPath]);
        }

        // Crear usuario administrador del emisor
        $rolEmisorAdmin = Role::where('nombre', 'ROLE_EMISOR_ADMIN')->first();
        User::create([
            'username' => $data['admin_username'],
            'nombre' => $data['admin_nombre'],
            'apellido' => $data['admin_apellido'],
            'email' => $data['admin_email'],
            'password' => $data['admin_password'],
            'rol_id' => $rolEmisorAdmin->id,
            'emisor_id' => $emisor->id,
            'activo' => true,
        ]);

        // Crear unidad de negocio por defecto
        $unidad = UnidadNegocio::create([
            'emisor_id' => $emisor->id,
            'nombre' => $emisor->nombre_comercial ?: $emisor->razon_social,
            'logo_path' => $emisor->logo_path,
            'activo' => true,
        ]);

        // Crear establecimiento principal (001) y punto de emision (001) automaticamente
        $this->crearEstablecimientoPrincipal($emisor, $data, $unidad);

        return $emisor;
    }

    /**
     * Crea un emisor desde WHMCS con datos mínimos.
     */
    public function crearDesdeWhmcs(array $data, Plan $plan): Emisor
    {
        // Auto-generar ruta usando el RUC como carpeta única
        $dirBase = rtrim(config('sri.dir_base'), '/');
        $dirDocAutorizados = $dirBase . '/' . $data['ruc'];

        $emisor = Emisor::create([
            'ruc' => $data['ruc'],
            'razon_social' => $data['razon_social'],
            'nombre_comercial' => $data['nombre_comercial'] ?? null,
            'ambiente' => '1', // Empieza en pruebas
            'tipo_emision' => '1',
            'codigo_numerico' => '00000001',
            'dir_doc_autorizados' => $dirDocAutorizados,
            'dir_proformas' => $dirDocAutorizados . '/proformas',
            'activo' => true,
            'origen' => 'WHMCS',
            'whmcs_service_id' => $data['whmcs_service_id'],
        ]);

        // Crear usuario del emisor
        $rolEmisorAdmin = Role::where('nombre', 'ROLE_EMISOR_ADMIN')->first();
        $password = bin2hex(random_bytes(8)); // Contraseña temporal

        $user = User::create([
            'username' => $data['ruc'], // RUC como username inicial
            'nombre' => $data['nombre_usuario'],
            'apellido' => $data['apellido_usuario'],
            'email' => $data['email'],
            'password' => $password,
            'rol_id' => $rolEmisorAdmin->id,
            'emisor_id' => $emisor->id,
            'activo' => true,
        ]);

        // Relación para notificaciones
        $emisor->setRelation('user', $user);

        return $emisor;
    }

    /**
     * Actualiza un emisor existente.
     */
    public function actualizar(Emisor $emisor, array $data, Request $request): Emisor
    {
        // Si el RUC cambió, actualizar las rutas automáticamente
        if (isset($data['ruc']) && $data['ruc'] !== $emisor->ruc) {
            $dirBase = rtrim(config('sri.dir_base'), '/');
            $dirDocAutorizados = $dirBase . '/' . $data['ruc'];
            $data['dir_doc_autorizados'] = $dirDocAutorizados;
            $data['dir_proformas'] = $dirDocAutorizados . '/proformas';
        }

        // No sobrescribir contraseñas con valores vacíos
        $excluir = ['firma', 'logo'];
        if (empty($data['firma_password'])) {
            $excluir[] = 'firma_password';
        }
        if (empty($data['mail_password'])) {
            $excluir[] = 'mail_password';
        }

        $emisor->update(collect($data)->except($excluir)->toArray());
        $emisor->refresh();

        $dirEmisor = $emisor->dir_doc_autorizados;

        if ($request->hasFile('firma') && $dirEmisor) {
            $firmaPath = $this->guardarFirma($request, $dirEmisor);
            $updateFirma = ['firma_path' => $firmaPath];
            if (!empty($data['firma_password'])) {
                $updateFirma['firma_password'] = $data['firma_password'];
            }
            $emisor->update($updateFirma);

            // Auto-registrar en gestión de firmas CRM
            $password = $data['firma_password'] ?? null;
            if ($password) {
                $this->firmaElectronicaService->registrarDesdeP12($firmaPath, $password, $emisor);
            }
        }

        if ($request->hasFile('logo') && $dirEmisor) {
            $logoPath = $this->guardarLogo($request, $dirEmisor);
            $emisor->update(['logo_path' => $logoPath]);
        }

        return $emisor;
    }

    /**
     * Crea el establecimiento principal (001) con su punto de emision (001).
     */
    public function crearEstablecimientoPrincipal(Emisor $emisor, array $data, ?UnidadNegocio $unidad = null): Establecimiento
    {
        $establecimiento = Establecimiento::create([
            'emisor_id' => $emisor->id,
            'unidad_negocio_id' => $unidad?->id,
            'codigo' => $data['estab_codigo'] ?? '001',
            'nombre' => $data['estab_nombre'] ?? 'Matriz',
            'direccion' => $data['estab_direccion'] ?? $data['direccion_matriz'] ?? null,
            'activo' => true,
        ]);

        PtoEmision::create([
            'establecimiento_id' => $establecimiento->id,
            'codigo' => $data['pto_codigo'] ?? '001',
            'nombre' => $data['pto_nombre'] ?? 'Punto de Emision 1',
            'activo' => true,
        ]);

        return $establecimiento;
    }

    /**
     * Guarda la firma (.p12) en la carpeta del emisor.
     * Retorna la ruta absoluta del archivo.
     */
    protected function guardarFirma(Request $request, string $dirEmisor): string
    {
        $dirFirmas = $dirEmisor . '/firmas';
        $this->crearDirectorioEmisor($dirFirmas);

        $archivo = $request->file('firma');
        $nombre = 'firma_' . time() . '.p12';
        $archivo->move($dirFirmas, $nombre);

        return $dirFirmas . '/' . $nombre;
    }

    /**
     * Guarda el logo en la carpeta del emisor.
     * Retorna la ruta absoluta del archivo.
     */
    protected function guardarLogo(Request $request, string $dirEmisor): string
    {
        $dirLogos = $dirEmisor . '/logos';
        $this->crearDirectorioEmisor($dirLogos);

        $archivo = $request->file('logo');
        $extension = $archivo->getClientOriginalExtension() ?: 'png';
        $nombre = 'logo_' . time() . '.' . $extension;
        $archivo->move($dirLogos, $nombre);

        return $dirLogos . '/' . $nombre;
    }

    /**
     * Crea el directorio del emisor con permisos correctos para cPanel + Tomcat.
     */
    public function crearDirectorioEmisor(string $ruta): void
    {
        if (!is_dir($ruta)) {
            mkdir($ruta, 0775, true);
        }

        // Verificar que se puede escribir
        $archivoTest = $ruta . '/.write_test';
        if (!@touch($archivoTest)) {
            throw new PermisosException(
                "La carpeta '{$ruta}' no tiene los permisos correctos. " .
                "Ejecute: sudo chmod -R 775 {$ruta} && sudo chgrp -R tomcat {$ruta}"
            );
        }
        @unlink($archivoTest);
    }
}
