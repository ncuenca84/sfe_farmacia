<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 0; size: 72mm auto; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans Mono', 'Courier New', monospace; font-size: 8px; color: #000; width: 72mm; }
        .ticket { padding: 4px 3px; width: 72mm; }
        .center { text-align: center; }
        .right { text-align: right; }
        .bold { font-weight: bold; }
        .separator { border-bottom: 1px dashed #000; margin: 3px 0; }
        .separator-double { border-bottom: 2px solid #000; margin: 3px 0; }
        .logo-area { text-align: center; margin-bottom: 2px; }
        .logo-area img { max-width: 50mm; max-height: 25mm; }
        .company-name { font-size: 9px; font-weight: bold; text-align: center; }
        .info-line { font-size: 7px; text-align: center; line-height: 1.3; }
        .doc-title { font-size: 10px; font-weight: bold; text-align: center; letter-spacing: 1px; }
        .doc-number { font-size: 9px; font-weight: bold; text-align: center; }
        .field-label { font-size: 7px; color: #333; }
        .field-value { font-size: 7px; font-weight: bold; }
        table.items { width: 100%; border-collapse: collapse; }
        table.items th { font-size: 7px; font-weight: bold; text-align: left; border-bottom: 1px solid #000; padding: 1px 0; }
        table.items td { font-size: 7px; padding: 1px 0; vertical-align: top; }
        table.items th.r, table.items td.r { text-align: right; }
        table.totals { width: 100%; border-collapse: collapse; }
        table.totals td { font-size: 7px; padding: 1px 0; }
        table.totals td.r { text-align: right; }
        table.totals tr.total-row td { font-size: 9px; font-weight: bold; border-top: 1px solid #000; padding-top: 2px; }
        .barcode-area { text-align: center; margin: 3px 0; }
        .barcode-area img { width: 100%; max-height: 25px; }
        .qr-area { text-align: center; margin: 2px 0; }
        .qr-area img { width: 30mm; height: 30mm; }
        .clave-text { font-size: 6px; word-break: break-all; text-align: center; line-height: 1.2; }
        .badge { font-size: 7px; font-weight: bold; padding: 1px 4px; display: inline; }
        .badge-ok { border: 1px solid #000; }
    </style>
</head>
<body>
<div class="ticket">

    {{-- ============== HEADER ============== --}}
    <div class="logo-area">
        @if($logoPath)
            <img src="{{ $logoPath }}">
        @else
            <div class="company-name">{{ $factura->emisor->nombre_comercial ?? $factura->emisor->razon_social }}</div>
        @endif
    </div>
    <div class="company-name">{{ $factura->emisor->razon_social }}</div>
    <div class="info-line">RUC: {{ $factura->emisor->ruc }}</div>
    <div class="info-line">Dir: {{ $factura->emisor->direccion_matriz }}</div>
    @if($factura->establecimiento->direccion ?? false)
    <div class="info-line">Suc: {{ $factura->establecimiento->direccion }}</div>
    @endif
    @if($factura->emisor->contribuyente_especial)
    <div class="info-line">Contrib. Especial: {{ $factura->emisor->contribuyente_especial }}</div>
    @endif
    <div class="info-line">Obligado Contab: {{ $factura->emisor->obligado_contabilidad ? 'SI' : 'NO' }}</div>
    @if($factura->emisor->agente_retencion)
    <div class="info-line">Agente Ret. Nro: {{ $factura->emisor->agente_retencion }}</div>
    @endif
    @if($factura->emisor->regimen?->esRimpe())
    <div class="info-line">{{ $factura->emisor->regimen->nombre() }}</div>
    @endif

    <div class="separator-double"></div>

    {{-- ============== DOCUMENTO ============== --}}
    <div class="doc-title">FACTURA</div>
    <div class="doc-number">{{ $factura->establecimiento->codigo }}-{{ $factura->ptoEmision->codigo }}-{{ str_pad($factura->secuencial, 9, '0', STR_PAD_LEFT) }}</div>
    <div class="info-line">Fecha: {{ $factura->fecha_emision->format('d/m/Y') }}</div>
    <div class="info-line">Ambiente: {{ ($factura->ambiente ?? $factura->emisor->ambiente->value) == '1' ? 'PRUEBAS' : 'PRODUCCION' }}</div>

    <div class="separator"></div>

    {{-- ============== CLIENTE ============== --}}
    <div class="field-value">CLIENTE: {{ $factura->cliente->razon_social }}</div>
    <div class="field-label">RUC/CI: {{ $factura->cliente->identificacion }}</div>
    @if($factura->cliente->direccion)
    <div class="field-label">Dir: {{ $factura->cliente->direccion }}</div>
    @endif
    @if($factura->guia_remision)
    <div class="field-label">Guia Rem: {{ $factura->guia_remision }}</div>
    @endif

    <div class="separator"></div>

    {{-- ============== DETALLES ============== --}}
    <table class="items">
        <thead>
            <tr>
                <th style="width: 25px;">Cant</th>
                <th>Descripcion</th>
                <th class="r" style="width: 45px;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($factura->detalles as $det)
            <tr>
                <td>{{ rtrim(rtrim(number_format($det->cantidad, 2), '0'), '.') }}</td>
                <td>{{ $det->descripcion }}</td>
                <td class="r">{{ number_format($det->precio_total_sin_impuesto, 2) }}</td>
            </tr>
            @if($det->descuento > 0)
            <tr>
                <td></td>
                <td style="font-size: 6px; color: #555;">  P.U: {{ number_format($det->precio_unitario, 2) }} Desc: {{ number_format($det->descuento, 2) }}</td>
                <td></td>
            </tr>
            @endif
            @endforeach
        </tbody>
    </table>

    <div class="separator-double"></div>

    {{-- ============== TOTALES ============== --}}
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
            '01' => 'SIN SIST. FINANCIERO',
            '15' => 'COMPENSACION DEUDAS',
            '16' => 'TARJETA DEBITO',
            '17' => 'DINERO ELECTRONICO',
            '18' => 'TARJETA PREPAGO',
            '19' => 'TARJETA CREDITO',
            '20' => 'OTROS SIST. FINANCIERO',
            '21' => 'ENDOSO TITULOS',
        ];
    @endphp

    <table class="totals">
        @if($subtotal15 > 0)
        <tr><td>SUBTOTAL 15%</td><td class="r">{{ number_format($subtotal15, 2) }}</td></tr>
        @endif
        @if($subtotal5 > 0)
        <tr><td>SUBTOTAL 5%</td><td class="r">{{ number_format($subtotal5, 2) }}</td></tr>
        @endif
        @if($subtotal0 > 0)
        <tr><td>SUBTOTAL 0%</td><td class="r">{{ number_format($subtotal0, 2) }}</td></tr>
        @endif
        @if($subtotalNoObj > 0)
        <tr><td>SUBTOTAL No Obj IVA</td><td class="r">{{ number_format($subtotalNoObj, 2) }}</td></tr>
        @endif
        @if($subtotalExento > 0)
        <tr><td>SUBTOTAL Exento</td><td class="r">{{ number_format($subtotalExento, 2) }}</td></tr>
        @endif
        <tr><td class="bold">SUBTOTAL</td><td class="r bold">{{ number_format($factura->total_sin_impuestos, 2) }}</td></tr>
        @if($factura->total_descuento > 0)
        <tr><td>DESCUENTO</td><td class="r">{{ number_format($factura->total_descuento, 2) }}</td></tr>
        @endif
        @if($iva15 > 0)
        <tr><td>IVA 15%</td><td class="r">{{ number_format($iva15, 2) }}</td></tr>
        @endif
        @if($iva5 > 0)
        <tr><td>IVA 5%</td><td class="r">{{ number_format($iva5, 2) }}</td></tr>
        @endif
        @if($factura->total_ice > 0)
        <tr><td>ICE</td><td class="r">{{ number_format($factura->total_ice, 2) }}</td></tr>
        @endif
        @if($factura->propina > 0)
        <tr><td>PROPINA</td><td class="r">{{ number_format($factura->propina, 2) }}</td></tr>
        @endif
        <tr class="total-row"><td>TOTAL</td><td class="r">{{ number_format($factura->importe_total, 2) }}</td></tr>
    </table>

    <div class="separator"></div>

    {{-- ============== FORMA DE PAGO ============== --}}
    <div class="field-label">F. PAGO: {{ $formas[$factura->forma_pago] ?? $factura->forma_pago }}</div>
    <div class="field-value" style="text-align: right;">{{ number_format($factura->forma_pago_valor, 2) }}</div>

    {{-- ============== INFO ADICIONAL ============== --}}
    @if($factura->observaciones)
    <div class="separator"></div>
    <div class="field-label">OBS: {{ $factura->observaciones }}</div>
    @endif

    @if($factura->camposAdicionales && $factura->camposAdicionales->count())
    <div class="separator"></div>
    @foreach($factura->camposAdicionales as $campo)
    <div class="field-label">{{ $campo->nombre }}: {{ $campo->valor }}</div>
    @endforeach
    @endif

    <div class="separator-double"></div>

    {{-- ============== AUTORIZACION ============== --}}
    <div class="center">
        @if($factura->estado === 'AUTORIZADO')
            <span class="badge badge-ok">AUTORIZADO</span>
        @elseif($factura->estado === 'NO AUTORIZADO')
            <span class="badge">NO AUTORIZADO</span>
        @else
            <span class="badge">{{ $factura->estado }}</span>
        @endif
    </div>

    @if($factura->numero_autorizacion ?? $factura->clave_acceso)
    <div class="info-line" style="margin-top: 2px;">Aut: {{ $factura->numero_autorizacion ?? $factura->clave_acceso ?? 'PENDIENTE' }}</div>
    @endif
    @if($factura->fecha_autorizacion)
    <div class="info-line">F. Aut: {{ $factura->fecha_autorizacion->format('d/m/Y H:i:s') }}</div>
    @endif

    {{-- ============== BARCODE / QR ============== --}}
    @if($barcode)
    <div class="barcode-area">
        <img src="{{ $barcode }}">
    </div>
    @endif

    @if($factura->clave_acceso)
    <div class="clave-text">{{ $factura->clave_acceso }}</div>
    @endif

    @if($qrCode)
    <div class="qr-area">
        <img src="{{ $qrCode }}">
    </div>
    @endif

    <div class="separator"></div>

</div>
</body>
</html>
