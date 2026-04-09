<x-emisor-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Proforma #{{ $proforma->id }}</h2>
            <a href="{{ route('emisor.comprobantes.proformas.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 text-sm">Volver</a>
        </div>
    </x-slot>

    <div class="flex flex-wrap gap-2 mb-6">
        @if(in_array($proforma->estado, ['VIGENTE', 'CREADA']))
        <a href="{{ route('emisor.comprobantes.proformas.edit', $proforma) }}" class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600 text-sm">Editar</a>
        @endif
        <a href="{{ route('emisor.comprobantes.proformas.pdf', $proforma) }}" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700 text-sm">PDF A4</a>
        @include('emisor.comprobantes.partials.email-modal', [
            'action' => route('emisor.comprobantes.proformas.email', $proforma),
            'clienteEmail' => $proforma->cliente->email ?? '',
        ])
        <form method="POST" action="{{ route('emisor.comprobantes.proformas.clonar', $proforma) }}">
            @csrf
            <button type="submit" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700 text-sm">Duplicar</button>
        </form>
        @if(in_array($proforma->estado, ['VIGENTE', 'CREADA']))
        <form method="POST" action="{{ route('emisor.comprobantes.proformas.facturar', $proforma) }}" onsubmit="return confirm('Se creara una factura con los datos de esta proforma y se enviara al SRI. Continuar?')">
            @csrf
            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 text-sm flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Convertir a Factura
            </button>
        </form>
        @endif
    </div>

    <div class="mb-6">
        @php
            $colors = ['CREADA' => 'gray', 'VIGENTE' => 'green', 'VENCIDA' => 'red', 'ANULADA' => 'yellow', 'FACTURADA' => 'blue'];
            $color = $colors[$proforma->estado] ?? 'gray';
        @endphp
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-{{ $color }}-100 text-{{ $color }}-800">{{ $proforma->estado }}</span>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-3">Emisor</h3>
            <dl class="space-y-1 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Razon Social:</dt><dd class="text-gray-900">{{ $proforma->emisor->razon_social ?? 'N/A' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">RUC:</dt><dd class="text-gray-900">{{ $proforma->emisor->ruc ?? 'N/A' }}</dd></div>
            </dl>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-3">Cliente</h3>
            <dl class="space-y-1 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Razon Social:</dt><dd class="text-gray-900">{{ $proforma->cliente->razon_social ?? 'N/A' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Identificacion:</dt><dd class="text-gray-900">{{ $proforma->cliente->identificacion ?? 'N/A' }}</dd></div>
            </dl>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h3 class="text-lg font-medium text-gray-900 mb-3">Datos del Documento</h3>
        <dl class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
            <div><dt class="text-gray-500">Fecha Emision:</dt><dd class="text-gray-900">{{ \Carbon\Carbon::parse($proforma->fecha_emision)->format('d/m/Y') }}</dd></div>
            <div><dt class="text-gray-500">Fecha Vencimiento:</dt><dd class="text-gray-900">{{ $proforma->fecha_vencimiento ? \Carbon\Carbon::parse($proforma->fecha_vencimiento)->format('d/m/Y') : 'N/A' }}</dd></div>
            <div><dt class="text-gray-500">Establecimiento:</dt><dd class="text-gray-900">{{ $proforma->establecimiento->codigo ?? 'N/A' }} - {{ $proforma->establecimiento->nombre ?? $proforma->establecimiento->direccion ?? '' }}</dd></div>
        </dl>
        @if($proforma->observaciones)
        <div class="mt-3 text-sm">
            <dt class="text-gray-500">Observaciones:</dt>
            <dd class="text-gray-900 mt-1">{{ $proforma->observaciones }}</dd>
        </div>
        @endif
    </div>

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
                @foreach($proforma->detalles as $detalle)
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

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-end">
            <div class="w-72 space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-gray-600">Subtotal:</span><span class="font-medium">${{ number_format($proforma->total_sin_impuestos ?? 0, 2) }}</span></div>
                <div class="flex justify-between font-bold border-t pt-2 text-base"><span>TOTAL:</span><span>${{ number_format($proforma->importe_total ?? 0, 2) }}</span></div>
            </div>
        </div>
    </div>
</x-emisor-layout>
