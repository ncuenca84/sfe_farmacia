<x-emisor-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Editar Producto: {{ $producto->nombre }}</h2>
            <a href="{{ route('emisor.configuracion.productos.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 text-sm">Volver</a>
        </div>
    </x-slot>

    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('emisor.configuracion.productos.update', $producto) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Codigo Principal</label>
                    <input type="text" name="codigo_principal" value="{{ old('codigo_principal', $producto->codigo_principal) }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm" required>
                    @error('codigo_principal') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Codigo Auxiliar</label>
                    <input type="text" name="codigo_auxiliar" value="{{ old('codigo_auxiliar', $producto->codigo_auxiliar) }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                    @error('codigo_auxiliar') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre / Descripcion</label>
                    <input type="text" name="nombre" value="{{ old('nombre', $producto->nombre) }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm" required>
                    @error('nombre') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Categoria</label>
                    <select name="categoria_producto_id" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                        <option value="">Sin categoria</option>
                        @foreach($categorias as $cat)
                            <option value="{{ $cat->id }}" {{ old('categoria_producto_id', $producto->categoria_producto_id) == $cat->id ? 'selected' : '' }}>{{ $cat->nombre }}</option>
                        @endforeach
                    </select>
                    @error('categoria_producto_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Proveedor</label>
                    <select name="proveedor_id" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                        <option value="">Sin proveedor</option>
                        @foreach($proveedores as $prov)
                            <option value="{{ $prov->id }}" {{ old('proveedor_id', $producto->proveedor_id) == $prov->id ? 'selected' : '' }}>{{ $prov->nombre }}</option>
                        @endforeach
                    </select>
                    @error('proveedor_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Numero de Lote</label>
                    <input type="text" name="numero_lote" value="{{ old('numero_lote', $producto->numero_lote) }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                    @error('numero_lote') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de Vencimiento</label>
                    <input type="date" name="fecha_vencimiento" value="{{ old('fecha_vencimiento', $producto->fecha_vencimiento?->format('Y-m-d')) }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                    @if($producto->estaVencido())
                        <p class="text-red-500 text-xs mt-1 font-bold">Este producto esta vencido.</p>
                    @elseif($producto->proximoAVencer())
                        <p class="text-yellow-600 text-xs mt-1 font-bold">Proximo a vencer.</p>
                    @endif
                    @error('fecha_vencimiento') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Precio Unitario</label>
                    <input type="number" name="precio_unitario" value="{{ old('precio_unitario', $producto->precio_unitario) }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm" step="any" min="0" required>
                    @error('precio_unitario') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">IVA</label>
                    <select name="impuesto_iva_id" class="w-full border-gray-300 rounded-md shadow-sm text-sm" required>
                        <option value="">Seleccionar...</option>
                        @if(isset($ivas))
                            @foreach($ivas as $iva)
                                <option value="{{ $iva->id }}" {{ old('impuesto_iva_id', $producto->impuesto_iva_id) == $iva->id ? 'selected' : '' }}>{{ $iva->nombre }} ({{ $iva->tarifa ?? 0 }}%)</option>
                            @endforeach
                        @else
                            <option value="1" {{ old('impuesto_iva_id', $producto->impuesto_iva_id) == '1' ? 'selected' : '' }}>IVA 0%</option>
                            <option value="2" {{ old('impuesto_iva_id', $producto->impuesto_iva_id) == '2' ? 'selected' : '' }}>IVA 12%</option>
                            <option value="3" {{ old('impuesto_iva_id', $producto->impuesto_iva_id) == '3' ? 'selected' : '' }}>IVA 15%</option>
                            <option value="4" {{ old('impuesto_iva_id', $producto->impuesto_iva_id) == '4' ? 'selected' : '' }}>No Objeto de Impuesto</option>
                            <option value="5" {{ old('impuesto_iva_id', $producto->impuesto_iva_id) == '5' ? 'selected' : '' }}>Exento de IVA</option>
                        @endif
                    </select>
                    @error('impuesto_iva_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Imagen del Producto</label>
                    @if($producto->imagen)
                        <div class="mb-2">
                            <img src="{{ asset('storage/' . $producto->imagen) }}" alt="{{ $producto->nombre }}" class="w-24 h-24 object-cover rounded border">
                        </div>
                    @endif
                    <input type="file" name="imagen" accept="image/*" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                    @error('imagen') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            @if($manejaInventario && $inventarios->count())
            <div class="mt-6">
                <h3 class="text-sm font-medium text-gray-700 mb-3">Stock por Establecimiento</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Establecimiento</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Stock Actual</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Stock Minimo</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($inventarios as $inv)
                            <tr>
                                <td class="px-4 py-2 text-sm text-gray-900">{{ $inv->establecimiento->codigo ?? '' }} - {{ $inv->establecimiento->nombre ?? '' }}</td>
                                <td class="px-4 py-2">
                                    <input type="number" name="stock_actual[{{ $inv->id }}]" value="{{ old('stock_actual.'.$inv->id, $inv->stock_actual) }}" class="w-32 border-gray-300 rounded-md shadow-sm text-sm text-right ml-auto block" step="any" min="0">
                                </td>
                                <td class="px-4 py-2">
                                    <input type="number" name="stock_minimo[{{ $inv->id }}]" value="{{ old('stock_minimo.'.$inv->id, $inv->stock_minimo) }}" class="w-32 border-gray-300 rounded-md shadow-sm text-sm text-right ml-auto block" step="any" min="0">
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            <div class="flex justify-end mt-6">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Actualizar Producto</button>
            </div>
        </form>
    </div>
</x-emisor-layout>
