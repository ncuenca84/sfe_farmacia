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
            <div class="company-name">{{ $nd->emisor->nombre_comercial ?? $nd->emisor->razon_social }}</div>
        @endif
    </div>
    <div class="company-name">{{ $nd->emisor->razon_social }}</div>
    <div class="info-line">RUC: {{ $nd->emisor->ruc }}</div>
    <div class="info-line">Dir: {{ $nd->emisor->direccion_matriz }}</div>
    @if($nd->establecimiento->direccion ?? false)
    <div class="info-line">Suc: {{ $nd->establecimiento->direccion }}</div>
    @endif
    @if($nd->emisor->contribuyente_especial)
    <div class="info-line">Contrib. Especial: {{ $nd->emisor->contribuyente_especial }}</div>
    @endif
    <div class="info-line">Obligado Contab: {{ $nd->emisor->obligado_contabilidad ? 'SI' : 'NO' }}</div>
    @if($nd->emisor->agente_retencion)
    <div class="info-line">Agente Ret. Nro: {{ $nd->emisor->agente_retencion }}</div>
    @endif
    @if($nd->emisor->regimen?->esRimpe())
    <div class="info-line">{{ $nd->emisor->regimen->nombre() }}</div>
    @endif

    <div class="separator-double"></div>

    {{-- ============== DOCUMENTO ============== --}}
    <div class="doc-title">NOTA DE DEBITO</div>
    <div class="doc-number">{{ $nd->establecimiento->codigo }}-{{ $nd->ptoEmision->codigo }}-{{ str_pad($nd->secuencial, 9, '0', STR_PAD_LEFT) }}</div>
    <div class="info-line">Fecha: {{ $nd->fecha_emision->format('d/m/Y') }}</div>
    <div class="info-line">Ambiente: {{ ($nd->ambiente ?? $nd->emisor->ambiente->value) == '1' ? 'PRUEBAS' : 'PRODUCCION' }}</div>

    <div class="separator"></div>

    {{-- ============== CLIENTE ============== --}}
    <div class="field-value">CLIENTE: {{ $nd->cliente->razon_social }}</div>
    <div class="field-label">RUC/CI: {{ $nd->cliente->identificacion }}</div>

    <div class="separator"></div>

    {{-- ============== DOC MODIFICADO ============== --}}
    <div class="field-value">DOC. QUE SE MODIFICA:</div>
    <div class="field-label">Tipo: {{ $nd->cod_doc_modificado == '01' ? 'FACTURA' : $nd->cod_doc_modificado }}</div>
    <div class="field-label">No: {{ $nd->num_doc_modificado }}</div>
    @if($nd->fecha_emision_doc_sustento)
    <div class="field-label">Fecha: {{ $nd->fecha_emision_doc_sustento->format('d/m/Y') }}</div>
    @endif

    <div class="separator"></div>

    {{-- ============== MOTIVOS ============== --}}
    <table class="items">
        <thead>
            <tr>
                <th>Razon / Motivo</th>
                <th class="r" style="width: 50px;">Valor</th>
            </tr>
        </thead>
        <tbody>
            @foreach($nd->motivos as $motivo)
            <tr>
                <td>{{ $motivo->razon }}</td>
                <td class="r">{{ number_format($motivo->valor, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="separator-double"></div>

    {{-- ============== TOTALES ============== --}}
    @php
        $subtotal15 = 0; $subtotal5 = 0; $subtotal0 = 0; $subtotalNoObj = 0; $subtotalExento = 0;
        $iva15 = 0; $iva5 = 0;
        foreach ($nd->motivos as $motivo) {
            $valor = $motivo->valor ?? 0;
            $impIva = $motivo->impuestoIva;
            if ($impIva) {
                $tarifa = (float)($impIva->tarifa ?? 0);
                $ivaValor = round($valor * ($tarifa / 100), 2);
                if ($tarifa == 15) { $subtotal15 += $valor; $iva15 += $ivaValor; }
                elseif ($tarifa == 5) { $subtotal5 += $valor; $iva5 += $ivaValor; }
                elseif ($tarifa == 0 && $impIva->codigo_porcentaje == '0') { $subtotal0 += $valor; }
                elseif ($impIva->codigo_porcentaje == '6') { $subtotalNoObj += $valor; }
                elseif ($impIva->codigo_porcentaje == '7') { $subtotalExento += $valor; }
                else { $subtotal15 += $valor; $iva15 += $ivaValor; }
            } else {
                $subtotal0 += $valor;
            }
        }
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
        <tr><td class="bold">SUBTOTAL</td><td class="r bold">{{ number_format($nd->total_sin_impuestos, 2) }}</td></tr>
        @if($iva15 > 0)
        <tr><td>IVA 15%</td><td class="r">{{ number_format($iva15, 2) }}</td></tr>
        @endif
        @if($iva5 > 0)
        <tr><td>IVA 5%</td><td class="r">{{ number_format($iva5, 2) }}</td></tr>
        @endif
        <tr class="total-row"><td>VALOR TOTAL</td><td class="r">{{ number_format($nd->importe_total, 2) }}</td></tr>
    </table>

    {{-- ============== INFO ADICIONAL ============== --}}
    @if($nd->observaciones)
    <div class="separator"></div>
    <div class="field-label">OBS: {{ $nd->observaciones }}</div>
    @endif

    @if($nd->camposAdicionales && $nd->camposAdicionales->count())
    <div class="separator"></div>
    @foreach($nd->camposAdicionales as $campo)
    <div class="field-label">{{ $campo->nombre }}: {{ $campo->valor }}</div>
    @endforeach
    @endif

    <div class="separator-double"></div>

    {{-- ============== AUTORIZACION ============== --}}
    <div class="center">
        @if($nd->estado === 'AUTORIZADO')
            <span class="badge badge-ok">AUTORIZADO</span>
        @elseif($nd->estado === 'NO AUTORIZADO')
            <span class="badge">NO AUTORIZADO</span>
        @else
            <span class="badge">{{ $nd->estado }}</span>
        @endif
    </div>

    @if($nd->numero_autorizacion ?? $nd->clave_acceso)
    <div class="info-line" style="margin-top: 2px;">Aut: {{ $nd->numero_autorizacion ?? $nd->clave_acceso ?? 'PENDIENTE' }}</div>
    @endif
    @if($nd->fecha_autorizacion)
    <div class="info-line">F. Aut: {{ $nd->fecha_autorizacion->format('d/m/Y H:i:s') }}</div>
    @endif

    {{-- ============== BARCODE / QR ============== --}}
    @if($barcode)
    <div class="barcode-area">
        <img src="{{ $barcode }}">
    </div>
    @endif

    @if($nd->clave_acceso)
    <div class="clave-text">{{ $nd->clave_acceso }}</div>
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
