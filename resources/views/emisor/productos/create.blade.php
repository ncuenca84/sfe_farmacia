<x-emisor-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Nuevo Producto</h2>
            <a href="{{ route('emisor.configuracion.productos.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 text-sm">Volver</a>
        </div>
    </x-slot>

    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('emisor.configuracion.productos.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Codigo Principal</label>
                    <input type="text" name="codigo_principal" value="{{ old('codigo_principal') }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm" required>
                    @error('codigo_principal') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Codigo Auxiliar</label>
                    <input type="text" name="codigo_auxiliar" value="{{ old('codigo_auxiliar') }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                    @error('codigo_auxiliar') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre / Descripcion</label>
                    <input type="text" name="nombre" value="{{ old('nombre') }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm" required>
                    @error('nombre') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Categoria</label>
                    <select name="categoria_producto_id" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                        <option value="">Sin categoria</option>
                        @foreach($categorias as $cat)
                            <option value="{{ $cat->id }}" {{ old('categoria_producto_id') == $cat->id ? 'selected' : '' }}>{{ $cat->nombre }}</option>
                        @endforeach
                    </select>
                    @error('categoria_producto_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Proveedor</label>
                    <select name="proveedor_id" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                        <option value="">Sin proveedor</option>
                        @foreach($proveedores as $prov)
                            <option value="{{ $prov->id }}" {{ old('proveedor_id') == $prov->id ? 'selected' : '' }}>{{ $prov->nombre }}</option>
                        @endforeach
                    </select>
                    @error('proveedor_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Principio Activo</label>
                    <input type="text" name="principio_activo" value="{{ old('principio_activo') }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm" placeholder="Ej: Paracetamol, Amoxicilina...">
                    @error('principio_activo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Concentracion</label>
                    <input type="text" name="concentracion" value="{{ old('concentracion') }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm" placeholder="Ej: 500mg, 5ml, 10mg/ml...">
                    @error('concentracion') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Presentacion</label>
                    <select name="presentacion_id" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                        <option value="">Sin presentacion</option>
                        @foreach($presentaciones as $pres)
                            <option value="{{ $pres->id }}" {{ old('presentacion_id') == $pres->id ? 'selected' : '' }}>{{ $pres->nombre }}</option>
                        @endforeach
                    </select>
                    @error('presentacion_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Laboratorio</label>
                    <select name="laboratorio_id" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                        <option value="">Sin laboratorio</option>
                        @foreach($laboratorios as $lab)
                            <option value="{{ $lab->id }}" {{ old('laboratorio_id') == $lab->id ? 'selected' : '' }}>{{ $lab->nombre }}</option>
                        @endforeach
                    </select>
                    @error('laboratorio_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Venta</label>
                    <select name="tipo_venta" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                        <option value="venta_libre" {{ old('tipo_venta', 'venta_libre') === 'venta_libre' ? 'selected' : '' }}>Venta Libre</option>
                        <option value="requiere_receta" {{ old('tipo_venta') === 'requiere_receta' ? 'selected' : '' }}>Requiere Receta</option>
                        <option value="controlado" {{ old('tipo_venta') === 'controlado' ? 'selected' : '' }}>Controlado</option>
                    </select>
                    @error('tipo_venta') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Registro Sanitario</label>
                    <input type="text" name="registro_sanitario" value="{{ old('registro_sanitario') }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                    @error('registro_sanitario') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Numero de Lote</label>
                    <input type="text" name="numero_lote" value="{{ old('numero_lote') }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                    @error('numero_lote') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de Vencimiento</label>
                    <input type="date" name="fecha_vencimiento" value="{{ old('fecha_vencimiento') }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                    @error('fecha_vencimiento') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Precio Unitario</label>
                    <input type="number" name="precio_unitario" value="{{ old('precio_unitario', '0.00') }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm" step="any" min="0" required>
                    @error('precio_unitario') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">IVA</label>
                    <select name="impuesto_iva_id" class="w-full border-gray-300 rounded-md shadow-sm text-sm" required>
                        <option value="">Seleccionar...</option>
                        @if(isset($ivas))
                            @foreach($ivas as $iva)
                                <option value="{{ $iva->id }}" {{ old('impuesto_iva_id') == $iva->id ? 'selected' : '' }}>{{ $iva->nombre }} ({{ $iva->tarifa ?? 0 }}%)</option>
                            @endforeach
                        @else
                            <option value="1" {{ old('impuesto_iva_id') == '1' ? 'selected' : '' }}>IVA 0%</option>
                            <option value="2" {{ old('impuesto_iva_id') == '2' ? 'selected' : '' }}>IVA 12%</option>
                            <option value="3" {{ old('impuesto_iva_id') == '3' ? 'selected' : '' }}>IVA 15%</option>
                            <option value="4" {{ old('impuesto_iva_id') == '4' ? 'selected' : '' }}>No Objeto de Impuesto</option>
                            <option value="5" {{ old('impuesto_iva_id') == '5' ? 'selected' : '' }}>Exento de IVA</option>
                        @endif
                    </select>
                    @error('impuesto_iva_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Imagen del Producto</label>
                    <input type="file" name="imagen" accept="image/*" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                    @error('imagen') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            @if($manejaInventario)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Stock Inicial</label>
                    <input type="number" name="stock_inicial" value="{{ old('stock_inicial', '0') }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm" step="any" min="0">
                    <p class="text-xs text-gray-400 mt-1">Cantidad inicial de stock (por defecto 0).</p>
                    @error('stock_inicial') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Stock Minimo</label>
                    <input type="number" name="stock_minimo" value="{{ old('stock_minimo', '0') }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm" step="any" min="0">
                    <p class="text-xs text-gray-400 mt-1">Se alerta cuando el stock baje de este valor.</p>
                    @error('stock_minimo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
            @endif

            <div class="flex justify-end mt-6">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Crear Producto</button>
            </div>
        </form>
    </div>
</x-emisor-layout>
