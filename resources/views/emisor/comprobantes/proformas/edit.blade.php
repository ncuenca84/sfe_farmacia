<x-emisor-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Editar Proforma</h2>
            <a href="{{ route('emisor.comprobantes.proformas.show', $proforma) }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 text-sm">Volver</a>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('emisor.comprobantes.proformas.update', $proforma) }}" id="proforma-form">
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
                    <input type="date" name="fecha_emision" value="{{ old('fecha_emision', $proforma->fecha_emision?->format('Y-m-d')) }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm" required>
                    @error('fecha_emision') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Vencimiento</label>
                    <input type="date" name="fecha_vencimiento" value="{{ old('fecha_vencimiento', $proforma->fecha_vencimiento?->format('Y-m-d')) }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                    @error('fecha_vencimiento') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <x-cliente-search :selected-id="old('cliente_id', $proforma->cliente_id)" :selected-display="$proforma->cliente ? $proforma->cliente->identificacion . ' - ' . $proforma->cliente->razon_social : ''" />
            </div>
        </div>

        @php
            $detallesData = $proforma->detalles->map(function($d) {
                return [
                    'codigo' => $d->codigo_principal ?? '',
                    'descripcion' => $d->descripcion,
                    'cantidad' => (float)$d->cantidad,
                    'precio' => (float)$d->precio_unitario,
                    'descuento' => (float)($d->descuento ?? 0),
                    'descuento_tipo' => '$',
                    'descuento_input' => (float)($d->descuento ?? 0),
                    'impuesto_iva_id' => \App\Models\ImpuestoIva::where('codigo_porcentaje', $d->impuestos->first()?->codigo_porcentaje)->first()?->id ?? '',
                    'subtotal' => (float)($d->precio_total_sin_impuesto ?? 0),
                ];
            })->values();
        @endphp
        <script>
            var _existingDetalles = @json($detallesData);
        </script>

        @include('emisor.comprobantes.partials.productos-table')

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-1">Observaciones</label>
            <textarea name="observaciones" rows="2" class="w-full border-gray-300 rounded-md shadow-sm text-sm">{{ old('observaciones', $proforma->observaciones) }}</textarea>
            @error('observaciones') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="flex justify-end">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Actualizar Proforma</button>
        </div>
    </form>

    @push('scripts')
    <script>
        // Initialize existing detalles in productos table
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                const el = document.querySelector('[x-data="productosSection"]');
                if (el && el._x_dataStack) {
                    const data = el._x_dataStack[0];
                    data.productos = _existingDetalles;
                    data.calcularTotales();
                }
            }, 50);
        });

    </script>
    @endpush
</x-emisor-layout>
