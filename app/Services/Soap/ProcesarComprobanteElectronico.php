<?php

namespace App\Services\Soap;

use SoapClient;

/**
 * Cliente SOAP modernizado para comunicación con MasterOffline.
 * Mantiene la interfaz del sistema viejo pero con constructor PHP 8.
 */
class ProcesarComprobanteElectronico extends SoapClient
{
    private const CLASSMAP = [];

    public function __construct(
        string $wsdl = '',
        array $options = []
    ) {
        if (empty($wsdl)) {
            $wsdl = config('sri.masteroffline_url');
        }

        $defaults = [
            'classmap' => self::CLASSMAP,
            'trace' => true,
            'exceptions' => true,
            'connection_timeout' => config('sri.timeout', 600),
            'cache_wsdl' => config('sri.soap_cache', 0),
        ];

        parent::__construct($wsdl, array_merge($defaults, $options));
    }

    /**
     * Procesa un comprobante electrónico (firma + envío SRI).
     */
    public function procesarComprobante(array $params): mixed
    {
        return $this->__soapCall('procesarComprobante', [$params]);
    }

    /**
     * Procesa un comprobante pendiente (reenvío al SRI).
     */
    public function procesarPendiente(array $params): mixed
    {
        return $this->__soapCall('procesarPendiente', [$params]);
    }

    /**
     * Reenvía email con comprobante autorizado.
     */
    public function reenviarEmail(array $params): mixed
    {
        return $this->__soapCall('reenviarEmail', [$params]);
    }

    /**
     * Procesa una proforma (no va al SRI).
     */
    public function procesarProforma(array $params): mixed
    {
        return $this->__soapCall('procesarProforma', [$params]);
    }

    /**
     * Procesa un XML ya generado (firma + envío SRI).
     */
    public function procesarXML(array $params): mixed
    {
        return $this->__soapCall('procesarXML', [$params]);
    }

    /**
     * Verifica el estado de un comprobante en el SRI.
     */
    public function verificarEstado(array $params): mixed
    {
        return $this->__soapCall('verificarEstado', [$params]);
    }
}
