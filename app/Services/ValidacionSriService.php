<?php

namespace App\Services;

use App\Enums\TipoIdentificacion;
use App\Models\Cliente;
use App\Models\Emisor;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Validaciones basadas en la ficha técnica de comprobantes electrónicos del SRI.
 * Previene errores de rechazo del SRI validando antes de generar el XML.
 */
class ValidacionSriService
{
    /**
     * Valida los datos antes de procesar una factura.
     */
    public function validarFactura(Emisor $emisor, Cliente $cliente, array $data): void
    {
        $this->validarEmisor($emisor);
        $this->validarCliente($cliente);
        $this->validarDetalles($data['detalles'] ?? []);
        $this->validarFormaPago($data['forma_pago'] ?? null);
        $this->validarInfoAdicional($cliente, $data['observaciones'] ?? null, $data['campos_adicionales'] ?? []);
    }

    /**
     * Valida los datos antes de procesar una nota de crédito.
     */
    public function validarNotaCredito(Emisor $emisor, Cliente $cliente, array $data): void
    {
        $this->validarEmisor($emisor);
        $this->validarCliente($cliente, prohibirConsumidorFinal: true);
        $this->validarDetalles($data['detalles'] ?? []);
        $this->validarDocumentoSustento($data);
        $this->validarInfoAdicional($cliente, $data['observaciones'] ?? null);
    }

    /**
     * Valida los datos antes de procesar una nota de débito.
     */
    public function validarNotaDebito(Emisor $emisor, Cliente $cliente, array $data): void
    {
        $this->validarEmisor($emisor);
        $this->validarCliente($cliente, prohibirConsumidorFinal: true);
        $this->validarDocumentoSustento($data);
        $this->validarInfoAdicional($cliente, $data['observaciones'] ?? null);

        foreach ($data['motivos'] ?? [] as $i => $motivo) {
            $razon = $motivo['razon'] ?? '';
            if (mb_strlen($razon) > 300) {
                $this->fallar("motivos.{$i}.razon", 'La razon del motivo no puede exceder 300 caracteres.');
            }
            if (empty(trim($razon))) {
                $this->fallar("motivos.{$i}.razon", 'La razon del motivo es obligatoria.');
            }
        }
    }

    /**
     * Valida los datos antes de procesar una retención.
     */
    public function validarRetencion(Emisor $emisor, Cliente $cliente, array $data): void
    {
        $this->validarEmisor($emisor);
        $this->validarCliente($cliente, prohibirConsumidorFinal: true);
        $this->validarDocumentoSustento($data, campoTipoDoc: 'tipo_doc_sustento');
        $this->validarInfoAdicional($cliente, null);

        foreach ($data['impuestos'] ?? [] as $i => $imp) {
            if (empty($imp['codigo'])) {
                $this->fallar("impuestos.{$i}.codigo", 'El codigo del impuesto es obligatorio.');
            }
            if (empty($imp['codigo_retencion'])) {
                $this->fallar("impuestos.{$i}.codigo_retencion", 'El codigo de retencion es obligatorio.');
            }
            $base = (float) ($imp['base_imponible'] ?? 0);
            if ($base < 0) {
                $this->fallar("impuestos.{$i}.base_imponible", 'La base imponible no puede ser negativa.');
            }
        }
    }

    /**
     * Valida los datos antes de procesar una guía de remisión.
     */
    public function validarGuia(Emisor $emisor, array $data): void
    {
        $this->validarEmisor($emisor);

        $dirPartida = $data['dir_partida'] ?? '';
        if (empty(trim($dirPartida))) {
            $this->fallar('dir_partida', 'La direccion de partida es obligatoria.');
        }
        if (mb_strlen($dirPartida) > 300) {
            $this->fallar('dir_partida', 'La direccion de partida no puede exceder 300 caracteres.');
        }

        $rucTransp = $data['ruc_transportista'] ?? '';
        if (!empty($rucTransp) && !preg_match('/^\d{13}$/', $rucTransp)) {
            $this->fallar('ruc_transportista', 'El RUC del transportista debe tener exactamente 13 digitos numericos.');
        }

        $placa = $data['placa'] ?? '';
        if (!empty($placa) && mb_strlen($placa) > 20) {
            $this->fallar('placa', 'La placa no puede exceder 20 caracteres.');
        }

        foreach ($data['destinatarios'] ?? [] as $i => $dest) {
            $this->validarIdentificacionValor(
                $dest['identificacion'] ?? '',
                "destinatarios.{$i}.identificacion"
            );
            $rs = $dest['razon_social'] ?? '';
            if (mb_strlen($rs) > 300) {
                $this->fallar("destinatarios.{$i}.razon_social", 'La razon social del destinatario no puede exceder 300 caracteres.');
            }
        }
    }

