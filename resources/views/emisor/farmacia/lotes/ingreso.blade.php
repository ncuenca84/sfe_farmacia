<x-emisor-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Ingresar Lote</h2>
            <a href="{{ route('emisor.farmacia.lotes.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 text-sm">Volver</a>
        </div>
    </x-slot>

    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('emisor.farmacia.lotes.store') }}">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Producto</label>
                    <select name="producto_id" class="w-full border-gray-300 rounded-md shadow-sm text-sm" required>
                        <option value="">Seleccionar producto...</option>
                        @foreach($productos as $prod)
                            <option value="{{ $prod->id }}" {{ old('producto_id') == $prod->id ? 'selected' : '' }}>
                                {{ $prod->codigo_principal ? $prod->codigo_principal.' - ' : '' }}{{ $prod->nombre }}
                                {{ $prod->principio_activo ? '('.$prod->principio_activo.')' : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('producto_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Establecimiento</label>
                    <select name="establecimiento_id" class="w-full border-gray-300 rounded-md shadow-sm text-sm" required>
                        <option value="">Seleccionar...</option>
                        @foreach($establecimientos as $est)
                            <option value="{{ $est->id }}" {{ old('establecimiento_id') == $est->id ? 'selected' : '' }}>
                                {{ $est->codigo }} - {{ $est->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('establecimiento_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Numero de Lote</label>
                    <input type="text" name="numero_lote" value="{{ old('numero_lote') }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm" required>
                    @error('numero_lote') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de Vencimiento</label>
                    <input type="date" name="fecha_vencimiento" value="{{ old('fecha_vencimiento') }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                    @error('fecha_vencimiento') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cantidad</label>
                    <input type="number" name="cantidad" value="{{ old('cantidad') }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm" step="any" min="0.0001" required>
                    @error('cantidad') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Costo Unitario</label>
                    <input type="number" name="costo_unitario" value="{{ old('costo_unitario', '0') }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm" step="any" min="0">
                    @error('costo_unitario') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de Ingreso</label>
                    <input type="date" name="fecha_ingreso" value="{{ old('fecha_ingreso', now()->format('Y-m-d')) }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                    @error('fecha_ingreso') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nota</label>
                    <input type="text" name="nota" value="{{ old('nota') }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm" placeholder="Ej: Compra a proveedor X, factura #123">
                    @error('nota') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-6">
                <p class="text-sm text-blue-700"><strong>FEFO:</strong> Al vender, el sistema consumira automaticamente del lote mas proximo a vencer primero (First Expired First Out).</p>
            </div>

            <div class="flex justify-end mt-6">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Ingresar Lote</button>
            </div>
        </form>
    </div>
</x-emisor-layout>
