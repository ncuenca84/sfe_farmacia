<x-emisor-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Confirmar Compra</h2>
            <a href="{{ route('emisor.compras.create') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 text-sm">Cancelar</a>
        </div>
    </x-slot>

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6 text-sm">
        @foreach($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
    @endif

    <form method="POST" action="{{ route('emisor.compras.store') }}" id="compra-form">
        @csrf
        <input type="hidden" name="xml_content" value="{{ $xmlBase64 }}">

        {{-- Proveedor Info --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-3">Proveedor (Emisor del XML)</h3>
                <dl class="space-y-1 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-500">Razon Social:</dt><dd class="text-gray-900 font-medium">{{ $datosXml['info_tributaria']['razon_social'] }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">RUC:</dt><dd class="text-gray-900">{{ $datosXml['info_tributaria']['ruc'] }}</dd></div>
                    @if($datosXml['info_tributaria']['nombre_comercial'])
                    <div class="flex justify-between"><dt class="text-gray-500">Nombre Comercial:</dt><dd class="text-gray-900">{{ $datosXml['info_tributaria']['nombre_comercial'] }}</dd></div>
                    @endif
                    <div class="flex justify-between"><dt class="text-gray-500">Dir. Matriz:</dt><dd class="text-gray-900 text-right max-w-[250px]">{{ $datosXml['info_tributaria']['dir_matriz'] }}</dd></div>
                </dl>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-3">Datos del Documento</h3>
                <dl class="space-y-1 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-500">Numero:</dt><dd class="text-gray-900 font-medium">{{ $datosXml['numero_comprobante'] }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Fecha Emision:</dt><dd class="text-gray-900">{{ $datosXml['info_factura']['fecha_emision'] }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Autorizacion:</dt><dd class="text-gray-900 break-all text-xs">{{ $datosXml['autorizacion']['numero_autorizacion'] ?? 'N/A' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Clave Acceso:</dt><dd class="text-gray-900 break-all text-xs">{{ $datosXml['info_tributaria']['clave_acceso'] }}</dd></div>
                </dl>
            </div>
        </div>

        {{-- Detalles con vinculacion de productos --}}
        <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
            <div class="px-6 py-4 border-b">
                <h3 class="text-lg font-medium text-gray-900">Detalles de la Compra</h3>
                @if(auth()->user()->establecimientosActivos()->first()?->maneja_inventario)
                <p class="text-xs text-gray-500 mt-1">Vincule productos a su catalogo y marque los que desea agregar al inventario.</p>
                @endif
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Codigo</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Descripcion XML</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Cant.</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">P. Unit.</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">IVA</th>
                            @if(auth()->user()->establecimientosActivos()->first()?->maneja_inventario)
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Producto Local</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Inventario</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($datosXml['detalles'] as $index => $detalle)
                        @php
                            $ivaDetalle = collect($detalle['impuestos'])->where('codigo', '2')->sum('valor');
                        @endphp
                        <tr class="hover:bg-gray-50" x-data="{ productoId: '{{ old("detalles.{$index}.producto_id", '') }}' }">
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $detalle['codigo_principal'] }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $detalle['descripcion'] }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ $detalle['cantidad'] }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900 text-right">${{ number_format($detalle['precio_unitario'], 6) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900 text-right">${{ number_format($detalle['precio_total_sin_impuesto'], 2) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900 text-right">${{ number_format($ivaDetalle, 2) }}</td>
                            @if(auth()->user()->establecimientosActivos()->first()?->maneja_inventario)
                            <td class="px-4 py-3">
                                <select name="detalles[{{ $index }}][producto_id]"
                                        class="w-full border-gray-300 rounded-md shadow-sm text-xs"
                                        x-model="productoId">
                                    <option value="">-- Sin vincular --</option>
                                    @foreach($productos as $producto)
                                        <option value="{{ $producto->id }}">{{ $producto->codigo_principal }} - {{ \Illuminate\Support\Str::limit($producto->descripcion, 40) }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <input type="hidden" name="detalles[{{ $index }}][agregar_inventario]" value="0">
                                <input type="checkbox" name="detalles[{{ $index }}][agregar_inventario]" value="1"
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                       :disabled="!productoId"
                                       {{ old("detalles.{$index}.agregar_inventario") ? 'checked' : '' }}>
                            </td>
                            @endif
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Totales --}}
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="flex justify-end">
                <div class="w-72 space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-gray-600">Subtotal sin impuestos:</span><span class="font-medium">${{ number_format($datosXml['info_factura']['total_sin_impuestos'], 2) }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-600">IVA:</span><span class="font-medium">${{ number_format($datosXml['total_iva'], 2) }}</span></div>
                    <div class="flex justify-between font-bold border-t pt-2 text-base"><span>TOTAL:</span><span>${{ number_format($datosXml['info_factura']['importe_total'], 2) }}</span></div>
                </div>
            </div>
        </div>

        {{-- Submit --}}
        <div class="flex justify-end gap-3">
            <a href="{{ route('emisor.compras.create') }}" class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm font-medium">Cancelar</a>
            <button type="submit" class="inline-flex items-center gap-2 bg-green-600 text-white px-8 py-3 rounded-xl hover:bg-green-700 text-sm font-medium shadow-sm transition-all duration-150">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Registrar Compra
            </button>
        </div>
    </form>
</x-emisor-layout>
