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
        .ret-block { margin-bottom: 3px; padding: 2px 0; border-bottom: 1px dotted #999; }
        .ret-block:last-child { border-bottom: none; }
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
        .ds-header { font-size: 7px; font-weight: bold; text-align: center; margin-top: 3px; }
    </style>
</head>
<body>
<div class="ticket">

    {{-- ============== HEADER ============== --}}
    <div class="logo-area">
        @if($logoPath)
            <img src="{{ $logoPath }}">
        @else
            <div class="company-name">{{ $ret->emisor->nombre_comercial ?? $ret->emisor->razon_social }}</div>
        @endif
    </div>
    <div class="company-name">{{ $ret->emisor->razon_social }}</div>
    <div class="info-line">RUC: {{ $ret->emisor->ruc }}</div>
    <div class="info-line">Dir: {{ $ret->emisor->direccion_matriz }}</div>
    @if($ret->establecimiento->direccion ?? false)
    <div class="info-line">Suc: {{ $ret->establecimiento->direccion }}</div>
    @endif
    @if($ret->emisor->contribuyente_especial)
    <div class="info-line">Contrib. Especial: {{ $ret->emisor->contribuyente_especial }}</div>
    @endif
    <div class="info-line">Obligado Contab: {{ $ret->emisor->obligado_contabilidad ? 'SI' : 'NO' }}</div>
    @if($ret->emisor->agente_retencion)
    <div class="info-line">Agente Ret. Nro: {{ $ret->emisor->agente_retencion }}</div>
    @endif
    @if($ret->emisor->regimen?->esRimpe())
    <div class="info-line">{{ $ret->emisor->regimen->nombre() }}</div>
    @endif

    <div class="separator-double"></div>

    {{-- ============== DOCUMENTO ============== --}}
    <div class="doc-title">RETENCION ATS 2.0</div>
    <div class="doc-number">{{ $ret->establecimiento->codigo }}-{{ $ret->ptoEmision->codigo }}-{{ str_pad($ret->secuencial, 9, '0', STR_PAD_LEFT) }}</div>
    <div class="info-line">Fecha: {{ $ret->fecha_emision->format('d/m/Y') }}</div>
    <div class="info-line">Periodo Fiscal: {{ $ret->periodo_fiscal }}</div>
    <div class="info-line">Ambiente: {{ ($ret->ambiente ?? $ret->emisor->ambiente->value) == '1' ? 'PRUEBAS' : 'PRODUCCION' }}</div>

    <div class="separator"></div>

    {{-- ============== SUJETO RETENIDO ============== --}}
    <div class="field-value">SUJETO RETENIDO:</div>
    <div class="field-value">{{ $ret->cliente->razon_social }}</div>
    <div class="field-label">RUC/CI: {{ $ret->cliente->identificacion }}</div>
    <div class="field-label">Parte Rel: {{ $ret->parte_rel ?? 'NO' }}</div>

    <div class="separator"></div>

    {{-- ============== DOCUMENTOS SUSTENTO Y RETENCIONES ============== --}}
    @php
        $codImpuestos = ['1' => 'RENTA', '2' => 'IVA', '6' => 'ISD'];
        $totalRetenido = 0;
    @endphp

    @foreach($ret->docSustentos as $ds)
    <div class="ds-header">--- DOC SUSTENTO ---</div>
    <div class="field-label">Tipo: {{ $ds->cod_doc_sustento }} | Num: {{ $ds->num_doc_sustento }}</div>
    <div class="field-label">Fecha: {{ $ds->fecha_emision_doc_sustento->format('d/m/Y') }}</div>
    <div class="field-label">Total: {{ number_format($ds->importe_total, 2) }}</div>

    <div class="field-value center" style="margin-top: 2px;">RETENCIONES</div>

    @foreach($ds->desgloses as $d)
    @php $totalRetenido += $d->valor_retenido; @endphp
    <div class="ret-block">
        <div class="field-value">{{ $codImpuestos[$d->codigo_impuesto] ?? $d->codigo_impuesto }} - Cod: {{ $d->codigo_retencion }}</div>
        <div class="field-label">Base: {{ number_format($d->base_imponible, 2) }} | %: {{ number_format($d->porcentaje_retener, 2) }}%</div>
        <div class="field-value">Valor Ret: {{ number_format($d->valor_retenido, 2) }}</div>
    </div>
    @endforeach
    <div class="separator"></div>
    @endforeach

    <div class="separator-double"></div>

    {{-- ============== TOTAL ============== --}}
    <table class="totals">
        <tr class="total-row"><td>TOTAL RETENIDO</td><td class="r">{{ number_format($totalRetenido, 2) }}</td></tr>
    </table>

    {{-- ============== INFO ADICIONAL ============== --}}
    @if($ret->cliente->direccion || $ret->cliente->telefono || $ret->cliente->email)
    <div class="separator"></div>
    @if($ret->cliente->direccion)
    <div class="field-label">Dir: {{ $ret->cliente->direccion }}</div>
    @endif
    @if($ret->cliente->telefono)
    <div class="field-label">Tel: {{ $ret->cliente->telefono }}</div>
    @endif
    @if($ret->cliente->email)
    <div class="field-label">Email: {{ $ret->cliente->email }}</div>
    @endif
    @endif

    <div class="separator-double"></div>

    {{-- ============== AUTORIZACION ============== --}}
    <div class="center">
        @if($ret->estado === 'AUTORIZADO')
            <span class="badge badge-ok">AUTORIZADO</span>
        @elseif($ret->estado === 'NO AUTORIZADO')
            <span class="badge">NO AUTORIZADO</span>
        @else
            <span class="badge">{{ $ret->estado }}</span>
        @endif
    </div>

    @if($ret->numero_autorizacion ?? $ret->clave_acceso)
    <div class="info-line" style="margin-top: 2px;">Aut: {{ $ret->numero_autorizacion ?? $ret->clave_acceso ?? 'PENDIENTE' }}</div>
    @endif
    @if($ret->fecha_autorizacion)
    <div class="info-line">F. Aut: {{ $ret->fecha_autorizacion->format('d/m/Y H:i:s') }}</div>
    @endif

    {{-- ============== BARCODE / QR ============== --}}
    @if($barcode)
    <div class="barcode-area">
        <img src="{{ $barcode }}">
    </div>
    @endif

    @if($ret->clave_acceso)
    <div class="clave-text">{{ $ret->clave_acceso }}</div>
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
