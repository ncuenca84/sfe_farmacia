<x-emisor-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Farmacia - Dashboard</h2>
    </x-slot>

    <!-- KPIs -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-5">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $totalProductos }}</p>
                    <p class="text-xs text-gray-500">Medicamentos Activos</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-5">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $totalProveedores }}</p>
                    <p class="text-xs text-gray-500">Proveedores Activos</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-5">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $totalCategorias }}</p>
                    <p class="text-xs text-gray-500">Categorias</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-5 {{ $productosVencidos > 0 ? 'ring-2 ring-red-300' : '' }}">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center mr-3">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold {{ $productosVencidos > 0 ? 'text-red-600' : 'text-gray-900' }}">{{ $productosVencidos }}</p>
                    <p class="text-xs text-gray-500">Productos Vencidos</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-5 {{ $proximosVencer > 0 ? 'ring-2 ring-yellow-300' : '' }}">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center mr-3">
                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold {{ $proximosVencer > 0 ? 'text-yellow-600' : 'text-gray-900' }}">{{ $proximosVencer }}</p>
                    <p class="text-xs text-gray-500">Proximos a Vencer (30d)</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-5 {{ $stockBajo > 0 ? 'ring-2 ring-orange-300' : '' }}">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center mr-3">
                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold {{ $stockBajo > 0 ? 'text-orange-600' : 'text-gray-900' }}">{{ $stockBajo }}</p>
                    <p class="text-xs text-gray-500">Stock Bajo</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Productos por Categoría -->
        @if($porCategoria->isNotEmpty())
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-4">Productos por Categoria</h3>
            <div class="space-y-3">
                @foreach($porCategoria as $cat)
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">{{ $cat->nombre }}</span>
                    <div class="flex items-center">
                        <div class="w-32 bg-gray-200 rounded-full h-2 mr-3">
                            <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $totalProductos > 0 ? round($cat->total / $totalProductos * 100) : 0 }}%"></div>
                        </div>
                        <span class="text-sm font-medium text-gray-700">{{ $cat->total }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Próximos a vencer -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-sm font-semibold text-gray-700">Proximos a Vencer</h3>
                <a href="{{ route('emisor.farmacia.vencidos', ['filtro' => 'proximos']) }}" class="text-xs text-blue-600 hover:underline">Ver todos</a>
            </div>
            @if($proximosVencerLista->isNotEmpty())
            <div class="space-y-2">
                @foreach($proximosVencerLista as $prod)
                <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                    <div>
                        <p class="text-sm font-medium text-gray-800">{{ $prod->nombre }}</p>
                        <p class="text-xs text-gray-400">Lote: {{ $prod->numero_lote ?? '-' }}</p>
                    </div>
                    <span class="text-xs font-medium px-2 py-1 rounded-full {{ $prod->fecha_vencimiento->diffInDays(now()) <= 7 ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700' }}">
                        {{ $prod->fecha_vencimiento->format('d/m/Y') }}
                    </span>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-sm text-gray-400 text-center py-4">No hay productos proximos a vencer.</p>
            @endif
        </div>

        <!-- Productos Vencidos -->
        @if($vencidosLista->isNotEmpty())
        <div class="bg-white rounded-lg shadow p-6 lg:col-span-2">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-sm font-semibold text-red-600">Productos Vencidos</h3>
                <a href="{{ route('emisor.farmacia.vencidos', ['filtro' => 'vencidos']) }}" class="text-xs text-blue-600 hover:underline">Ver todos</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Producto</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Lote</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Vencimiento</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Dias Vencido</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($vencidosLista as $prod)
                        <tr class="bg-red-50">
                            <td class="px-4 py-2 text-sm text-gray-800">{{ $prod->nombre }}</td>
                            <td class="px-4 py-2 text-sm text-gray-500">{{ $prod->numero_lote ?? '-' }}</td>
                            <td class="px-4 py-2 text-sm text-red-600 font-medium">{{ $prod->fecha_vencimiento->format('d/m/Y') }}</td>
                            <td class="px-4 py-2 text-sm text-red-600 font-bold">{{ $prod->fecha_vencimiento->diffInDays(now()) }}d</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
</x-emisor-layout>
