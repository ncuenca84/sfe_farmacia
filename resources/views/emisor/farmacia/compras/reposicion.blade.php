<x-emisor-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">Sugerencia de Reposicion</h2>
                <p class="text-sm text-gray-500">Productos con stock por debajo del minimo</p>
            </div>
            <a href="{{ route('emisor.farmacia.compras.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 text-sm">Volver</a>
        </div>
    </x-slot>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Codigo</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Producto</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Establecimiento</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Stock Actual</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Stock Minimo</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Cant. Sugerida</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($productos as $prod)
                <tr class="bg-orange-50">
                    <td class="px-4 py-3 text-sm font-mono text-gray-900">{{ $prod->codigo_principal ?? '-' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-900">
                        {{ $prod->nombre }}
                        @if($prod->principio_activo)
                            <p class="text-xs text-gray-400">{{ $prod->principio_activo }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-500">{{ $prod->establecimiento }}</td>
                    <td class="px-4 py-3 text-sm text-right font-bold text-red-600">{{ number_format($prod->stock_actual, 2) }}</td>
                    <td class="px-4 py-3 text-sm text-right text-gray-500">{{ number_format($prod->stock_minimo, 2) }}</td>
                    <td class="px-4 py-3 text-sm text-right font-bold text-orange-600">{{ number_format($prod->cantidad_sugerida, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">Todos los productos tienen stock suficiente.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-emisor-layout>
