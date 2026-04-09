<x-emisor-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Reporte de Ventas</h2>
    </x-slot>

    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form method="GET" action="{{ route('emisor.reportes.ventas') }}">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Desde</label>
                    <input type="date" name="desde" value="{{ request('desde') }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Hasta</label>
                    <input type="date" name="hasta" value="{{ request('hasta') }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Buscar Cliente</label>
                    <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Nombre o RUC/CI" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm">Filtrar</button>
                    <a href="{{ route('emisor.reportes.export-ventas', request()->query()) }}" class="bg-green-600 text-white px-3 py-2 rounded hover:bg-green-700 text-sm flex items-center" title="Exportar Excel">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- Resumen global --}}
    @if($totales && $totales->cantidad > 0)
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-sm text-gray-500">Facturas</p>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($totales->cantidad) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-sm text-gray-500">Subtotal</p>
            <p class="text-2xl font-bold text-gray-900">${{ number_format($totales->subtotal, 2) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-sm text-gray-500">IVA</p>
            <p class="text-2xl font-bold text-gray-900">${{ number_format($totales->iva, 2) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-sm text-gray-500">Total</p>
            <p class="text-2xl font-bold text-green-700">${{ number_format($totales->total, 2) }}</p>
        </div>
    </div>
    @endif

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Numero</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">RUC/CI</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">IVA</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @php $pSubtotal = 0; $pIva = 0; $pTotal = 0; @endphp
                    @forelse($facturas as $factura)
                    @php
                        $pSubtotal += $factura->total_sin_impuestos ?? 0;
                        $pIva += $factura->total_iva ?? 0;
                        $pTotal += $factura->importe_total ?? 0;
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $factura->fecha_emision->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 font-mono">
                            {{ $factura->establecimiento->codigo ?? '000' }}-{{ $factura->ptoEmision->codigo ?? '000' }}-{{ str_pad($factura->secuencial ?? 0, 9, '0', STR_PAD_LEFT) }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $factura->cliente->razon_social ?? 'N/A' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $factura->cliente->identificacion ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right font-mono">${{ number_format($factura->total_sin_impuestos ?? 0, 2) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right font-mono">${{ number_format($factura->total_iva ?? 0, 2) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right font-mono">${{ number_format($factura->importe_total ?? 0, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500">No se encontraron facturas autorizadas.</td>
                    </tr>
                    @endforelse
                </tbody>
                @if($facturas->count())
                <tfoot class="bg-gray-100">
                    <tr class="font-semibold text-sm">
                        <td colspan="4" class="px-4 py-3 text-right text-gray-700">Subtotal pagina:</td>
                        <td class="px-4 py-3 text-right font-mono">${{ number_format($pSubtotal, 2) }}</td>
                        <td class="px-4 py-3 text-right font-mono">${{ number_format($pIva, 2) }}</td>
                        <td class="px-4 py-3 text-right font-mono">${{ number_format($pTotal, 2) }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
        <div class="px-4 py-3 border-t">
            {{ $facturas->withQueryString()->links() }}
        </div>
    </div>
</x-emisor-layout>
