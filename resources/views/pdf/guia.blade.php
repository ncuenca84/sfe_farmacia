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
        .doc-type { font-size: 12px; font-weight: bold; margin: 3px 0 2px; letter-spacing: 1px; }
        .doc-number { font-size: 10px; font-weight: bold; }
        .doc-body { padding: 6px 10px; }
        .doc-field { margin-bottom: 4px; }
        .doc-field-label { font-size: 7px; color: #718096; text-transform: uppercase; font-weight: bold; }
        .doc-field-value { font-size: 8px; font-weight: bold; color: #2d3748; word-break: break-all; }

        .clave-box { border: 1px solid #cbd5e0; padding: 5px 8px; text-align: center; margin-top: 5px; }
        .clave-label { font-size: 7px; font-weight: bold; color: #718096; text-transform: uppercase; }
        .clave-number { font-size: 7px; font-family: 'DejaVu Sans Mono', monospace; color: #4a5568; word-break: break-all; }

        .info-table { width: 100%; border-collapse: collapse; margin-top: 6px; border: 1px solid #cbd5e0; }
        .info-table td { padding: 2px 8px; font-size: 8px; }
        .info-table .lbl { font-weight: bold; color: #4a5568; width: 180px; }

        table.detalles { width: 100%; border-collapse: collapse; margin-top: 6px; }
        table.detalles th { background: #2b6cb0; color: #fff; padding: 4px 3px; font-size: 7px; text-align: center; text-transform: uppercase; font-weight: bold; }
        table.detalles td { padding: 3px 4px; font-size: 8px; border-bottom: 1px solid #e2e8f0; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }

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
                            <div class="logo-fallback">{{ $guia->emisor->nombre_comercial ?? $guia->emisor->razon_social }}</div>
                        @endif
                    </div>
                    <div class="emisor-name">{{ $guia->emisor->razon_social }}</div>
                    <div class="emisor-detail"><b>DIR MATRIZ:</b> {{ $guia->emisor->direccion_matriz }}</div>
                    <div class="emisor-detail"><b>DIR SUCURSAL:</b> {{ $guia->establecimiento->direccion ?? '' }}</div>
                    <div class="emisor-detail"><b>CONTRIBUYENTE ESPECIAL NRO:</b> {{ $guia->emisor->contribuyente_especial ?? '' }}</div>
                    <div class="emisor-detail"><b>OBLIGADO A LLEVAR CONTABILIDAD:</b> {{ $guia->emisor->obligado_contabilidad ? 'SI' : 'NO' }}</div>
                    @if($guia->emisor->agente_retencion)
                        <div class="emisor-detail"><b>AGENTE DE RETENCIÓN RES. NRO:</b> {{ $guia->emisor->agente_retencion }}</div>
                    @endif
                    @if($guia->emisor->regimen?->esRimpe())
                        <div class="emisor-badge">{{ $guia->emisor->regimen->nombre() }}</div>
                    @endif
                </div>
            </td>
            <td style="width: 48%;">
                <div class="doc-box">
                    <div class="doc-header">
                        <div class="doc-ruc">R.U.C.: {{ $guia->emisor->ruc }}</div>
                        <div class="doc-type">GUÍA DE REMISIÓN</div>
                        <div class="doc-number">No. {{ $guia->establecimiento->codigo }}-{{ $guia->ptoEmision->codigo }}-{{ str_pad($guia->secuencial, 9, '0', STR_PAD_LEFT) }}</div>
                    </div>
                    <div class="doc-body">
                        <div class="doc-field">
                            <div class="doc-field-label">Número de Autorización</div>
                            <div class="doc-field-value">{{ $guia->numero_autorizacion ?? $guia->clave_acceso ?? 'PENDIENTE' }}</div>
                        </div>
                        <div class="doc-field">
                            <div class="doc-field-label">Fecha y Hora de Autorización</div>
                            <div class="doc-field-value">{{ $guia->fecha_autorizacion ? $guia->fecha_autorizacion->format('d/m/Y H:i:s') : 'PENDIENTE' }}</div>
                        </div>
                        <table class="layout" style="margin-top: 4px;">
                            <tr>
                                <td style="width: 50%; text-align: center; padding: 3px; background: #f7fafc; border: 1px solid #e2e8f0; font-size: 8px;">
                                    <b style="display: block; font-size: 7px; color: #718096;">AMBIENTE</b>
                                    {{ ($guia->ambiente ?? $guia->emisor->ambiente->value) == '1' ? 'PRUEBAS' : 'PRODUCCIÓN' }}
                                </td>
                                <td style="width: 50%; text-align: center; padding: 3px; background: #f7fafc; border: 1px solid #e2e8f0; font-size: 8px;">
                                    <b style="display: block; font-size: 7px; color: #718096;">EMISIÓN</b>
                                    NORMAL
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                @if($guia->clave_acceso)
                <div class="clave-box">
                    <div class="clave-label">CLAVE DE ACCESO</div>
                    @if($barcode)
                        <img src="{{ $barcode }}" style="width: 100%; max-height: 30px; margin: 3px 0;">
                    @endif
                    <div class="clave-number">{{ $guia->clave_acceso }}</div>
                </div>
                @endif
            </td>
        </tr>
    </table>

    {{-- ============== INFO TRANSPORTE ============== --}}
    <table class="info-table">
        <tr><td colspan="4" class="section-header">INFORMACIÓN DEL TRANSPORTE</td></tr>
        <tr>
            <td class="lbl">RUC Transportista:</td>
            <td>{{ $guia->ruc_transportista }}</td>
            <td class="lbl" style="width: 130px;">Fecha de Emisión:</td>
            <td>{{ $guia->fecha_emision->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td class="lbl">Razón Social Transportista:</td>
            <td colspan="3">{{ $guia->razon_social_transportista }}</td>
        </tr>
        <tr>
            <td class="lbl">Placa:</td>
            <td>{{ $guia->placa }}</td>
            <td class="lbl" style="width: 130px;">Dir. Partida:</td>
            <td>{{ $guia->dir_partida }}</td>
        </tr>
        <tr>
            <td class="lbl">Fecha Inicio Transporte:</td>
            <td>{{ $guia->fecha_ini_transporte ? $guia->fecha_ini_transporte->format('d/m/Y') : '' }}</td>
            <td class="lbl" style="width: 130px;">Fecha Fin Transporte:</td>
            <td>{{ $guia->fecha_fin_transporte ? $guia->fecha_fin_transporte->format('d/m/Y') : '' }}</td>
        </tr>
    </table>

    {{-- ============== DESTINATARIOS ============== --}}
    @foreach($guia->detalles as $det)
    <table class="info-table" style="margin-top: 6px;">
        <tr><td colspan="4" class="section-header">DESTINATARIO</td></tr>
        <tr>
            <td class="lbl">Identificación:</td>
            <td>{{ $det->identificacion_destinatario }}</td>
            <td class="lbl" style="width: 130px;">Razón Social:</td>
            <td>{{ $det->razon_social_destinatario }}</td>
        </tr>
        <tr>
            <td class="lbl">Dirección:</td>
            <td colspan="3">{{ $det->dir_destinatario }}</td>
        </tr>
        <tr>
            <td class="lbl">Motivo Traslado:</td>
            <td colspan="3">{{ $det->motivo_traslado }}</td>
        </tr>
        @if($det->num_doc_sustento)
        <tr>
            <td class="lbl">Doc. Sustento:</td>
            <td>{{ $det->num_doc_sustento }}</td>
            <td class="lbl" style="width: 130px;">No. Autorización:</td>
            <td>{{ $det->num_aut_doc_sustento }}</td>
        </tr>
        @endif
    </table>

    <table class="detalles">
        <thead>
            <tr>
                <th style="width: 80px;">Cod. Principal</th>
                <th>Descripción</th>
                <th style="width: 60px;">Cantidad</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">{{ $det->codigo_principal }}</td>
                <td>{{ $det->descripcion }}</td>
                <td class="text-center">{{ rtrim(rtrim(number_format($det->cantidad, 4), '0'), '.') }}</td>
            </tr>
        </tbody>
    </table>
    @endforeach

    @if($guia->observaciones)
    <table style="width: 100%; border-collapse: collapse; border: 1px solid #cbd5e0; margin-top: 6px;">
        <tr><td class="section-header">Observaciones</td></tr>
        <tr><td style="padding: 4px 6px; font-size: 7.5px;">{{ $guia->observaciones }}</td></tr>
    </table>
    @endif

    {{-- ============== INFO ADICIONAL ============== --}}
    @if($guia->camposAdicionales && $guia->camposAdicionales->count())
    <table style="width: 100%; border-collapse: collapse; border: 1px solid #cbd5e0; margin-top: 6px;">
        <tr><td colspan="2" class="section-header">Información Adicional</td></tr>
        @foreach($guia->camposAdicionales as $campo)
        <tr><td style="padding: 1px 6px; font-size: 7.5px; width: 70px; font-weight: bold; color: #4a5568;">{{ $campo->nombre }}:</td><td style="padding: 1px 6px; font-size: 7.5px;">{{ $campo->valor }}</td></tr>
        @endforeach
    </table>
    @endif

    {{-- ============== FOOTER ============== --}}
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
                <div class="footer-clave">{{ $guia->clave_acceso }}</div>
            </td>
            <td style="width: 95px;">
                <div class="footer-label">Estado</div>
                @if($guia->estado === 'AUTORIZADO')
                    <span class="auth-badge auth-autorizado">AUTORIZADO</span>
                @elseif($guia->estado === 'NO AUTORIZADO')
                    <span class="auth-badge auth-no-autorizado">NO AUTORIZADO</span>
                @else
                    <span class="auth-badge auth-pendiente">{{ $guia->estado }}</span>
                @endif
                @if($guia->fecha_autorizacion)
                    <div style="font-size: 7px; color: #718096; margin-top: 3px;">
                        {{ $guia->fecha_autorizacion->format('d/m/Y') }}<br>
                        {{ $guia->fecha_autorizacion->format('H:i:s') }}
                    </div>
                @endif
            </td>
        </tr>
    </table>
    @endif

</div>
</body>
</html>
