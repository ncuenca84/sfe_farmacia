<x-emisor-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Compra {{ $compra->numero_comprobante }}</h2>
            <a href="{{ route('emisor.compras.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 text-sm">Volver</a>
        </div>
    </x-slot>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6 text-sm">{{ session('success') }}</div>
    @endif

    {{-- Action Buttons --}}
    <div class="flex flex-wrap gap-2 mb-6">
        <a href="{{ route('emisor.comprobantes.retenciones.create', ['from_compra' => $compra->id]) }}" class="inline-flex items-center gap-2 bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 text-sm font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            Crear Retencion
        </a>
        <form method="POST" action="{{ route('emisor.compras.destroy', $compra) }}" onsubmit="return confirm('¿Eliminar esta compra? Esta accion no se puede deshacer.')">
            @csrf
            @method('DELETE')
            <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 text-sm">Eliminar</button>
        </form>
    </div>

    {{-- Estado --}}
    <div class="mb-6">
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">{{ $compra->estado }}</span>
    </div>

    {{-- Header Info --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-3">Proveedor</h3>
            <dl class="space-y-1 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Razon Social:</dt><dd class="text-gray-900 font-medium">{{ $compra->razon_social_proveedor }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">RUC:</dt><dd class="text-gray-900">{{ $compra->ruc_proveedor }}</dd></div>
            </dl>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-3">Datos del Documento</h3>
            <dl class="space-y-1 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Numero:</dt><dd class="text-gray-900 font-medium">{{ $compra->numero_comprobante }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Fecha Emision:</dt><dd class="text-gray-900">{{ \Carbon\Carbon::parse($compra->fecha_emision)->format('d/m/Y') }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Autorizacion:</dt><dd class="text-gray-900 break-all text-xs">{{ $compra->autorizacion ?? 'N/A' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Clave Acceso:</dt><dd class="text-gray-900 break-all text-xs">{{ $compra->clave_acceso ?? 'N/A' }}</dd></div>
            </dl>
        </div>
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
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">IVA</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Producto</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Inventario</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($compra->detalles as $detalle)
                <tr>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $detalle->codigo_principal ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-900">{{ $detalle->descripcion }}</td>
                    <td class="px-6 py-4 text-sm text-gray-900 text-right">{{ $detalle->cantidad }}</td>
                    <td class="px-6 py-4 text-sm text-gray-900 text-right">${{ number_format($detalle->precio_unitario, 6) }}</td>
                    <td class="px-6 py-4 text-sm text-gray-900 text-right">${{ number_format($detalle->subtotal, 2) }}</td>
                    <td class="px-6 py-4 text-sm text-gray-900 text-right">${{ number_format($detalle->iva, 2) }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $detalle->producto->descripcion ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-center">
                        @if($detalle->agregar_inventario)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Si</span>
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
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
                <div class="flex justify-between"><span class="text-gray-600">Subtotal sin impuestos:</span><span class="font-medium">${{ number_format($compra->total_sin_impuestos, 2) }}</span></div>
                <div class="flex justify-between"><span class="text-gray-600">IVA:</span><span class="font-medium">${{ number_format($compra->total_iva, 2) }}</span></div>
                <div class="flex justify-between font-bold border-t pt-2 text-base"><span>TOTAL:</span><span>${{ number_format($compra->importe_total, 2) }}</span></div>
            </div>
        </div>
    </div>
</x-emisor-layout>
