<?php

namespace App\Services;

use App\Models\Emisor;
use App\Models\FirmaElectronica;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class FirmaElectronicaService
{
    /**
     * Registra o actualiza automáticamente una firma electrónica
     * a partir del archivo .p12 subido por un emisor.
     *
     * Se llama automáticamente cada vez que se sube un .p12 en:
     * - Admin: crear/editar emisor
     * - Emisor: configuración
     */
    public function registrarDesdeP12(string $p12Path, string $password, Emisor $emisor): ?FirmaElectronica
    {
        try {
            $datos = $this->leerCertificado($p12Path, $password);
        } catch (\RuntimeException $e) {
            Log::warning("CRM: No se pudo leer certificado del emisor {$emisor->ruc}: {$e->getMessage()}");
            return null;
        }

        // Actualizar firma_vigencia en el emisor automáticamente
        if ($datos['fecha_fin']) {
            $emisor->update(['firma_vigencia' => $datos['fecha_fin']]);
        }

        // Determinar identificación: usar la del certificado o el RUC del emisor
        $identificacion = !empty($datos['identificacion']) ? $datos['identificacion'] : $emisor->ruc;

        // Crear o actualizar el registro en firmas_electronicas
        return FirmaElectronica::updateOrCreate(
            ['emisor_id' => $emisor->id],
            [
                'identificacion' => $identificacion,
                'nombres' => $datos['nombres'] ?: $emisor->razon_social,
                'apellidos' => $datos['apellidos'] ?: '',
                'correo' => $datos['correo'] ?: $emisor->mail_from_address,
                'fecha_inicio' => $datos['fecha_inicio'],
                'fecha_fin' => $datos['fecha_fin'],
                'archivo_p12' => $p12Path,
                'password_p12' => $password,
                'emisor_cn' => $datos['emisor_cn'],
                'serial_number' => $datos['serial_number'],
                'organizacion' => $datos['organizacion'],
            ]
        );
    }
    /**
     * Extrae información del certificado .p12 usando OpenSSL.
     *
     * @return array{
     *   nombres: string,
     *   apellidos: string,
     *   identificacion: string,
     *   correo: string,
     *   fecha_inicio: ?Carbon,
     *   fecha_fin: ?Carbon,
     *   emisor_cn: string,
     *   serial_number: string,
     *   organizacion: string,
     * }
     * @throws \RuntimeException
     */
    public function leerCertificado(string $p12Path, string $password): array
    {
        $p12Content = file_get_contents($p12Path);
        if ($p12Content === false) {
            throw new \RuntimeException('No se pudo leer el archivo .p12');
        }

        $certs = [];
        if (!openssl_pkcs12_read($p12Content, $certs, $password)) {
            throw new \RuntimeException('No se pudo abrir el certificado. Verifique la contraseña.');
        }

        $certData = openssl_x509_parse($certs['cert']);
        if (!$certData) {
            throw new \RuntimeException('No se pudo leer la información del certificado.');
        }

        $subject = $certData['subject'] ?? [];
        $issuer = $certData['issuer'] ?? [];

        // Extraer nombre del CN (Common Name)
        // Formato típico Ecuador: "APELLIDO1 APELLIDO2 NOMBRE1 NOMBRE2" o similar
        $cn = $subject['CN'] ?? '';
        $serialNumber = $subject['serialNumber'] ?? '';
        $email = $subject['emailAddress'] ?? ($subject['email'] ?? '');
        $organizacion = $subject['O'] ?? ($issuer['O'] ?? '');

        // Intentar separar nombres y apellidos del CN
        // Los certificados ecuatorianos suelen tener el formato completo en CN
        $partes = $this->separarNombreApellido($cn);

        // Extraer cédula/RUC del serialNumber o del UID
        $identificacion = $serialNumber;
        if (empty($identificacion)) {
            $identificacion = $subject['UID'] ?? '';
        }
        // Limpiar identificación (a veces viene con prefijos)
        $identificacion = preg_replace('/[^0-9]/', '', $identificacion);

        // Fechas de vigencia
        $fechaInicio = null;
        $fechaFin = null;
        if (isset($certData['validFrom_time_t'])) {
            $fechaInicio = Carbon::createFromTimestamp($certData['validFrom_time_t']);
        }
        if (isset($certData['validTo_time_t'])) {
            $fechaFin = Carbon::createFromTimestamp($certData['validTo_time_t']);
        }

        return [
            'nombres' => $partes['nombres'],
            'apellidos' => $partes['apellidos'],
            'identificacion' => $identificacion,
            'correo' => $email,
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'emisor_cn' => $cn,
            'serial_number' => $serialNumber,
            'organizacion' => $organizacion,
        ];
    }

    /**
     * Intenta separar nombres y apellidos del CN.
     * Formato típico certificados Ecuador: "APELLIDO1 APELLIDO2 NOMBRE1 NOMBRE2"
     * A veces: "NOMBRE APELLIDO"
     */
    private function separarNombreApellido(string $cn): array
    {
        $cn = trim($cn);
        if (empty($cn)) {
            return ['nombres' => '', 'apellidos' => ''];
        }

        $partes = preg_split('/\s+/', $cn);
        $total = count($partes);

        if ($total <= 1) {
            return ['nombres' => $cn, 'apellidos' => ''];
        }

        if ($total === 2) {
            return ['nombres' => $partes[0], 'apellidos' => $partes[1]];
        }

        if ($total === 3) {
            return ['nombres' => $partes[2], 'apellidos' => $partes[0] . ' ' . $partes[1]];
        }

        // 4+ partes: primeros 2 = apellidos, resto = nombres
        $apellidos = $partes[0] . ' ' . $partes[1];
        $nombres = implode(' ', array_slice($partes, 2));
        return ['nombres' => $nombres, 'apellidos' => $apellidos];
    }
}
