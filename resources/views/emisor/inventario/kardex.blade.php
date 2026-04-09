<x-emisor-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">Kardex</h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ $inventario->producto->codigo_principal }} - {{ $inventario->producto->nombre }}
                    | {{ $inventario->establecimiento->codigo }} - {{ $inventario->establecimiento->nombre }}
                </p>
            </div>
            <a href="{{ route('emisor.inventario.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700 text-sm">Volver</a>
        </div>
    </x-slot>

    <!-- Resumen -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-xs text-gray-500 uppercase font-medium">Stock Actual</p>
            <p class="text-2xl font-bold {{ $inventario->stock_actual < 0 ? 'text-red-600' : 'text-gray-900' }}">{{ number_format($inventario->stock_actual, 2) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-xs text-gray-500 uppercase font-medium">Stock Minimo</p>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($inventario->stock_minimo, 2) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-xs text-gray-500 uppercase font-medium">Costo Promedio</p>
            <p class="text-2xl font-bold text-gray-900">${{ number_format($inventario->costo_promedio, 4) }}</p>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form method="GET" action="{{ route('emisor.inventario.kardex', $inventario) }}" class="flex flex-wrap gap-4">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Desde</label>
                <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}" class="border-gray-300 rounded-md shadow-sm text-sm">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Hasta</label>
                <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}" class="border-gray-300 rounded-md shadow-sm text-sm">
            </div>
            <div class="flex items-end">
                <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded hover:bg-gray-700 text-sm">Filtrar</button>
            </div>
        </form>
    </div>

    <!-- Movimientos -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Cantidad</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Costo Unit.</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Costo Total</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Stock Result.</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Descripcion</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Usuario</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($movimientos as $mov)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $mov->created_at->format('d/m/Y H:i') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $mov->tipo->badge() }}">{{ $mov->tipo->nombre() }}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium {{ (in_array($mov->tipo->value, ['SALIDA', 'TRANSFERENCIA']) || $mov->cantidad < 0) ? 'text-red-600' : 'text-green-600' }}">
                        @if(in_array($mov->tipo->value, ['SALIDA', 'TRANSFERENCIA']))
                            -{{ number_format(abs($mov->cantidad), 2) }}
                        @elseif($mov->cantidad < 0)
                            {{ number_format($mov->cantidad, 2) }}
                        @else
                            +{{ number_format($mov->cantidad, 2) }}
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500">${{ number_format($mov->costo_unitario, 4) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500">${{ number_format($mov->costo_total, 2) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-gray-900">{{ number_format($mov->stock_resultante, 2) }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $mov->descripcion ?? '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $mov->user->nombre ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">No se encontraron movimientos.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 border-t">
            {{ $movimientos->withQueryString()->links() }}
        </div>
    </div>
</x-emisor-layout>
