<x-emisor-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Editar Retencion {{ $retencion->establecimiento->codigo ?? '000' }}-{{ $retencion->ptoEmision->codigo ?? '000' }}-{{ str_pad($retencion->secuencial ?? 0, 9, '0', STR_PAD_LEFT) }}</h2>
            <a href="{{ route('emisor.comprobantes.retenciones.show', $retencion) }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 text-sm">Volver</a>
        </div>
    </x-slot>

    {{-- Guia de codigos de retencion --}}
    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
        <p class="text-sm text-green-800">
            <strong>Guia:</strong> Use el boton <span class="inline-flex items-center justify-center w-6 h-6 bg-indigo-100 text-indigo-700 rounded text-xs font-bold">&#128269;</span> junto al campo "Cod. Retencion" para buscar codigos.
            El SRI actualiza constantemente estos codigos.
            <a href="{{ route('emisor.codigos-retencion.index') }}" class="underline font-medium text-green-700 hover:text-green-900">Ver / Editar todos los codigos de retencion</a>
        </p>
    </div>

    <form method="POST" action="{{ route('emisor.comprobantes.retenciones.update', $retencion) }}" id="retencion-form">
        @csrf
        @include('emisor.comprobantes.partials.validation-errors')
        @method('PUT')

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Datos Generales</h3>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Emision</label>
                    <input type="date" name="fecha_emision" value="{{ old('fecha_emision', $retencion->fecha_emision->format('Y-m-d')) }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm" required>
                    @error('fecha_emision') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <x-cliente-search label="Sujeto Retenido" :selectedId="$retencion->cliente_id" :selectedDisplay="($retencion->cliente->identificacion ?? '') . ' - ' . ($retencion->cliente->razon_social ?? '')" />
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Documento Sustento</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo Doc. Sustento</label>
                    <select name="tipo_doc_sustento" class="w-full border-gray-300 rounded-md shadow-sm text-sm" required>
                        <option value="01" {{ old('tipo_doc_sustento', $retencion->cod_doc_sustento) == '01' ? 'selected' : '' }}>01 - Factura</option>
                        <option value="02" {{ old('tipo_doc_sustento', $retencion->cod_doc_sustento) == '02' ? 'selected' : '' }}>02 - Nota de Credito</option>
                        <option value="03" {{ old('tipo_doc_sustento', $retencion->cod_doc_sustento) == '03' ? 'selected' : '' }}>03 - Liquidacion de Compra</option>
                        <option value="04" {{ old('tipo_doc_sustento', $retencion->cod_doc_sustento) == '04' ? 'selected' : '' }}>04 - Nota de Debito</option>
                    </select>
                    @error('tipo_doc_sustento') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Numero Doc. Sustento</label>
                    <input type="text" name="num_doc_sustento" value="{{ old('num_doc_sustento', $retencion->num_doc_sustento) }}" placeholder="001-001-000000001" class="w-full border-gray-300 rounded-md shadow-sm text-sm" required>
                    @error('num_doc_sustento') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Emision Doc. Sustento</label>
                    <input type="date" name="fecha_emision_doc_sustento" value="{{ old('fecha_emision_doc_sustento', $retencion->fecha_emision_doc_sustento?->format('Y-m-d')) }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm" required>
                    @error('fecha_emision_doc_sustento') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Impuestos</h3>
                <button type="button" id="agregar-impuesto" class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700 text-sm">Agregar impuesto</button>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200" id="impuestos-table">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Impuesto</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Cod. Retencion</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase w-28">Base Imponible</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase w-24">% Retener</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase w-28">Valor Retenido</th>
                            <th class="px-3 py-2 w-10"></th>
                        </tr>
                    </thead>
                    <tbody id="impuestos-body"></tbody>
                </table>
            </div>
            <div class="mt-4 flex justify-end">
                <div class="w-64">
                    <div class="flex justify-between text-sm font-bold border-t pt-2">
                        <span>Total Retenido:</span>
                        <span id="total-retenido">$0.00</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('emisor.comprobantes.retenciones.show', $retencion) }}" class="bg-gray-300 text-gray-700 px-6 py-2 rounded hover:bg-gray-400">Cancelar</a>
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Guardar Cambios</button>
        </div>
    </form>

    {{-- Modal de busqueda de codigos de retencion --}}
    <div id="modal-codigos" class="fixed inset-0 z-50 hidden">
        <div class="fixed inset-0 bg-black bg-opacity-50" id="modal-overlay"></div>
        <div class="fixed inset-0 flex items-center justify-center p-4">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-[80vh] flex flex-col relative">
                <div class="p-4 border-b flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900">Seleccione el Codigo de Retencion</h3>
                    <button type="button" id="modal-close" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
                </div>
                <div class="p-4 border-b">
                    <div class="flex gap-3">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Tipo</label>
                            <select id="modal-tipo-filter" class="border-gray-300 rounded-md shadow-sm text-sm">
                                <option value="">Todos</option>
                                <option value="RENTA">Renta</option>
                                <option value="IVA">IVA</option>
                                <option value="ISD">ISD</option>
                            </select>
                        </div>
                        <div class="flex-1">
                            <label class="block text-xs text-gray-500 mb-1">Buscar</label>
                            <input type="text" id="modal-buscar" placeholder="Buscar por codigo o descripcion..." class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                        </div>
                    </div>
                </div>
                <div class="overflow-y-auto flex-1 p-4">
                    <table class="min-w-full divide-y divide-gray-200" id="modal-codigos-table">
                        <thead class="bg-gray-50 sticky top-0">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Codigo</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">% Retencion</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Descripcion</th>
                            </tr>
                        </thead>
                        <tbody id="modal-codigos-body" class="divide-y divide-gray-200"></tbody>
                    </table>
                    <div id="modal-loading" class="text-center py-8 text-gray-500 text-sm hidden">Cargando...</div>
                    <div id="modal-empty" class="text-center py-8 text-gray-500 text-sm hidden">No se encontraron codigos.</div>
                </div>
                <div class="p-3 border-t flex justify-between items-center text-sm text-gray-500">
                    <span id="modal-info"></span>
                    <div class="flex gap-2">
                        <button type="button" id="modal-prev" class="px-3 py-1 border rounded text-sm hover:bg-gray-50 disabled:opacity-50" disabled>Anterior</button>
                        <button type="button" id="modal-next" class="px-3 py-1 border rounded text-sm hover:bg-gray-50 disabled:opacity-50" disabled>Siguiente</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        let impuestoIndex = 0;
        let activeCodigoInput = null;
        let activeRow = null;
        let modalPage = 1;

        function addImpuesto(data) {
            const tbody = document.getElementById('impuestos-body');
            const tr = document.createElement('tr');
            tr.className = 'impuesto-row';

            const codigo = data ? data.codigo_impuesto : '1';
            const codigoRetencion = data ? data.codigo_retencion : '';
            const baseImponible = data ? parseFloat(data.base_imponible).toFixed(2) : '0.00';
            const porcentaje = data ? parseFloat(data.porcentaje_retener).toFixed(2) : '0';
            const valorRetenido = data ? parseFloat(data.valor_retenido).toFixed(2) : '0.00';

            tr.innerHTML = `
                <td class="px-3 py-2">
                    <select name="impuestos[${impuestoIndex}][codigo]" class="w-full border-gray-300 rounded-md shadow-sm text-sm impuesto-tipo">
                        <option value="1" ${codigo == '1' ? 'selected' : ''}>1 - Renta</option>
                        <option value="2" ${codigo == '2' ? 'selected' : ''}>2 - IVA</option>
                        <option value="6" ${codigo == '6' ? 'selected' : ''}>6 - ISD</option>
                    </select>
                </td>
                <td class="px-3 py-2">
                    <div class="flex gap-1">
                        <input type="text" name="impuestos[${impuestoIndex}][codigo_retencion]" value="${codigoRetencion}" class="w-full border-gray-300 rounded-md shadow-sm text-sm codigo-retencion-input" required>
                        <button type="button" class="buscar-codigo bg-indigo-100 text-indigo-700 px-2 rounded hover:bg-indigo-200 text-sm flex-shrink-0" title="Buscar codigo">&#128269;</button>
                    </div>
                </td>
                <td class="px-3 py-2"><input type="number" name="impuestos[${impuestoIndex}][base_imponible]" class="w-full border-gray-300 rounded-md shadow-sm text-sm base" value="${baseImponible}" min="0" step="any" required></td>
                <td class="px-3 py-2"><input type="number" name="impuestos[${impuestoIndex}][porcentaje_retener]" class="w-full border-gray-300 rounded-md shadow-sm text-sm porcentaje" value="${porcentaje}" min="0" step="any" required></td>
                <td class="px-3 py-2 text-right valor-cell">$${valorRetenido}</td>
                <td class="px-3 py-2"><button type="button" class="text-red-500 hover:text-red-700 remove-impuesto">&times;</button></td>
            `;
            tbody.appendChild(tr);

            const hiddenValor = document.createElement('input');
            hiddenValor.type = 'hidden';
            hiddenValor.name = `impuestos[${impuestoIndex}][valor_retenido]`;
            hiddenValor.className = 'valor-retenido-hidden';
            hiddenValor.value = valorRetenido;
            tr.querySelector('.valor-cell').appendChild(hiddenValor);

            impuestoIndex++;

            tr.querySelectorAll('.base, .porcentaje').forEach(el => {
                el.addEventListener('input', calcularRetenciones);
            });
            tr.querySelector('.remove-impuesto').addEventListener('click', function() {
                tr.remove();
                calcularRetenciones();
            });
            tr.querySelector('.buscar-codigo').addEventListener('click', function() {
                activeCodigoInput = tr.querySelector('.codigo-retencion-input');
                activeRow = tr;
                openModal();
            });
        }

        function calcularRetenciones() {
            let total = 0;
            document.querySelectorAll('.impuesto-row').forEach(row => {
                const base = parseFloat(row.querySelector('.base').value) || 0;
                const porcentaje = parseFloat(row.querySelector('.porcentaje').value) || 0;
                const valor = base * (porcentaje / 100);
                total += valor;
                row.querySelector('.valor-cell').childNodes[0].textContent = '$' + valor.toFixed(2);
                row.querySelector('.valor-retenido-hidden').value = valor.toFixed(2);
            });
            document.getElementById('total-retenido').textContent = '$' + total.toFixed(2);
        }

        // Modal logic
        function openModal() {
            const modal = document.getElementById('modal-codigos');
            modal.classList.remove('hidden');
            if (activeRow) {
                const tipoSelect = activeRow.querySelector('.impuesto-tipo');
                const tipoMap = {'1': 'RENTA', '2': 'IVA', '6': 'ISD'};
                document.getElementById('modal-tipo-filter').value = tipoMap[tipoSelect.value] || '';
            }
            document.getElementById('modal-buscar').value = '';
            modalPage = 1;
            loadCodigos();
            document.getElementById('modal-buscar').focus();
        }

        function closeModal() {
            document.getElementById('modal-codigos').classList.add('hidden');
            activeCodigoInput = null;
            activeRow = null;
        }

        let searchTimeout = null;
        function loadCodigos() {
            const tipo = document.getElementById('modal-tipo-filter').value;
            const q = document.getElementById('modal-buscar').value;
            const body = document.getElementById('modal-codigos-body');
            const loading = document.getElementById('modal-loading');
            const empty = document.getElementById('modal-empty');

            body.innerHTML = '';
            loading.classList.remove('hidden');
            empty.classList.add('hidden');

            const params = new URLSearchParams();
            if (tipo) params.append('tipo', tipo);
            if (q) params.append('q', q);
            params.append('page', modalPage);

            fetch(`{{ route('emisor.codigos-retencion.buscar') }}?${params.toString()}`)
                .then(r => r.json())
                .then(data => {
                    loading.classList.add('hidden');
                    if (data.data.length === 0) {
                        empty.classList.remove('hidden');
                        document.getElementById('modal-info').textContent = '';
                        document.getElementById('modal-prev').disabled = true;
                        document.getElementById('modal-next').disabled = true;
                        return;
                    }

                    document.getElementById('modal-info').textContent =
                        `Mostrando del ${data.from} al ${data.to} de ${data.total} registros`;
                    document.getElementById('modal-prev').disabled = !data.prev_page_url;
                    document.getElementById('modal-next').disabled = !data.next_page_url;

                    data.data.forEach(codigo => {
                        const tr = document.createElement('tr');
                        tr.className = 'hover:bg-indigo-50 cursor-pointer';
                        tr.innerHTML = `
                            <td class="px-3 py-2 text-sm"><span class="px-2 py-0.5 rounded-full text-xs font-semibold ${codigo.tipo === 'RENTA' ? 'bg-blue-100 text-blue-800' : (codigo.tipo === 'IVA' ? 'bg-green-100 text-green-800' : 'bg-purple-100 text-purple-800')}">${codigo.tipo}</span></td>
                            <td class="px-3 py-2 text-sm font-medium">${codigo.codigo}</td>
                            <td class="px-3 py-2 text-sm">${codigo.porcentaje}%</td>
                            <td class="px-3 py-2 text-sm text-gray-600">${codigo.descripcion}</td>
                        `;
                        tr.addEventListener('click', function() {
                            selectCodigo(codigo);
                        });
                        body.appendChild(tr);
                    });
                })
                .catch(() => {
                    loading.classList.add('hidden');
                    empty.textContent = 'Error al cargar codigos.';
                    empty.classList.remove('hidden');
                });
        }

        function selectCodigo(codigo) {
            if (activeCodigoInput) {
                activeCodigoInput.value = codigo.codigo;
            }
            if (activeRow) {
                const porcentajeInput = activeRow.querySelector('.porcentaje');
                if (porcentajeInput) {
                    porcentajeInput.value = codigo.porcentaje;
                }
                const tipoSelect = activeRow.querySelector('.impuesto-tipo');
                const tipoReverseMap = {'RENTA': '1', 'IVA': '2', 'ISD': '6'};
                if (tipoSelect && tipoReverseMap[codigo.tipo]) {
                    tipoSelect.value = tipoReverseMap[codigo.tipo];
                }
                calcularRetenciones();
            }
            closeModal();
        }

        // Event listeners
        document.getElementById('agregar-impuesto').addEventListener('click', function() { addImpuesto(null); });
        document.getElementById('modal-close').addEventListener('click', closeModal);
        document.getElementById('modal-overlay').addEventListener('click', closeModal);

        document.getElementById('modal-buscar').addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                modalPage = 1;
                loadCodigos();
            }, 300);
        });

        document.getElementById('modal-tipo-filter').addEventListener('change', function() {
            modalPage = 1;
            loadCodigos();
        });

        document.getElementById('modal-prev').addEventListener('click', function() {
            if (modalPage > 1) {
                modalPage--;
                loadCodigos();
            }
        });

        document.getElementById('modal-next').addEventListener('click', function() {
            modalPage++;
            loadCodigos();
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeModal();
        });

        // Load existing impuestos
        const existingImpuestos = @json($retencion->impuestosRetencion);
        if (existingImpuestos.length > 0) {
            existingImpuestos.forEach(imp => addImpuesto(imp));
        } else {
            addImpuesto(null);
        }

        calcularRetenciones();
    </script>
    @endpush
</x-emisor-layout>
