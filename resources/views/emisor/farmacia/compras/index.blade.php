<x-emisor-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Ordenes de Compra</h2>
            <div class="space-x-2">
                <a href="{{ route('emisor.farmacia.compras.reposicion') }}" class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600 text-sm">Sugerencia Reposicion</a>
                <a href="{{ route('emisor.farmacia.compras.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm">Nueva Orden</a>
            </div>
        </div>
    </x-slot>

    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form method="GET" class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Estado</label>
                <select name="estado" class="border-gray-300 rounded-md shadow-sm text-sm" onchange="this.form.submit()">
                    <option value="">Todos</option>
                    <option value="PENDIENTE" {{ request('estado') === 'PENDIENTE' ? 'selected' : '' }}>Pendiente</option>
                    <option value="PARCIAL" {{ request('estado') === 'PARCIAL' ? 'selected' : '' }}>Parcial</option>
                    <option value="RECIBIDA" {{ request('estado') === 'RECIBIDA' ? 'selected' : '' }}>Recibida</option>
                    <option value="CANCELADA" {{ request('estado') === 'CANCELADA' ? 'selected' : '' }}>Cancelada</option>
                </select>
            </div>
            <div class="flex-1">
                <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar por numero o proveedor..." class="w-full border-gray-300 rounded-md shadow-sm text-sm">
            </div>
            <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded hover:bg-gray-700 text-sm">Filtrar</button>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Numero</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Proveedor</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Establecimiento</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Estado</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($ordenes as $orden)
                <tr>
                    <td class="px-4 py-3 text-sm font-mono text-gray-900">{{ $orden->numero ?? '#'.$orden->id }}</td>
                    <td class="px-4 py-3 text-sm text-gray-900">{{ $orden->proveedor->nombre }}</td>
                    <td class="px-4 py-3 text-sm text-gray-500">{{ $orden->establecimiento->nombre }}</td>
                    <td class="px-4 py-3 text-sm text-gray-500">{{ $orden->fecha->format('d/m/Y') }}</td>
                    <td class="px-4 py-3 text-sm text-right font-medium">${{ number_format($orden->total, 2) }}</td>
                    <td class="px-4 py-3 text-center">
                        @php $colors = ['PENDIENTE' => 'bg-yellow-100 text-yellow-700', 'PARCIAL' => 'bg-blue-100 text-blue-700', 'RECIBIDA' => 'bg-green-100 text-green-700', 'CANCELADA' => 'bg-gray-100 text-gray-500']; @endphp
                        <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full {{ $colors[$orden->estado] ?? 'bg-gray-100 text-gray-500' }}">{{ $orden->estado }}</span>
                    </td>
                    <td class="px-4 py-3 text-sm">
                        <a href="{{ route('emisor.farmacia.compras.show', $orden) }}" class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700">Ver</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-4 text-center text-sm text-gray-500">No hay ordenes de compra.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 border-t">{{ $ordenes->withQueryString()->links() }}</div>
    </div>
</x-emisor-layout>
