<x-emisor-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Rentabilidad por Categoria</h2>
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

    @php $totalGeneral = $categorias->sum('total_ventas'); @endphp

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Categoria</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Productos</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Uds. Vendidas</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total Ventas</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">% del Total</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Participacion</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($categorias as $cat)
                @php $porcentaje = $totalGeneral > 0 ? ($cat->total_ventas / $totalGeneral) * 100 : 0; @endphp
                <tr>
                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $cat->categoria }}</td>
                    <td class="px-4 py-3 text-sm text-right text-gray-500">{{ $cat->num_productos }}</td>
                    <td class="px-4 py-3 text-sm text-right">{{ number_format($cat->total_vendido, 2) }}</td>
                    <td class="px-4 py-3 text-sm text-right font-bold">${{ number_format($cat->total_ventas, 2) }}</td>
                    <td class="px-4 py-3 text-sm text-right text-gray-600">{{ number_format($porcentaje, 1) }}%</td>
                    <td class="px-4 py-3">
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-500 h-2 rounded-full" style="width: {{ min($porcentaje, 100) }}%"></div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">No hay datos.</td></tr>
                @endforelse
            </tbody>
            @if($categorias->isNotEmpty())
            <tfoot class="bg-gray-50">
                <tr>
                    <td class="px-4 py-3 text-sm font-bold">TOTAL</td>
                    <td class="px-4 py-3 text-sm text-right font-bold">{{ $categorias->sum('num_productos') }}</td>
                    <td class="px-4 py-3 text-sm text-right font-bold">{{ number_format($categorias->sum('total_vendido'), 2) }}</td>
                    <td class="px-4 py-3 text-sm text-right font-bold">${{ number_format($totalGeneral, 2) }}</td>
                    <td class="px-4 py-3 text-sm text-right font-bold">100%</td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</x-emisor-layout>
