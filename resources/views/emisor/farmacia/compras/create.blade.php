<x-emisor-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Nueva Orden de Compra</h2>
            <a href="{{ route('emisor.farmacia.compras.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 text-sm">Volver</a>
        </div>
    </x-slot>

    <div class="bg-white rounded-lg shadow p-6" x-data="ordenCompra()">
        <form method="POST" action="{{ route('emisor.farmacia.compras.store') }}">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Proveedor</label>
                    <select name="proveedor_id" class="w-full border-gray-300 rounded-md shadow-sm text-sm" required>
                        <option value="">Seleccionar...</option>
                        @foreach($proveedores as $prov)
                            <option value="{{ $prov->id }}" {{ old('proveedor_id') == $prov->id ? 'selected' : '' }}>{{ $prov->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Establecimiento</label>
                    <select name="establecimiento_id" class="w-full border-gray-300 rounded-md shadow-sm text-sm" required>
                        @foreach($establecimientos as $est)
                            <option value="{{ $est->id }}">{{ $est->codigo }} - {{ $est->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha</label>
                    <input type="date" name="fecha" value="{{ old('fecha', now()->format('Y-m-d')) }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Numero de Orden</label>
                    <input type="text" name="numero" value="{{ old('numero') }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm" placeholder="Opcional">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Observaciones</label>
                    <input type="text" name="observaciones" value="{{ old('observaciones') }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                </div>
            </div>

            <h3 class="text-sm font-semibold text-gray-700 mb-3">Items</h3>
            <template x-for="(item, i) in items" :key="i">
                <div class="grid grid-cols-6 gap-2 mb-2 items-end">
                    <div class="col-span-2">
                        <select :name="'items['+i+'][producto_id]'" class="w-full border-gray-300 rounded-md shadow-sm text-xs" required>
                            <option value="">Producto...</option>
                            @foreach($productos as $prod)
                                <option value="{{ $prod->id }}">{{ $prod->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <input type="number" :name="'items['+i+'][cantidad_pedida]'" placeholder="Cantidad" class="w-full border-gray-300 rounded-md shadow-sm text-xs" step="any" min="0.0001" required>
                    </div>
                    <div>
                        <input type="number" :name="'items['+i+'][costo_unitario]'" placeholder="Costo Unit." class="w-full border-gray-300 rounded-md shadow-sm text-xs" step="any" min="0">
                    </div>
                    <div>
                        <input type="text" :name="'items['+i+'][numero_lote]'" placeholder="Lote" class="w-full border-gray-300 rounded-md shadow-sm text-xs">
                    </div>
                    <div class="flex gap-1">
                        <input type="date" :name="'items['+i+'][fecha_vencimiento]'" class="w-full border-gray-300 rounded-md shadow-sm text-xs">
                        <button type="button" @click="items.splice(i, 1)" class="text-red-500 hover:text-red-700 px-1" x-show="items.length > 1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>
            </template>
            <button type="button" @click="items.push({})" class="text-blue-600 text-sm hover:underline mb-4">+ Agregar item</button>

            <div class="flex justify-end mt-6">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Crear Orden</button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
    function ordenCompra() { return { items: [{}] } }
    </script>
    @endpush
</x-emisor-layout>
