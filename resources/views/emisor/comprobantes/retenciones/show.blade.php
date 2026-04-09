<x-emisor-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Retencion {{ $retencion->establecimiento->codigo ?? '000' }}-{{ $retencion->ptoEmision->codigo ?? '000' }}-{{ str_pad($retencion->secuencial ?? 0, 9, '0', STR_PAD_LEFT) }}</h2>
            <a href="{{ route('emisor.comprobantes.retenciones.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 text-sm">Volver</a>
        </div>
    </x-slot>

    <div class="flex flex-wrap gap-2 mb-6">
        @if(in_array($retencion->estado, ['CREADA', 'NO AUTORIZADO', 'DEVUELTA']))
        <a href="{{ route('emisor.comprobantes.retenciones.edit', $retencion) }}" class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600 text-sm">Editar</a>
        @endif
        @if(in_array($retencion->estado, ['CREADA', 'NO AUTORIZADO', 'RECHAZADO', 'FIRMADA', 'ENVIADA', 'PROCESANDOSE', 'EN PROCESO']))
        <form method="POST" action="{{ route('emisor.comprobantes.retenciones.procesar', $retencion) }}" onsubmit="return confirm('¿Enviar esta retención al SRI?')">
            @csrf
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm">Enviar al SRI</button>
        </form>
        @endif
        <a href="{{ route('emisor.comprobantes.retenciones.pdf', $retencion) }}" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700 text-sm">PDF A4</a>
        <a href="{{ route('emisor.comprobantes.retenciones.pdf-pos', $retencion) }}" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700 text-sm">PDF Ticket</a>
        @include('emisor.comprobantes.partials.email-modal', [
            'action' => route('emisor.comprobantes.retenciones.email', $retencion),
            'clienteEmail' => $retencion->proveedor->email ?? $retencion->cliente->email ?? '',
        ])
        <form method="POST" action="{{ route('emisor.comprobantes.retenciones.clonar', $retencion) }}">
            @csrf
            <button type="submit" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700 text-sm">Duplicar</button>
        </form>
    </div>

    @if($retencion->estado === 'AUTORIZADO')
    <div class="flex flex-wrap gap-2 mb-6">
        <form method="POST" action="{{ route('emisor.comprobantes.retenciones.anular', $retencion) }}" onsubmit="return confirm('¿Está seguro de anular esta retención?\n\nEsto la marcará como ANULADA en el sistema.\nDeberá completar la anulación en el portal del SRI.')">
            @csrf
            <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 text-sm">Anular</button>
        </form>
        <a href="https://srienlinea.sri.gob.ec/tuportal-internet/accederAplicacion.jspa?redireccion=57&idGrupo=55" target="_blank" rel="noopener" class="inline-flex items-center text-sm" style="background-color:#d97706;color:#fff;padding:0.5rem 1rem;border-radius:0.25rem;text-decoration:none">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            Anular en SRI
        </a>
    </div>
    @endif
    @if($retencion->estado === 'ANULADA')
    <div class="flex flex-wrap gap-2 mb-6">
        <a href="https://srienlinea.sri.gob.ec/tuportal-internet/accederAplicacion.jspa?redireccion=57&idGrupo=55" target="_blank" rel="noopener" class="inline-flex items-center text-sm" style="background-color:#d97706;color:#fff;padding:0.5rem 1rem;border-radius:0.25rem;text-decoration:none">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            Completar anulación en SRI
        </a>
    </div>
    @endif

    <div class="mb-6">
        @php
            $colors = ['CREADA' => 'gray', 'AUTORIZADO' => 'green', 'RECHAZADO' => 'red', 'NO AUTORIZADO' => 'red', 'DEVUELTA' => 'red', 'ANULADA' => 'yellow', 'ENVIADA' => 'blue', 'FIRMADA' => 'blue', 'PROCESANDOSE' => 'blue', 'EN PROCESO' => 'blue'];
            $color = $colors[$retencion->estado] ?? 'gray';
        @endphp
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-{{ $color }}-100 text-{{ $color }}-800">{{ $retencion->estado }}</span>
        @if($retencion->ambiente)
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $retencion->ambiente === '1' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' }}">{{ $retencion->ambiente === '1' ? 'PRUEBAS' : 'PRODUCCIÓN' }}</span>
        @endif
        @if($retencion->motivo_rechazo)
        <div class="mt-3 bg-red-50 border-l-4 border-red-400 text-red-700 px-4 py-3 rounded-lg text-sm">
            <div class="flex items-start">
                <svg class="w-5 h-5 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                <div>
                    <span class="font-semibold">Motivo:</span> {!! nl2br(e($retencion->motivo_rechazo)) !!}
                    @if(in_array($retencion->estado, ['NO AUTORIZADO', 'RECHAZADO', 'DEVUELTA']))
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
                <div class="flex justify-between"><dt class="text-gray-500">Razon Social:</dt><dd class="text-gray-900">{{ $retencion->emisor->razon_social ?? 'N/A' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">RUC:</dt><dd class="text-gray-900">{{ $retencion->emisor->ruc ?? 'N/A' }}</dd></div>
            </dl>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-3">Sujeto Retenido</h3>
            <dl class="space-y-1 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Razon Social:</dt><dd class="text-gray-900">{{ $retencion->cliente->razon_social ?? 'N/A' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Identificacion:</dt><dd class="text-gray-900">{{ $retencion->cliente->identificacion ?? 'N/A' }}</dd></div>
            </dl>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h3 class="text-lg font-medium text-gray-900 mb-3">Datos del Documento</h3>
        <dl class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
            <div><dt class="text-gray-500">Numero:</dt><dd class="text-gray-900 font-medium">{{ $retencion->establecimiento->codigo ?? '000' }}-{{ $retencion->ptoEmision->codigo ?? '000' }}-{{ str_pad($retencion->secuencial ?? 0, 9, '0', STR_PAD_LEFT) }}</dd></div>
            <div><dt class="text-gray-500">Fecha Emision:</dt><dd class="text-gray-900">{{ \Carbon\Carbon::parse($retencion->fecha_emision)->format('d/m/Y') }}</dd></div>
            <div><dt class="text-gray-500">Clave de Acceso:</dt><dd class="text-gray-900 break-all text-xs">{{ $retencion->clave_acceso ?? 'N/A' }}</dd></div>
            <div><dt class="text-gray-500">Periodo Fiscal:</dt><dd class="text-gray-900">{{ $retencion->periodo_fiscal ?? 'N/A' }}</dd></div>
        </dl>
    </div>

    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h3 class="text-lg font-medium text-gray-900 mb-3">Documento Sustento</h3>
        <dl class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
            <div><dt class="text-gray-500">Tipo:</dt><dd class="text-gray-900">{{ $retencion->tipo_doc_sustento ?? 'N/A' }}</dd></div>
            <div><dt class="text-gray-500">Numero:</dt><dd class="text-gray-900">{{ $retencion->num_doc_sustento ?? 'N/A' }}</dd></div>
            <div><dt class="text-gray-500">Fecha Emision:</dt><dd class="text-gray-900">{{ $retencion->fecha_emision_doc_sustento ? \Carbon\Carbon::parse($retencion->fecha_emision_doc_sustento)->format('d/m/Y') : 'N/A' }}</dd></div>
        </dl>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
        <div class="px-6 py-4 border-b">
            <h3 class="text-lg font-medium text-gray-900">Impuestos</h3>
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
                @foreach($retencion->impuestosRetencion ?? [] as $imp)
                <tr>
                    <td class="px-6 py-4 text-sm text-gray-900">
                        @switch($imp->codigo_impuesto)
                            @case('1') Renta @break
                            @case('2') IVA @break
                            @case('6') ISD @break
                            @default {{ $imp->codigo_impuesto }}
                        @endswitch
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900">{{ $imp->codigo_retencion }}</td>
                    <td class="px-6 py-4 text-sm text-gray-900 text-right">${{ number_format($imp->base_imponible ?? 0, 2) }}</td>
                    <td class="px-6 py-4 text-sm text-gray-900 text-right">{{ $imp->porcentaje_retener ?? 0 }}%</td>
                    <td class="px-6 py-4 text-sm text-gray-900 text-right">${{ number_format($imp->valor_retenido ?? 0, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-end">
            <div class="w-72">
                <div class="flex justify-between text-sm font-bold border-t pt-2 text-base">
                    <span>Total Retenido:</span>
                    <span>${{ number_format($retencion->importe_total ?? 0, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Mensajes del SRI --}}
    @if($retencion->mensajes && $retencion->mensajes->count())
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h3 class="text-lg font-medium text-gray-900 mb-3">Mensajes del SRI</h3>
        <div class="space-y-3">
            @foreach($retencion->mensajes as $mensaje)
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
