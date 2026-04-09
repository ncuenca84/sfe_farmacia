<x-emisor-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Inventario</h2>
            <div class="flex gap-2">
                <a href="{{ route('emisor.inventario.export-pdf', array_merge(request()->only(['buscar', 'establecimiento_id', 'stock_bajo']), ['tipo' => 'stock'])) }}" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 text-sm" title="Descargar PDF">PDF</a>
                <a href="{{ route('emisor.inventario.export-excel', array_merge(request()->only(['buscar', 'establecimiento_id', 'stock_bajo']), ['tipo' => 'stock'])) }}" class="bg-emerald-600 text-white px-4 py-2 rounded hover:bg-emerald-700 text-sm" title="Descargar Excel">Excel</a>
                <a href="{{ route('emisor.inventario.valorizado') }}" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 text-sm">Valorizado</a>
                <a href="{{ route('emisor.inventario.ajuste') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm">Ajuste Manual</a>
            </div>
        </div>
    </x-slot>

    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form method="GET" action="{{ route('emisor.inventario.index') }}" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar por codigo o nombre..." class="w-full border-gray-300 rounded-md shadow-sm text-sm">
            </div>
            @if($establecimientos->count() > 1)
            <div>
                <select name="establecimiento_id" class="border-gray-300 rounded-md shadow-sm text-sm">
                    <option value="">Todos los establecimientos</option>
                    @foreach($establecimientos as $est)
                        <option value="{{ $est->id }}" {{ request('establecimiento_id') == $est->id ? 'selected' : '' }}>{{ $est->codigo }} - {{ $est->nombre }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="flex items-center gap-2">
                <label class="flex items-center text-sm text-gray-600">
                    <input type="checkbox" name="stock_bajo" value="1" {{ request('stock_bajo') ? 'checked' : '' }} class="rounded border-gray-300 mr-1">
                    Stock bajo
                </label>
            </div>
            <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded hover:bg-gray-700 text-sm">Filtrar</button>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Codigo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Producto</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Establecimiento</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Stock</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Stock Min.</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Costo Prom.</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($inventarios as $inv)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $inv->producto->codigo_principal ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-900">{{ $inv->producto->nombre }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $inv->establecimiento->codigo }} - {{ $inv->establecimiento->nombre }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium {{ $inv->stock_actual < 0 ? 'text-red-600' : 'text-gray-900' }}">{{ number_format($inv->stock_actual, 2) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500">{{ number_format($inv->stock_minimo, 2) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500">${{ number_format($inv->costo_promedio, 4) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        @if($inv->stockBajo())
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Stock Bajo</span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Normal</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <a href="{{ route('emisor.inventario.kardex', $inv) }}" class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700">Kardex</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">No se encontraron registros de inventario.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 border-t">
            {{ $inventarios->withQueryString()->links() }}
        </div>
    </div>
</x-emisor-layout>
