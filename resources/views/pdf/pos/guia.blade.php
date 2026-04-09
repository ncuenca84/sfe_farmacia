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
        .section-title { font-size: 7px; font-weight: bold; text-align: center; text-transform: uppercase; margin: 2px 0; }
        table.items { width: 100%; border-collapse: collapse; }
        table.items th { font-size: 7px; font-weight: bold; text-align: left; border-bottom: 1px solid #000; padding: 1px 0; }
        table.items td { font-size: 7px; padding: 1px 0; vertical-align: top; }
        table.items th.r, table.items td.r { text-align: right; }
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
            <div class="company-name">{{ $guia->emisor->nombre_comercial ?? $guia->emisor->razon_social }}</div>
        @endif
    </div>
    <div class="company-name">{{ $guia->emisor->razon_social }}</div>
    <div class="info-line">RUC: {{ $guia->emisor->ruc }}</div>
    <div class="info-line">Dir: {{ $guia->emisor->direccion_matriz }}</div>
    @if($guia->establecimiento->direccion ?? false)
    <div class="info-line">Suc: {{ $guia->establecimiento->direccion }}</div>
    @endif
    @if($guia->emisor->contribuyente_especial)
    <div class="info-line">Contrib. Especial: {{ $guia->emisor->contribuyente_especial }}</div>
    @endif
    <div class="info-line">Obligado Contab: {{ $guia->emisor->obligado_contabilidad ? 'SI' : 'NO' }}</div>
    @if($guia->emisor->agente_retencion)
    <div class="info-line">Agente Ret. Nro: {{ $guia->emisor->agente_retencion }}</div>
    @endif
    @if($guia->emisor->regimen?->esRimpe())
    <div class="info-line">{{ $guia->emisor->regimen->nombre() }}</div>
    @endif

    <div class="separator-double"></div>

    {{-- ============== DOCUMENTO ============== --}}
    <div class="doc-title">GUIA DE REMISION</div>
    <div class="doc-number">{{ $guia->establecimiento->codigo }}-{{ $guia->ptoEmision->codigo }}-{{ str_pad($guia->secuencial, 9, '0', STR_PAD_LEFT) }}</div>
    <div class="info-line">Fecha: {{ $guia->fecha_emision->format('d/m/Y') }}</div>
    <div class="info-line">Ambiente: {{ ($guia->ambiente ?? $guia->emisor->ambiente->value) == '1' ? 'PRUEBAS' : 'PRODUCCION' }}</div>

    <div class="separator"></div>

    {{-- ============== TRANSPORTE ============== --}}
    <div class="section-title">TRANSPORTE</div>
    <div class="field-label">Transportista: {{ $guia->razon_social_transportista }}</div>
    <div class="field-label">RUC Transp: {{ $guia->ruc_transportista }}</div>
    <div class="field-label">Placa: {{ $guia->placa }}</div>
    <div class="field-label">Dir. Partida: {{ $guia->dir_partida }}</div>
    @if($guia->fecha_ini_transporte)
    <div class="field-label">F. Inicio: {{ $guia->fecha_ini_transporte->format('d/m/Y') }}</div>
    @endif
    @if($guia->fecha_fin_transporte)
    <div class="field-label">F. Fin: {{ $guia->fecha_fin_transporte->format('d/m/Y') }}</div>
    @endif

    <div class="separator"></div>

    {{-- ============== DESTINATARIOS ============== --}}
    @foreach($guia->detalles as $det)
    <div class="section-title">DESTINATARIO</div>
    <div class="field-value">{{ $det->razon_social_destinatario }}</div>
    <div class="field-label">ID: {{ $det->identificacion_destinatario }}</div>
    <div class="field-label">Dir: {{ $det->dir_destinatario }}</div>
    <div class="field-label">Motivo: {{ $det->motivo_traslado }}</div>
    @if($det->num_doc_sustento)
    <div class="field-label">Doc: {{ $det->num_doc_sustento }}</div>
    <div class="field-label">Aut: {{ $det->num_aut_doc_sustento }}</div>
    @endif

    <table class="items">
        <thead>
            <tr>
                <th style="width: 50px;">Codigo</th>
                <th>Descripcion</th>
                <th class="r" style="width: 35px;">Cant</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $det->codigo_principal }}</td>
                <td>{{ $det->descripcion }}</td>
                <td class="r">{{ rtrim(rtrim(number_format($det->cantidad, 2), '0'), '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="separator"></div>
    @endforeach

    {{-- ============== INFO ADICIONAL ============== --}}
    @if($guia->observaciones)
    <div class="field-label">OBS: {{ $guia->observaciones }}</div>
    @endif

    @if($guia->camposAdicionales && $guia->camposAdicionales->count())
    @foreach($guia->camposAdicionales as $campo)
    <div class="field-label">{{ $campo->nombre }}: {{ $campo->valor }}</div>
    @endforeach
    @endif

    <div class="separator-double"></div>

    {{-- ============== AUTORIZACION ============== --}}
    <div class="center">
        @if($guia->estado === 'AUTORIZADO')
            <span class="badge badge-ok">AUTORIZADO</span>
        @elseif($guia->estado === 'NO AUTORIZADO')
            <span class="badge">NO AUTORIZADO</span>
        @else
            <span class="badge">{{ $guia->estado }}</span>
        @endif
    </div>

    @if($guia->numero_autorizacion ?? $guia->clave_acceso)
    <div class="info-line" style="margin-top: 2px;">Aut: {{ $guia->numero_autorizacion ?? $guia->clave_acceso ?? 'PENDIENTE' }}</div>
    @endif
    @if($guia->fecha_autorizacion)
    <div class="info-line">F. Aut: {{ $guia->fecha_autorizacion->format('d/m/Y H:i:s') }}</div>
    @endif

    {{-- ============== BARCODE / QR ============== --}}
    @if($barcode)
    <div class="barcode-area">
        <img src="{{ $barcode }}">
    </div>
    @endif

    @if($guia->clave_acceso)
    <div class="clave-text">{{ $guia->clave_acceso }}</div>
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
