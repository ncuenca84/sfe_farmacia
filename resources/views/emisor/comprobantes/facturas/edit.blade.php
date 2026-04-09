<x-emisor-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Editar Factura</h2>
            <a href="{{ route('emisor.comprobantes.facturas.show', $factura) }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 text-sm">Volver</a>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('emisor.comprobantes.facturas.update', $factura) }}" id="factura-form">
        @csrf
        @include('emisor.comprobantes.partials.validation-errors')
        @method('PUT')

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Datos Generales</h3>
                <div class="flex items-center gap-3 text-sm">
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Emision</label>
                    <input type="date" name="fecha_emision" value="{{ old('fecha_emision', $factura->fecha_emision instanceof \Carbon\Carbon ? $factura->fecha_emision->format('Y-m-d') : $factura->fecha_emision) }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm" required>
                    @error('fecha_emision') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <x-cliente-search :selected-id="old('cliente_id', $factura->cliente_id)" :selected-display="$factura->cliente ? $factura->cliente->identificacion . ' - ' . $factura->cliente->razon_social : ''" />
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Forma de Pago</label>
                    <select name="forma_pago" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                        <option value="01" {{ old('forma_pago', $factura->forma_pago) == '01' ? 'selected' : '' }}>Sin utilizacion del sistema financiero</option>
                        <option value="15" {{ old('forma_pago', $factura->forma_pago) == '15' ? 'selected' : '' }}>Compensacion de deudas</option>
                        <option value="16" {{ old('forma_pago', $factura->forma_pago) == '16' ? 'selected' : '' }}>Tarjeta de debito</option>
                        <option value="17" {{ old('forma_pago', $factura->forma_pago) == '17' ? 'selected' : '' }}>Dinero electronico</option>
                        <option value="18" {{ old('forma_pago', $factura->forma_pago) == '18' ? 'selected' : '' }}>Tarjeta prepago</option>
                        <option value="19" {{ old('forma_pago', $factura->forma_pago) == '19' ? 'selected' : '' }}>Tarjeta de credito</option>
                        <option value="20" {{ old('forma_pago', $factura->forma_pago) == '20' ? 'selected' : '' }}>Otros con utilizacion del sistema financiero</option>
                        <option value="21" {{ old('forma_pago', $factura->forma_pago) == '21' ? 'selected' : '' }}>Endoso de titulos</option>
                    </select>
                    @error('forma_pago') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Plazo (dias)</label>
                    <input type="number" name="plazo" value="{{ old('plazo', $factura->plazo) }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm" min="0" placeholder="0">
                    @error('plazo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- Build the initial products array for Alpine, mapping impuesto.codigo_porcentaje back to ImpuestoIva.id --}}
        @php
            $initialProducts = $factura->detalles->map(function ($d) use ($ivas) {
                $impuesto = $d->impuestos->first();
                $ivaId = '';
                if ($impuesto) {
                    $matchedIva = $ivas->firstWhere('codigo_porcentaje', $impuesto->codigo_porcentaje);
                    $ivaId = $matchedIva ? $matchedIva->id : ($ivas->first()?->id ?? '');
                } else {
                    $ivaId = $ivas->first()?->id ?? '';
                }
                return [
                    'codigo'          => $d->codigo_principal ?? '',
                    'descripcion'     => $d->descripcion,
                    'cantidad'        => (float) $d->cantidad,
                    'precio'          => (float) $d->precio_unitario,
                    'descuento'       => (float) ($d->descuento ?? 0),
                    'descuento_tipo'  => '$',
                    'descuento_input' => (float) ($d->descuento ?? 0),
                    'impuesto_iva_id' => $ivaId,
                    'subtotal'        => (float) ($d->precio_total_sin_impuesto ?? 0),
                ];
            })->values();
        @endphp

        <script>var _initialProducts = @json($initialProducts);</script>

        @include('emisor.comprobantes.partials.productos-table')

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-1">Observaciones</label>
            <textarea name="observaciones" rows="2" class="w-full border-gray-300 rounded-md shadow-sm text-sm">{{ old('observaciones', $factura->observaciones) }}</textarea>
            @error('observaciones') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="flex justify-end">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Actualizar Factura</button>
        </div>
    </form>

    @push('scripts')
    <script>
        // Initialize Alpine productosSection with existing detalles.
        // We hook into 'alpine:initialized' (fires after Alpine has processed all x-data
        // elements on the page) to safely mutate the component's reactive data.
        document.addEventListener('alpine:initialized', () => {
            if (typeof _initialProducts === 'undefined' || !_initialProducts.length) return;
            const el = document.querySelector('[x-data="productosSection"]');
            if (!el) return;
            // Alpine v3 stores component data in _x_dataStack
            const component = el._x_dataStack && el._x_dataStack[0];
            if (component) {
                component.productos = _initialProducts;
                component.calcularTotales();
            }
        });
    </script>
    @endpush
</x-emisor-layout>
