<x-emisor-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Lotes de Inventario</h2>
            <a href="{{ route('emisor.farmacia.lotes.ingreso') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm">Ingresar Lote</a>
        </div>
    </x-slot>

    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form method="GET" action="{{ route('emisor.farmacia.lotes.index') }}" class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                <select name="filtro" class="border-gray-300 rounded-md shadow-sm text-sm" onchange="this.form.submit()">
                    <option value="con_stock" {{ $filtro === 'con_stock' ? 'selected' : '' }}>Con stock</option>
                    <option value="vencidos" {{ $filtro === 'vencidos' ? 'selected' : '' }}>Vencidos</option>
                    <option value="agotados" {{ $filtro === 'agotados' ? 'selected' : '' }}>Agotados</option>
                    <option value="todos" {{ $filtro === 'todos' ? 'selected' : '' }}>Todos</option>
                </select>
            </div>
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Lote, producto o principio activo..." class="w-full border-gray-300 rounded-md shadow-sm text-sm">
            </div>
            <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded hover:bg-gray-700 text-sm">Filtrar</button>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lote</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Producto</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Establecimiento</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Cant. Actual</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Cant. Inicial</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vencimiento</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ingreso</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($lotes as $lote)
                <tr class="{{ $lote->estaVencido() ? 'bg-red-50' : ($lote->cantidad_actual <= 0 ? 'bg-gray-50' : '') }}">
                    <td class="px-4 py-3 text-sm font-mono font-medium text-gray-900">{{ $lote->numero_lote }}</td>
                    <td class="px-4 py-3 text-sm text-gray-900">
                        {{ $lote->producto->nombre ?? '-' }}
                        @if($lote->producto->principio_activo)
                            <p class="text-xs text-gray-400">{{ $lote->producto->principio_activo }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-500">{{ $lote->establecimiento->codigo ?? '' }} - {{ $lote->establecimiento->nombre ?? '' }}</td>
                    <td class="px-4 py-3 text-sm text-right font-bold {{ $lote->cantidad_actual <= 0 ? 'text-gray-400' : 'text-gray-900' }}">{{ number_format($lote->cantidad_actual, 2) }}</td>
                    <td class="px-4 py-3 text-sm text-right text-gray-500">{{ number_format($lote->cantidad_inicial, 2) }}</td>
                    <td class="px-4 py-3 text-sm">
                        @if($lote->fecha_vencimiento)
                            <span class="{{ $lote->estaVencido() ? 'text-red-600 font-bold' : 'text-gray-500' }}">
                                {{ $lote->fecha_vencimiento->format('d/m/Y') }}
                            </span>
                            @if($lote->estaVencido())
                                <span class="text-[10px] text-red-500 block">VENCIDO</span>
                            @endif
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-500">{{ $lote->fecha_ingreso->format('d/m/Y') }}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm space-x-1">
                        <a href="{{ route('emisor.farmacia.lotes.kardex', $lote) }}" class="inline-flex items-center px-2 py-1 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700">Kardex</a>
                        <a href="{{ route('emisor.farmacia.lotes.ajuste', $lote) }}" class="inline-flex items-center px-2 py-1 bg-gray-600 text-white text-xs font-medium rounded hover:bg-gray-700">Ajustar</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-4 text-center text-sm text-gray-500">No se encontraron lotes.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 border-t">{{ $lotes->withQueryString()->links() }}</div>
    </div>
</x-emisor-layout>
