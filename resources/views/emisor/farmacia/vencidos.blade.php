<x-emisor-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Control de Vencimientos</h2>
            <a href="{{ route('emisor.farmacia.dashboard') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 text-sm">Volver</a>
        </div>
    </x-slot>

    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form method="GET" action="{{ route('emisor.farmacia.vencidos') }}" class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                <select name="filtro" class="border-gray-300 rounded-md shadow-sm text-sm" onchange="this.form.submit()">
                    <option value="vencidos" {{ $filtro === 'vencidos' ? 'selected' : '' }}>Vencidos</option>
                    <option value="proximos" {{ $filtro === 'proximos' ? 'selected' : '' }}>Proximos a vencer</option>
                    <option value="todos" {{ $filtro === 'todos' ? 'selected' : '' }}>Todos con fecha</option>
                </select>
            </div>
            @if($filtro === 'proximos')
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Dias</label>
                <select name="dias" class="border-gray-300 rounded-md shadow-sm text-sm" onchange="this.form.submit()">
                    <option value="7" {{ request('dias') == '7' ? 'selected' : '' }}>7 dias</option>
                    <option value="15" {{ request('dias') == '15' ? 'selected' : '' }}>15 dias</option>
                    <option value="30" {{ request('dias', '30') == '30' ? 'selected' : '' }}>30 dias</option>
                    <option value="60" {{ request('dias') == '60' ? 'selected' : '' }}>60 dias</option>
                    <option value="90" {{ request('dias') == '90' ? 'selected' : '' }}>90 dias</option>
                </select>
            </div>
            @endif
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Nombre, codigo o lote..." class="w-full border-gray-300 rounded-md shadow-sm text-sm">
            </div>
            <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded hover:bg-gray-700 text-sm">Filtrar</button>
        </form>
    </div>

    <!-- Tabla -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Codigo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Producto</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Categoria</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Proveedor</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lote</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vencimiento</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($productos as $producto)
                <tr class="{{ $producto->estaVencido() ? 'bg-red-50' : ($producto->proximoAVencer() ? 'bg-yellow-50' : '') }}">
                    <td class="px-6 py-4 text-sm text-gray-900">{{ $producto->codigo_principal ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-900">{{ $producto->nombre }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $producto->categoriaProducto->nombre ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $producto->proveedor->nombre ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $producto->numero_lote ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm font-medium {{ $producto->estaVencido() ? 'text-red-600' : 'text-yellow-600' }}">
                        {{ $producto->fecha_vencimiento->format('d/m/Y') }}
                    </td>
                    <td class="px-6 py-4">
                        @if($producto->estaVencido())
                            <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-700">Vencido ({{ $producto->fecha_vencimiento->diffInDays(now()) }}d)</span>
                        @elseif($producto->proximoAVencer(7))
                            <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-orange-100 text-orange-700">Vence en {{ $producto->fecha_vencimiento->diffInDays(now()) }}d</span>
                        @else
                            <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-700">Vence en {{ $producto->fecha_vencimiento->diffInDays(now()) }}d</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">No se encontraron productos.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 border-t">
            {{ $productos->withQueryString()->links() }}
        </div>
    </div>
</x-emisor-layout>
