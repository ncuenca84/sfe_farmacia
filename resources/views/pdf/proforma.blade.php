<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 0; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 9px; color: #2d3748; }
        .page-wrapper { padding: 45px 35px; }

        table.layout { width: 100%; border-collapse: collapse; }
        table.layout td { vertical-align: top; }

        .section-header { background: #2b6cb0; color: #fff; padding: 3px 8px; font-size: 8px; font-weight: bold; }

        .logo-emisor-box { border: 1px solid #cbd5e0; padding: 8px; }
        .logo-area { text-align: center; padding-bottom: 6px; border-bottom: 1px solid #e2e8f0; margin-bottom: 6px; }
        .logo-area img { max-width: 280px; max-height: 90px; }
        .logo-fallback { font-size: 16px; font-weight: bold; color: #2b6cb0; padding: 10px 0; }
        .emisor-name { font-size: 10px; font-weight: bold; color: #1a202c; }
        .emisor-detail { font-size: 7.5px; color: #4a5568; margin-top: 1px; }
        .emisor-detail b { color: #2d3748; }
        .emisor-badge { background: #ebf4ff; color: #2b6cb0; font-size: 7px; font-weight: bold; padding: 2px 6px; margin-top: 3px; display: inline; }

        .doc-box { border: 2px solid #2b6cb0; }
        .doc-header { background: #2b6cb0; color: #fff; padding: 8px 10px; text-align: center; }
        .doc-ruc { font-size: 11px; font-weight: bold; }
        .doc-type { font-size: 15px; font-weight: bold; margin: 3px 0 2px; letter-spacing: 1px; }
        .doc-number { font-size: 10px; font-weight: bold; }
        .doc-body { padding: 6px 10px; }
        .doc-field { margin-bottom: 4px; }
        .doc-field-label { font-size: 7px; color: #718096; text-transform: uppercase; font-weight: bold; }
        .doc-field-value { font-size: 8px; font-weight: bold; color: #2d3748; word-break: break-all; }

        .cliente-table { width: 100%; border-collapse: collapse; margin-top: 6px; border: 1px solid #cbd5e0; }
        .cliente-table td { padding: 2px 8px; font-size: 8px; }
        .cliente-table .lbl { font-weight: bold; color: #4a5568; width: 160px; }

        table.detalles { width: 100%; border-collapse: collapse; margin-top: 6px; }
        table.detalles th { background: #2b6cb0; color: #fff; padding: 4px 3px; font-size: 7px; text-align: center; text-transform: uppercase; font-weight: bold; }
        table.detalles td { padding: 3px 4px; font-size: 8px; border-bottom: 1px solid #e2e8f0; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }

        .totales-table { width: 100%; border-collapse: collapse; border: 1px solid #cbd5e0; }
        .totales-table td { padding: 2px 6px; font-size: 7.5px; border-bottom: 1px solid #edf2f7; }
        .totales-table .lbl { font-weight: bold; color: #4a5568; }
        .totales-table .val { text-align: right; width: 70px; color: #2d3748; }
        .totales-table .total-row td { background: #2b6cb0; color: #fff; font-weight: bold; font-size: 9px; border: none; padding: 4px 6px; }

        .proforma-badge {
            background: #ebf4ff; color: #2b6cb0; border: 2px dashed #2b6cb0;
            text-align: center; padding: 6px; margin-top: 6px; font-size: 9px; font-weight: bold;
        }
    </style>
</head>
<body>
<div class="page-wrapper">

    <div class="proforma-badge">DOCUMENTO SIN VALIDEZ TRIBUTARIA - PROFORMA</div>

    {{-- ============== HEADER ============== --}}
    <table class="layout" style="margin-top: 6px;">
        <tr>
            <td style="width: 52%; padding-right: 6px;">
                <div class="logo-emisor-box">
                    <div class="logo-area">
                        @if($logoPath)
                            <img src="{{ $logoPath }}">
                        @else
                            <div class="logo-fallback">{{ $proforma->emisor->nombre_comercial ?? $proforma->emisor->razon_social }}</div>
                        @endif
                    </div>
                    <div class="emisor-name">{{ $proforma->emisor->razon_social }}</div>
                    <div class="emisor-detail"><b>DIR MATRIZ:</b> {{ $proforma->emisor->direccion_matriz }}</div>
                    <div class="emisor-detail"><b>DIR SUCURSAL:</b> {{ $proforma->establecimiento->direccion ?? '' }}</div>
                    <div class="emisor-detail"><b>CONTRIBUYENTE ESPECIAL NRO:</b> {{ $proforma->emisor->contribuyente_especial ?? '' }}</div>
                    <div class="emisor-detail"><b>OBLIGADO A LLEVAR CONTABILIDAD:</b> {{ $proforma->emisor->obligado_contabilidad ? 'SI' : 'NO' }}</div>
                    @if($proforma->emisor->agente_retencion)
                        <div class="emisor-detail"><b>AGENTE DE RETENCIÓN RES. NRO:</b> {{ $proforma->emisor->agente_retencion }}</div>
                    @endif
                </div>
            </td>
            <td style="width: 48%;">
                <div class="doc-box">
                    <div class="doc-header">
                        <div class="doc-ruc">R.U.C.: {{ $proforma->emisor->ruc }}</div>
                        <div class="doc-type">PROFORMA</div>
                        <div class="doc-number">No. {{ $proforma->establecimiento->codigo }}-{{ $proforma->ptoEmision->codigo }}-{{ str_pad($proforma->secuencial, 9, '0', STR_PAD_LEFT) }}</div>
                    </div>
                    <div class="doc-body">
                        <div class="doc-field">
                            <div class="doc-field-label">Estado</div>
                            <div class="doc-field-value">{{ $proforma->estado }}</div>
                        </div>
                        @if($proforma->fecha_vencimiento)
                        <div class="doc-field">
                            <div class="doc-field-label">Fecha de Vencimiento</div>
                            <div class="doc-field-value">{{ $proforma->fecha_vencimiento->format('d/m/Y') }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </td>
        </tr>
    </table>

    {{-- ============== CLIENTE ============== --}}
    <table class="cliente-table">
        <tr><td colspan="4" class="section-header">DATOS DEL CLIENTE</td></tr>
        <tr>
            <td class="lbl">Razón Social / Nombres:</td>
            <td colspan="3">{{ $proforma->cliente->razon_social }}</td>
        </tr>
        <tr>
            <td class="lbl">Identificación:</td>
            <td>{{ $proforma->cliente->identificacion }}</td>
            <td class="lbl" style="width: 110px;">Fecha de Emisión:</td>
            <td>{{ $proforma->fecha_emision->format('d/m/Y') }}</td>
        </tr>
        @if($proforma->cliente->direccion)
        <tr>
            <td class="lbl">Dirección:</td>
            <td colspan="3">{{ $proforma->cliente->direccion }}</td>
        </tr>
        @endif
        @if($proforma->cliente->email)
        <tr>
            <td class="lbl">Email:</td>
            <td colspan="3">{{ $proforma->cliente->email }}</td>
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
            @foreach($proforma->detalles as $det)
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

    {{-- ============== BOTTOM ============== --}}
    @php
        $subtotal15 = 0; $subtotal5 = 0; $subtotal0 = 0; $subtotalNoObj = 0; $subtotalExento = 0;
        $iva15 = 0; $iva5 = 0;
        foreach ($proforma->detalles as $det) {
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
                @if($proforma->observaciones)
                <table style="width: 100%; border-collapse: collapse; border: 1px solid #cbd5e0; margin-bottom: 5px;">
                    <tr><td class="section-header">Observaciones</td></tr>
                    <tr><td style="padding: 4px 6px; font-size: 7.5px;">{{ $proforma->observaciones }}</td></tr>
                </table>
                @endif

                @if($proforma->forma_pago)
                <table style="width: 100%; border-collapse: collapse; border: 1px solid #cbd5e0;">
                    <tr>
                        <td class="section-header">Forma de Pago</td>
                        <td class="section-header" style="width: 70px; text-align: right;">Valor</td>
                    </tr>
                    <tr>
                        <td style="padding: 3px 6px; font-size: 7.5px;">{{ $formas[$proforma->forma_pago] ?? $proforma->forma_pago }}</td>
                        <td style="padding: 3px 6px; font-size: 7.5px; text-align: right; font-weight: bold;">{{ number_format($proforma->forma_pago_valor, 2) }}</td>
                    </tr>
                </table>
                @endif
            </td>
            <td style="width: 45%;">
                <table class="totales-table">
                    <tr><td class="lbl">SUBTOTAL 15%</td><td class="val">{{ number_format($subtotal15, 2) }}</td></tr>
                    <tr><td class="lbl">SUBTOTAL 5%</td><td class="val">{{ number_format($subtotal5, 2) }}</td></tr>
                    <tr><td class="lbl">SUBTOTAL 0%</td><td class="val">{{ number_format($subtotal0, 2) }}</td></tr>
                    <tr><td class="lbl">SUBTOTAL no objeto de IVA</td><td class="val">{{ number_format($subtotalNoObj, 2) }}</td></tr>
                    <tr><td class="lbl">SUBTOTAL exento de IVA</td><td class="val">{{ number_format($subtotalExento, 2) }}</td></tr>
                    <tr style="border-bottom: 2px solid #cbd5e0;"><td class="lbl">SUBTOTAL SIN IMPUESTOS</td><td class="val" style="font-weight: bold;">{{ number_format($proforma->total_sin_impuestos, 2) }}</td></tr>
                    <tr><td class="lbl">TOTAL Descuento</td><td class="val">{{ number_format($proforma->total_descuento, 2) }}</td></tr>
                    @if($iva15 > 0)
                    <tr><td class="lbl">IVA 15%</td><td class="val">{{ number_format($iva15, 2) }}</td></tr>
                    @endif
                    @if($iva5 > 0)
                    <tr><td class="lbl">IVA 5%</td><td class="val">{{ number_format($iva5, 2) }}</td></tr>
                    @endif
                    <tr class="total-row"><td>IMPORTE TOTAL</td><td style="text-align: right;">{{ number_format($proforma->importe_total, 2) }}</td></tr>
                </table>
            </td>
        </tr>
    </table>

</div>
</body>
</html>
