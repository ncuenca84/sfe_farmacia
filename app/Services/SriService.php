<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\Factura;
use App\Models\Guia;
use App\Models\GuiaDetalle;
use App\Models\LiquidacionCompra;
use App\Models\Mensaje;
use App\Models\NotaCredito;
use App\Models\NotaDebito;
use App\Models\NotaDebitoMotivo;
use App\Models\Retencion;
use App\Models\RetencionImpuesto;
use App\Services\Soap\ProcesarComprobanteElectronico;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SriService
{
    public function __construct(
        private ClaveAccesoService $claveAccesoService
    ) {}

    /**
     * Parsea la fecha de autorización del SRI que viene en formato d/m/Y H:i:s.
     */
    private function parsearFechaAutorizacion(?string $fecha): ?Carbon
    {
        if (!$fecha) return null;

        try {
            return Carbon::createFromFormat('d/m/Y H:i:s', $fecha);
        } catch (\Exception $e) {
            try {
                return Carbon::parse($fecha);
            } catch (\Exception $e2) {
                Log::warning("No se pudo parsear fecha de autorización: {$fecha}");
                return null;
            }
        }
    }

    /**
     * Procesa una factura: genera clave de acceso, envía a MasterOffline.
     */
    public function procesarFactura(Factura $factura): void
    {
        $emisor = $factura->emisor;
        $establecimiento = $factura->establecimiento;
        $ptoEmision = $factura->ptoEmision;

        // Generar clave de acceso si no existe
        if (!$factura->clave_acceso) {
            $claveAcceso = $this->claveAccesoService->generar(
                fecha: $factura->fecha_emision->format('dmY'),
                tipoDoc: '01', // Factura
                ruc: $emisor->ruc,
                ambiente: $emisor->ambiente->value,
                establecimiento: $establecimiento->codigo,
                ptoEmision: $ptoEmision->codigo,
                secuencial: $factura->secuencial,
                codigoNumerico: $emisor->codigo_numerico,
            );
            $factura->update([
                'clave_acceso' => $claveAcceso,
                'ambiente' => $emisor->ambiente->value,
            ]);
        }

        // Validar campos requeridos antes de enviar a MasterOffline
        $errores = [];
        if (empty($emisor->firma_path)) {
            $errores[] = 'No se ha configurado la firma electrónica (firma_path)';
        } elseif (!file_exists($emisor->firma_path)) {
            $errores[] = "El archivo de firma no existe en: {$emisor->firma_path}";
        }
        if (empty($emisor->firma_password)) {
            $errores[] = 'No se ha configurado la contraseña de la firma electrónica';
        }
        if (empty($emisor->dir_doc_autorizados)) {
            $errores[] = 'No se ha configurado el directorio de documentos autorizados';
        } elseif (!is_dir($emisor->dir_doc_autorizados)) {
            $errores[] = "El directorio de documentos autorizados no existe: {$emisor->dir_doc_autorizados}";
        }
        if (!empty($errores)) {
            $factura->update([
                'estado' => 'NO AUTORIZADO',
                'motivo_rechazo' => 'Configuración incompleta del emisor: ' . implode('. ', $errores),
            ]);
            return;
        }

        $factura->update(['estado' => 'PROCESANDOSE']);

        $xmlContent = $this->generarXmlFactura($factura);

        // Guardar XML en disco
        $xmlPath = $emisor->dir_doc_autorizados . '/' . $factura->clave_acceso . '.xml';
        file_put_contents($xmlPath, $xmlContent);
        $factura->update(['xml_path' => $xmlPath]);

        try {
            $soap = new ProcesarComprobanteElectronico();

            // Log de funciones disponibles en el WSDL para depuración
            try {
                $funciones = $soap->__getFunctions();
                $tipos = $soap->__getTypes();
                Log::info("WSDL MasterOffline funciones", ['funciones' => $funciones, 'tipos' => $tipos]);
            } catch (\Throwable $e) {
                Log::warning("No se pudieron obtener funciones WSDL: {$e->getMessage()}");
            }

            // Usar procesarXML según WSDL:
            // struct procesarXML { string xml; configAplicacion configAplicacion; configCorreo configCorreo; }
            // struct configAplicacion { string dirAutorizados; string dirFirma; string dirLogo; string passFirma; }
            $params = [
                'xml' => $xmlContent,
                'configAplicacion' => [
                    'dirAutorizados' => $emisor->dir_doc_autorizados,
                    'dirFirma' => $emisor->firma_path,
                    'dirLogo' => $emisor->dir_logo ?? '',
                    'passFirma' => $emisor->firma_password,
                ],
                'configCorreo' => [
                    'BBC' => '',
                    'CC' => '',
                    'correoAsunto' => 'Comprobante Electrónico',
                    'correoHost' => $emisor->mail_host ?? '',
                    'correoPass' => $emisor->mail_password ?? '',
                    'correoPort' => (string) ($emisor->mail_port ?? '587'),
                    'correoRemitente' => $emisor->mail_from_address ?? $emisor->email,
                    'sslHabilitado' => ($emisor->mail_encryption === 'ssl'),
                ],
            ];

            Log::info("Enviando a MasterOffline (procesarXML) factura {$factura->id}", [
                'claveAcceso' => $factura->clave_acceso,
                'ruc' => $emisor->ruc,
                'dirFirma' => $emisor->firma_path,
                'dirAutorizados' => $emisor->dir_doc_autorizados,
                'xmlLength' => strlen($xmlContent),
            ]);

            $resultado = $soap->procesarXML($params);

            // Log de respuesta y request/response SOAP para depuración
            Log::info("Respuesta MasterOffline factura {$factura->id}", [
                'tipo' => get_debug_type($resultado),
                'respuesta' => $this->serializarRespuesta($resultado),
            ]);
            Log::debug("SOAP Request factura {$factura->id}", [
                'request' => $soap->__getLastRequest(),
            ]);
            Log::debug("SOAP Response factura {$factura->id}", [
                'response' => $soap->__getLastResponse(),
            ]);

            $this->procesarRespuesta($factura, $resultado);
        } catch (\SoapFault $e) {
            Log::error("Error SOAP procesando factura {$factura->id}: {$e->getMessage()}", [
                'code' => $e->faultcode ?? null,
                'detail' => $e->detail ?? null,
                'lastRequest' => $soap->__getLastRequest() ?? null,
                'lastResponse' => $soap->__getLastResponse() ?? null,
            ]);
            $factura->update([
                'estado' => 'NO AUTORIZADO',
                'motivo_rechazo' => 'Error de comunicación con MasterOffline: ' . $e->getMessage(),
            ]);
        } catch (\Throwable $e) {
            Log::error("Error inesperado procesando factura {$factura->id}: {$e->getMessage()}", [
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString(),
            ]);
            $factura->update([
                'estado' => 'NO AUTORIZADO',
                'motivo_rechazo' => 'Error inesperado: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Verifica el estado de un comprobante directamente en el SRI
     * usando el web service de autorización.
     */
    public function verificarEstado(string $claveAcceso, string $ambiente): ?array
    {
        $host = $ambiente === '2' ? 'cel.sri.gob.ec' : 'celcer.sri.gob.ec';
        $wsdls = [
            "https://{$host}/comprobantes-electronicos-ws/AutorizacionComprobantesOffline?wsdl",
            "https://{$host}/comprobantes-electronicos-ws/AutorizacionComprobantes?wsdl",
        ];

        foreach ($wsdls as $wsdl) {
            try {
                $soap = new \SoapClient($wsdl, [
                    'trace' => true,
                    'exceptions' => true,
                    'connection_timeout' => 30,
                    'cache_wsdl' => WSDL_CACHE_NONE,
                ]);

                $resultado = $soap->autorizacionComprobante(['claveAccesoComprobante' => $claveAcceso]);

                Log::info("Respuesta SRI autorizacion para clave {$claveAcceso}", [
                    'wsdl' => $wsdl,
                    'respuesta' => json_decode(json_encode($resultado), true),
                ]);

                $parsed = $this->aplanarRespuestaAutorizacion($resultado);
                if ($parsed) {
                    return $parsed;
                }
            } catch (\SoapFault $e) {
                Log::warning("Error consultando autorizacion SRI ({$wsdl}): {$e->getMessage()}");
                continue;
            } catch (\Throwable $e) {
                Log::warning("Error inesperado consultando SRI ({$wsdl}): {$e->getMessage()}");
                continue;
            }
        }

        Log::error("No se pudo verificar estado en SRI para clave {$claveAcceso} en ningún endpoint");
        return null;
    }

    /**
     * Aplana la respuesta del web service de autorizacion del SRI.
     */
    private function aplanarRespuestaAutorizacion(mixed $resultado): ?array
    {
        $data = is_object($resultado) ? (array) $resultado : (array) $resultado;

        // Navegar: RespuestaAutorizacionComprobante -> autorizaciones -> autorizacion
        if (isset($data['RespuestaAutorizacionComprobante'])) {
            $data = is_object($data['RespuestaAutorizacionComprobante'])
                ? (array) $data['RespuestaAutorizacionComprobante']
                : (array) $data['RespuestaAutorizacionComprobante'];
        }

        $autorizaciones = $data['autorizaciones'] ?? null;
        if (is_object($autorizaciones)) {
            $autorizaciones = (array) $autorizaciones;
        }

        $autorizacion = $autorizaciones['autorizacion'] ?? null;
        if (is_array($autorizacion) && isset($autorizacion[0])) {
            $autorizacion = $autorizacion[0];
        }
        if (is_object($autorizacion)) {
            $autorizacion = (array) $autorizacion;
        }

        if (!$autorizacion) {
            return null;
        }

        $estado = strtoupper((string) ($autorizacion['estado'] ?? ''));

        $result = [
            'estado' => $estado,
            'numeroAutorizacion' => $autorizacion['numeroAutorizacion'] ?? null,
            'fechaAutorizacion' => $autorizacion['fechaAutorizacion'] ?? null,
        ];

        // Extraer mensajes si existen
        $mensajes = $autorizacion['mensajes'] ?? null;
        if ($mensajes) {
            $result['mensajes'] = $mensajes;
        }

        return $result;
    }

    /**
     * Consulta el estado de autorización de un comprobante en el SRI
     * y actualiza el modelo si fue autorizado.
     */
    public function consultarYActualizar(Model $comprobante): string
    {
        if (!$comprobante->clave_acceso) {
            return 'NO AUTORIZADO';
        }

        $respuesta = $this->verificarEstado(
            $comprobante->clave_acceso,
            $comprobante->ambiente ?? $comprobante->emisor->ambiente->value
        );

        if (!$respuesta) {
            // Si el SRI no tiene este comprobante y ya pasó tiempo, marcarlo como error
            if ($comprobante->estado === 'PROCESANDOSE' || $comprobante->estado === 'RECIBIDA') {
                $comprobante->update([
                    'estado' => 'NO AUTORIZADO',
                    'motivo_rechazo' => 'El comprobante fue rechazado por el SRI en la recepcion. Verifique que el establecimiento y punto de emision existan y esten activos en el SRI (sri.gob.ec).',
                ]);
                return 'NO AUTORIZADO';
            }
            return $comprobante->estado;
        }

        $estado = strtoupper((string) ($respuesta['estado'] ?? ''));

        if ($estado === 'AUTORIZADO') {
            $comprobante->update([
                'estado' => 'AUTORIZADO',
                'numero_autorizacion' => $respuesta['numeroAutorizacion'] ?? null,
                'fecha_autorizacion' => $this->parsearFechaAutorizacion($respuesta['fechaAutorizacion'] ?? null),
                'motivo_rechazo' => null,
            ]);
            return 'AUTORIZADO';
        }

        if (in_array($estado, ['NO AUTORIZADO', 'RECHAZADA', 'DEVUELTA'])) {
            $mensajeRaw = $this->extraerMensajeSri($respuesta);
            [$mensajeAmigable, $detalleTecnico] = $this->traducirMensajeSri($mensajeRaw);

            $comprobante->update([
                'estado' => 'NO AUTORIZADO',
                'motivo_rechazo' => $mensajeAmigable,
            ]);

            if ($detalleTecnico) {
                Log::warning("SRI detalle técnico comprobante {$comprobante->id}: {$detalleTecnico}");
            }

            $this->guardarMensajesSri($comprobante, $respuesta);
            return 'NO AUTORIZADO';
        }

        // RECIBIDA, EN PROCESO, etc. - aún procesándose
        return $comprobante->estado;
    }

    /**
     * Procesa un comprobante genérico (no factura): genera clave de acceso, XML, envía a MasterOffline.
     * @param Model $comprobante NotaCredito|NotaDebito|Retencion|Guia|LiquidacionCompra
     * @param string $tipoDoc Código de tipo de documento SRI ('03','04','05','06','07')
     */
    public function procesarComprobante(Model $comprobante, string $tipoDoc): void
    {
        $emisor = $comprobante->emisor;
        $establecimiento = $comprobante->establecimiento;
        $ptoEmision = $comprobante->ptoEmision;

        // Generar clave de acceso si no existe
        if (!$comprobante->clave_acceso) {
            $claveAcceso = $this->claveAccesoService->generar(
                fecha: $comprobante->fecha_emision->format('dmY'),
                tipoDoc: $tipoDoc,
                ruc: $emisor->ruc,
                ambiente: $emisor->ambiente->value,
                establecimiento: $establecimiento->codigo,
                ptoEmision: $ptoEmision->codigo,
                secuencial: $comprobante->secuencial,
                codigoNumerico: $emisor->codigo_numerico,
            );
            $comprobante->update([
                'clave_acceso' => $claveAcceso,
                'ambiente' => $emisor->ambiente->value,
            ]);
        }

        // Validar campos requeridos antes de enviar a MasterOffline
        $errores = [];
        if (empty($emisor->firma_path)) {
            $errores[] = 'No se ha configurado la firma electrónica (firma_path)';
        } elseif (!file_exists($emisor->firma_path)) {
            $errores[] = "El archivo de firma no existe en: {$emisor->firma_path}";
        }
        if (empty($emisor->firma_password)) {
            $errores[] = 'No se ha configurado la contraseña de la firma electrónica';
        }
        if (empty($emisor->dir_doc_autorizados)) {
            $errores[] = 'No se ha configurado el directorio de documentos autorizados';
        } elseif (!is_dir($emisor->dir_doc_autorizados)) {
            $errores[] = "El directorio de documentos autorizados no existe: {$emisor->dir_doc_autorizados}";
        }
        if (!empty($errores)) {
            $comprobante->update([
                'estado' => 'NO AUTORIZADO',
                'motivo_rechazo' => 'Configuración incompleta del emisor: ' . implode('. ', $errores),
            ]);
            return;
        }

        $comprobante->update(['estado' => 'PROCESANDOSE']);

        $xmlContent = match (true) {
            $tipoDoc === '03' => $this->generarXmlLiquidacion($comprobante),
            $tipoDoc === '04' => $this->generarXmlNotaCredito($comprobante),
            $tipoDoc === '05' => $this->generarXmlNotaDebito($comprobante),
            $tipoDoc === '06' => $this->generarXmlGuia($comprobante),
            $tipoDoc === '07' && $comprobante instanceof \App\Models\RetencionAts => $this->generarXmlRetencionAts($comprobante),
            $tipoDoc === '07' => $this->generarXmlRetencion($comprobante),
            default => throw new \InvalidArgumentException("Tipo de documento no soportado: {$tipoDoc}"),
        };

        // Guardar XML en disco
        $xmlPath = $emisor->dir_doc_autorizados . '/' . $comprobante->clave_acceso . '.xml';
        file_put_contents($xmlPath, $xmlContent);
        $comprobante->update(['xml_path' => $xmlPath]);

        $nombreDoc = match ($tipoDoc) {
            '03' => 'liquidación de compra',
            '04' => 'nota de crédito',
            '05' => 'nota de débito',
            '06' => 'guía de remisión',
            '07' => 'retención',
            default => 'comprobante',
        };

        try {
            $soap = new ProcesarComprobanteElectronico();

            $params = [
                'xml' => $xmlContent,
                'configAplicacion' => [
                    'dirAutorizados' => $emisor->dir_doc_autorizados,
                    'dirFirma' => $emisor->firma_path,
                    'dirLogo' => $emisor->dir_logo ?? '',
                    'passFirma' => $emisor->firma_password,
                ],
                'configCorreo' => [
                    'BBC' => '',
                    'CC' => '',
                    'correoAsunto' => 'Comprobante Electrónico',
                    'correoHost' => $emisor->mail_host ?? '',
                    'correoPass' => $emisor->mail_password ?? '',
                    'correoPort' => (string) ($emisor->mail_port ?? '587'),
                    'correoRemitente' => $emisor->mail_from_address ?? $emisor->email,
                    'sslHabilitado' => ($emisor->mail_encryption === 'ssl'),
                ],
            ];

            Log::info("Enviando a MasterOffline (procesarXML) {$nombreDoc} {$comprobante->id}", [
                'claveAcceso' => $comprobante->clave_acceso,
                'ruc' => $emisor->ruc,
                'tipoDoc' => $tipoDoc,
                'xmlLength' => strlen($xmlContent),
            ]);

            $resultado = $soap->procesarXML($params);

            Log::info("Respuesta MasterOffline {$nombreDoc} {$comprobante->id}", [
                'tipo' => get_debug_type($resultado),
                'respuesta' => $this->serializarRespuesta($resultado),
            ]);

            $this->procesarRespuestaGeneric($comprobante, $resultado);
        } catch (\SoapFault $e) {
            Log::error("Error SOAP procesando {$nombreDoc} {$comprobante->id}: {$e->getMessage()}", [
                'code' => $e->faultcode ?? null,
                'detail' => $e->detail ?? null,
            ]);
            $comprobante->update([
                'estado' => 'NO AUTORIZADO',
                'motivo_rechazo' => 'Error de comunicación con MasterOffline: ' . $e->getMessage(),
            ]);
        } catch (\Throwable $e) {
            Log::error("Error inesperado procesando {$nombreDoc} {$comprobante->id}: {$e->getMessage()}", [
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString(),
            ]);
            $comprobante->update([
                'estado' => 'NO AUTORIZADO',
                'motivo_rechazo' => 'Error inesperado: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Procesa la respuesta de MasterOffline para un comprobante genérico.
     */
    private function procesarRespuestaGeneric(Model $comprobante, mixed $resultado): void
    {
        if (!$resultado) {
            $comprobante->update(['estado' => 'NO AUTORIZADO', 'motivo_rechazo' => 'Sin respuesta de MasterOffline']);
            return;
        }

        $respuesta = $this->aplanarRespuesta($resultado);

        Log::info("procesarRespuestaGeneric {$comprobante->id}: respuesta aplanada", [
            'estado' => $respuesta['estado'] ?? null,
            'numeroAutorizacion' => $respuesta['numeroAutorizacion'] ?? null,
            'keys' => array_keys($respuesta),
        ]);

        $estado = $respuesta['estado'] ?? null;
        $autorizacion = $respuesta['numeroAutorizacion'] ?? null;
        $fechaAut = $this->parsearFechaAutorizacion($respuesta['fechaAutorizacion'] ?? null);

        if (strtoupper((string) $estado) === 'AUTORIZADO') {
            $comprobante->update([
                'estado' => 'AUTORIZADO',
                'numero_autorizacion' => $autorizacion,
                'fecha_autorizacion' => $fechaAut,
                'motivo_rechazo' => null,
            ]);
        } else {
            $estadoFinal = strtoupper((string) $estado);

            if (in_array($estadoFinal, ['RECIBIDA', 'PROCESANDOSE', 'EN PROCESO'])) {
                // Si la respuesta trae mensajes de error, es un rechazo
                $mensajeRecibida = $this->extraerMensajeSri($respuesta);

                if ($mensajeRecibida) {
                    [$mensajeAmigable, $detalleTecnico] = $this->traducirMensajeSri($mensajeRecibida);
                    $comprobante->update([
                        'estado' => 'NO AUTORIZADO',
                        'motivo_rechazo' => $mensajeAmigable,
                    ]);
                    if ($detalleTecnico) {
                        Log::warning("SRI detalle técnico comprobante {$comprobante->id}: {$detalleTecnico}");
                    }
                    $this->guardarMensajesSri($comprobante, $respuesta);
                    return;
                }

                sleep(3);
                $estadoConsulta = $this->consultarYActualizar($comprobante);
                if ($estadoConsulta === 'AUTORIZADO') {
                    $this->guardarMensajesSri($comprobante, $respuesta);
                    return;
                }
                if ($estadoConsulta === 'NO AUTORIZADO') {
                    // consultarYActualizar ya actualizó el comprobante con el motivo
                    $this->guardarMensajesSri($comprobante, $respuesta);
                    return;
                }

                $comprobante->update([
                    'estado' => 'PROCESANDOSE',
                    'motivo_rechazo' => 'El comprobante fue recibido por el SRI y está siendo procesado. Use el botón "Consultar Estado SRI" para verificar la autorización.',
                ]);
                $this->guardarMensajesSri($comprobante, $respuesta);
                return;
            }

            $mensajeRaw = $this->extraerMensajeSri($respuesta);
            [$mensajeAmigable, $detalleTecnico] = $this->traducirMensajeSri($mensajeRaw);

            if (in_array($estadoFinal, ['DEVUELTA', 'RECHAZADA', ''])) {
                $estadoFinal = 'NO AUTORIZADO';
            }

            $comprobante->update([
                'estado' => $estadoFinal ?: 'NO AUTORIZADO',
                'motivo_rechazo' => $mensajeAmigable,
            ]);

            if ($detalleTecnico) {
                Log::warning("SRI detalle técnico comprobante {$comprobante->id}: {$detalleTecnico}");
            }
        }

        $this->guardarMensajesSri($comprobante, $respuesta);
    }

    private function procesarRespuesta(Factura $factura, mixed $resultado): void
    {
        if (!$resultado) {
            $factura->update(['estado' => 'NO AUTORIZADO', 'motivo_rechazo' => 'Sin respuesta de MasterOffline']);
            return;
        }

        $respuesta = $this->aplanarRespuesta($resultado);

        Log::info("procesarRespuesta factura {$factura->id}: respuesta aplanada", [
            'estado' => $respuesta['estado'] ?? null,
            'numeroAutorizacion' => $respuesta['numeroAutorizacion'] ?? null,
            'keys' => array_keys($respuesta),
        ]);

        $estado = $respuesta['estado'] ?? null;
        $autorizacion = $respuesta['numeroAutorizacion'] ?? null;
        $fechaAut = $this->parsearFechaAutorizacion($respuesta['fechaAutorizacion'] ?? null);

        if (strtoupper((string) $estado) === 'AUTORIZADO') {
            $factura->update([
                'estado' => 'AUTORIZADO',
                'numero_autorizacion' => $autorizacion,
                'fecha_autorizacion' => $fechaAut,
                'motivo_rechazo' => null,
            ]);
        } else {
            $estadoFinal = strtoupper((string) $estado);

            // Si el SRI dice que está procesando, intentar consultar autorización
            if (in_array($estadoFinal, ['RECIBIDA', 'PROCESANDOSE', 'EN PROCESO'])) {
                // Si la respuesta trae mensajes de error, es un rechazo
                // (MasterOffline a veces devuelve estado RECIBIDA pero con error del SRI)
                $mensajeRecibida = $this->extraerMensajeSri($respuesta);

                if ($mensajeRecibida) {
                    [$mensajeAmigable, $detalleTecnico] = $this->traducirMensajeSri($mensajeRecibida);
                    $factura->update([
                        'estado' => 'NO AUTORIZADO',
                        'motivo_rechazo' => $mensajeAmigable,
                    ]);
                    if ($detalleTecnico) {
                        Log::warning("SRI detalle técnico factura {$factura->id}: {$detalleTecnico}");
                    }
                    $this->guardarMensajesSri($factura, $respuesta);
                    return;
                }

                // No hay mensaje de error: realmente está procesando
                // Esperar brevemente y consultar estado de autorización
                sleep(3);
                $estadoConsulta = $this->consultarYActualizar($factura);
                if (in_array($estadoConsulta, ['AUTORIZADO', 'NO AUTORIZADO'])) {
                    $this->guardarMensajesSri($factura, $respuesta);
                    return;
                }

                // Si sigue procesando, guardar con mensaje informativo
                $factura->update([
                    'estado' => 'PROCESANDOSE',
                    'motivo_rechazo' => 'El comprobante fue recibido por el SRI y está siendo procesado. Use el botón "Consultar Estado SRI" para verificar la autorización.',
                ]);
                $this->guardarMensajesSri($factura, $respuesta);
                return;
            }

            $mensajeRaw = $this->extraerMensajeSri($respuesta);
            [$mensajeAmigable, $detalleTecnico] = $this->traducirMensajeSri($mensajeRaw);

            // DEVUELTA y RECHAZADA son estados de rechazo del SRI
            if (in_array($estadoFinal, ['DEVUELTA', 'RECHAZADA', ''])) {
                $estadoFinal = 'NO AUTORIZADO';
            }

            $factura->update([
                'estado' => $estadoFinal ?: 'NO AUTORIZADO',
                'motivo_rechazo' => $mensajeAmigable,
            ]);

            if ($detalleTecnico) {
                Log::warning("SRI detalle técnico factura {$factura->id}: {$detalleTecnico}");
            }
        }

        // Guardar mensajes del SRI en tabla polimórfica
        $this->guardarMensajesSri($factura, $respuesta);
    }

    /**
     * Extrae el mensaje de error/información de la respuesta del SRI.
     */
    private function extraerMensajeSri(array $respuesta): ?string
    {
        $mensaje = $respuesta['mensaje'] ?? null;

        // Si 'mensaje' es un objeto/array en vez de string, ignorarlo y buscar en 'mensajes'
        if (is_object($mensaje) || is_array($mensaje)) {
            $mensaje = null;
        }

        if (!$mensaje && !empty($respuesta['mensajes'])) {
            $msgs = $respuesta['mensajes'];
            $msgs = is_object($msgs) ? (array) $msgs : $msgs;
            $firstMsg = isset($msgs['mensaje']) ? $msgs : (is_array($msgs) ? reset($msgs) : null);
            if (is_object($firstMsg)) {
                $firstMsg = (array) $firstMsg;
            }
            if ($firstMsg && is_array($firstMsg)) {
                $rawMsg = $firstMsg['mensaje'] ?? $firstMsg['MENSAJE'] ?? '';
                $mensaje = is_string($rawMsg) ? $rawMsg : (is_object($rawMsg) || is_array($rawMsg) ? json_encode($rawMsg) : (string) $rawMsg);
                $info = $firstMsg['informacionAdicional'] ?? $firstMsg['INFORMACIONADICIONAL'] ?? '';
                if (is_object($info) || is_array($info)) {
                    $info = json_encode($info);
                }
                if ($info) {
                    $mensaje .= ': ' . $info;
                }
            }
        }

        return $mensaje;
    }

    /**
     * Traduce un mensaje técnico del SRI a un mensaje amigable para el usuario.
     * Devuelve [mensaje_amigable, detalle_tecnico].
     */
    private function traducirMensajeSri(?string $mensajeTecnico): array
    {
        if (!$mensajeTecnico) {
            return ['Error desconocido al procesar el comprobante.', null];
        }

        $original = $mensajeTecnico;

        // Limpiar prefijos técnicos del SRI para facilitar el match
        $mensajeLimpio = preg_replace('/^ERROR EN DIFERENCIAS:\s*---\s*Inventario de errores\s*---\s*[-–]\s*/i', '', $mensajeTecnico);
        $mensajeLimpio = preg_replace('/^Validador\w+:\s*/i', '', $mensajeLimpio);

        // Patrones de errores comunes del SRI y sus traducciones amigables
        $traducciones = [
            // Errores de estructura XML
            [
                'patron' => '/ARCHIVO NO CUMPLE ESTRUCTURA XML/i',
                'mensaje' => 'El comprobante tiene un error en su estructura. Revise que todos los campos obligatorios estén completos y correctos.',
            ],
            [
                'patron' => '/cvc-complex-type.*Invalid content.*element\s+[\'"]?(\w+)[\'"]?/i',
                'mensaje' => 'El comprobante contiene un campo no válido o en posición incorrecta: "$1". Verifique los datos del comprobante e intente nuevamente.',
            ],
            [
                'patron' => '/cvc-type.*not a valid value.*for\s+[\'"]?(\w+)[\'"]?/i',
                'mensaje' => 'El valor ingresado en el campo "$1" no es válido. Revise y corrija el dato.',
            ],
            [
                'patron' => '/cvc-minLength-valid.*value.*length\s*=\s*[\'"]?0[\'"]?.*(\w+)/i',
                'mensaje' => 'El campo "$1" está vacío pero es obligatorio. Complete el campo e intente nuevamente.',
            ],
            [
                'patron' => '/cvc-pattern-valid.*value\s+[\'"]?([^"\']+)[\'"]?.*pattern.*for\s+[\'"]?(\w+)/i',
                'mensaje' => 'El valor "$1" en el campo "$2" no tiene el formato correcto. Revise el dato.',
            ],
            [
                'patron' => '/cvc-enumeration-valid.*value\s+[\'"]?([^"\']+)[\'"]?.*enumeration.*for\s+[\'"]?(\w+)/i',
                'mensaje' => 'El valor "$1" no es una opción válida para "$2". Seleccione un valor de la lista permitida.',
            ],
            // ===== ERRORES DE FIRMA ELECTRÓNICA (MasterOffline internos - FirmaElectronica.java) =====
            [
                'patron' => '/GESTOR DE CLAVES NO SE HA OBTENIDO|keystore.*password|password.*incorrect|pkcs12.*mac.*invalid|mac verify.*fail|wrong password|clave.*firma.*incorrect/i',
                'mensaje' => 'La contraseña del certificado de firma electrónica (.p12) es incorrecta. Verifique la clave del certificado en la configuración del emisor.',
            ],
            [
                'patron' => '/FALLO OBTENIENDO EL LISTADO DE CERTIFICADOS/i',
                'mensaje' => 'No se pudo leer el archivo de firma electrónica (.p12). Verifique que el archivo exista y no esté corrupto en la configuración del emisor.',
            ],
            [
                'patron' => '/NO EXISTE NINGUN CERTIFICADO PARA FIRMAR/i',
                'mensaje' => 'El archivo de firma electrónica (.p12) no contiene un certificado válido para firmar. Verifique el archivo en la configuración del emisor.',
            ],
            [
                'patron' => '/ERROR AL ACCEDER AL ALMACEN/i',
                'mensaje' => 'Error al acceder al almacén de certificados. Verifique que el archivo de firma (.p12) y su contraseña sean correctos.',
            ],
            [
                'patron' => '/ERROR PROCESANDO EL XML A FIRMAR/i',
                'mensaje' => 'Error interno al preparar el comprobante para firmarlo. Verifique que los datos del comprobante estén completos.',
            ],
            [
                'patron' => '/ERROR REALIZANDO LA FIRMA DEL COMPROBANTE/i',
                'mensaje' => 'Error al firmar el comprobante electrónico. Verifique que el certificado de firma (.p12) sea válido y esté vigente.',
            ],
            [
                'patron' => '/FIRMA INVALIDA/i',
                'mensaje' => 'La firma electrónica es inválida. Posibles causas: el certificado (.p12) no pertenece al RUC del emisor (ej: firma de persona natural usada para empresa), el certificado está caducado, o la firma es incorrecta. Verifique que el certificado corresponda al emisor.',
            ],
            [
                'patron' => '/CERTIFICADO NO VIGENTE|certificado.*caducado|firma.*expirada|certificado.*revocado/i',
                'mensaje' => 'El certificado de firma electrónica ha expirado o fue revocado. Renueve su firma electrónica y actualícela en la configuración.',
            ],
            [
                'patron' => '/firma.*no.*coincide.*RUC|RUC.*firma.*diferente|certificado.*no.*corresponde.*emisor/i',
                'mensaje' => 'El certificado de firma electrónica (.p12) no corresponde al RUC del emisor. Verifique que está usando el certificado correcto (no confunda firma de persona natural con persona jurídica).',
            ],
            [
                'patron' => '/no se encontr[oó].*certificado|no se puede convertir.*certificad.*X509/i',
                'mensaje' => 'No se encontró el certificado de firma electrónica o el archivo .p12 es inválido. Verifique el archivo en la configuración del emisor.',
            ],
            [
                'patron' => '/keystore|p12.*error|firma.*error|error.*firmar|no se pudo firmar|firma.*no.*encontr/i',
                'mensaje' => 'Error al firmar el comprobante. Verifique que el archivo de firma (.p12) y su contraseña sean correctos en la configuración del emisor.',
            ],

            // ===== ERRORES DE COMUNICACIÓN SRI (MasterOffline - Facturacion.java) =====
            [
                'patron' => '/PROBLEMAS EN LA COMUNICACION CON EL SERVICIO DE RECEPCION/i',
                'mensaje' => 'No se pudo conectar con el servicio de recepción del SRI. Verifique la conexión a internet e intente nuevamente.',
            ],
            [
                'patron' => '/PROBLEMAS EN LA COMUNICACION CON EL SERVICIO DE AUTORIZACION/i',
                'mensaje' => 'No se pudo conectar con el servicio de autorización del SRI. Verifique la conexión a internet e intente nuevamente.',
            ],
            [
                'patron' => '/PROBLEMAS CREANDO LA RESPUESTA DE RECEPCION/i',
                'mensaje' => 'Error interno al procesar la respuesta del SRI. Intente nuevamente.',
            ],
            [
                'patron' => '/RESPUESTA DE RECEPCION CON ARREGLO DE COMPROBANTES VACIOS/i',
                'mensaje' => 'El SRI devolvió una respuesta vacía. Intente enviar el comprobante nuevamente.',
            ],
            [
                'patron' => '/COMPROBANTE SE ENCUENTRA EN PROCESO.*NO HA SIDO AUTORIZADO.*NUNCA SE HA ENVIADO/i',
                'mensaje' => 'El comprobante aún no ha sido autorizado por el SRI o nunca fue enviado. Use "Consultar Estado SRI" para verificar.',
            ],
            [
                'patron' => '/COMPROBANTE SE ENCUENTRA SIENDO PROCESADO.*ENVIAR NUEVAMENTE/i',
                'mensaje' => 'El comprobante se encuentra siendo procesado por el SRI. Espere 2 minutos e intente consultar su estado.',
            ],
            [
                'patron' => '/ERROR CREANDO EL XML DE RESPUESTA DE AUTORIZACION/i',
                'mensaje' => 'Error interno al procesar la respuesta de autorización del SRI. Intente nuevamente.',
            ],

            // ===== ERRORES DEL SERVICIO SOAP (MasterOffline - ProcesarComprobanteElectronico.java) =====
            [
                'patron' => '/ERROR CREANDO EL XML DEL DOCUMENTO RECIBIDO/i',
                'mensaje' => 'Error interno al generar el XML del comprobante. Verifique que todos los datos del documento estén completos y correctos.',
            ],
            [
                'patron' => '/ERROR PROCESANDO EL XML DEL COMPROBANTE ELECTRONICO/i',
                'mensaje' => 'Error interno al procesar el comprobante electrónico. Intente nuevamente.',
            ],
            [
                'patron' => '/ERROR CONVIRTIENDO EL STRING XML A Document/i',
                'mensaje' => 'Error interno al leer el XML del comprobante. El formato del XML es inválido.',
            ],
            [
                'patron' => '/ERROR CREANDO EL XML DEL COMPROBANTE\s+\d/i',
                'mensaje' => 'Error interno al crear el XML de uno de los comprobantes del lote. Verifique los datos del documento.',
            ],
            [
                'patron' => '/ERROR CONVIRTIENDO A STRING EL XML DEL COMPROBANTE/i',
                'mensaje' => 'Error interno al convertir el XML firmado del comprobante. Contacte soporte técnico.',
            ],
            [
                'patron' => '/ERROR CONVIRTIENDO XML DE RESPUESTA A TEXTO/i',
                'mensaje' => 'Error interno al procesar la respuesta. El comprobante puede haberse autorizado. Use "Consultar Estado SRI" para verificar.',
            ],
            [
                'patron' => '/ERROR GUARDANDO EL XML AUTORIZADO/i',
                'mensaje' => 'El comprobante fue autorizado pero hubo un error al guardar el archivo XML. Contacte soporte técnico.',
            ],
            [
                'patron' => '/ERROR GUARDANDO EL PDF|ERROR GUARDANDO EL PDF AUTORIZADO|ERROR GUARDANDO EL PDF PROFORMA/i',
                'mensaje' => 'Error al generar el PDF del comprobante (RIDE). El comprobante fue procesado correctamente.',
            ],
            [
                'patron' => '/ERROR ENVIANDO EL CORREO AL CLIENTE/i',
                'mensaje' => 'Error al enviar el correo electrónico al cliente. Verifique la configuración de correo del emisor.',
            ],
            [
                'patron' => '/EL XML NO EXISTE/i',
                'mensaje' => 'El archivo XML del comprobante autorizado no se encontró en el servidor. Contacte soporte técnico.',
            ],

            // ===== ERRORES SRI: RUC Y EMISOR (Códigos 2, 10, 27, 37, 57, 63) =====
            [
                'patron' => '/RUC.*NO EXISTE|contribuyente no encontrado/i',
                'mensaje' => 'El RUC del emisor no está registrado en el SRI. Verifique el RUC en la configuración del emisor.',
            ],
            [
                'patron' => '/RUC.*se encuentra.*NO ACTIVO|RUC.*no.*activo/i',
                'mensaje' => 'El RUC del emisor no se encuentra activo en el SRI. Verifique el estado de su RUC.',
            ],
            [
                'patron' => '/RUC.*clausurado|clausurado.*proceso.*control/i',
                'mensaje' => 'El RUC del emisor se encuentra clausurado por procesos de control del SRI. Contacte al SRI para regularizar su situación.',
            ],
            [
                'patron' => '/clase.*contribuyente.*no puede emitir/i',
                'mensaje' => 'Su tipo de contribuyente no está habilitado para emitir comprobantes electrónicos. Contacte al SRI.',
            ],
            [
                'patron' => '/RUC.*sin autorizaci[oó]n.*emisi[oó]n|no cuenta con.*solicitud.*emisi[oó]n/i',
                'mensaje' => 'El RUC del emisor no tiene autorización para emitir comprobantes electrónicos. Solicite la certificación en el portal del SRI (Servicios en línea).',
            ],
            [
                'patron' => '/autorizaci[oó]n.*suspendida|emisi[oó]n.*suspendida/i',
                'mensaje' => 'La autorización para emitir comprobantes electrónicos está suspendida. Contacte al SRI para verificar el estado de su certificación.',
            ],

            // ===== ERRORES SRI: ESTABLECIMIENTO (Códigos 10, 56) =====
            [
                'patron' => '/ESTABLECIMIENTO.*NO REGISTRADO|establecimiento.*cerrado|establecimiento.*clausurado/i',
                'mensaje' => 'El establecimiento no está registrado o se encuentra cerrado en el SRI. Verifique el código de establecimiento.',
            ],

            // ===== ERRORES SRI: CLAVE DE ACCESO (Códigos 43, 45, 58, 70, 80) =====
            [
                'patron' => '/CLAVE ACCESO REGISTRADA/i',
                'mensaje' => 'Este comprobante ya fue enviado anteriormente al SRI. Use "Consultar Estado SRI" para verificar su autorización.',
            ],
            [
                'patron' => '/SECUENCIAL.*REGISTRADO|secuencial.*ya se encuentra registrad|numero.*comprobante.*duplicado/i',
                'mensaje' => 'El número secuencial del comprobante ya existe en el SRI. Verifique el secuencial.',
            ],
            [
                'patron' => '/CLAVE ACCESO DEBE TENER 49|clave.*acceso.*componentes.*diferentes|error.*estructura.*clave.*acceso/i',
                'mensaje' => 'Error interno: la clave de acceso es inválida (debe tener 49 dígitos y sus componentes deben coincidir con el comprobante). Contacte soporte técnico.',
            ],
            [
                'patron' => '/clave.*acceso.*en procesamiento|clave.*acceso.*no ha terminado/i',
                'mensaje' => 'El comprobante aún se encuentra en procesamiento en el SRI. Espere unos minutos y consulte su estado antes de reenviarlo.',
            ],
            [
                'patron' => '/claveAccesoComprobante.*vac[ií]/i',
                'mensaje' => 'Error interno: la clave de acceso está vacía. Contacte soporte técnico.',
            ],

            // ===== ERRORES SRI: DOCUMENTO XML (Códigos 26, 35, 36, 47, 48, 49) =====
            [
                'patron' => '/tama[ñn]o.*m[aá]ximo.*superado|tama[ñn]o.*archivo.*supera/i',
                'mensaje' => 'El archivo XML del comprobante supera el tamaño máximo permitido (320 KB individual, 500 KB por lote). Reduzca la cantidad de ítems.',
            ],
            [
                'patron' => '/DOCUMENTO INV[AÁ]LIDO|XML no pasa validaci[oó]n.*esquema/i',
                'mensaje' => 'El documento XML no es válido. Revise que todos los campos obligatorios estén completos y con el formato correcto.',
            ],
            [
                'patron' => '/versi[oó]n.*esquema.*descontinuada|versi[oó]n.*esquema.*no.*correcta/i',
                'mensaje' => 'La versión del esquema XML está descontinuada. Contacte soporte técnico para actualizar el formato de los comprobantes.',
            ],
            [
                'patron' => '/tipo.*comprobante.*no existe|tipo.*comprobante.*no.*cat[aá]logo/i',
                'mensaje' => 'El tipo de comprobante enviado no es válido. Verifique el código del tipo de documento.',
            ],
            [
                'patron' => '/esquema XSD no existe|esquema.*tipo.*comprobante.*no existe/i',
                'mensaje' => 'El esquema de validación para este tipo de comprobante no existe en el SRI. Contacte soporte técnico.',
            ],
            [
                'patron' => '/argumentos.*nulos|argumentos.*WS.*nulos/i',
                'mensaje' => 'Error interno: el comprobante se envió con datos vacíos. Contacte soporte técnico.',
            ],

            // ===== ERRORES SRI: ACUERDO MEDIOS ELECTRÓNICOS (Código 28) =====
            [
                'patron' => '/acuerdo.*medios.*electr[oó]nicos.*no.*aceptado/i',
                'mensaje' => 'El contribuyente debe aceptar el acuerdo de medios electrónicos en el portal del SRI antes de emitir comprobantes.',
            ],

            // ===== ERRORES SRI: RETENCIONES =====
            [
                'patron' => '/codigo.*retencion.*no.*valido|codigo.*retencion.*no existe|no existe.*c[oó]digo.*retenci[oó]n|no.*vigente.*c[oó]digo.*retenci[oó]n/i',
                'mensaje' => 'El código de retención utilizado no es válido o no está vigente. Revise la tabla de códigos de retención en Configuración y actualice los códigos.',
            ],
            [
                'patron' => '/porcentaje.*retencion.*no.*coincide|porcentaje.*no.*valido|porcentaje.*retenci[oó]n.*no.*vigente/i',
                'mensaje' => 'El porcentaje de retención no es correcto para el código seleccionado. Verifique el porcentaje en la configuración de retenciones.',
            ],
            [
                'patron' => '/comprobante.*no.*autorizado.*solicitud.*emisi[oó]n/i',
                'mensaje' => 'Este tipo de comprobante no ha sido autorizado en su solicitud de emisión. Verifique en el portal del SRI que tenga habilitado este tipo de documento.',
            ],

            // ===== ERRORES SRI: FECHAS (Códigos 65, 67, 82) =====
            [
                'patron' => '/FECHA.*EMISION.*EXTEMPORANEA|fecha.*fuera.*rango|fecha.*emisi[oó]n.*no fue enviado.*acuerdo.*tiempo/i',
                'mensaje' => 'La fecha de emisión del comprobante está fuera del rango permitido por el SRI.',
            ],
            [
                'patron' => '/fecha.*inv[aá]lida|error.*formato.*fecha/i',
                'mensaje' => 'La fecha del comprobante tiene un formato inválido. El formato correcto es dd/mm/aaaa.',
            ],
            [
                'patron' => '/fecha.*inicio.*transporte.*menor.*fecha.*emisi[oó]n|fecha.*inicio.*transporte/i',
                'mensaje' => 'La fecha de inicio de transporte no puede ser anterior a la fecha de emisión de la guía de remisión.',
            ],

            // ===== ERRORES SRI: CÁLCULOS Y DIFERENCIAS (Código 52, 92) =====
            [
                'patron' => '/error en diferencias|error.*c[aá]lculos.*comprobante/i',
                'mensaje' => 'Existen diferencias en los cálculos del comprobante (subtotales, impuestos o totales no cuadran). Revise los valores del documento.',
            ],
            [
                'patron' => '/error.*validar.*monto.*devoluci[oó]n.*IVA|valor.*devoluci[oó]n.*IVA.*no corresponde/i',
                'mensaje' => 'El valor de devolución del IVA registrado no corresponde al autorizado por el servicio web DIG.',
            ],

            // ===== ERRORES SRI: IDENTIFICACIÓN RECEPTOR (Códigos 59, 62, 69) =====
            [
                'patron' => '/identificaci[oó]n.*no existe|n[uú]mero.*identificaci[oó]n.*adquirente.*no existe/i',
                'mensaje' => 'El número de identificación del cliente/receptor no existe. Verifique que esté correcto.',
            ],
            [
                'patron' => '/identificaci[oó]n.*incorrecta|c[eé]dula.*no pasa.*d[ií]gito verificador|identificaci[oó]n.*receptor/i',
                'mensaje' => 'La identificación del cliente/receptor es incorrecta (no pasa la validación del dígito verificador). Verifique el número.',
            ],
            [
                'patron' => '/documento.*sustento.*no existe.*electr[oó]nico|c[oó]digo.*documento.*sustento/i',
                'mensaje' => 'El documento de sustento referenciado no existe como comprobante electrónico o su código no es válido.',
            ],

            // ===== ERRORES SRI: AMBIENTE (Código 60) =====
            [
                'patron' => '/AMBIENTE.*NO COINCIDE/i',
                'mensaje' => 'El ambiente de facturación no coincide (pruebas vs producción). Verifique la configuración del emisor.',
            ],

            // ===== ERRORES SRI: ERROR INTERNO SERVIDOR (Código 50) =====
            [
                'patron' => '/error interno general|error inesperado.*servidor/i',
                'mensaje' => 'Ocurrió un error interno en el servidor del SRI. Intente nuevamente en unos minutos.',
            ],

            // ===== ERRORES DE CONEXIÓN =====
            [
                'patron' => '/Error connecting to|Connection refused|timeout|SOAP-ERROR.*Parsing|Could not connect/i',
                'mensaje' => 'No se pudo conectar con el servicio de facturación (MasterOffline). Verifique que el servicio esté en ejecución e intente nuevamente.',
            ],

            // ===== ERRORES GENÉRICOS DE MASTEROFFLINE =====
            [
                'patron' => '/Errores? ocurridos? durante el proceso/i',
                'mensaje' => 'Ocurrió un error durante el envío al SRI.',
            ],
        ];

        foreach ($traducciones as $t) {
            if (preg_match($t['patron'], $mensajeLimpio, $matches)) {
                $amigable = $t['mensaje'];
                // Reemplazar capturas $1, $2, etc.
                foreach ($matches as $i => $match) {
                    if ($i > 0) {
                        $amigable = str_replace('$' . $i, $match, $amigable);
                    }
                }
                // Incluir mensaje técnico del SRI para diagnóstico
                if ($original && $original !== $amigable) {
                    $amigable .= "\n\nDetalle SRI: " . $original;
                }
                return [$amigable, $original];
            }
        }

        // Si no hay traducción, limpiar el mensaje técnico para el usuario
        $limpio = $mensajeTecnico;
        // Quitar prefijos técnicos del SRI
        $limpio = preg_replace('/^Errores? ocurridos? durante.*?[!.]\s*/i', '', $limpio);
        $limpio = preg_replace('/^ERROR EN DIFERENCIAS:\s*---\s*Inventario de errores\s*---\s*[-–]\s*/i', '', $limpio);
        $limpio = preg_replace('/^Validador\w+:\s*/i', '', $limpio);
        return [$limpio ?: $mensajeTecnico, $original];
    }

    /**
     * Aplana la respuesta SOAP de MasterOffline que puede venir anidada.
     * MasterOffline puede devolver:
     *   {return: {estado: "RECIBIDA", autorizaciones: {autorizacion: {estado: "AUTORIZADO", ...}}}}
     *   {return: {estado: "AUTORIZADO", numeroAutorizacion: "...", ...}}
     *   {estado: "AUTORIZADO", ...}
     */
    private function aplanarRespuesta(mixed $resultado): array
    {
        $data = is_object($resultado) ? (array) $resultado : (array) $resultado;

        // Navegar la propiedad 'return' si existe (respuesta SOAP envuelta)
        if (isset($data['return'])) {
            $data = is_object($data['return']) ? (array) $data['return'] : (array) $data['return'];
        }

        // Normalizar claves a camelCase (MasterOffline puede devolver en mayúsculas)
        $mapa = [
            'ESTADO' => 'estado',
            'ESTADOCOMPROBANTE' => 'estado',
            'NUMEROAUTORIZACION' => 'numeroAutorizacion',
            'FECHAAUTORIZACION' => 'fechaAutorizacion',
            'MENSAJE' => 'mensaje',
            'MENSAJES' => 'mensajes',
            'ENVIOSRI' => 'envioSRI',
            'CLAVEACCESOCOMPROBANTE' => 'claveAccesoComprobante',
            'CLAVEACCESOCONSULTADA' => 'claveAccesoConsultada',
            'AUTORIZACIONES' => 'autorizaciones',
        ];

        $normalizada = [];
        foreach ($data as $key => $value) {
            $keyNorm = $mapa[strtoupper($key)] ?? $key;
            $normalizada[$keyNorm] = $value;
        }

        // Si la respuesta tiene autorizaciones anidadas, extraer los datos de autorización
        // MasterOffline puede devolver: {estado: "RECIBIDA", autorizaciones: {autorizacion: {estado: "AUTORIZADO", ...}}}
        // En ese caso, el estado real de autorización está dentro de autorizaciones.autorizacion
        if (isset($normalizada['autorizaciones'])) {
            $autorizaciones = is_object($normalizada['autorizaciones'])
                ? (array) $normalizada['autorizaciones']
                : (array) $normalizada['autorizaciones'];

            $autorizacion = $autorizaciones['autorizacion'] ?? null;
            if (is_array($autorizacion) && isset($autorizacion[0])) {
                $autorizacion = $autorizacion[0];
            }
            if (is_object($autorizacion)) {
                $autorizacion = (array) $autorizacion;
            }

            if ($autorizacion) {
                $estadoAut = strtoupper((string) ($autorizacion['estado'] ?? $autorizacion['ESTADO'] ?? ''));
                $estadoTop = strtoupper((string) ($normalizada['estado'] ?? ''));

                // Si el estado de autorización es más definitivo que el top-level, usarlo
                if (in_array($estadoAut, ['AUTORIZADO', 'NO AUTORIZADO', 'RECHAZADA', 'DEVUELTA'])) {
                    $normalizada['estado'] = $estadoAut;
                    $normalizada['numeroAutorizacion'] = $autorizacion['numeroAutorizacion']
                        ?? $autorizacion['NUMEROAUTORIZACION']
                        ?? $normalizada['numeroAutorizacion'] ?? null;
                    $normalizada['fechaAutorizacion'] = $autorizacion['fechaAutorizacion']
                        ?? $autorizacion['FECHAAUTORIZACION']
                        ?? $normalizada['fechaAutorizacion'] ?? null;

                    // Extraer mensajes de la autorización si existen
                    $mensajesAut = $autorizacion['mensajes'] ?? $autorizacion['MENSAJES'] ?? null;
                    if ($mensajesAut && !isset($normalizada['mensajes'])) {
                        $normalizada['mensajes'] = $mensajesAut;
                    }
                }

                Log::info("aplanarRespuesta: estadoTop={$estadoTop}, estadoAutorizacion={$estadoAut}");
            }
        }

        return $normalizada;
    }

    /**
     * Guarda los mensajes del SRI en la tabla polimórfica mensajes.
     */
    private function guardarMensajesSri(Model $comprobante, array $respuesta): void
    {
        $mensajes = $respuesta['mensajes'] ?? null;

        if (!$mensajes) {
            // Si hay un mensaje simple en la respuesta, guardarlo como mensaje
            if (!empty($respuesta['mensaje'])) {
                $rawMsg = is_string($respuesta['mensaje']) ? $respuesta['mensaje'] : json_encode($respuesta['mensaje']);
                [$amigable, $tecnico] = $this->traducirMensajeSri($rawMsg);
                $infoAdicional = $respuesta['informacionAdicional'] ?? null;
                if ($tecnico) {
                    $infoAdicional = $tecnico . ($infoAdicional ? ' | ' . $infoAdicional : '');
                }
                $comprobante->mensajes()->create([
                    'identificador' => '000',
                    'mensaje' => $amigable,
                    'informacion_adicional' => $infoAdicional,
                    'tipo' => strtoupper((string) ($respuesta['estado'] ?? '')) === 'AUTORIZADO' ? 'INFORMATIVO' : 'ERROR',
                ]);
            }
            return;
        }

        // Normalizar: puede ser un solo objeto o un array de mensajes
        if (is_object($mensajes)) {
            $mensajes = (array) $mensajes;
            // Si tiene propiedad 'mensaje', es un wrapper {mensaje: [...]}
            if (isset($mensajes['mensaje'])) {
                $mensajes = is_array($mensajes['mensaje']) ? $mensajes['mensaje'] : [$mensajes['mensaje']];
            } else {
                $mensajes = [$mensajes];
            }
        }

        if (!is_array($mensajes)) {
            return;
        }

        foreach ($mensajes as $msg) {
            $msg = is_object($msg) ? (array) $msg : (array) $msg;
            $rawMsg = $msg['mensaje'] ?? $msg['MENSAJE'] ?? 'Sin mensaje';
            if (!is_string($rawMsg)) {
                $rawMsg = json_encode($rawMsg);
            }
            $rawInfo = $msg['informacionAdicional'] ?? $msg['INFORMACIONADICIONAL'] ?? null;
            if (is_object($rawInfo) || is_array($rawInfo)) {
                $rawInfo = json_encode($rawInfo);
            }

            // Traducir el mensaje + info adicional juntos para mejor detección
            $textoCompleto = $rawMsg . ($rawInfo ? ': ' . $rawInfo : '');
            [$amigable, $tecnico] = $this->traducirMensajeSri($textoCompleto);

            $infoFinal = $rawInfo;
            if ($tecnico) {
                // Guardar el detalle técnico original en informacion_adicional
                $infoFinal = $tecnico;
            }

            $comprobante->mensajes()->create([
                'identificador' => $msg['identificador'] ?? $msg['IDENTIFICADOR'] ?? '000',
                'mensaje' => $amigable,
                'informacion_adicional' => $infoFinal,
                'tipo' => $msg['tipo'] ?? $msg['TIPO'] ?? 'ERROR',
            ]);
        }
    }

    /**
     * Serializa la respuesta SOAP para logging.
     */
    private function serializarRespuesta(mixed $resultado): mixed
    {
        if (is_object($resultado)) {
            return json_decode(json_encode($resultado), true);
        }
        return $resultado;
    }

    /**
     * Genera el XML de la factura según norma técnica SRI v2.1.0.
     */
    private function generarXmlFactura(Factura $factura): string
    {
        $factura->load(['emisor', 'establecimiento', 'ptoEmision', 'cliente', 'detalles.impuestos', 'camposAdicionales']);

        $emisor = $factura->emisor;
        $establecimiento = $factura->establecimiento;
        $cliente = $factura->cliente;

        $xml = new \XMLWriter();
        $xml->openMemory();
        $xml->startDocument('1.0', 'UTF-8');

        $xml->startElement('factura');
        $xml->writeAttribute('id', 'comprobante');
        $xml->writeAttribute('version', '2.1.0');

        // -- infoTributaria --
        $this->escribirInfoTributaria($xml, $factura, '01');

        // -- infoFactura --
        $xml->startElement('infoFactura');
        $xml->writeElement('fechaEmision', $factura->fecha_emision->format('d/m/Y'));
        $xml->writeElement('dirEstablecimiento', $establecimiento->direccion ?? $emisor->direccion_matriz);
        if ($emisor->contribuyente_especial) {
            $xml->writeElement('contribuyenteEspecial', $emisor->contribuyente_especial);
        }
        if ($emisor->obligado_contabilidad) {
            $xml->writeElement('obligadoContabilidad', 'SI');
        } else {
            $xml->writeElement('obligadoContabilidad', 'NO');
        }
        $xml->writeElement('tipoIdentificacionComprador', $cliente->tipo_identificacion->value);
        if ($factura->guia_remision) {
            $xml->writeElement('guiaRemision', $factura->guia_remision);
        }
        $xml->writeElement('razonSocialComprador', $cliente->razon_social);
        $xml->writeElement('identificacionComprador', $cliente->identificacion);
        if ($cliente->direccion) {
            $xml->writeElement('direccionComprador', $cliente->direccion);
        }
        $xml->writeElement('totalSinImpuestos', number_format((float) $factura->total_sin_impuestos, 2, '.', ''));
        $xml->writeElement('totalDescuento', number_format((float) $factura->total_descuento, 2, '.', ''));

        // totalConImpuestos
        $xml->startElement('totalConImpuestos');
        $impuestosAgrupados = $this->agruparImpuestos($factura->detalles);
        foreach ($impuestosAgrupados as $imp) {
            $xml->startElement('totalImpuesto');
            $xml->writeElement('codigo', $imp['codigo']);
            $xml->writeElement('codigoPorcentaje', $imp['codigo_porcentaje']);
            $xml->writeElement('baseImponible', number_format($imp['base_imponible'], 2, '.', ''));
            $xml->writeElement('valor', number_format($imp['valor'], 2, '.', ''));
            $xml->endElement(); // totalImpuesto
        }
        $xml->endElement(); // totalConImpuestos

        $xml->writeElement('propina', number_format((float) $factura->propina, 2, '.', ''));
        $xml->writeElement('importeTotal', number_format((float) $factura->importe_total, 2, '.', ''));
        $xml->writeElement('moneda', $factura->moneda ?? 'DOLAR');

        // pagos
        $xml->startElement('pagos');
        $xml->startElement('pago');
        $xml->writeElement('formaPago', $factura->forma_pago ?? '01');
        $xml->writeElement('total', number_format((float) $factura->importe_total, 2, '.', ''));
        if ($factura->forma_pago_plazo) {
            $xml->writeElement('plazo', $factura->forma_pago_plazo);
            $xml->writeElement('unidadTiempo', $factura->forma_pago_unidad_tiempo ?? 'dias');
        }
        $xml->endElement(); // pago
        $xml->endElement(); // pagos

        $xml->endElement(); // infoFactura

        // -- detalles --
        $this->escribirDetalles($xml, $factura->detalles);

        // -- infoAdicional --
        $this->escribirInfoAdicional($xml, $factura);

        $xml->endElement(); // factura
        $xml->endDocument();

        return $xml->outputMemory();
    }

    /**
     * Agrupa los impuestos de todos los detalles por codigo + codigo_porcentaje.
     */
    private function agruparImpuestos($detalles): array
    {
        $agrupados = [];

        foreach ($detalles as $detalle) {
            foreach ($detalle->impuestos ?? [] as $imp) {
                $key = $imp->codigo . '_' . $imp->codigo_porcentaje;
                if (!isset($agrupados[$key])) {
                    $agrupados[$key] = [
                        'codigo' => $imp->codigo,
                        'codigo_porcentaje' => $imp->codigo_porcentaje,
                        'tarifa' => (float) $imp->tarifa,
                        'base_imponible' => 0,
                        'valor' => 0,
                    ];
                }
                $agrupados[$key]['base_imponible'] += (float) $imp->base_imponible;
                $agrupados[$key]['valor'] += (float) $imp->valor;
            }
        }

        return array_values($agrupados);
    }

    /**
     * Escribe los campos comunes de infoTributaria para cualquier comprobante.
     */
    private function escribirInfoTributaria(\XMLWriter $xml, Model $comprobante, string $codDoc): void
    {
        $emisor = $comprobante->emisor;
        $establecimiento = $comprobante->establecimiento;
        $ptoEmision = $comprobante->ptoEmision;
        $secuencial = str_pad($comprobante->secuencial, 9, '0', STR_PAD_LEFT);

        $xml->startElement('infoTributaria');
        $xml->writeElement('ambiente', $comprobante->ambiente ?? $emisor->ambiente->value);
        $xml->writeElement('tipoEmision', '1');
        $xml->writeElement('razonSocial', $emisor->razon_social);
        if ($emisor->nombre_comercial) {
            $xml->writeElement('nombreComercial', $emisor->nombre_comercial);
        }
        $xml->writeElement('ruc', $emisor->ruc);
        $xml->writeElement('claveAcceso', $comprobante->clave_acceso);
        $xml->writeElement('codDoc', $codDoc);
        $xml->writeElement('estab', $establecimiento->codigo);
        $xml->writeElement('ptoEmi', $ptoEmision->codigo);
        $xml->writeElement('secuencial', $secuencial);
        $xml->writeElement('dirMatriz', $emisor->direccion_matriz);
        if ($emisor->agente_retencion) {
            $xml->writeElement('agenteRetencion', $emisor->agente_retencion);
        }
        $leyendaRimpe = $emisor->regimen?->leyendaRimpeXml();
        if ($leyendaRimpe) {
            $xml->writeElement('contribuyenteRimpe', $leyendaRimpe);
        }
        $xml->endElement(); // infoTributaria
    }

    /**
     * Escribe campos adicionales e info adicional para comprobantes con cliente.
     */
    private function escribirInfoAdicional(\XMLWriter $xml, Model $comprobante, ?Cliente $cliente = null): void
    {
        $cliente = $cliente ?? ($comprobante->cliente ?? null);
        $hasCampos = $comprobante->camposAdicionales && $comprobante->camposAdicionales->count();
        $hasCliente = $cliente && ($cliente->email || $cliente->direccion || $cliente->telefono);

        if (!$hasCampos && !$hasCliente && empty($comprobante->observaciones)) {
            return;
        }

        $xml->startElement('infoAdicional');
        if ($cliente?->email) {
            $xml->startElement('campoAdicional');
            $xml->writeAttribute('nombre', 'email');
            $xml->text($cliente->email);
            $xml->endElement();
        }
        if ($cliente?->direccion) {
            $xml->startElement('campoAdicional');
            $xml->writeAttribute('nombre', 'direccion');
            $xml->text($cliente->direccion);
            $xml->endElement();
        }
        if ($cliente?->telefono) {
            $xml->startElement('campoAdicional');
            $xml->writeAttribute('nombre', 'telefono');
            $xml->text($cliente->telefono);
            $xml->endElement();
        }
        if ($comprobante->observaciones) {
            $xml->startElement('campoAdicional');
            $xml->writeAttribute('nombre', 'observaciones');
            $xml->text($comprobante->observaciones);
            $xml->endElement();
        }
        if ($hasCampos) {
            foreach ($comprobante->camposAdicionales as $campo) {
                $xml->startElement('campoAdicional');
                $xml->writeAttribute('nombre', $campo->nombre);
                $xml->text($campo->valor);
                $xml->endElement();
            }
        }
        $xml->endElement(); // infoAdicional
    }

    /**
     * Escribe la sección detalles común a facturas, notas de crédito y liquidaciones.
     */
    private function escribirDetalles(\XMLWriter $xml, $detalles): void
    {
        $xml->startElement('detalles');
        foreach ($detalles as $detalle) {
            $xml->startElement('detalle');
            $xml->writeElement('codigoPrincipal', $detalle->codigo_principal ?? 'N/A');
            if ($detalle->codigo_auxiliar) {
                $xml->writeElement('codigoAuxiliar', $detalle->codigo_auxiliar);
            }
            $xml->writeElement('descripcion', $detalle->descripcion);
            $xml->writeElement('cantidad', number_format((float) $detalle->cantidad, 6, '.', ''));
            $xml->writeElement('precioUnitario', number_format((float) $detalle->precio_unitario, 6, '.', ''));
            $xml->writeElement('descuento', number_format((float) $detalle->descuento, 2, '.', ''));
            $xml->writeElement('precioTotalSinImpuesto', number_format((float) $detalle->precio_total_sin_impuesto, 2, '.', ''));

            if ($detalle->impuestos && $detalle->impuestos->count()) {
                $xml->startElement('impuestos');
                foreach ($detalle->impuestos as $imp) {
                    $xml->startElement('impuesto');
                    $xml->writeElement('codigo', $imp->codigo);
                    $xml->writeElement('codigoPorcentaje', $imp->codigo_porcentaje);
                    $xml->writeElement('tarifa', number_format((float) $imp->tarifa, 2, '.', ''));
                    $xml->writeElement('baseImponible', number_format((float) $imp->base_imponible, 2, '.', ''));
                    $xml->writeElement('valor', number_format((float) $imp->valor, 2, '.', ''));
                    $xml->endElement(); // impuesto
                }
                $xml->endElement(); // impuestos
            }

            $xml->endElement(); // detalle
        }
        $xml->endElement(); // detalles
    }

    /**
     * Genera el XML de una nota de crédito según norma técnica SRI.
     */
    private function generarXmlNotaCredito(NotaCredito $nc): string
    {
        $nc->load(['emisor', 'establecimiento', 'ptoEmision', 'cliente', 'detalles.impuestos', 'camposAdicionales']);

        $emisor = $nc->emisor;
        $establecimiento = $nc->establecimiento;
        $cliente = $nc->cliente;

        $xml = new \XMLWriter();
        $xml->openMemory();
        $xml->startDocument('1.0', 'UTF-8');

        $xml->startElement('notaCredito');
        $xml->writeAttribute('id', 'comprobante');
        $xml->writeAttribute('version', '1.1.0');

        $this->escribirInfoTributaria($xml, $nc, '04');

        // -- infoNotaCredito --
        $xml->startElement('infoNotaCredito');
        $xml->writeElement('fechaEmision', $nc->fecha_emision->format('d/m/Y'));
        $xml->writeElement('dirEstablecimiento', $establecimiento->direccion ?? $emisor->direccion_matriz);
        $xml->writeElement('tipoIdentificacionComprador', $cliente->tipo_identificacion->value);
        $xml->writeElement('razonSocialComprador', $cliente->razon_social);
        $xml->writeElement('identificacionComprador', $cliente->identificacion);
        if ($emisor->contribuyente_especial) {
            $xml->writeElement('contribuyenteEspecial', $emisor->contribuyente_especial);
        }
        $xml->writeElement('obligadoContabilidad', $emisor->obligado_contabilidad ? 'SI' : 'NO');
        $xml->writeElement('codDocModificado', $nc->cod_doc_modificado);
        $xml->writeElement('numDocModificado', $nc->num_doc_modificado);
        $xml->writeElement('fechaEmisionDocSustento', $nc->fecha_emision_doc_sustento->format('d/m/Y'));
        $xml->writeElement('totalSinImpuestos', number_format((float) $nc->total_sin_impuestos, 2, '.', ''));
        $xml->writeElement('valorModificacion', number_format((float) $nc->importe_total, 2, '.', ''));
        $xml->writeElement('moneda', $nc->moneda ?? 'DOLAR');
        // totalConImpuestos
        $xml->startElement('totalConImpuestos');
        foreach ($this->agruparImpuestos($nc->detalles) as $imp) {
            $xml->startElement('totalImpuesto');
            $xml->writeElement('codigo', $imp['codigo']);
            $xml->writeElement('codigoPorcentaje', $imp['codigo_porcentaje']);
            $xml->writeElement('baseImponible', number_format($imp['base_imponible'], 2, '.', ''));
            $xml->writeElement('valor', number_format($imp['valor'], 2, '.', ''));
            $xml->endElement();
        }
        $xml->endElement(); // totalConImpuestos
        $xml->writeElement('motivo', $nc->motivo);
        $xml->endElement(); // infoNotaCredito

        // -- detalles (nota de crédito usa codigoInterno/codigoAdicional) --
        $xml->startElement('detalles');
        foreach ($nc->detalles as $detalle) {
            $xml->startElement('detalle');
            $xml->writeElement('codigoInterno', $detalle->codigo_principal ?? 'N/A');
            if ($detalle->codigo_auxiliar) {
                $xml->writeElement('codigoAdicional', $detalle->codigo_auxiliar);
            }
            $xml->writeElement('descripcion', $detalle->descripcion);
            $xml->writeElement('cantidad', number_format((float) $detalle->cantidad, 6, '.', ''));
            $xml->writeElement('precioUnitario', number_format((float) $detalle->precio_unitario, 6, '.', ''));
            $xml->writeElement('descuento', number_format((float) $detalle->descuento, 2, '.', ''));
            $xml->writeElement('precioTotalSinImpuesto', number_format((float) $detalle->precio_total_sin_impuesto, 2, '.', ''));

            if ($detalle->impuestos && $detalle->impuestos->count()) {
                $xml->startElement('impuestos');
                foreach ($detalle->impuestos as $imp) {
                    $xml->startElement('impuesto');
                    $xml->writeElement('codigo', $imp->codigo);
                    $xml->writeElement('codigoPorcentaje', $imp->codigo_porcentaje);
                    $xml->writeElement('tarifa', number_format((float) $imp->tarifa, 2, '.', ''));
                    $xml->writeElement('baseImponible', number_format((float) $imp->base_imponible, 2, '.', ''));
                    $xml->writeElement('valor', number_format((float) $imp->valor, 2, '.', ''));
                    $xml->endElement(); // impuesto
                }
                $xml->endElement(); // impuestos
            }

            $xml->endElement(); // detalle
        }
        $xml->endElement(); // detalles

        $this->escribirInfoAdicional($xml, $nc, $cliente);

        $xml->endElement(); // notaCredito
        $xml->endDocument();

        return $xml->outputMemory();
    }

    /**
     * Genera el XML de una nota de débito según norma técnica SRI.
     */
    private function generarXmlNotaDebito(NotaDebito $nd): string
    {
        $nd->load(['emisor', 'establecimiento', 'ptoEmision', 'cliente', 'motivos.impuestoIva', 'camposAdicionales']);

        $emisor = $nd->emisor;
        $establecimiento = $nd->establecimiento;
        $cliente = $nd->cliente;

        $xml = new \XMLWriter();
        $xml->openMemory();
        $xml->startDocument('1.0', 'UTF-8');

        $xml->startElement('notaDebito');
        $xml->writeAttribute('id', 'comprobante');
        $xml->writeAttribute('version', '1.0.0');

        $this->escribirInfoTributaria($xml, $nd, '05');

        // -- infoNotaDebito --
        $xml->startElement('infoNotaDebito');
        $xml->writeElement('fechaEmision', $nd->fecha_emision->format('d/m/Y'));
        $xml->writeElement('dirEstablecimiento', $establecimiento->direccion ?? $emisor->direccion_matriz);
        $xml->writeElement('tipoIdentificacionComprador', $cliente->tipo_identificacion->value);
        $xml->writeElement('razonSocialComprador', $cliente->razon_social);
        $xml->writeElement('identificacionComprador', $cliente->identificacion);
        if ($emisor->contribuyente_especial) {
            $xml->writeElement('contribuyenteEspecial', $emisor->contribuyente_especial);
        }
        $xml->writeElement('obligadoContabilidad', $emisor->obligado_contabilidad ? 'SI' : 'NO');
        $xml->writeElement('codDocModificado', $nd->cod_doc_modificado);
        $xml->writeElement('numDocModificado', $nd->num_doc_modificado);
        $xml->writeElement('fechaEmisionDocSustento', $nd->fecha_emision_doc_sustento->format('d/m/Y'));
        $xml->writeElement('totalSinImpuestos', number_format((float) $nd->total_sin_impuestos, 2, '.', ''));
        // impuestos
        $impuestosAgrupados = [];
        foreach ($nd->motivos as $motivo) {
            $iva = $motivo->impuestoIva;
            if ($iva) {
                $key = '2_' . $iva->codigo_porcentaje;
                if (!isset($impuestosAgrupados[$key])) {
                    $impuestosAgrupados[$key] = [
                        'codigo' => '2',
                        'codigo_porcentaje' => $iva->codigo_porcentaje,
                        'tarifa' => (float) $iva->tarifa,
                        'base_imponible' => 0,
                        'valor' => 0,
                    ];
                }
                $impuestosAgrupados[$key]['base_imponible'] += (float) $motivo->valor;
                $impuestosAgrupados[$key]['valor'] += round((float) $motivo->valor * (float) $iva->tarifa / 100, 2);
            }
        }
        $xml->startElement('impuestos');
        foreach ($impuestosAgrupados as $imp) {
            $xml->startElement('impuesto');
            $xml->writeElement('codigo', $imp['codigo']);
            $xml->writeElement('codigoPorcentaje', $imp['codigo_porcentaje']);
            $xml->writeElement('tarifa', number_format($imp['tarifa'], 2, '.', ''));
            $xml->writeElement('baseImponible', number_format($imp['base_imponible'], 2, '.', ''));
            $xml->writeElement('valor', number_format($imp['valor'], 2, '.', ''));
            $xml->endElement();
        }
        $xml->endElement(); // impuestos
        $xml->writeElement('valorTotal', number_format((float) $nd->importe_total, 2, '.', ''));
        // pagos
        $xml->startElement('pagos');
        $xml->startElement('pago');
        $xml->writeElement('formaPago', '01');
        $xml->writeElement('total', number_format((float) $nd->importe_total, 2, '.', ''));
        $xml->endElement();
        $xml->endElement(); // pagos
        $xml->endElement(); // infoNotaDebito

        // -- motivos --
        $xml->startElement('motivos');
        foreach ($nd->motivos as $motivo) {
            $xml->startElement('motivo');
            $xml->writeElement('razon', $motivo->razon);
            $xml->writeElement('valor', number_format((float) $motivo->valor, 2, '.', ''));
            $xml->endElement();
        }
        $xml->endElement(); // motivos

        $this->escribirInfoAdicional($xml, $nd, $cliente);

        $xml->endElement(); // notaDebito
        $xml->endDocument();

        return $xml->outputMemory();
    }

    /**
     * Genera el XML de una retención según norma técnica SRI.
     */
    private function generarXmlRetencion(Retencion $ret): string
    {
        $ret->load(['emisor', 'establecimiento', 'ptoEmision', 'cliente', 'impuestosRetencion', 'camposAdicionales']);

        $emisor = $ret->emisor;
        $establecimiento = $ret->establecimiento;
        $cliente = $ret->cliente;

        $xml = new \XMLWriter();
        $xml->openMemory();
        $xml->startDocument('1.0', 'UTF-8');

        $xml->startElement('comprobanteRetencion');
        $xml->writeAttribute('id', 'comprobante');
        $xml->writeAttribute('version', '2.0.0');

        $this->escribirInfoTributaria($xml, $ret, '07');

        // -- infoCompRetencion --
        $xml->startElement('infoCompRetencion');
        $xml->writeElement('fechaEmision', $ret->fecha_emision->format('d/m/Y'));
        $xml->writeElement('dirEstablecimiento', $establecimiento->direccion ?? $emisor->direccion_matriz);
        if ($emisor->contribuyente_especial) {
            $xml->writeElement('contribuyenteEspecial', $emisor->contribuyente_especial);
        }
        $xml->writeElement('obligadoContabilidad', $emisor->obligado_contabilidad ? 'SI' : 'NO');
        $xml->writeElement('tipoIdentificacionSujetoRetenido', $cliente->tipo_identificacion->value);
        if ($cliente->tipo_identificacion === \App\Enums\TipoIdentificacion::IDENTIFICACION_EXTERIOR) {
            $tipoSujeto = '01';
            if (strlen($cliente->identificacion) >= 3) {
                $tercerDigito = (int) $cliente->identificacion[2];
                if ($tercerDigito === 6 || $tercerDigito === 9) {
                    $tipoSujeto = '02';
                }
            }
            $xml->writeElement('tipoSujetoRetenido', $tipoSujeto);
        }
        $xml->writeElement('parteRel', 'NO');
        $xml->writeElement('razonSocialSujetoRetenido', $cliente->razon_social);
        $xml->writeElement('identificacionSujetoRetenido', $cliente->identificacion);
        $xml->writeElement('periodoFiscal', $ret->periodo_fiscal ?? $ret->fecha_emision->format('m/Y'));
        $xml->endElement(); // infoCompRetencion

        // -- docsSustento --
        $xml->startElement('docsSustento');
        $xml->startElement('docSustento');
        $xml->writeElement('codSustento', $ret->cod_doc_sustento);
        $xml->writeElement('codDocSustento', $ret->cod_doc_sustento);
        $xml->writeElement('numDocSustento', $ret->num_doc_sustento);
        $xml->writeElement('fechaEmisionDocSustento', $ret->fecha_emision_doc_sustento->format('d/m/Y'));
        $xml->writeElement('fechaRegistroContable', $ret->fecha_emision->format('d/m/Y'));
        $xml->writeElement('numAutDocSustento', '0000000000');
        $xml->writeElement('pagoLocExt', '01');
        // Calcular totales desde los impuestos
        $totalBase = $ret->impuestosRetencion->sum('base_imponible');
        $xml->writeElement('totalSinImpuestos', number_format((float) $totalBase, 2, '.', ''));
        $xml->writeElement('importeTotal', number_format((float) $totalBase, 2, '.', ''));
        // impuestosDocSustento (IVA 0% por defecto)
        $xml->startElement('impuestosDocSustento');
        $xml->startElement('impuestoDocSustento');
        $xml->writeElement('codImpuestoDocSustento', '2');
        $xml->writeElement('codigoPorcentaje', '0');
        $xml->writeElement('baseImponible', number_format((float) $totalBase, 2, '.', ''));
        $xml->writeElement('tarifa', '0.00');
        $xml->writeElement('valorImpuesto', '0.00');
        $xml->endElement(); // impuestoDocSustento
        $xml->endElement(); // impuestosDocSustento
        // retenciones
        $xml->startElement('retenciones');
        foreach ($ret->impuestosRetencion as $imp) {
            $xml->startElement('retencion');
            $xml->writeElement('codigo', $imp->codigo_impuesto);
            $xml->writeElement('codigoRetencion', $imp->codigo_retencion);
            $xml->writeElement('baseImponible', number_format((float) $imp->base_imponible, 2, '.', ''));
            $xml->writeElement('porcentajeRetener', number_format((float) $imp->porcentaje_retener, 2, '.', ''));
            $xml->writeElement('valorRetenido', number_format((float) $imp->valor_retenido, 2, '.', ''));
            $xml->endElement();
        }
        $xml->endElement(); // retenciones
        // pagos
        $xml->startElement('pagos');
        $xml->startElement('pago');
        $xml->writeElement('formaPago', '20');
        $xml->writeElement('total', number_format((float) $totalBase, 2, '.', ''));
        $xml->endElement(); // pago
        $xml->endElement(); // pagos
        $xml->endElement(); // docSustento
        $xml->endElement(); // docsSustento

        $this->escribirInfoAdicional($xml, $ret, $cliente);

        $xml->endElement(); // comprobanteRetencion
        $xml->endDocument();

        return $xml->outputMemory();
    }

    /**
     * Genera el XML de una retención ATS v2.0.0 según norma técnica SRI.
     */
    private function generarXmlRetencionAts(\App\Models\RetencionAts $ret): string
    {
        $ret->load(['emisor', 'establecimiento', 'ptoEmision', 'cliente', 'docSustentos.desgloses', 'docSustentos.impuestos', 'camposAdicionales']);

        $emisor = $ret->emisor;
        $establecimiento = $ret->establecimiento;
        $cliente = $ret->cliente;

        $xml = new \XMLWriter();
        $xml->openMemory();
        $xml->startDocument('1.0', 'UTF-8');

        $xml->startElement('comprobanteRetencion');
        $xml->writeAttribute('id', 'comprobante');
        $xml->writeAttribute('version', '2.0.0');

        $this->escribirInfoTributaria($xml, $ret, '07');

        // -- infoCompRetencion --
        $xml->startElement('infoCompRetencion');
        $xml->writeElement('fechaEmision', $ret->fecha_emision->format('d/m/Y'));
        $xml->writeElement('dirEstablecimiento', $establecimiento->direccion ?? $emisor->direccion_matriz);
        if ($emisor->contribuyente_especial) {
            $xml->writeElement('contribuyenteEspecial', $emisor->contribuyente_especial);
        }
        $xml->writeElement('obligadoContabilidad', $emisor->obligado_contabilidad ? 'SI' : 'NO');
        $xml->writeElement('tipoIdentificacionSujetoRetenido', $cliente->tipo_identificacion->value);
        if ($cliente->tipo_identificacion === \App\Enums\TipoIdentificacion::IDENTIFICACION_EXTERIOR) {
            $tipoSujeto = '01';
            if (strlen($cliente->identificacion) >= 3) {
                $tercerDigito = (int) $cliente->identificacion[2];
                if ($tercerDigito === 6 || $tercerDigito === 9) {
                    $tipoSujeto = '02';
                }
            }
            $xml->writeElement('tipoSujetoRetenido', $tipoSujeto);
        }
        $xml->writeElement('parteRel', $ret->parte_rel ?? 'NO');
        $xml->writeElement('razonSocialSujetoRetenido', $cliente->razon_social);
        $xml->writeElement('identificacionSujetoRetenido', $cliente->identificacion);
        $xml->writeElement('periodoFiscal', $ret->periodo_fiscal ?? $ret->fecha_emision->format('m/Y'));
        $xml->endElement(); // infoCompRetencion

        // -- docsSustento --
        $xml->startElement('docsSustento');
        foreach ($ret->docSustentos as $ds) {
            $xml->startElement('docSustento');
            $xml->writeElement('codSustento', $ds->cod_sustento ?? '01');
            $xml->writeElement('codDocSustento', $ds->cod_doc_sustento);
            $xml->writeElement('numDocSustento', $ds->num_doc_sustento);
            $xml->writeElement('fechaEmisionDocSustento', $ds->fecha_emision_doc_sustento->format('d/m/Y'));
            if ($ds->fecha_registro_contable) {
                $xml->writeElement('fechaRegistroContable', $ds->fecha_registro_contable->format('d/m/Y'));
            }
            if ($ds->num_aut_doc_sustento) {
                $xml->writeElement('numAutDocSustento', $ds->num_aut_doc_sustento);
            }
            $xml->writeElement('pagoLocExt', $ds->pago_loc_ext ?? '01');
            $xml->writeElement('totalSinImpuestos', number_format((float) $ds->total_sin_impuestos, 2, '.', ''));
            $xml->writeElement('importeTotal', number_format((float) $ds->importe_total, 2, '.', ''));

            // impuestosDocSustento
            if ($ds->impuestos && $ds->impuestos->count() > 0) {
                $xml->startElement('impuestosDocSustento');
                foreach ($ds->impuestos as $imp) {
                    $xml->startElement('impuestoDocSustento');
                    $xml->writeElement('codImpuestoDocSustento', $imp->codigo_impuesto);
                    $xml->writeElement('codigoPorcentaje', $imp->codigo_porcentaje);
                    $xml->writeElement('baseImponible', number_format((float) $imp->base_imponible, 2, '.', ''));
                    $xml->writeElement('tarifa', number_format((float) $imp->tarifa, 2, '.', ''));
                    $xml->writeElement('valorImpuesto', number_format((float) $imp->valor_impuesto, 2, '.', ''));
                    $xml->endElement(); // impuestoDocSustento
                }
                $xml->endElement(); // impuestosDocSustento
            }

            // retenciones
            $xml->startElement('retenciones');
            foreach ($ds->desgloses as $desglose) {
                $xml->startElement('retencion');
                $xml->writeElement('codigo', $desglose->codigo_impuesto);
                $xml->writeElement('codigoRetencion', $desglose->codigo_retencion);
                $xml->writeElement('baseImponible', number_format((float) $desglose->base_imponible, 2, '.', ''));
                $xml->writeElement('porcentajeRetener', number_format((float) $desglose->porcentaje_retener, 2, '.', ''));
                $xml->writeElement('valorRetenido', number_format((float) $desglose->valor_retenido, 2, '.', ''));
                $xml->endElement(); // retencion
            }
            $xml->endElement(); // retenciones

            // pagos
            $xml->startElement('pagos');
            $xml->startElement('pago');
            $xml->writeElement('formaPago', $ds->forma_pago ?? '20');
            $xml->writeElement('total', number_format((float) $ds->importe_total, 2, '.', ''));
            $xml->endElement(); // pago
            $xml->endElement(); // pagos

            $xml->endElement(); // docSustento
        }
        $xml->endElement(); // docsSustento

        $this->escribirInfoAdicional($xml, $ret, $cliente);

        $xml->endElement(); // comprobanteRetencion
        $xml->endDocument();

        return $xml->outputMemory();
    }

    /**
     * Genera el XML de una guía de remisión según norma técnica SRI.
     */
    private function generarXmlGuia(Guia $guia): string
    {
        $guia->load(['emisor', 'establecimiento', 'ptoEmision', 'detalles', 'camposAdicionales']);

        $emisor = $guia->emisor;
        $establecimiento = $guia->establecimiento;

        $xml = new \XMLWriter();
        $xml->openMemory();
        $xml->startDocument('1.0', 'UTF-8');

        $xml->startElement('guiaRemision');
        $xml->writeAttribute('id', 'comprobante');
        $xml->writeAttribute('version', '1.1.0');

        $this->escribirInfoTributaria($xml, $guia, '06');

        // -- infoGuiaRemision --
        $xml->startElement('infoGuiaRemision');
        $xml->writeElement('dirEstablecimiento', $establecimiento->direccion ?? $emisor->direccion_matriz);
        $xml->writeElement('dirPartida', $guia->dir_partida);
        $xml->writeElement('razonSocialTransportista', $guia->razon_social_transportista);
        $xml->writeElement('tipoIdentificacionTransportista', $guia->tipo_identificacion_transportista ?? '04');
        $xml->writeElement('rucTransportista', $guia->ruc_transportista);
        $xml->writeElement('obligadoContabilidad', $emisor->obligado_contabilidad ? 'SI' : 'NO');
        if ($emisor->contribuyente_especial) {
            $xml->writeElement('contribuyenteEspecial', $emisor->contribuyente_especial);
        }
        $xml->writeElement('fechaIniTransporte', $guia->fecha_ini_transporte->format('d/m/Y'));
        $xml->writeElement('fechaFinTransporte', $guia->fecha_fin_transporte->format('d/m/Y'));
        $xml->writeElement('placa', $guia->placa ?? '');
        $xml->endElement(); // infoGuiaRemision

        // -- destinatarios --
        $destinatarios = [];
        foreach ($guia->detalles as $det) {
            $key = $det->identificacion_destinatario . '|' . $det->razon_social_destinatario;
            if (!isset($destinatarios[$key])) {
                $destinatarios[$key] = [
                    'identificacion' => $det->identificacion_destinatario,
                    'razon_social' => $det->razon_social_destinatario,
                    'direccion' => $det->dir_destinatario,
                    'motivo_traslado' => $det->motivo_traslado,
                    'doc_aduanero_unico' => $det->doc_aduanero_unico,
                    'cod_establecimiento_destino' => $det->cod_establecimiento_destino,
                    'ruta' => $det->ruta,
                    'cod_doc_sustento' => $det->cod_doc_sustento,
                    'num_doc_sustento' => $det->num_doc_sustento,
                    'num_aut_doc_sustento' => $det->num_aut_doc_sustento,
                    'fecha_emision_doc_sustento' => $det->fecha_emision_doc_sustento,
                    'detalles' => [],
                ];
            }
            if ($det->descripcion) {
                $destinatarios[$key]['detalles'][] = $det;
            }
        }

        $xml->startElement('destinatarios');
        foreach ($destinatarios as $dest) {
            $xml->startElement('destinatario');
            $xml->writeElement('identificacionDestinatario', $dest['identificacion']);
            $xml->writeElement('razonSocialDestinatario', $dest['razon_social']);
            $xml->writeElement('dirDestinatario', $dest['direccion']);
            $xml->writeElement('motivoTraslado', $dest['motivo_traslado']);
            if ($dest['doc_aduanero_unico']) {
                $xml->writeElement('docAduaneroUnico', $dest['doc_aduanero_unico']);
            }
            if ($dest['cod_establecimiento_destino']) {
                $xml->writeElement('codEstabDestino', $dest['cod_establecimiento_destino']);
            }
            if ($dest['ruta']) {
                $xml->writeElement('ruta', $dest['ruta']);
            }
            if ($dest['cod_doc_sustento']) {
                $xml->writeElement('codDocSustento', $dest['cod_doc_sustento']);
            }
            if ($dest['num_doc_sustento']) {
                $xml->writeElement('numDocSustento', $dest['num_doc_sustento']);
            }
            if ($dest['num_aut_doc_sustento']) {
                $xml->writeElement('numAutDocSustento', $dest['num_aut_doc_sustento']);
            }
            if ($dest['fecha_emision_doc_sustento']) {
                $xml->writeElement('fechaEmisionDocSustento', $dest['fecha_emision_doc_sustento']->format('d/m/Y'));
            }
            if (!empty($dest['detalles'])) {
                $xml->startElement('detalles');
                foreach ($dest['detalles'] as $det) {
                    $xml->startElement('detalle');
                    $xml->writeElement('codigoInterno', $det->codigo_principal ?? 'N/A');
                    $xml->writeElement('descripcion', $det->descripcion);
                    $xml->writeElement('cantidad', number_format((float) $det->cantidad, 6, '.', ''));
                    $xml->endElement();
                }
                $xml->endElement(); // detalles
            }
            $xml->endElement(); // destinatario
        }
        $xml->endElement(); // destinatarios

        $this->escribirInfoAdicional($xml, $guia);

        $xml->endElement(); // guiaRemision
        $xml->endDocument();

        return $xml->outputMemory();
    }

    /**
     * Genera el XML de una liquidación de compra según norma técnica SRI.
     */
    private function generarXmlLiquidacion(LiquidacionCompra $liq): string
    {
        $liq->load(['emisor', 'establecimiento', 'ptoEmision', 'cliente', 'detalles.impuestos', 'camposAdicionales']);

        $emisor = $liq->emisor;
        $establecimiento = $liq->establecimiento;
        $cliente = $liq->cliente;

        $xml = new \XMLWriter();
        $xml->openMemory();
        $xml->startDocument('1.0', 'UTF-8');

        $xml->startElement('liquidacionCompra');
        $xml->writeAttribute('id', 'comprobante');
        $xml->writeAttribute('version', '1.1.0');

        $this->escribirInfoTributaria($xml, $liq, '03');

        // -- infoLiquidacionCompra --
        $xml->startElement('infoLiquidacionCompra');
        $xml->writeElement('fechaEmision', $liq->fecha_emision->format('d/m/Y'));
        $xml->writeElement('dirEstablecimiento', $establecimiento->direccion ?? $emisor->direccion_matriz);
        if ($emisor->contribuyente_especial) {
            $xml->writeElement('contribuyenteEspecial', $emisor->contribuyente_especial);
        }
        $xml->writeElement('obligadoContabilidad', $emisor->obligado_contabilidad ? 'SI' : 'NO');
        $xml->writeElement('tipoIdentificacionProveedor', $cliente->tipo_identificacion->value);
        $xml->writeElement('razonSocialProveedor', $cliente->razon_social);
        $xml->writeElement('identificacionProveedor', $cliente->identificacion);
        if ($cliente->direccion) {
            $xml->writeElement('direccionProveedor', $cliente->direccion);
        }
        $xml->writeElement('totalSinImpuestos', number_format((float) $liq->total_sin_impuestos, 2, '.', ''));
        $xml->writeElement('totalDescuento', number_format((float) $liq->total_descuento, 2, '.', ''));
        // totalConImpuestos
        $xml->startElement('totalConImpuestos');
        foreach ($this->agruparImpuestos($liq->detalles) as $imp) {
            $xml->startElement('totalImpuesto');
            $xml->writeElement('codigo', $imp['codigo']);
            $xml->writeElement('codigoPorcentaje', $imp['codigo_porcentaje']);
            $xml->writeElement('baseImponible', number_format($imp['base_imponible'], 2, '.', ''));
            $xml->writeElement('valor', number_format($imp['valor'], 2, '.', ''));
            $xml->endElement();
        }
        $xml->endElement(); // totalConImpuestos
        $xml->writeElement('importeTotal', number_format((float) $liq->importe_total, 2, '.', ''));
        $xml->writeElement('moneda', $liq->moneda ?? 'DOLAR');
        // pagos
        $xml->startElement('pagos');
        $xml->startElement('pago');
        $xml->writeElement('formaPago', $liq->forma_pago ?? '01');
        $xml->writeElement('total', number_format((float) $liq->importe_total, 2, '.', ''));
        if ($liq->forma_pago_plazo) {
            $xml->writeElement('plazo', $liq->forma_pago_plazo);
            $xml->writeElement('unidadTiempo', $liq->forma_pago_unidad_tiempo ?? 'dias');
        }
        $xml->endElement(); // pago
        $xml->endElement(); // pagos
        $xml->endElement(); // infoLiquidacionCompra

        // -- detalles --
        $this->escribirDetalles($xml, $liq->detalles);

        $this->escribirInfoAdicional($xml, $liq, $cliente);

        $xml->endElement(); // liquidacionCompra
        $xml->endDocument();

        return $xml->outputMemory();
    }
}
