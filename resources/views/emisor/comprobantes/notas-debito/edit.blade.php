<x-emisor-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Editar Nota de Debito</h2>
            <a href="{{ route('emisor.comprobantes.notas-debito.show', $notaDebito) }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 text-sm">Volver</a>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('emisor.comprobantes.notas-debito.update', $notaDebito) }}" id="nd-form">
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
                    <input type="date" name="fecha_emision" value="{{ old('fecha_emision', $notaDebito->fecha_emision?->format('Y-m-d')) }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm" required>
                    @error('fecha_emision') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <x-cliente-search :selected-id="old('cliente_id', $notaDebito->cliente_id)" :selected-display="$notaDebito->cliente ? $notaDebito->cliente->identificacion . ' - ' . $notaDebito->cliente->razon_social : ''" />
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Documento Modificado</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo Documento</label>
                    <select name="cod_doc_modificado" class="w-full border-gray-300 rounded-md shadow-sm text-sm" required>
                        <option value="01" {{ old('cod_doc_modificado', $notaDebito->cod_doc_modificado) == '01' ? 'selected' : '' }}>01 - Factura</option>
                    </select>
                    @error('cod_doc_modificado') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Numero Doc. Modificado</label>
                    <input type="text" name="num_doc_modificado" value="{{ old('num_doc_modificado', $notaDebito->num_doc_modificado) }}" placeholder="001-001-000000001" class="w-full border-gray-300 rounded-md shadow-sm text-sm" required>
                    @error('num_doc_modificado') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Emision Doc. Sustento</label>
                    <input type="date" name="fecha_emision_doc_sustento" value="{{ old('fecha_emision_doc_sustento', $notaDebito->fecha_emision_doc_sustento?->format('Y-m-d')) }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm" required>
                    @error('fecha_emision_doc_sustento') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Motivos</h3>
                <button type="button" id="agregar-motivo" class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700 text-sm">Agregar motivo</button>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200" id="motivos-table">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Razon</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase w-32">Valor</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase w-40">IVA</th>
                            <th class="px-3 py-2 w-10"></th>
                        </tr>
                    </thead>
                    <tbody id="motivos-body"></tbody>
                </table>
            </div>
            <div class="mt-4 flex justify-end">
                <div class="w-72 space-y-1">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Subtotal sin impuestos:</span>
                        <span id="total-subtotal">$0.00</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">IVA:</span>
                        <span id="total-iva">$0.00</span>
                    </div>
                    <div class="flex justify-between text-sm font-bold border-t pt-2">
                        <span>TOTAL:</span>
                        <span id="total-total">$0.00</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-1">Observaciones</label>
            <textarea name="observaciones" rows="2" class="w-full border-gray-300 rounded-md shadow-sm text-sm">{{ old('observaciones', $notaDebito->observaciones) }}</textarea>
            @error('observaciones') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="flex justify-end">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Actualizar Nota de Debito</button>
        </div>
    </form>

    @push('scripts')
    <script>
        const ivasData = @json($ivas);
        @php
            $motivosData = $notaDebito->motivos->map(function($m) {
                return [
                    'razon' => $m->razon,
                    'valor' => (float)$m->valor,
                    'impuesto_iva_id' => $m->impuesto_iva_id,
                ];
            })->values();
        @endphp
        const existingMotivos = @json($motivosData);

        let motivoIndex = 0;

        function buildIvaOptions(selectedId) {
            return ivasData.map(iva =>
                `<option value="${iva.id}" data-tarifa="${iva.tarifa}" ${iva.id == selectedId ? 'selected' : ''}>${iva.nombre} (${iva.tarifa}%)</option>`
            ).join('');
        }

        function addMotivo(data) {
            const tbody = document.getElementById('motivos-body');
            const tr = document.createElement('tr');
            tr.className = 'motivo-row';
            const razon = data?.razon ?? '';
            const valor = data?.valor ?? '0.00';
            const ivaId = data?.impuesto_iva_id ?? '';
            tr.innerHTML = `
                <td class="px-3 py-2"><input type="text" name="motivos[${motivoIndex}][razon]" class="w-full border-gray-300 rounded-md shadow-sm text-sm" value="${razon.replace(/"/g, '&quot;')}" required></td>
                <td class="px-3 py-2"><input type="number" name="motivos[${motivoIndex}][valor]" class="w-full border-gray-300 rounded-md shadow-sm text-sm valor" value="${valor}" min="0" step="any" required></td>
                <td class="px-3 py-2">
                    <select name="motivos[${motivoIndex}][impuesto_iva_id]" class="w-full border-gray-300 rounded-md shadow-sm text-sm iva-select" required>
                        ${buildIvaOptions(ivaId)}
                    </select>
                </td>
                <td class="px-3 py-2"><button type="button" class="text-red-500 hover:text-red-700 remove-motivo">&times;</button></td>
            `;
            tbody.appendChild(tr);
            motivoIndex++;

            tr.querySelector('.valor').addEventListener('input', calcularTotales);
            tr.querySelector('.iva-select').addEventListener('change', calcularTotales);
            tr.querySelector('.remove-motivo').addEventListener('click', function() {
                tr.remove();
                calcularTotales();
            });
        }

        function calcularTotales() {
            let subtotal = 0;
            let totalIva = 0;
            document.querySelectorAll('.motivo-row').forEach(row => {
                const valor = parseFloat(row.querySelector('.valor').value) || 0;
                const ivaSelect = row.querySelector('.iva-select');
                const tarifa = parseFloat(ivaSelect.options[ivaSelect.selectedIndex]?.dataset.tarifa) || 0;
                const iva = Math.round(valor * tarifa) / 100;
                subtotal += valor;
                totalIva += iva;
            });
            document.getElementById('total-subtotal').textContent = '$' + subtotal.toFixed(2);
            document.getElementById('total-iva').textContent = '$' + totalIva.toFixed(2);
            document.getElementById('total-total').textContent = '$' + (subtotal + totalIva).toFixed(2);
        }

        document.getElementById('agregar-motivo').addEventListener('click', function() {
            addMotivo(null);
            calcularTotales();
        });

        // Load existing motivos or start with one empty row
        if (existingMotivos.length > 0) {
            existingMotivos.forEach(function(m) {
                addMotivo(m);
            });
            calcularTotales();
        } else {
            addMotivo(null);
        }

    </script>
    @endpush
</x-emisor-layout>
