<x-emisor-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Factura {{ $factura->establecimiento->codigo ?? '000' }}-{{ $factura->ptoEmision->codigo ?? '000' }}-{{ str_pad($factura->secuencial ?? 0, 9, '0', STR_PAD_LEFT) }}</h2>
            <a href="{{ route('emisor.comprobantes.facturas.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 text-sm">Volver</a>
        </div>
    </x-slot>

    {{-- Action Buttons --}}
    <div class="flex flex-wrap gap-2 mb-4">
        @if(in_array($factura->estado, ['CREADA', 'NO AUTORIZADO', 'DEVUELTA']))
        <a href="{{ route('emisor.comprobantes.facturas.edit', $factura) }}" class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600 text-sm">Editar</a>
        @endif
        @if(in_array($factura->estado, ['CREADA', 'NO AUTORIZADO', 'RECHAZADO', 'FIRMADA', 'ENVIADA', 'PROCESANDOSE', 'EN PROCESO']))
        <form method="POST" action="{{ route('emisor.comprobantes.facturas.procesar', $factura) }}" onsubmit="return confirm('¿Enviar esta factura al SRI? Una vez autorizada no podrá editarse.')">
            @csrf
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm">Enviar al SRI</button>
        </form>
        @endif
        @if(in_array($factura->estado, ['PROCESANDOSE', 'EN PROCESO', 'ENVIADA', 'FIRMADA']) && $factura->clave_acceso)
        <form method="POST" action="{{ route('emisor.comprobantes.facturas.consultar-estado', $factura) }}">
            @csrf
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 text-sm">Consultar Estado SRI</button>
        </form>
        @endif
        <a href="{{ route('emisor.comprobantes.facturas.pdf', $factura) }}" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700 text-sm">PDF A4</a>
        <a href="{{ route('emisor.comprobantes.facturas.pdf-pos', $factura) }}" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700 text-sm">PDF Ticket</a>
        <a href="{{ route('emisor.comprobantes.facturas.xml', $factura) }}" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700 text-sm">Descargar XML</a>
        @include('emisor.comprobantes.partials.email-modal', [
            'action' => route('emisor.comprobantes.facturas.email', $factura),
            'clienteEmail' => $factura->cliente->email ?? '',
        ])
        <form method="POST" action="{{ route('emisor.comprobantes.facturas.clonar', $factura) }}">
            @csrf
            <button type="submit" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700 text-sm">Duplicar</button>
        </form>
        <a href="{{ route('emisor.comprobantes.facturas.crear-guia', $factura) }}" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 text-sm">Crear Guia</a>
    </div>
    @if($factura->estado === 'AUTORIZADO')
    <div class="flex flex-wrap gap-2 mb-6">
        <form method="POST" action="{{ route('emisor.comprobantes.facturas.anular', $factura) }}" onsubmit="return confirm('¿Está seguro de anular esta factura?\n\nEsto la marcará como ANULADA en el sistema.\nDeberá completar la anulación en el portal del SRI.')">
            @csrf
            <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 text-sm">Anular</button>
        </form>
        <a href="https://srienlinea.sri.gob.ec/tuportal-internet/accederAplicacion.jspa?redireccion=57&idGrupo=55" target="_blank" rel="noopener" class="inline-flex items-center text-sm" style="background-color:#d97706;color:#fff;padding:0.5rem 1rem;border-radius:0.25rem;text-decoration:none">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            Anular en SRI
        </a>
    </div>
    @endif
    @if($factura->estado === 'ANULADA')
    <div class="flex flex-wrap gap-2 mb-6">
        <a href="https://srienlinea.sri.gob.ec/tuportal-internet/accederAplicacion.jspa?redireccion=57&idGrupo=55" target="_blank" rel="noopener" class="inline-flex items-center text-sm" style="background-color:#d97706;color:#fff;padding:0.5rem 1rem;border-radius:0.25rem;text-decoration:none">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            Completar anulación en SRI
        </a>
    </div>
    @endif

    {{-- Estado --}}
    <div class="mb-6">
        @php
            $colors = ['CREADA' => 'gray', 'AUTORIZADO' => 'green', 'RECHAZADO' => 'red', 'NO AUTORIZADO' => 'red', 'DEVUELTA' => 'red', 'ANULADA' => 'yellow', 'ENVIADA' => 'blue', 'FIRMADA' => 'blue', 'PROCESANDOSE' => 'blue', 'EN PROCESO' => 'blue'];
            $color = $colors[$factura->estado] ?? 'gray';
        @endphp
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-{{ $color }}-100 text-{{ $color }}-800">{{ $factura->estado }}</span>
        @if($factura->ambiente)
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $factura->ambiente === '1' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' }}">{{ $factura->ambiente === '1' ? 'PRUEBAS' : 'PRODUCCIÓN' }}</span>
        @endif
        @if($factura->motivo_rechazo)
        <div class="mt-3 bg-red-50 border-l-4 border-red-400 text-red-700 px-4 py-3 rounded-lg text-sm">
            <div class="flex items-start">
                <svg class="w-5 h-5 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                <div>
                    <span class="font-semibold">Motivo:</span> {!! nl2br(e($factura->motivo_rechazo)) !!}
                    @if(in_array($factura->estado, ['NO AUTORIZADO', 'RECHAZADO', 'DEVUELTA']))
                    <p class="mt-2 text-xs text-red-600">Puede corregir los datos del comprobante y volver a enviarlo al SRI.</p>
                    @endif
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- Header Info --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-3">Emisor</h3>
            <dl class="space-y-1 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Razon Social:</dt><dd class="text-gray-900">{{ $factura->emisor->razon_social ?? 'N/A' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">RUC:</dt><dd class="text-gray-900">{{ $factura->emisor->ruc ?? 'N/A' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Establecimiento:</dt><dd class="text-gray-900">{{ $factura->establecimiento->codigo ?? '' }} - {{ $factura->establecimiento->direccion ?? '' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Punto Emision:</dt><dd class="text-gray-900">{{ $factura->ptoEmision->codigo ?? '' }}</dd></div>
            </dl>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-3">Cliente</h3>
            <dl class="space-y-1 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Razon Social:</dt><dd class="text-gray-900">{{ $factura->cliente->razon_social ?? 'N/A' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Identificacion:</dt><dd class="text-gray-900">{{ $factura->cliente->identificacion ?? 'N/A' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Email:</dt><dd class="text-gray-900">{{ $factura->cliente->email ?? 'N/A' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Direccion:</dt><dd class="text-gray-900">{{ $factura->cliente->direccion ?? 'N/A' }}</dd></div>
            </dl>
        </div>
    </div>

    {{-- Document Info --}}
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h3 class="text-lg font-medium text-gray-900 mb-3">Datos del Documento</h3>
        <dl class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
            <div><dt class="text-gray-500">Numero:</dt><dd class="text-gray-900 font-medium">{{ $factura->establecimiento->codigo ?? '000' }}-{{ $factura->ptoEmision->codigo ?? '000' }}-{{ str_pad($factura->secuencial ?? 0, 9, '0', STR_PAD_LEFT) }}</dd></div>
            <div><dt class="text-gray-500">Fecha Emision:</dt><dd class="text-gray-900">{{ \Carbon\Carbon::parse($factura->fecha_emision)->format('d/m/Y') }}</dd></div>
            <div><dt class="text-gray-500">Clave de Acceso:</dt><dd class="text-gray-900 break-all text-xs">{{ $factura->clave_acceso ?? 'N/A' }}</dd></div>
        </dl>
    </div>

    {{-- Detalles --}}
    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
        <div class="px-6 py-4 border-b">
            <h3 class="text-lg font-medium text-gray-900">Detalles</h3>
        </div>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Codigo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Descripcion</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Cantidad</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Precio Unit.</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Descuento</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">IVA</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($factura->detalles as $detalle)
                <tr>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $detalle->codigo_principal ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-900">{{ $detalle->descripcion }}</td>
                    <td class="px-6 py-4 text-sm text-gray-900 text-right">{{ $detalle->cantidad }}</td>
                    <td class="px-6 py-4 text-sm text-gray-900 text-right">${{ number_format($detalle->precio_unitario, 2) }}</td>
                    <td class="px-6 py-4 text-sm text-gray-900 text-right">${{ number_format($detalle->descuento ?? 0, 2) }}</td>
                    <td class="px-6 py-4 text-sm text-gray-900 text-right">${{ number_format($detalle->precio_total_sin_impuesto ?? 0, 2) }}</td>
                    <td class="px-6 py-4 text-sm text-gray-900 text-right">
                        @foreach($detalle->impuestos ?? [] as $imp)
                            ${{ number_format($imp->valor ?? 0, 2) }}
                        @endforeach
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Totals --}}
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="flex justify-end">
            <div class="w-72 space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-gray-600">Subtotal sin impuestos:</span><span class="font-medium">${{ number_format($factura->total_sin_impuestos ?? 0, 2) }}</span></div>
                <div class="flex justify-between"><span class="text-gray-600">Total Descuento:</span><span class="font-medium">${{ number_format($factura->total_descuento ?? 0, 2) }}</span></div>
                <div class="flex justify-between"><span class="text-gray-600">IVA:</span><span class="font-medium">${{ number_format($factura->total_iva ?? 0, 2) }}</span></div>
                <div class="flex justify-between font-bold border-t pt-2 text-base"><span>TOTAL:</span><span>${{ number_format($factura->importe_total ?? 0, 2) }}</span></div>
            </div>
        </div>
    </div>

    {{-- Observaciones --}}
    @if($factura->observaciones)
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h3 class="text-lg font-medium text-gray-900 mb-3">Observaciones</h3>
        <p class="text-sm text-gray-900">{{ $factura->observaciones }}</p>
    </div>
    @endif

    {{-- Campos Adicionales --}}
    @if($factura->camposAdicionales && $factura->camposAdicionales->count())
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h3 class="text-lg font-medium text-gray-900 mb-3">Campos Adicionales</h3>
        <dl class="space-y-1 text-sm">
            @foreach($factura->camposAdicionales as $campo)
            <div class="flex justify-between">
                <dt class="text-gray-500">{{ $campo->nombre }}:</dt>
                <dd class="text-gray-900">{{ $campo->valor }}</dd>
            </div>
            @endforeach
        </dl>
    </div>
    @endif

    {{-- Mensajes SRI --}}
    @if($factura->mensajes && $factura->mensajes->count())
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h3 class="text-lg font-medium text-gray-900 mb-3">Mensajes del SRI</h3>
        <div class="space-y-3">
            @foreach($factura->mensajes as $mensaje)
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
