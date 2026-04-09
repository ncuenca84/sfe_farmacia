<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SriRucService
{
    private const SRI_CATASTRO_URL = 'https://srienlinea.sri.gob.ec/sri-catastro-sujeto-servicio-internet/rest/ConsolidadoContribuyente/obtenerPorNumerosRuc';
    private const SRI_ESTABLECIMIENTO_URL = 'https://srienlinea.sri.gob.ec/sri-catastro-sujeto-servicio-internet/rest/Establecimiento/consultarPorNumeroRuc';

    /**
     * Consulta datos de un contribuyente por RUC.
     *
     * @return array|null Datos del contribuyente o null si no se encontro
     */
    public function consultar(string $ruc): ?array
    {
        $cacheKey = 'sri_ruc_' . $ruc;
        if ($cached = Cache::get($cacheKey)) {
            return $cached;
        }

        $resultado = $this->consultarSriRest($ruc);

        if ($resultado) {
            Cache::put($cacheKey, $resultado, now()->addHours(24));
        }

        return $resultado;
    }

    /**
     * API REST del catastro SRI.
     * Endpoint publico que devuelve JSON sin necesidad de token.
     *
     * Respuesta esperada (array con un objeto):
     * [{
     *   "numeroRuc": "1790016919001",
     *   "razonSocial": "CORPORACION FAVORITA C.A.",
     *   "estadoContribuyenteRuc": "ACTIVO",
     *   "actividadEconomicaPrincipal": "VENTA AL POR MAYOR...",
     *   "tipoContribuyente": "SOCIEDAD",
     *   "regimen": "GENERAL",
     *   "obligadoLlevarContabilidad": "SI",
     *   "agenteRetencion": "SI",
     *   "contribuyenteEspecial": "SI",
     *   "informacionFechasContribuyente": {...},
     *   "representantesLegales": [{...}],
     *   "contribuyenteFantasma": "NO",
     *   "transaccionesInexistente": "NO"
     * }]
     */
    protected function consultarSriRest(string $ruc): ?array
    {
        try {
            $http = Http::timeout(15)
                ->withHeaders([
                    'Content-Type' => 'application/json; charset=UTF-8',
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                ]);

            // Solo deshabilitar verificacion SSL en desarrollo
            if (app()->environment('local', 'testing')) {
                $http = $http->withoutVerifying();
            }

            $response = $http->get(self::SRI_CATASTRO_URL, [
                'ruc' => $ruc,
            ]);

            if (!$response->successful()) {
                Log::info("SRI REST retorno status {$response->status()} para RUC: {$ruc}");
                return null;
            }

            $data = $response->json();
            if (empty($data)) {
                return null;
            }

            $c = is_array($data) && isset($data[0]) ? $data[0] : $data;

            $razonSocial = $c['razonSocial'] ?? null;
            if (!$razonSocial) {
                return null;
            }

            // Consultar direccion del establecimiento matriz
            $direccion = $this->consultarDireccionMatriz($ruc);

            $regimen = $c['regimen'] ?? null;

            return [
                'ruc' => $c['numeroRuc'] ?? $ruc,
                'razon_social' => $razonSocial,
                'nombre_comercial' => $c['nombreComercial'] ?? null,
                'direccion' => $direccion,
                'obligado_contabilidad' => $this->esAfirmativo($c['obligadoLlevarContabilidad'] ?? null),
                'estado' => $c['estadoContribuyenteRuc'] ?? null,
                'tipo_contribuyente' => $c['tipoContribuyente'] ?? null,
                'actividad_economica' => $c['actividadEconomicaPrincipal'] ?? null,
                'regimen' => $this->detectarRegimen($regimen),
                'agente_retencion' => $this->esAfirmativo($c['agenteRetencion'] ?? null) ? 'SI' : null,
                'contribuyente_especial' => $this->esAfirmativo($c['contribuyenteEspecial'] ?? null) ? 'SI' : null,
                'contribuyente_fantasma' => $this->esAfirmativo($c['contribuyenteFantasma'] ?? null),
                'transacciones_inexistentes' => $this->esAfirmativo($c['transaccionesInexistente'] ?? null),
                'representante_legal' => $this->extraerRepresentanteLegal($c),
                'fecha_inicio_actividades' => $c['informacionFechasContribuyente']['fechaInicioActividades'] ?? null,
                'fuente' => 'sri_rest',
            ];
        } catch (\Exception $e) {
            Log::info("Error SRI REST: {$e->getMessage()}");
            return null;
        }
    }

    /**
     * Consulta la direccion del establecimiento matriz.
     */
    protected function consultarDireccionMatriz(string $ruc): ?string
    {
        try {
            $http = Http::timeout(10)
                ->withHeaders([
                    'Content-Type' => 'application/json; charset=UTF-8',
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                ]);

            if (app()->environment('local', 'testing')) {
                $http = $http->withoutVerifying();
            }

            $response = $http->get(self::SRI_ESTABLECIMIENTO_URL, [
                'numeroRuc' => $ruc,
            ]);

            if (!$response->successful()) {
                return null;
            }

            $data = $response->json();
            if (empty($data)) {
                return null;
            }

            // El primer establecimiento suele ser la matriz
            $establecimiento = is_array($data) && isset($data[0]) ? $data[0] : $data;

            return $establecimiento['direccionCompleta'] ?? null;
        } catch (\Exception $e) {
            Log::info("Error consultando establecimiento SRI: {$e->getMessage()}");
            return null;
        }
    }

    protected function extraerRepresentanteLegal(array $data): ?string
    {
        $representantes = $data['representantesLegales'] ?? [];
        if (!empty($representantes) && isset($representantes[0]['nombre'])) {
            return $representantes[0]['nombre'];
        }
        return null;
    }

    protected function esAfirmativo($valor): bool
    {
        if (is_bool($valor)) return $valor;
        if (is_string($valor)) {
            $v = strtoupper(trim($valor));
            return $v === 'SI' || $v === 'S' || $v === 'TRUE' || $v === '1';
        }
        return false;
    }

    protected function detectarRegimen(?string $regimen): string
    {
        if (!$regimen) {
            return 'GENERAL';
        }
        $upper = strtoupper($regimen);
        if (str_contains($upper, 'NEGOCIO POPULAR')) {
            return 'NEGOCIO_POPULAR';
        }
        if (str_contains($upper, 'RIMPE')) {
            return 'RIMPE';
        }
        if (str_contains($upper, 'EPS')) {
            return 'EPS';
        }
        return 'GENERAL';
    }
}
