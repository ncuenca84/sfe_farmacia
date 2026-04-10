<x-emisor-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">Kardex de Lote: {{ $lote->numero_lote }}</h2>
                <p class="text-sm text-gray-500 mt-1">{{ $lote->producto->nombre }} - {{ $lote->establecimiento->nombre }}</p>
            </div>
            <a href="{{ route('emisor.farmacia.lotes.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 text-sm">Volver</a>
        </div>
    </x-slot>

    <!-- Info del lote -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-xs text-gray-500">Stock Actual</p>
            <p class="text-xl font-bold {{ $lote->cantidad_actual > 0 ? 'text-gray-900' : 'text-red-600' }}">{{ number_format($lote->cantidad_actual, 2) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-xs text-gray-500">Stock Inicial</p>
            <p class="text-xl font-bold text-gray-900">{{ number_format($lote->cantidad_inicial, 2) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-xs text-gray-500">Costo Unitario</p>
            <p class="text-xl font-bold text-gray-900">${{ number_format($lote->costo_unitario, 4) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-xs text-gray-500">Vencimiento</p>
            <p class="text-xl font-bold {{ $lote->estaVencido() ? 'text-red-600' : 'text-gray-900' }}">
                {{ $lote->fecha_vencimiento ? $lote->fecha_vencimiento->format('d/m/Y') : '-' }}
            </p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-xs text-gray-500">Ingreso</p>
            <p class="text-xl font-bold text-gray-900">{{ $lote->fecha_ingreso->format('d/m/Y') }}</p>
        </div>
    </div>

    <!-- Movimientos -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Cantidad</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Anterior</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Posterior</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Descripcion</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Usuario</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($movimientos as $mov)
                <tr>
                    <td class="px-4 py-3 text-sm text-gray-500">{{ $mov->created_at->format('d/m/Y H:i') }}</td>
                    <td class="px-4 py-3">
                        @if($mov->tipo === 'ENTRADA')
                            <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700">Entrada</span>
                        @elseif($mov->tipo === 'SALIDA')
                            <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-700">Salida</span>
                        @else
                            <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-700">Ajuste</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-right font-medium {{ $mov->tipo === 'SALIDA' ? 'text-red-600' : ($mov->tipo === 'ENTRADA' ? 'text-green-600' : 'text-yellow-600') }}">
                        {{ $mov->tipo === 'SALIDA' ? '-' : '+' }}{{ number_format(abs($mov->cantidad), 2) }}
                    </td>
                    <td class="px-4 py-3 text-sm text-right text-gray-500">{{ number_format($mov->cantidad_anterior, 2) }}</td>
                    <td class="px-4 py-3 text-sm text-right font-medium text-gray-900">{{ number_format($mov->cantidad_posterior, 2) }}</td>
                    <td class="px-4 py-3 text-sm text-gray-500">{{ $mov->descripcion ?? '-' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-500">{{ $mov->user->nombre_completo ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-4 text-center text-sm text-gray-500">No hay movimientos.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 border-t">{{ $movimientos->links() }}</div>
    </div>
</x-emisor-layout>
