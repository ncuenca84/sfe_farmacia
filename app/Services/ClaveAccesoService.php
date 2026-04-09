<?php

namespace App\Services;

class ClaveAccesoService
{
    /**
     * Genera la clave de acceso de 49 dígitos para comprobantes electrónicos SRI.
     */
    public function generar(
        string $fecha,           // ddmmaaaa
        string $tipoDoc,         // TipoComprobante->value (01, 04, etc)
        string $ruc,             // 13 dígitos
        string $ambiente,        // '1' o '2'
        string $establecimiento, // 3 dígitos
        string $ptoEmision,      // 3 dígitos
        int    $secuencial,      // se formatea a 9 dígitos
        string $codigoNumerico,  // 8 dígitos
        string $tipoEmision = '1'
    ): string {
        $serie = $establecimiento . $ptoEmision;
        $sec = str_pad($secuencial, 9, '0', STR_PAD_LEFT);
        $cod = str_pad($codigoNumerico, 8, '0', STR_PAD_LEFT);

        $clave = $fecha . $tipoDoc . $ruc . $ambiente
               . $serie . $sec . $cod . $tipoEmision;

        return $clave . $this->modulo11($clave);
    }

    /**
     * Calcula el dígito verificador usando módulo 11.
     */
    private function modulo11(string $clave): string
    {
        $multiplos = [2, 3, 4, 5, 6, 7];
        $total = 0;
        $len = strlen($clave);

        for ($i = 0; $i < $len; $i++) {
            $total += (int) $clave[$len - 1 - $i] * $multiplos[$i % 6];
        }

        $r = 11 - ($total % 11);

        if ($r === 11) return '0';
        if ($r === 10) return '1';

        return (string) $r;
    }
}
