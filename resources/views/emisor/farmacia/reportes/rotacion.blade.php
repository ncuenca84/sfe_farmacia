<x-emisor-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Rotacion de Inventario</h2>
    </x-slot>

    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form method="GET" class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Desde</label>
                <input type="date" name="desde" value="{{ $desde }}" class="border-gray-300 rounded-md shadow-sm text-sm">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Hasta</label>
                <input type="date" name="hasta" value="{{ $hasta }}" class="border-gray-300 rounded-md shadow-sm text-sm">
            </div>
            <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded hover:bg-gray-700 text-sm">Filtrar</button>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Codigo</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Producto</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Principio Activo</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Uds. Vendidas</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Facturas</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total Ventas</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($productos as $i => $prod)
                <tr>
                    <td class="px-4 py-3 text-sm text-gray-400">{{ $i + 1 }}</td>
                    <td class="px-4 py-3 text-sm font-mono text-gray-900">{{ $prod->codigo_principal ?? '-' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-900">{{ $prod->nombre }}</td>
                    <td class="px-4 py-3 text-sm text-gray-500">{{ $prod->principio_activo ?? '-' }}</td>
                    <td class="px-4 py-3 text-sm text-right font-bold text-blue-600">{{ number_format($prod->total_vendido, 2) }}</td>
                    <td class="px-4 py-3 text-sm text-right text-gray-500">{{ $prod->num_facturas }}</td>
                    <td class="px-4 py-3 text-sm text-right font-medium">${{ number_format($prod->total_ventas, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500">No hay datos para el periodo seleccionado.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-emisor-layout>
