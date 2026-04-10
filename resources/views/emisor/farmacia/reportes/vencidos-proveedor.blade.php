<x-emisor-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Productos Vencidos por Proveedor</h2>
    </x-slot>

    @forelse($porProveedor as $proveedor => $productos)
    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
        <div class="bg-red-50 px-4 py-3 border-b">
            <h3 class="text-sm font-bold text-red-700">{{ $proveedor }} ({{ $productos->count() }} productos vencidos)</h3>
        </div>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Producto</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Lote</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Vencimiento</th>
                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Precio Unit.</th>
                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Dias Vencido</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($productos as $prod)
                <tr class="bg-red-50">
                    <td class="px-4 py-2 text-sm text-gray-900">{{ $prod->nombre }}</td>
                    <td class="px-4 py-2 text-sm text-gray-500">{{ $prod->numero_lote ?? '-' }}</td>
                    <td class="px-4 py-2 text-sm text-red-600 font-medium">{{ \Carbon\Carbon::parse($prod->fecha_vencimiento)->format('d/m/Y') }}</td>
                    <td class="px-4 py-2 text-sm text-right">${{ number_format($prod->precio_unitario, 2) }}</td>
                    <td class="px-4 py-2 text-sm text-right text-red-600 font-bold">{{ \Carbon\Carbon::parse($prod->fecha_vencimiento)->diffInDays(now()) }}d</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @empty
    <div class="bg-white rounded-lg shadow p-8 text-center text-sm text-gray-500">No hay productos vencidos.</div>
    @endforelse
</x-emisor-layout>
