<x-emisor-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">Orden de Compra {{ $compra->numero ?? '#'.$compra->id }}</h2>
                <p class="text-sm text-gray-500">{{ $compra->proveedor->nombre }} | {{ $compra->fecha->format('d/m/Y') }}</p>
            </div>
            <a href="{{ route('emisor.farmacia.compras.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 text-sm">Volver</a>
        </div>
    </x-slot>

    @php $colors = ['PENDIENTE' => 'bg-yellow-100 text-yellow-700', 'PARCIAL' => 'bg-blue-100 text-blue-700', 'RECIBIDA' => 'bg-green-100 text-green-700', 'CANCELADA' => 'bg-gray-100 text-gray-500']; @endphp

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-xs text-gray-500">Estado</p>
            <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full {{ $colors[$compra->estado] ?? '' }}">{{ $compra->estado }}</span>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-xs text-gray-500">Total</p>
            <p class="text-xl font-bold">${{ number_format($compra->total, 2) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-xs text-gray-500">Establecimiento</p>
            <p class="text-sm font-medium">{{ $compra->establecimiento->nombre }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-xs text-gray-500">Creado por</p>
            <p class="text-sm font-medium">{{ $compra->user->nombre_completo ?? '-' }}</p>
        </div>
    </div>

    <!-- Items -->
    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Producto</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Pedida</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Recibida</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Pendiente</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Costo Unit.</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lote</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vencimiento</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($compra->items as $item)
                <tr class="{{ $item->pendiente() <= 0 ? 'bg-green-50' : '' }}">
                    <td class="px-4 py-3 text-sm text-gray-900">{{ $item->producto->nombre }}</td>
                    <td class="px-4 py-3 text-sm text-right">{{ number_format($item->cantidad_pedida, 2) }}</td>
                    <td class="px-4 py-3 text-sm text-right font-medium text-green-600">{{ number_format($item->cantidad_recibida, 2) }}</td>
                    <td class="px-4 py-3 text-sm text-right font-medium {{ $item->pendiente() > 0 ? 'text-yellow-600' : 'text-gray-400' }}">{{ number_format($item->pendiente(), 2) }}</td>
                    <td class="px-4 py-3 text-sm text-right">${{ number_format($item->costo_unitario, 4) }}</td>
                    <td class="px-4 py-3 text-sm text-gray-500">{{ $item->numero_lote ?? '-' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-500">{{ $item->fecha_vencimiento?->format('d/m/Y') ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Formulario de recepción -->
    @if(in_array($compra->estado, ['PENDIENTE', 'PARCIAL']))
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-sm font-semibold text-gray-700 mb-4">Registrar Recepcion</h3>
        <form method="POST" action="{{ route('emisor.farmacia.compras.recibir', $compra) }}">
            @csrf
            <table class="min-w-full divide-y divide-gray-200 mb-4">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Producto</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Pendiente</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Recibir</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Lote</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Vencimiento</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($compra->items as $item)
                    @if($item->pendiente() > 0)
                    <tr>
                        <td class="px-4 py-2 text-sm">{{ $item->producto->nombre }}</td>
                        <td class="px-4 py-2 text-sm text-right text-yellow-600 font-medium">{{ number_format($item->pendiente(), 2) }}</td>
                        <td class="px-4 py-2">
                            <input type="number" name="cantidades[{{ $item->id }}]" value="{{ $item->pendiente() }}" class="w-24 border-gray-300 rounded text-sm text-right" step="any" min="0" max="{{ $item->pendiente() }}">
                        </td>
                        <td class="px-4 py-2">
                            <input type="text" name="lotes[{{ $item->id }}]" value="{{ $item->numero_lote }}" class="w-28 border-gray-300 rounded text-sm" placeholder="Lote">
                        </td>
                        <td class="px-4 py-2">
                            <input type="date" name="vencimientos[{{ $item->id }}]" value="{{ $item->fecha_vencimiento?->format('Y-m-d') }}" class="border-gray-300 rounded text-sm">
                        </td>
                    </tr>
                    @endif
                    @endforeach
                </tbody>
            </table>
            <div class="flex justify-end">
                <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">Registrar Recepcion</button>
            </div>
        </form>
    </div>
    @endif

    @if($compra->observaciones)
    <div class="bg-white rounded-lg shadow p-4 mt-4">
        <p class="text-xs text-gray-500 mb-1">Observaciones</p>
        <p class="text-sm text-gray-700">{{ $compra->observaciones }}</p>
    </div>
    @endif
</x-emisor-layout>