    /**
     * Valida los datos antes de procesar una liquidación de compra.
     */
    public function validarLiquidacion(Emisor $emisor, Cliente $cliente, array $data): void
    {
        $this->validarEmisor($emisor);

        // Liquidaciones no permiten consumidor final
        if ($cliente->tipo_identificacion === TipoIdentificacion::CONSUMIDOR_FINAL) {
            $this->fallar('cliente_id', 'Las liquidaciones de compra no permiten el tipo de identificacion "Consumidor Final".');
        }

        $this->validarCliente($cliente);
        $this->validarDetalles($data['detalles'] ?? []);
        $this->validarFormaPago($data['forma_pago'] ?? null);
    }

    /**
     * Validaciones del emisor según ficha técnica.
     */
    private function validarEmisor(Emisor $emisor): void
    {
        if (!preg_match('/^\d{13}$/', $emisor->ruc)) {
            $this->fallar('emisor', 'El RUC del emisor debe tener exactamente 13 digitos numericos. Verifique la configuracion del emisor.');
        }

        if (empty(trim($emisor->razon_social))) {
            $this->fallar('emisor', 'La razon social del emisor es obligatoria. Verifique la configuracion del emisor.');
        }

        if (mb_strlen($emisor->razon_social) > 300) {
            $this->fallar('emisor', 'La razon social del emisor no puede exceder 300 caracteres.');
        }

        if (empty(trim($emisor->direccion_matriz))) {
            $this->fallar('emisor', 'La direccion matriz del emisor es obligatoria. Verifique la configuracion del emisor.');
        }

        if (mb_strlen($emisor->direccion_matriz) > 300) {
            $this->fallar('emisor', 'La direccion matriz del emisor no puede exceder 300 caracteres.');
        }

        if (!$emisor->firma_path || !file_exists($emisor->firma_path)) {
            $this->fallar('emisor', 'No se encontro el archivo de firma electronica (.p12). Suba la firma en la configuracion del emisor.');
        }

        if (empty($emisor->firma_password)) {
            $this->fallar('emisor', 'La contrasena de la firma electronica no esta configurada.');
        }
    }

    /**
     * Validaciones del cliente/receptor según ficha técnica.
     */
    private function validarCliente(Cliente $cliente, bool $prohibirConsumidorFinal = false): void
    {
        if ($prohibirConsumidorFinal && $cliente->tipo_identificacion === TipoIdentificacion::CONSUMIDOR_FINAL) {
            $this->fallar('cliente_id', 'Este tipo de comprobante no permite el tipo de identificacion "Consumidor Final".');
        }

        $this->validarIdentificacion($cliente);

        $razonSocial = $cliente->razon_social ?? '';
        if (empty(trim($razonSocial))) {
            $this->fallar('cliente_id', 'La razon social del cliente es obligatoria.');
        }
        if (mb_strlen($razonSocial) > 300) {
            $this->fallar('cliente_id', 'La razon social del cliente no puede exceder 300 caracteres (actualmente: ' . mb_strlen($razonSocial) . ').');
        }

        if ($cliente->direccion && mb_strlen($cliente->direccion) > 300) {
            $this->fallar('cliente_id', 'La direccion del cliente no puede exceder 300 caracteres.');
        }
    }

    /**
     * Valida la identificación del cliente según su tipo (ficha técnica SRI).
     */
    private function validarIdentificacion(Cliente $cliente): void
    {
        $identificacion = $cliente->identificacion ?? '';
        $tipo = $cliente->tipo_identificacion;

        if (empty(trim($identificacion))) {
            $this->fallar('cliente_id', 'La identificacion del cliente es obligatoria.');
            return;
        }

        match ($tipo) {
            TipoIdentificacion::RUC => $this->validarRuc($identificacion),
            TipoIdentificacion::CEDULA => $this->validarCedula($identificacion),
            TipoIdentificacion::PASAPORTE => $this->validarPasaporte($identificacion),
            TipoIdentificacion::CONSUMIDOR_FINAL => $this->validarConsumidorFinal($identificacion),
            TipoIdentificacion::IDENTIFICACION_EXTERIOR => $this->validarIdentificacionExterior($identificacion),
            default => null,
        };
    }

