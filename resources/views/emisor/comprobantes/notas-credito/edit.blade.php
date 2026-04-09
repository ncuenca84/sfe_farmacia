<x-emisor-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Editar Nota de Credito</h2>
            <a href="{{ route('emisor.comprobantes.notas-credito.show', $notaCredito) }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 text-sm">Volver</a>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('emisor.comprobantes.notas-credito.update', $notaCredito) }}" id="nc-form">
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
                    <input type="date" name="fecha_emision" value="{{ old('fecha_emision', $notaCredito->fecha_emision?->format('Y-m-d')) }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm" required>
                    @error('fecha_emision') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <x-cliente-search :selected-id="old('cliente_id', $notaCredito->cliente_id)" :selected-display="$notaCredito->cliente ? $notaCredito->cliente->identificacion . ' - ' . $notaCredito->cliente->razon_social : ''" />

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Motivo</label>
                    <input type="text" name="motivo" value="{{ old('motivo', $notaCredito->motivo) }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm" required placeholder="Motivo de la nota de credito">
                    @error('motivo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Documento Modificado</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo Documento</label>
                    <select name="cod_doc_modificado" class="w-full border-gray-300 rounded-md shadow-sm text-sm" required>
                        <option value="01" {{ old('cod_doc_modificado', $notaCredito->cod_doc_modificado) == '01' ? 'selected' : '' }}>01 - Factura</option>
                    </select>
                    @error('cod_doc_modificado') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Numero Doc. Modificado</label>
                    <input type="text" name="num_doc_modificado" value="{{ old('num_doc_modificado', $notaCredito->num_doc_modificado) }}" placeholder="001-001-000000001" class="w-full border-gray-300 rounded-md shadow-sm text-sm" required>
                    @error('num_doc_modificado') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Emision Doc. Sustento</label>
                    <input type="date" name="fecha_emision_doc_sustento" value="{{ old('fecha_emision_doc_sustento', $notaCredito->fecha_emision_doc_sustento?->format('Y-m-d')) }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm" required>
                    @error('fecha_emision_doc_sustento') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        @php
            $detallesData = $notaCredito->detalles->map(function($d) {
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
            <textarea name="observaciones" rows="2" class="w-full border-gray-300 rounded-md shadow-sm text-sm">{{ old('observaciones', $notaCredito->observaciones) }}</textarea>
            @error('observaciones') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="flex justify-end">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Actualizar Nota de Credito</button>
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
