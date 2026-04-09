<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 0; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 9px; color: #2d3748; }
        .page-wrapper { padding: 45px 35px; }

        /* Layout helpers */
        table.layout { width: 100%; border-collapse: collapse; }
        table.layout td { vertical-align: top; }

        /* Section header bar */
        .section-header {
            background: #2b6cb0;
            color: #fff;
            padding: 3px 8px;
            font-size: 8px;
            font-weight: bold;
        }

        /* ---------- HEADER ---------- */
        .logo-emisor-box {
            border: 1px solid #cbd5e0;
            padding: 8px;
        }
        .logo-area {
            text-align: center;
            padding-bottom: 6px;
            border-bottom: 1px solid #e2e8f0;
            margin-bottom: 6px;
        }
        .logo-area img { max-width: 280px; max-height: 90px; }
        .logo-fallback {
            font-size: 16px; font-weight: bold; color: #2b6cb0;
            padding: 10px 0;
        }
        .emisor-name { font-size: 10px; font-weight: bold; color: #1a202c; }
        .emisor-commercial { font-size: 9px; color: #4a5568; margin-top: 1px; }
        .emisor-detail { font-size: 7.5px; color: #4a5568; margin-top: 1px; }
        .emisor-detail b { color: #2d3748; }
        .emisor-badge {
            background: #ebf4ff;
            color: #2b6cb0;
            font-size: 7px;
            font-weight: bold;
            padding: 2px 6px;
            margin-top: 3px;
            display: inline;
        }

        /* Doc info */
        .doc-box { border: 2px solid #2b6cb0; }
        .doc-header {
            background: #2b6cb0;
            color: #fff;
            padding: 8px 10px;
            text-align: center;
        }
        .doc-ruc { font-size: 11px; font-weight: bold; }
        .doc-type { font-size: 15px; font-weight: bold; margin: 3px 0 2px; letter-spacing: 1px; }
        .doc-number { font-size: 10px; font-weight: bold; }
        .doc-body { padding: 6px 10px; }
        .doc-field { margin-bottom: 4px; }
        .doc-field-label { font-size: 7px; color: #718096; text-transform: uppercase; font-weight: bold; }
        .doc-field-value { font-size: 8px; font-weight: bold; color: #2d3748; word-break: break-all; }

        /* Clave acceso */
        .clave-box {
            border: 1px solid #cbd5e0;
            padding: 5px 8px;
            text-align: center;
            margin-top: 5px;
        }
        .clave-label { font-size: 7px; font-weight: bold; color: #718096; text-transform: uppercase; }
        .barcode-img { width: 100%; max-height: 30px; margin: 3px 0; }
        .clave-number { font-size: 7px; font-family: 'DejaVu Sans Mono', monospace; color: #4a5568; word-break: break-all; }

        /* ---------- CLIENTE ---------- */
        .cliente-table { width: 100%; border-collapse: collapse; margin-top: 6px; border: 1px solid #cbd5e0; }
        .cliente-table td { padding: 2px 8px; font-size: 8px; }
        .cliente-table .lbl { font-weight: bold; color: #4a5568; width: 160px; }

        /* ---------- DETALLES ---------- */
        table.detalles { width: 100%; border-collapse: collapse; margin-top: 6px; }
        table.detalles th {
            background: #2b6cb0; color: #fff;
            padding: 4px 3px; font-size: 7px;
            text-align: center; text-transform: uppercase; font-weight: bold;
        }
        table.detalles td { padding: 3px 4px; font-size: 8px; border-bottom: 1px solid #e2e8f0; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }

        /* ---------- INFO ADICIONAL + FORMA PAGO ---------- */
        .info-table { width: 100%; border-collapse: collapse; }
        .info-table td { padding: 1px 6px; font-size: 7.5px; }
        .info-table .lbl { font-weight: bold; color: #4a5568; width: 70px; }

        .fpago-table { width: 100%; border-collapse: collapse; }
        .fpago-table td { padding: 3px 6px; font-size: 7.5px; }

        /* ---------- TOTALES ---------- */
        .totales-table { width: 100%; border-collapse: collapse; border: 1px solid #cbd5e0; }
        .totales-table td { padding: 2px 6px; font-size: 7.5px; border-bottom: 1px solid #edf2f7; }
        .totales-table .lbl { font-weight: bold; color: #4a5568; }
        .totales-table .val { text-align: right; width: 70px; color: #2d3748; }
        .totales-table .total-row td { background: #2b6cb0; color: #fff; font-weight: bold; font-size: 9px; border: none; padding: 4px 6px; }

        /* ---------- FOOTER ---------- */
        .footer-table { width: 100%; border-collapse: collapse; margin-top: 6px; border: 1px solid #cbd5e0; }
        .footer-table td { padding: 6px; vertical-align: middle; text-align: center; }
        .qr-img { width: 80px; height: 80px; }
        .footer-label { font-size: 6.5px; color: #718096; text-transform: uppercase; font-weight: bold; margin-bottom: 2px; }
        .footer-barcode { width: 100%; max-height: 35px; }
        .footer-clave { font-size: 7px; font-family: 'DejaVu Sans Mono', monospace; color: #4a5568; word-break: break-all; margin-top: 2px; }

        .auth-badge { padding: 2px 8px; font-size: 7px; font-weight: bold; }
        .auth-autorizado { background: #c6f6d5; color: #22543d; }
        .auth-pendiente { background: #fefcbf; color: #744210; }
        .auth-no-autorizado { background: #fed7d7; color: #9b2c2c; }
    </style>
</head>
<body>
<div class="page-wrapper">

    {{-- ============== HEADER ============== --}}
    <table class="layout">
        <tr>
            <td style="width: 52%; padding-right: 6px;">
                <div class="logo-emisor-box">
                    <div class="logo-area">
                        @if($logoPath)
                            <img src="{{ $logoPath }}">
                        @else
                            <div class="logo-fallback">{{ $factura->emisor->nombre_comercial ?? $factura->emisor->razon_social }}</div>
                        @endif
                    </div>
                    <div class="emisor-name">{{ $factura->emisor->razon_social }}</div>
                    <div class="emisor-detail"><b>DIR MATRIZ:</b> {{ $factura->emisor->direccion_matriz }}</div>
                    <div class="emisor-detail"><b>DIR SUCURSAL:</b> {{ $factura->establecimiento->direccion ?? '' }}</div>
                    <div class="emisor-detail"><b>CONTRIBUYENTE ESPECIAL NRO:</b> {{ $factura->emisor->contribuyente_especial ?? '' }}</div>
                    <div class="emisor-detail"><b>OBLIGADO A LLEVAR CONTABILIDAD:</b> {{ $factura->emisor->obligado_contabilidad ? 'SI' : 'NO' }}</div>
                    @if($factura->emisor->agente_retencion)
                        <div class="emisor-detail"><b>AGENTE DE RETENCIÓN RES. NRO:</b> {{ $factura->emisor->agente_retencion }}</div>
                    @endif
                    @if($factura->emisor->regimen?->esRimpe())
                        <div class="emisor-badge">{{ $factura->emisor->regimen->nombre() }}</div>
                    @endif
                </div>
            </td>
            <td style="width: 48%;">
                <div class="doc-box">
                    <div class="doc-header">
                        <div class="doc-ruc">R.U.C.: {{ $factura->emisor->ruc }}</div>
                        <div class="doc-type">FACTURA</div>
                        <div class="doc-number">No. {{ $factura->establecimiento->codigo }}-{{ $factura->ptoEmision->codigo }}-{{ str_pad($factura->secuencial, 9, '0', STR_PAD_LEFT) }}</div>
                    </div>
                    <div class="doc-body">
                        <div class="doc-field">
                            <div class="doc-field-label">Número de Autorización</div>
                            <div class="doc-field-value">{{ $factura->numero_autorizacion ?? $factura->clave_acceso ?? 'PENDIENTE' }}</div>
                        </div>
                        <div class="doc-field">
                            <div class="doc-field-label">Fecha y Hora de Autorización</div>
                            <div class="doc-field-value">{{ $factura->fecha_autorizacion ? $factura->fecha_autorizacion->format('d/m/Y H:i:s') : 'PENDIENTE' }}</div>
                        </div>
                        <table class="layout" style="margin-top: 4px;">
                            <tr>
                                <td style="width: 50%; text-align: center; padding: 3px; background: #f7fafc; border: 1px solid #e2e8f0; font-size: 8px;">
                                    <b style="display: block; font-size: 7px; color: #718096;">AMBIENTE</b>
                                    {{ ($factura->ambiente ?? $factura->emisor->ambiente->value) == '1' ? 'PRUEBAS' : 'PRODUCCIÓN' }}
                                </td>
                                <td style="width: 50%; text-align: center; padding: 3px; background: #f7fafc; border: 1px solid #e2e8f0; font-size: 8px;">
                                    <b style="display: block; font-size: 7px; color: #718096;">EMISIÓN</b>
                                    NORMAL
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                @if($factura->clave_acceso)
                <div class="clave-box">
                    <div class="clave-label">CLAVE DE ACCESO</div>
                    @if($barcode)
                        <img src="{{ $barcode }}" style="width: 100%; max-height: 30px; margin: 3px 0;">
                    @endif
                    <div class="clave-number">{{ $factura->clave_acceso }}</div>
                </div>
                @endif
            </td>
        </tr>
    </table>

    {{-- ============== CLIENTE ============== --}}
    <table class="cliente-table">
        <tr><td colspan="4" class="section-header">DATOS DEL CLIENTE</td></tr>
        <tr>
            <td class="lbl">Razón Social / Nombres:</td>
            <td colspan="3">{{ $factura->cliente->razon_social }}</td>
        </tr>
        <tr>
            <td class="lbl">Identificación:</td>
            <td>{{ $factura->cliente->identificacion }}</td>
            <td class="lbl" style="width: 110px;">Fecha de Emisión:</td>
            <td>{{ $factura->fecha_emision->format('d/m/Y') }}</td>
        </tr>
        @if($factura->guia_remision)
        <tr>
            <td class="lbl">Guía Remisión:</td>
            <td colspan="3">{{ $factura->guia_remision }}</td>
        </tr>
        @endif
    </table>

    {{-- ============== DETALLES ============== --}}
    <table class="detalles">
        <thead>
            <tr>
                <th style="width: 65px;">Cod. Principal</th>
                <th style="width: 55px;">Cod. Auxiliar</th>
                <th>Descripción</th>
                <th style="width: 40px;">Cant.</th>
                <th style="width: 60px;">P. Unitario</th>
                <th style="width: 55px;">Descuento</th>
                <th style="width: 65px;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($factura->detalles as $det)
            <tr>
                <td class="text-center">{{ $det->codigo_principal }}</td>
                <td class="text-center">{{ $det->codigo_auxiliar ?? '' }}</td>
                <td>{{ $det->descripcion }}</td>
                <td class="text-center">{{ rtrim(rtrim(number_format($det->cantidad, 4), '0'), '.') }}</td>
                <td class="text-right">{{ number_format($det->precio_unitario, 2) }}</td>
                <td class="text-right">{{ number_format($det->descuento ?? 0, 2) }}</td>
                <td class="text-right">{{ number_format($det->precio_total_sin_impuesto, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- ============== BOTTOM: Info + Pagos | Totales ============== --}}
    @php
        $subtotal15 = 0; $subtotal5 = 0; $subtotal0 = 0; $subtotalNoObj = 0; $subtotalExento = 0;
        $iva15 = 0; $iva5 = 0;
        foreach ($factura->detalles as $det) {
            $base = $det->precio_total_sin_impuesto ?? 0;
            $impIva = $det->impuestos->where('codigo', '2')->first();
            if ($impIva) {
                $tarifa = (float)($impIva->tarifa ?? 0);
                if ($tarifa == 15) { $subtotal15 += $base; $iva15 += $impIva->valor ?? 0; }
                elseif ($tarifa == 5) { $subtotal5 += $base; $iva5 += $impIva->valor ?? 0; }
                elseif ($tarifa == 0 && $impIva->codigo_porcentaje == '0') { $subtotal0 += $base; }
                elseif ($impIva->codigo_porcentaje == '6') { $subtotalNoObj += $base; }
                elseif ($impIva->codigo_porcentaje == '7') { $subtotalExento += $base; }
                else { $subtotal15 += $base; $iva15 += $impIva->valor ?? 0; }
            } else {
                $subtotal0 += $base;
            }
        }
        $formas = [
            '01' => 'SIN UTILIZACIÓN DEL SISTEMA FINANCIERO',
            '15' => 'COMPENSACIÓN DE DEUDAS',
            '16' => 'TARJETA DE DÉBITO',
            '17' => 'DINERO ELECTRÓNICO',
            '18' => 'TARJETA PREPAGO',
            '19' => 'TARJETA DE CRÉDITO',
            '20' => 'OTROS CON UTILIZACIÓN DEL SISTEMA FINANCIERO',
            '21' => 'ENDOSO DE TÍTULOS',
        ];
    @endphp
    <table class="layout" style="margin-top: 6px;">
        <tr>
            <td style="width: 55%; padding-right: 6px;">
                @if($factura->observaciones)
                <table style="width: 100%; border-collapse: collapse; border: 1px solid #cbd5e0; margin-bottom: 5px;">
                    <tr><td class="section-header">Observaciones</td></tr>
                    <tr><td style="padding: 4px 6px; font-size: 7.5px;">{{ $factura->observaciones }}</td></tr>
                </table>
                @endif

                {{-- Info Adicional --}}
                @if(($factura->camposAdicionales && $factura->camposAdicionales->count()) || $factura->cliente->direccion || $factura->cliente->telefono || $factura->cliente->email)
                <table style="width: 100%; border-collapse: collapse; border: 1px solid #cbd5e0; margin-bottom: 5px;">
                    <tr><td colspan="2" class="section-header">Información Adicional</td></tr>
                    @if($factura->cliente->direccion)
                    <tr><td class="lbl" style="padding: 1px 6px; font-size: 7.5px; width: 70px; font-weight: bold; color: #4a5568;">Dirección:</td><td style="padding: 1px 6px; font-size: 7.5px;">{{ $factura->cliente->direccion }}</td></tr>
                    @endif
                    @if($factura->cliente->telefono)
                    <tr><td class="lbl" style="padding: 1px 6px; font-size: 7.5px; width: 70px; font-weight: bold; color: #4a5568;">Teléfono:</td><td style="padding: 1px 6px; font-size: 7.5px;">{{ $factura->cliente->telefono }}</td></tr>
                    @endif
                    @if($factura->cliente->email)
                    <tr><td class="lbl" style="padding: 1px 6px; font-size: 7.5px; width: 70px; font-weight: bold; color: #4a5568;">Email:</td><td style="padding: 1px 6px; font-size: 7.5px;">{{ $factura->cliente->email }}</td></tr>
                    @endif
                    @if($factura->camposAdicionales)
                        @foreach($factura->camposAdicionales as $campo)
                        <tr><td class="lbl" style="padding: 1px 6px; font-size: 7.5px; width: 70px; font-weight: bold; color: #4a5568;">{{ $campo->nombre }}:</td><td style="padding: 1px 6px; font-size: 7.5px;">{{ $campo->valor }}</td></tr>
                        @endforeach
                    @endif
                </table>
                @endif

                {{-- Forma de Pago --}}
                <table style="width: 100%; border-collapse: collapse; border: 1px solid #cbd5e0;">
                    <tr>
                        <td class="section-header">Forma de Pago</td>
                        <td class="section-header" style="width: 70px; text-align: right;">Valor</td>
                    </tr>
                    <tr>
                        <td style="padding: 3px 6px; font-size: 7.5px;">{{ $formas[$factura->forma_pago] ?? $factura->forma_pago }}</td>
                        <td style="padding: 3px 6px; font-size: 7.5px; text-align: right; font-weight: bold;">{{ number_format($factura->forma_pago_valor, 2) }}</td>
                    </tr>
                </table>
            </td>
            <td style="width: 45%;">
                {{-- Totales --}}
                <table class="totales-table">
                    <tr><td class="lbl">SUBTOTAL 15%</td><td class="val">{{ number_format($subtotal15, 2) }}</td></tr>
                    <tr><td class="lbl">SUBTOTAL 5%</td><td class="val">{{ number_format($subtotal5, 2) }}</td></tr>
                    <tr><td class="lbl">SUBTOTAL 0%</td><td class="val">{{ number_format($subtotal0, 2) }}</td></tr>
                    <tr><td class="lbl">SUBTOTAL no objeto de IVA</td><td class="val">{{ number_format($subtotalNoObj, 2) }}</td></tr>
                    <tr><td class="lbl">SUBTOTAL exento de IVA</td><td class="val">{{ number_format($subtotalExento, 2) }}</td></tr>
                    <tr style="border-bottom: 2px solid #cbd5e0;"><td class="lbl">SUBTOTAL SIN IMPUESTOS</td><td class="val" style="font-weight: bold;">{{ number_format($factura->total_sin_impuestos, 2) }}</td></tr>
                    <tr><td class="lbl">TOTAL Descuento</td><td class="val">{{ number_format($factura->total_descuento, 2) }}</td></tr>
                    @if($iva15 > 0)
                    <tr><td class="lbl">IVA 15%</td><td class="val">{{ number_format($iva15, 2) }}</td></tr>
                    @endif
                    @if($iva5 > 0)
                    <tr><td class="lbl">IVA 5%</td><td class="val">{{ number_format($iva5, 2) }}</td></tr>
                    @endif
                    @if($factura->total_ice > 0)
                    <tr><td class="lbl">ICE</td><td class="val">{{ number_format($factura->total_ice, 2) }}</td></tr>
                    @endif
                    @if($factura->propina > 0)
                    <tr><td class="lbl">PROPINA</td><td class="val">{{ number_format($factura->propina, 2) }}</td></tr>
                    @endif
                    <tr class="total-row"><td>IMPORTE TOTAL</td><td style="text-align: right;">{{ number_format($factura->importe_total, 2) }}</td></tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- ============== FOOTER: QR + BARCODE + ESTADO ============== --}}
    @if($barcode || $qrCode)
    <table class="footer-table">
        <tr>
            @if($qrCode)
            <td style="width: 100px;">
                <div class="footer-label">Código QR</div>
                <img src="{{ $qrCode }}" class="qr-img">
            </td>
            @endif
            <td>
                <div class="footer-label">Clave de Acceso</div>
                @if($barcode)
                    <img src="{{ $barcode }}" class="footer-barcode">
                @endif
                <div class="footer-clave">{{ $factura->clave_acceso }}</div>
            </td>
            <td style="width: 95px;">
                <div class="footer-label">Estado</div>
                @if($factura->estado === 'AUTORIZADO')
                    <span class="auth-badge auth-autorizado">AUTORIZADO</span>
                @elseif($factura->estado === 'NO AUTORIZADO')
                    <span class="auth-badge auth-no-autorizado">NO AUTORIZADO</span>
                @else
                    <span class="auth-badge auth-pendiente">{{ $factura->estado }}</span>
                @endif
                @if($factura->fecha_autorizacion)
                    <div style="font-size: 7px; color: #718096; margin-top: 3px;">
                        {{ $factura->fecha_autorizacion->format('d/m/Y') }}<br>
                        {{ $factura->fecha_autorizacion->format('H:i:s') }}
                    </div>
                @endif
            </td>
        </tr>
    </table>
    @endif

</div>
</body>
</html>