    private function validarRuc(string $identificacion): void
    {
        if (!preg_match('/^\d{13}$/', $identificacion)) {
            $this->fallar('cliente_id', "El RUC del cliente debe tener exactamente 13 digitos numericos (valor actual: '{$identificacion}').");
        }
    }

    private function validarCedula(string $identificacion): void
    {
        if (!preg_match('/^\d{10}$/', $identificacion)) {
            $this->fallar('cliente_id', "La cedula del cliente debe tener exactamente 10 digitos numericos (valor actual: '{$identificacion}').");
        }

        // Validar dígito verificador (módulo 10) - el SRI rechaza cédulas inválidas
        if (strlen($identificacion) === 10) {
            $coeficientes = [2, 1, 2, 1, 2, 1, 2, 1, 2];
            $suma = 0;
            for ($i = 0; $i < 9; $i++) {
                $val = (int) $identificacion[$i] * $coeficientes[$i];
                if ($val >= 10) {
                    $val -= 9;
                }
                $suma += $val;
            }
            $digitoCalculado = (10 - ($suma % 10)) % 10;
            if ($digitoCalculado !== (int) $identificacion[9]) {
                $this->fallar('cliente_id', "La cedula del cliente no es valida (digito verificador incorrecto). Verifique el numero: '{$identificacion}'.");
            }
        }
    }

    private function validarPasaporte(string $identificacion): void
    {
        if (mb_strlen($identificacion) < 3 || mb_strlen($identificacion) > 20) {
            $this->fallar('cliente_id', 'El pasaporte del cliente debe tener entre 3 y 20 caracteres.');
        }
    }

    private function validarConsumidorFinal(string $identificacion): void
    {
        if ($identificacion !== '9999999999999') {
            $this->fallar('cliente_id', 'Para consumidor final, la identificacion debe ser 9999999999999.');
        }
    }

    private function validarIdentificacionExterior(string $identificacion): void
    {
        if (mb_strlen($identificacion) < 3 || mb_strlen($identificacion) > 20) {
            $this->fallar('cliente_id', 'La identificacion del exterior debe tener entre 3 y 20 caracteres.');
        }
    }

    /**
     * Valida un valor de identificación sin modelo Cliente (para destinatarios de guías).
     */
    private function validarIdentificacionValor(string $identificacion, string $campo): void
    {
        if (empty(trim($identificacion))) {
            $this->fallar($campo, 'La identificacion es obligatoria.');
        }
        if (mb_strlen($identificacion) > 20) {
            $this->fallar($campo, 'La identificacion no puede exceder 20 caracteres.');
        }
    }

    /**
     * Valida los detalles/líneas del comprobante.
     */
    private function validarDetalles(array $detalles): void
    {
        if (empty($detalles)) {
            $this->fallar('detalles', 'El comprobante debe tener al menos un detalle/item.');
            return;
        }

        foreach ($detalles as $i => $detalle) {
            // Descripción: obligatoria, máx 300
            $desc = $detalle['descripcion'] ?? '';
            if (empty(trim($desc))) {
                $this->fallar("detalles.{$i}.descripcion", 'La descripcion del item ' . ($i + 1) . ' es obligatoria.');
            }
            if (mb_strlen($desc) > 300) {
                $this->fallar("detalles.{$i}.descripcion", 'La descripcion del item ' . ($i + 1) . ' no puede exceder 300 caracteres (actualmente: ' . mb_strlen($desc) . ').');
            }

            // Código principal: máx 25 según XSD
            $codigo = $detalle['codigo_principal'] ?? '';
            if (mb_strlen($codigo) > 25) {
                $this->fallar("detalles.{$i}.codigo_principal", 'El codigo principal del item ' . ($i + 1) . ' no puede exceder 25 caracteres.');
            }

            // Cantidad: > 0, máx 14 dígitos con 6 decimales
            $cantidad = (float) ($detalle['cantidad'] ?? 0);
            if ($cantidad <= 0) {
                $this->fallar("detalles.{$i}.cantidad", 'La cantidad del item ' . ($i + 1) . ' debe ser mayor a cero.');
            }

            // Precio unitario: >= 0, máx 14 dígitos con 6 decimales
            $precio = (float) ($detalle['precio_unitario'] ?? 0);
            if ($precio < 0) {
                $this->fallar("detalles.{$i}.precio_unitario", 'El precio unitario del item ' . ($i + 1) . ' no puede ser negativo.');
            }

            // Descuento: >= 0
            $descuento = (float) ($detalle['descuento'] ?? 0);
            if ($descuento < 0) {
                $this->fallar("detalles.{$i}.descuento", 'El descuento del item ' . ($i + 1) . ' no puede ser negativo.');
            }

            // Descuento no puede ser mayor al subtotal
            $subtotal = $cantidad * $precio;
            if ($descuento > $subtotal + 0.01) {
                $this->fallar("detalles.{$i}.descuento", 'El descuento del item ' . ($i + 1) . ' ($' . number_format($descuento, 2) . ') no puede ser mayor al subtotal ($' . number_format($subtotal, 2) . ').');
            }
        }
    }

