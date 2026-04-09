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
        .doc-type { font-size: 14px; font-weight: bold; margin: 3px 0 2px; letter-spacing: 1px; }
        .doc-number { font-size: 10px; font-weight: bold; }
        .doc-body { padding: 6px 10px; }
        .doc-field { margin-bottom: 4px; }
        .doc-field-label { font-size: 7px; color: #718096; text-transform: uppercase; font-weight: bold; }
        .doc-field-value { font-size: 8px; font-weight: bold; color: #2d3748; word-break: break-all; }

        .clave-box { border: 1px solid #cbd5e0; padding: 5px 8px; text-align: center; margin-top: 5px; }
        .clave-label { font-size: 7px; font-weight: bold; color: #718096; text-transform: uppercase; }
        .clave-number { font-size: 7px; font-family: 'DejaVu Sans Mono', monospace; color: #4a5568; word-break: break-all; }

        .cliente-table { width: 100%; border-collapse: collapse; margin-top: 6px; border: 1px solid #cbd5e0; }
        .cliente-table td { padding: 2px 8px; font-size: 8px; }
        .cliente-table .lbl { font-weight: bold; color: #4a5568; width: 160px; }

        table.detalles { width: 100%; border-collapse: collapse; margin-top: 6px; }
        table.detalles th { background: #2b6cb0; color: #fff; padding: 4px 3px; font-size: 7px; text-align: center; text-transform: uppercase; font-weight: bold; }
        table.detalles td { padding: 3px 4px; font-size: 8px; border-bottom: 1px solid #e2e8f0; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }

        .totales-table { width: 100%; border-collapse: collapse; border: 1px solid #cbd5e0; margin-top: 6px; }
        .totales-table td { padding: 2px 6px; font-size: 7.5px; border-bottom: 1px solid #edf2f7; }
        .totales-table .lbl { font-weight: bold; color: #4a5568; }
        .totales-table .val { text-align: right; width: 80px; color: #2d3748; }
        .totales-table .total-row td { background: #2b6cb0; color: #fff; font-weight: bold; font-size: 9px; border: none; padding: 4px 6px; }

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
                            <div class="logo-fallback">{{ $ret->emisor->nombre_comercial ?? $ret->emisor->razon_social }}</div>
                        @endif
                    </div>
                    <div class="emisor-name">{{ $ret->emisor->razon_social }}</div>
                    <div class="emisor-detail"><b>DIR MATRIZ:</b> {{ $ret->emisor->direccion_matriz }}</div>
                    <div class="emisor-detail"><b>DIR SUCURSAL:</b> {{ $ret->establecimiento->direccion ?? '' }}</div>
                    <div class="emisor-detail"><b>CONTRIBUYENTE ESPECIAL NRO:</b> {{ $ret->emisor->contribuyente_especial ?? '' }}</div>
                    <div class="emisor-detail"><b>OBLIGADO A LLEVAR CONTABILIDAD:</b> {{ $ret->emisor->obligado_contabilidad ? 'SI' : 'NO' }}</div>
                    @if($ret->emisor->agente_retencion)
                        <div class="emisor-detail"><b>AGENTE DE RETENCIÓN RES. NRO:</b> {{ $ret->emisor->agente_retencion }}</div>
                    @endif
                    @if($ret->emisor->regimen?->esRimpe())
                        <div class="emisor-badge">{{ $ret->emisor->regimen->nombre() }}</div>
                    @endif
                </div>
            </td>
            <td style="width: 48%;">
                <div class="doc-box">
                    <div class="doc-header">
                        <div class="doc-ruc">R.U.C.: {{ $ret->emisor->ruc }}</div>
                        <div class="doc-type">RETENCIÓN</div>
                        <div class="doc-number">No. {{ $ret->establecimiento->codigo }}-{{ $ret->ptoEmision->codigo }}-{{ str_pad($ret->secuencial, 9, '0', STR_PAD_LEFT) }}</div>
                    </div>
                    <div class="doc-body">
                        <div class="doc-field">
                            <div class="doc-field-label">Número de Autorización</div>
                            <div class="doc-field-value">{{ $ret->numero_autorizacion ?? $ret->clave_acceso ?? 'PENDIENTE' }}</div>
                        </div>
                        <div class="doc-field">
                            <div class="doc-field-label">Fecha y Hora de Autorización</div>
                            <div class="doc-field-value">{{ $ret->fecha_autorizacion ? $ret->fecha_autorizacion->format('d/m/Y H:i:s') : 'PENDIENTE' }}</div>
                        </div>
                        <table class="layout" style="margin-top: 4px;">
                            <tr>
                                <td style="width: 50%; text-align: center; padding: 3px; background: #f7fafc; border: 1px solid #e2e8f0; font-size: 8px;">
                                    <b style="display: block; font-size: 7px; color: #718096;">AMBIENTE</b>
                                    {{ ($ret->ambiente ?? $ret->emisor->ambiente->value) == '1' ? 'PRUEBAS' : 'PRODUCCIÓN' }}
                                </td>
                                <td style="width: 50%; text-align: center; padding: 3px; background: #f7fafc; border: 1px solid #e2e8f0; font-size: 8px;">
                                    <b style="display: block; font-size: 7px; color: #718096;">EMISIÓN</b>
                                    NORMAL
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                @if($ret->clave_acceso)
                <div class="clave-box">
                    <div class="clave-label">CLAVE DE ACCESO</div>
                    @if($barcode)
                        <img src="{{ $barcode }}" style="width: 100%; max-height: 30px; margin: 3px 0;">
                    @endif
                    <div class="clave-number">{{ $ret->clave_acceso }}</div>
                </div>
                @endif
            </td>
        </tr>
    </table>

    {{-- ============== SUJETO RETENIDO ============== --}}
    <table class="cliente-table">
        <tr><td colspan="4" class="section-header">SUJETO RETENIDO</td></tr>
        <tr>
            <td class="lbl">Razón Social / Nombres:</td>
            <td colspan="3">{{ $ret->cliente->razon_social }}</td>
        </tr>
        <tr>
            <td class="lbl">Identificación:</td>
            <td>{{ $ret->cliente->identificacion }}</td>
            <td class="lbl" style="width: 110px;">Fecha de Emisión:</td>
            <td>{{ $ret->fecha_emision->format('d/m/Y') }}</td>
        </tr>
    </table>

    {{-- ============== IMPUESTOS RETENIDOS ============== --}}
    @php
        $codImpuestos = ['1' => 'RENTA', '2' => 'IVA', '6' => 'ISD'];
        $totalRetenido = 0;
    @endphp
    <table class="detalles">
        <thead>
            <tr>
                <th>Impuesto</th>
                <th style="width: 70px;">Cód. Retención</th>
                <th style="width: 80px;">Base Imponible</th>
                <th style="width: 50px;">% Retención</th>
                <th style="width: 80px;">Valor Retenido</th>
                <th style="width: 60px;">Tipo Doc.</th>
                <th style="width: 90px;">No. Doc. Sustento</th>
                <th style="width: 70px;">Fecha Emisión</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ret->impuestosRetencion as $imp)
            @php $totalRetenido += $imp->valor_retenido; @endphp
            <tr>
                <td class="text-center">{{ $codImpuestos[$imp->codigo_impuesto] ?? $imp->codigo_impuesto }}</td>
                <td class="text-center">{{ $imp->codigo_retencion }}</td>
                <td class="text-right">{{ number_format($imp->base_imponible, 2) }}</td>
                <td class="text-center">{{ number_format($imp->porcentaje_retener, 2) }}%</td>
                <td class="text-right">{{ number_format($imp->valor_retenido, 2) }}</td>
                <td class="text-center">{{ $imp->cod_doc_sustento }}</td>
                <td class="text-center">{{ $imp->num_doc_sustento }}</td>
                <td class="text-center">{{ $imp->fecha_emision_doc_sustento ? $imp->fecha_emision_doc_sustento->format('d/m/Y') : '' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- ============== TOTAL + INFO ADICIONAL ============== --}}
    <table class="layout" style="margin-top: 6px;">
        <tr>
            <td style="width: 55%; padding-right: 6px;">
                @if(($ret->camposAdicionales && $ret->camposAdicionales->count()) || $ret->cliente->direccion || $ret->cliente->telefono || $ret->cliente->email)
                <table style="width: 100%; border-collapse: collapse; border: 1px solid #cbd5e0;">
                    <tr><td colspan="2" class="section-header">Información Adicional</td></tr>
                    @if($ret->cliente->direccion)
                    <tr><td style="padding: 1px 6px; font-size: 7.5px; width: 70px; font-weight: bold; color: #4a5568;">Dirección:</td><td style="padding: 1px 6px; font-size: 7.5px;">{{ $ret->cliente->direccion }}</td></tr>
                    @endif
                    @if($ret->cliente->telefono)
                    <tr><td style="padding: 1px 6px; font-size: 7.5px; width: 70px; font-weight: bold; color: #4a5568;">Teléfono:</td><td style="padding: 1px 6px; font-size: 7.5px;">{{ $ret->cliente->telefono }}</td></tr>
                    @endif
                    @if($ret->cliente->email)
                    <tr><td style="padding: 1px 6px; font-size: 7.5px; width: 70px; font-weight: bold; color: #4a5568;">Email:</td><td style="padding: 1px 6px; font-size: 7.5px;">{{ $ret->cliente->email }}</td></tr>
                    @endif
                    @if($ret->camposAdicionales)
                        @foreach($ret->camposAdicionales as $campo)
                        <tr><td style="padding: 1px 6px; font-size: 7.5px; width: 70px; font-weight: bold; color: #4a5568;">{{ $campo->nombre }}:</td><td style="padding: 1px 6px; font-size: 7.5px;">{{ $campo->valor }}</td></tr>
                        @endforeach
                    @endif
                </table>
                @endif
            </td>
            <td style="width: 45%;">
                <table class="totales-table">
                    <tr class="total-row"><td>TOTAL RETENIDO</td><td style="text-align: right;">{{ number_format($totalRetenido, 2) }}</td></tr>
                </table>
            </td>
        </tr>
    </table>

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
                <div class="footer-clave">{{ $ret->clave_acceso }}</div>
            </td>
            <td style="width: 95px;">
                <div class="footer-label">Estado</div>
                @if($ret->estado === 'AUTORIZADO')
                    <span class="auth-badge auth-autorizado">AUTORIZADO</span>
                @elseif($ret->estado === 'NO AUTORIZADO')
                    <span class="auth-badge auth-no-autorizado">NO AUTORIZADO</span>
                @else
                    <span class="auth-badge auth-pendiente">{{ $ret->estado }}</span>
                @endif
                @if($ret->fecha_autorizacion)
                    <div style="font-size: 7px; color: #718096; margin-top: 3px;">
                        {{ $ret->fecha_autorizacion->format('d/m/Y') }}<br>
                        {{ $ret->fecha_autorizacion->format('H:i:s') }}
                    </div>
                @endif
            </td>
        </tr>
    </table>
    @endif

</div>
</body>
</html>
