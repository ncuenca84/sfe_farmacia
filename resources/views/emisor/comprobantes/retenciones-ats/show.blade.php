<x-emisor-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Retencion ATS {{ $retencionAt->establecimiento->codigo ?? '000' }}-{{ $retencionAt->ptoEmision->codigo ?? '000' }}-{{ str_pad($retencionAt->secuencial ?? 0, 9, '0', STR_PAD_LEFT) }}</h2>
            <a href="{{ route('emisor.comprobantes.retenciones-ats.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 text-sm">Volver</a>
        </div>
    </x-slot>

    <div class="flex flex-wrap gap-2 mb-6">
        @if(in_array($retencionAt->estado, ['CREADA', 'NO AUTORIZADO', 'DEVUELTA']))
        <a href="{{ route('emisor.comprobantes.retenciones-ats.edit', $retencionAt) }}" class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600 text-sm">Editar</a>
        @endif
        @if(in_array($retencionAt->estado, ['CREADA', 'NO AUTORIZADO', 'RECHAZADO', 'FIRMADA', 'ENVIADA', 'PROCESANDOSE', 'EN PROCESO']))
        <form method="POST" action="{{ route('emisor.comprobantes.retenciones-ats.procesar', $retencionAt) }}" onsubmit="return confirm('¿Enviar esta retención al SRI?')">
            @csrf
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm">Enviar al SRI</button>
        </form>
        @endif
        @if(in_array($retencionAt->estado, ['PROCESANDOSE', 'ENVIADA', 'FIRMADA', 'EN PROCESO']))
        <form method="POST" action="{{ route('emisor.comprobantes.retenciones-ats.consultar-estado', $retencionAt) }}">
            @csrf
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 text-sm">Consultar Estado</button>
        </form>
        @endif
        <a href="{{ route('emisor.comprobantes.retenciones-ats.pdf', $retencionAt) }}" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700 text-sm">PDF A4</a>
        <a href="{{ route('emisor.comprobantes.retenciones-ats.pdf-pos', $retencionAt) }}" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700 text-sm">PDF Ticket</a>
        @include('emisor.comprobantes.partials.email-modal', [
            'action' => route('emisor.comprobantes.retenciones-ats.email', $retencionAt),
            'clienteEmail' => $retencionAt->cliente->email ?? '',
        ])
        <form method="POST" action="{{ route('emisor.comprobantes.retenciones-ats.clonar', $retencionAt) }}">
            @csrf
            <button type="submit" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700 text-sm">Duplicar</button>
        </form>
    </div>

    <div class="mb-6">
        @php
            $colors = ['CREADA' => 'gray', 'AUTORIZADO' => 'green', 'RECHAZADO' => 'red', 'NO AUTORIZADO' => 'red', 'DEVUELTA' => 'red', 'ANULADA' => 'yellow', 'ENVIADA' => 'blue', 'FIRMADA' => 'blue', 'PROCESANDOSE' => 'blue', 'EN PROCESO' => 'blue'];
            $color = $colors[$retencionAt->estado] ?? 'gray';
        @endphp
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-{{ $color }}-100 text-{{ $color }}-800">{{ $retencionAt->estado }}</span>
        @if($retencionAt->ambiente)
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $retencionAt->ambiente === '1' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' }}">{{ $retencionAt->ambiente === '1' ? 'PRUEBAS' : 'PRODUCCIÓN' }}</span>
        @endif
        @if($retencionAt->motivo_rechazo)
        <div class="mt-3 bg-red-50 border-l-4 border-red-400 text-red-700 px-4 py-3 rounded-lg text-sm">
            <div class="flex items-start">
                <svg class="w-5 h-5 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                <div>
                    <span class="font-semibold">Motivo:</span> {!! nl2br(e($retencionAt->motivo_rechazo)) !!}
                    @if(in_array($retencionAt->estado, ['NO AUTORIZADO', 'RECHAZADO', 'DEVUELTA']))
                    <p class="mt-2 text-xs text-red-600">Puede corregir los datos del comprobante y volver a enviarlo al SRI.</p>
                    @endif
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-3">Emisor</h3>
            <dl class="space-y-1 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Razon Social:</dt><dd class="text-gray-900">{{ $retencionAt->emisor->razon_social ?? 'N/A' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">RUC:</dt><dd class="text-gray-900">{{ $retencionAt->emisor->ruc ?? 'N/A' }}</dd></div>
            </dl>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-3">Sujeto Retenido</h3>
            <dl class="space-y-1 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Razon Social:</dt><dd class="text-gray-900">{{ $retencionAt->cliente->razon_social ?? 'N/A' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Identificacion:</dt><dd class="text-gray-900">{{ $retencionAt->cliente->identificacion ?? 'N/A' }}</dd></div>
            </dl>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h3 class="text-lg font-medium text-gray-900 mb-3">Datos del Documento</h3>
        <dl class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
            <div><dt class="text-gray-500">Numero:</dt><dd class="text-gray-900 font-medium">{{ $retencionAt->establecimiento->codigo ?? '000' }}-{{ $retencionAt->ptoEmision->codigo ?? '000' }}-{{ str_pad($retencionAt->secuencial ?? 0, 9, '0', STR_PAD_LEFT) }}</dd></div>
            <div><dt class="text-gray-500">Fecha Emision:</dt><dd class="text-gray-900">{{ $retencionAt->fecha_emision?->format('d/m/Y') ?? 'N/A' }}</dd></div>
            <div><dt class="text-gray-500">Clave de Acceso:</dt><dd class="text-gray-900 break-all text-xs">{{ $retencionAt->clave_acceso ?? 'N/A' }}</dd></div>
            <div><dt class="text-gray-500">Periodo Fiscal:</dt><dd class="text-gray-900">{{ $retencionAt->periodo_fiscal ?? 'N/A' }}</dd></div>
            <div><dt class="text-gray-500">Parte Relacionada:</dt><dd class="text-gray-900">{{ $retencionAt->parte_rel ?? 'NO' }}</dd></div>
        </dl>
    </div>

    @php $totalRetenido = 0; @endphp
    @foreach($retencionAt->docSustentos as $docSustento)
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h3 class="text-lg font-medium text-gray-900 mb-3">Documento Sustento</h3>
        <dl class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm mb-4">
            @if($docSustento->cod_sustento)
            <div><dt class="text-gray-500">Cod. Sustento:</dt><dd class="text-gray-900">{{ $docSustento->cod_sustento }}</dd></div>
            @endif
            <div><dt class="text-gray-500">Tipo:</dt><dd class="text-gray-900">{{ $docSustento->cod_doc_sustento }}</dd></div>
            <div><dt class="text-gray-500">Numero:</dt><dd class="text-gray-900">{{ $docSustento->num_doc_sustento }}</dd></div>
            <div><dt class="text-gray-500">Fecha Emision:</dt><dd class="text-gray-900">{{ $docSustento->fecha_emision_doc_sustento->format('d/m/Y') }}</dd></div>
            @if($docSustento->fecha_registro_contable)
            <div><dt class="text-gray-500">Fecha Reg. Contable:</dt><dd class="text-gray-900">{{ $docSustento->fecha_registro_contable->format('d/m/Y') }}</dd></div>
            @endif
            @if($docSustento->num_aut_doc_sustento)
            <div><dt class="text-gray-500">No. Autorizacion:</dt><dd class="text-gray-900">{{ $docSustento->num_aut_doc_sustento }}</dd></div>
            @endif
            <div><dt class="text-gray-500">Total Sin Impuestos:</dt><dd class="text-gray-900">${{ number_format($docSustento->total_sin_impuestos, 2) }}</dd></div>
            <div><dt class="text-gray-500">Importe Total:</dt><dd class="text-gray-900">${{ number_format($docSustento->importe_total, 2) }}</dd></div>
        </dl>

        @if($docSustento->impuestos && $docSustento->impuestos->count() > 0)
        <div class="mb-4">
            <h4 class="text-sm font-medium text-gray-700 mb-2">Impuestos Documento Sustento</h4>
            <div class="overflow-hidden rounded-lg border">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cod. Porcentaje</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Base Imponible</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Tarifa</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Valor Impuesto</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($docSustento->impuestos as $impuesto)
                        <tr>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $impuesto->codigo_impuesto == '2' ? 'IVA' : ($impuesto->codigo_impuesto == '3' ? 'ICE' : 'IRBPNR') }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $impuesto->codigo_porcentaje }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900 text-right">${{ number_format($impuesto->base_imponible, 2) }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900 text-right">{{ number_format($impuesto->tarifa, 2) }}%</td>
                            <td class="px-6 py-4 text-sm text-gray-900 text-right">${{ number_format($impuesto->valor_impuesto, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <div class="overflow-hidden rounded-lg border">
            <div class="px-6 py-3 bg-gray-50 border-b">
                <h4 class="text-sm font-medium text-gray-700">Retenciones</h4>
            </div>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Impuesto</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cod. Retencion</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Base Imponible</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">% Retencion</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Valor Retenido</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($docSustento->desgloses as $desglose)
                    @php $totalRetenido += $desglose->valor_retenido; @endphp
                    <tr>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            @switch($desglose->codigo_impuesto)
                                @case('1') Renta @break
                                @case('2') IVA @break
                                @case('6') ISD @break
                                @default {{ $desglose->codigo_impuesto }}
                            @endswitch
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $desglose->codigo_retencion }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900 text-right">${{ number_format($desglose->base_imponible, 2) }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900 text-right">{{ number_format($desglose->porcentaje_retener, 2) }}%</td>
                        <td class="px-6 py-4 text-sm text-gray-900 text-right">${{ number_format($desglose->valor_retenido, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endforeach

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-end">
            <div class="w-72">
                <div class="flex justify-between text-sm font-bold border-t pt-2 text-base">
                    <span>Total Retenido:</span>
                    <span>${{ number_format($totalRetenido, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Mensajes del SRI --}}
    @if($retencionAt->mensajes && $retencionAt->mensajes->count())
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h3 class="text-lg font-medium text-gray-900 mb-3">Mensajes del SRI</h3>
        <div class="space-y-3">
            @foreach($retencionAt->mensajes as $mensaje)
            <div class="border rounded-lg p-4 text-sm {{ $mensaje->tipo === 'ERROR' ? 'border-red-300 bg-red-50' : ($mensaje->tipo === 'ADVERTENCIA' ? 'border-yellow-300 bg-yellow-50' : 'border-blue-300 bg-blue-50') }}">
                <div class="flex items-start">
                    @if($mensaje->tipo === 'ERROR')
                    <svg class="w-5 h-5 mr-2 mt-0.5 flex-shrink-0 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                    @else
                    <svg class="w-5 h-5 mr-2 mt-0.5 flex-shrink-0 text-blue-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                    @endif
                    <div class="flex-1">
                        <div class="font-medium">{{ $mensaje->mensaje ?? '' }}</div>
                        @if($mensaje->informacion_adicional)
                        <details class="mt-2">
                            <summary class="text-xs text-gray-500 cursor-pointer hover:text-gray-700">Ver detalle técnico</summary>
                            <div class="mt-1 text-xs text-gray-500 bg-gray-100 rounded p-2 font-mono break-all">{{ $mensaje->informacion_adicional }}</div>
                        </details>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</x-emisor-layout>