    /**
     * Valida la forma de pago (tabla 24 SRI).
     */
    private function validarFormaPago(?string $formaPago): void
    {
        $formasValidas = [
            '01', // Sin utilizacion del sistema financiero
            '15', // Compensación de deudas
            '16', // Tarjeta de débito
            '17', // Dinero electrónico
            '18', // Tarjeta prepago
            '19', // Tarjeta de crédito
            '20', // Otros con utilizacion del sistema financiero
            '21', // Endoso de títulos
        ];

        if ($formaPago && !in_array($formaPago, $formasValidas, true)) {
            $this->fallar('forma_pago', "La forma de pago '{$formaPago}' no es valida segun el catalogo del SRI.");
        }
    }

    /**
     * Valida documento sustento (para NC, ND, retenciones).
     */
    private function validarDocumentoSustento(array $data, string $campoTipoDoc = 'cod_doc_modificado'): void
    {
        $tipoDoc = $data[$campoTipoDoc] ?? '';
        $tiposValidos = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '11', '12', '15', '18', '19', '20', '21', '41', '43', '44', '45', '47', '48'];

        if (!empty($tipoDoc) && !in_array($tipoDoc, $tiposValidos, true)) {
            $this->fallar($campoTipoDoc, "El tipo de documento sustento '{$tipoDoc}' no es valido.");
        }

        $numDoc = $data['num_doc_sustento'] ?? $data['num_doc_modificado'] ?? '';
        if (!empty($numDoc)) {
            $numLimpio = str_replace('-', '', $numDoc);
            // Formato: 3 dígitos estab + 3 dígitos pto emisión + 9 dígitos secuencial = 15
            if (!preg_match('/^\d{3}-?\d{3}-?\d{9}$/', $numDoc) && !preg_match('/^\d{15}$/', $numLimpio)) {
                $this->fallar('num_doc_sustento', "El numero de documento sustento '{$numDoc}' no tiene el formato correcto (debe ser 001-001-000000001).");
            }
        }
    }

    /**
     * Valida info adicional (máximo 15 campos, nombre máx 300, valor máx 300).
     */
    private function validarInfoAdicional(?Cliente $cliente, ?string $observaciones, array $camposAdicionales = []): void
    {
        $totalCampos = count($camposAdicionales);

        // Contar campos automáticos del cliente
        if ($cliente) {
            if ($cliente->email) $totalCampos++;
            if ($cliente->direccion) $totalCampos++;
            if ($cliente->telefono) $totalCampos++;
        }
        if ($observaciones) $totalCampos++;

        if ($totalCampos > 15) {
            $this->fallar('campos_adicionales', 'El comprobante no puede tener mas de 15 campos adicionales (incluyendo email, direccion, telefono y observaciones del cliente). Actualmente tiene ' . $totalCampos . '.');
        }

        // Validar longitud de cada campo adicional
        foreach ($camposAdicionales as $i => $campo) {
            $nombre = $campo['nombre'] ?? '';
            $valor = $campo['valor'] ?? '';
            if (mb_strlen($nombre) > 300) {
                $this->fallar("campos_adicionales.{$i}.nombre", 'El nombre del campo adicional no puede exceder 300 caracteres.');
            }
            if (mb_strlen($valor) > 300) {
                $this->fallar("campos_adicionales.{$i}.valor", 'El valor del campo adicional no puede exceder 300 caracteres.');
            }
        }

        // Validar que email del cliente no exceda 300 chars (se envía como campo adicional)
        if ($cliente?->email && mb_strlen($cliente->email) > 300) {
            $this->fallar('cliente_id', 'El email del cliente no puede exceder 300 caracteres (se envia como campo adicional al SRI).');
        }

        if ($observaciones && mb_strlen($observaciones) > 300) {
            $this->fallar('observaciones', 'Las observaciones no pueden exceder 300 caracteres (limite del SRI para campos adicionales).');
        }
    }

    /**
     * Lanza una excepción de validación con un mensaje amigable.
     */
    private function fallar(string $campo, string $mensaje): void
    {
        throw ValidationException::withMessages([
            $campo => [$mensaje],
        ]);
    }
}
