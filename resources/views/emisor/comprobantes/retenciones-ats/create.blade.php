<x-emisor-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Nueva Retencion ATS 2.0</h2>
            <a href="{{ route('emisor.comprobantes.retenciones-ats.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 text-sm">Volver</a>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('emisor.comprobantes.retenciones-ats.store') }}" id="retencion-ats-form">
        @csrf
        @include('emisor.comprobantes.partials.validation-errors')

        {{-- Datos Generales / Comprobante --}}
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Comprobante</h3>
                <div class="flex items-center gap-3 text-sm">
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Emision</label>
                    <input type="date" name="fecha_emision" value="{{ old('fecha_emision', date('Y-m-d')) }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm" required>
                    @error('fecha_emision') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Periodo Fiscal (MM/YYYY)</label>
                    <input type="text" name="periodo_fiscal" value="{{ old('periodo_fiscal', date('m/Y')) }}" placeholder="03/2026" class="w-full border-gray-300 rounded-md shadow-sm text-sm" required maxlength="7">
                    @error('periodo_fiscal') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Parte Relacionada</label>
                    <select name="parte_rel" class="w-full border-gray-300 rounded-md shadow-sm text-sm" required>
                        <option value="NO" {{ old('parte_rel', 'NO') == 'NO' ? 'selected' : '' }}>NO</option>
                        <option value="SI" {{ old('parte_rel') == 'SI' ? 'selected' : '' }}>SI</option>
                    </select>
                    @error('parte_rel') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- Sujeto Retenido --}}
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Sujeto Retenido</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <x-cliente-search label="Sujeto Retenido" />
            </div>
        </div>

        {{-- Documentos Sustento (dinámico) --}}
        <div id="doc-sustentos-container"></div>

        <div class="flex justify-between mb-6">
            <button type="button" id="agregar-doc-sustento" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 text-sm">+ Documento Sustento</button>
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Guardar Retencion ATS</button>
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
                    <table class="min-w-full divide-y divide-gray-200">
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
        let docSustentoIndex = 0;
        let activeCodigoInput = null;
        let activeRow = null;
        let modalPage = 1;
        let searchTimeout = null;
        const codigosRetencion = @json($codigosRetencion);

        const COD_SUSTENTOS = [
            {code: '01', desc: 'Credito Tributario para declaracion de IVA'},
            {code: '02', desc: 'Costo o Gasto para declaracion de IR'},
            {code: '03', desc: 'Activo Fijo - Credito Tributario para declaracion de IVA'},
            {code: '04', desc: 'Activo Fijo - Costo o Gasto para declaracion de IR'},
            {code: '05', desc: 'Liquidacion de Gastos de Viaje, hospedaje y alimentacion'},
            {code: '06', desc: 'Inventario - Credito Tributario para declaracion de IVA'},
            {code: '07', desc: 'Inventario - Costo o Gasto para declaracion de IR'},
            {code: '00', desc: 'Casos especiales cuyo sustento no aplica en las opciones anteriores'},
        ];

        const COD_DOC_SUSTENTOS = [
            {code: '01', desc: 'Factura'},
            {code: '02', desc: 'Nota o boleta de venta'},
            {code: '03', desc: 'Liquidacion de compra de bienes y prestacion de servicios'},
            {code: '04', desc: 'Nota de credito'},
            {code: '05', desc: 'Nota de debito'},
            {code: '07', desc: 'Comprobante de retencion'},
            {code: '08', desc: 'Boletos o entradas'},
            {code: '09', desc: 'Tiquetes o vales emitidos por maquinas registradoras'},
            {code: '12', desc: 'Documentos autorizados utilizados en ventas excepto N/C N/D'},
            {code: '15', desc: 'Comprobante de venta emitido en exterior'},
            {code: '41', desc: 'Comprobante de venta emitido por reembolso'},
            {code: '47', desc: 'Nota de credito por reembolso emitida por intermediario'},
        ];

        const PAGO_LOC_EXT = [
            {code: '01', desc: 'Pago a residente / Establecimiento permanente'},
            {code: '02', desc: 'Pago a no residente - paraiso fiscal'},
            {code: '03', desc: 'Pago a no residente - regimen fiscal preferente'},
        ];

        const FORMAS_PAGO = [
            {code: '01', desc: 'SIN UTILIZACION DEL SISTEMA FINANCIERO'},
            {code: '15', desc: 'COMPENSACION DE DEUDAS'},
            {code: '16', desc: 'TARJETA DE DEBITO'},
            {code: '17', desc: 'DINERO ELECTRONICO'},
            {code: '18', desc: 'TARJETA PREPAGO'},
            {code: '19', desc: 'TARJETA DE CREDITO'},
            {code: '20', desc: 'OTROS CON UTILIZACION DEL SISTEMA FINANCIERO'},
            {code: '21', desc: 'ENDOSO DE TITULOS'},
        ];

        const IVA_PORCENTAJES = [
            {code: '0', desc: 'IVA 0%', tarifa: 0},
            {code: '2', desc: 'IVA 12%', tarifa: 12},
            {code: '3', desc: 'IVA 14%', tarifa: 14},
            {code: '4', desc: 'IVA 15%', tarifa: 15},
            {code: '6', desc: 'No objeto de impuesto', tarifa: 0},
            {code: '7', desc: 'Exento de IVA', tarifa: 0},
        ];

        function buildOptions(list, selectedCode) {
            return list.map(item => `<option value="${item.code}" ${item.code === selectedCode ? 'selected' : ''}>${item.code} - ${item.desc}</option>`).join('');
        }

        function addDocSustento() {
            const container = document.getElementById('doc-sustentos-container');
            const dsIdx = docSustentoIndex;
            const div = document.createElement('div');
            div.className = 'bg-white rounded-lg shadow p-6 mb-6 doc-sustento-block';
            div.dataset.dsIdx = dsIdx;
            div.innerHTML = `
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-green-700">Documento Sustento #${dsIdx + 1}</h3>
                    <button type="button" class="text-red-500 hover:text-red-700 text-sm remove-doc-sustento">&times; Eliminar</button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Codigo Sustento *</label>
                        <select name="doc_sustentos[${dsIdx}][cod_sustento]" class="w-full border-gray-300 rounded-md shadow-sm text-sm" required>
                            ${buildOptions(COD_SUSTENTOS, '01')}
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Codigo Documento Sustento *</label>
                        <select name="doc_sustentos[${dsIdx}][cod_doc_sustento]" class="w-full border-gray-300 rounded-md shadow-sm text-sm" required>
                            ${buildOptions(COD_DOC_SUSTENTOS, '01')}
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Num Doc Sustento</label>
                        <input type="text" name="doc_sustentos[${dsIdx}][num_doc_sustento]" value="001001000000001" placeholder="001001000000001" class="w-full border-gray-300 rounded-md shadow-sm text-sm" required>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Emision Doc Sustento</label>
                        <input type="date" name="doc_sustentos[${dsIdx}][fecha_emision_doc_sustento]" value="${new Date().toISOString().split('T')[0]}" class="w-full border-gray-300 rounded-md shadow-sm text-sm" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Registro Contable</label>
                        <input type="date" name="doc_sustentos[${dsIdx}][fecha_registro_contable]" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Num Aut Doc Sustento</label>
                        <input type="text" name="doc_sustentos[${dsIdx}][num_aut_doc_sustento]" class="w-full border-gray-300 rounded-md shadow-sm text-sm" maxlength="49">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pago Loc Ext *</label>
                        <select name="doc_sustentos[${dsIdx}][pago_loc_ext]" class="w-full border-gray-300 rounded-md shadow-sm text-sm" required>
                            ${buildOptions(PAGO_LOC_EXT, '01')}
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Total Sin Impuestos *</label>
                        <input type="number" name="doc_sustentos[${dsIdx}][total_sin_impuestos]" value="0.00" class="w-full border-gray-300 rounded-md shadow-sm text-sm" min="0" step="any" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Importe Total *</label>
                        <input type="number" name="doc_sustentos[${dsIdx}][importe_total]" value="0.00" class="w-full border-gray-300 rounded-md shadow-sm text-sm" min="0" step="any" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Forma Pago *</label>
                        <select name="doc_sustentos[${dsIdx}][forma_pago]" class="w-full border-gray-300 rounded-md shadow-sm text-sm" required>
                            ${buildOptions(FORMAS_PAGO, '20')}
                        </select>
                    </div>
                </div>

                {{-- Impuestos Documento Sustento --}}
                <div class="border rounded-lg p-4 mb-4 bg-gray-50">
                    <div class="flex justify-between items-center mb-3">
                        <h4 class="text-sm font-semibold text-gray-700">Impuestos Documento Sustento</h4>
                        <button type="button" class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-600 text-xs agregar-impuesto-doc" data-ds-idx="${dsIdx}">+ Impuesto Documento Sustento</button>
                    </div>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Cod. Porcentaje</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase w-28">Base Imp.</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase w-24">Tarifa</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase w-28">Valor Impuesto</th>
                                <th class="px-3 py-2 w-10"></th>
                            </tr>
                        </thead>
                        <tbody class="impuestos-doc-body" data-ds-idx="${dsIdx}"></tbody>
                    </table>
                </div>

                {{-- Retenciones --}}
                <div class="border rounded-lg p-4 bg-gray-50">
                    <div class="flex justify-between items-center mb-3">
                        <h4 class="text-sm font-semibold text-gray-700">Retenciones</h4>
                        <button type="button" class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-600 text-xs agregar-retencion" data-ds-idx="${dsIdx}">+ Retencion Documento Sustento</button>
                    </div>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Cod. Reten</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase w-24">%</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase w-28">Base Imp.</th>
                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase w-28">Total</th>
                                <th class="px-3 py-2 w-10"></th>
                            </tr>
                        </thead>
                        <tbody class="retenciones-body" data-ds-idx="${dsIdx}"></tbody>
                    </table>
                </div>
            `;
            container.appendChild(div);

            // Event listeners
            div.querySelector('.remove-doc-sustento').addEventListener('click', function() {
                div.remove();
            });
            div.querySelector('.agregar-impuesto-doc').addEventListener('click', function() {
                addImpuestoDoc(dsIdx, div);
            });
            div.querySelector('.agregar-retencion').addEventListener('click', function() {
                addRetencion(dsIdx, div);
            });

            // Add default impuesto doc and retencion
            addImpuestoDoc(dsIdx, div);
            addRetencion(dsIdx, div);

            docSustentoIndex++;
        }

        let impDocCounters = {};
        function addImpuestoDoc(dsIdx, dsDiv) {
            if (!impDocCounters[dsIdx]) impDocCounters[dsIdx] = 0;
            const idx = impDocCounters[dsIdx]++;
            const tbody = dsDiv.querySelector(`.impuestos-doc-body[data-ds-idx="${dsIdx}"]`);
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="px-3 py-2">
                    <select name="doc_sustentos[${dsIdx}][impuestos_doc][${idx}][codigo_impuesto]" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                        <option value="2">IVA</option>
                        <option value="3">ICE</option>
                        <option value="5">IRBPNR</option>
                    </select>
                </td>
                <td class="px-3 py-2">
                    <select name="doc_sustentos[${dsIdx}][impuestos_doc][${idx}][codigo_porcentaje]" class="w-full border-gray-300 rounded-md shadow-sm text-sm iva-porcentaje-select">
                        ${IVA_PORCENTAJES.map(p => `<option value="${p.code}" data-tarifa="${p.tarifa}">${p.desc}</option>`).join('')}
                    </select>
                </td>
                <td class="px-3 py-2"><input type="number" name="doc_sustentos[${dsIdx}][impuestos_doc][${idx}][base_imponible]" class="w-full border-gray-300 rounded-md shadow-sm text-sm imp-base" value="0.00" min="0" step="any" required></td>
                <td class="px-3 py-2"><input type="number" name="doc_sustentos[${dsIdx}][impuestos_doc][${idx}][tarifa]" class="w-full border-gray-300 rounded-md shadow-sm text-sm imp-tarifa" value="0" min="0" step="any" required></td>
                <td class="px-3 py-2"><input type="number" name="doc_sustentos[${dsIdx}][impuestos_doc][${idx}][valor_impuesto]" class="w-full border-gray-300 rounded-md shadow-sm text-sm imp-valor" value="0.00" min="0" step="any" required></td>
                <td class="px-3 py-2"><button type="button" class="text-red-500 hover:text-red-700" onclick="this.closest('tr').remove();">&times;</button></td>
            `;
            tbody.appendChild(tr);

            // Auto-fill tarifa on porcentaje change
            const pctSelect = tr.querySelector('.iva-porcentaje-select');
            const tarifaInput = tr.querySelector('.imp-tarifa');
            const baseInput = tr.querySelector('.imp-base');
            const valorInput = tr.querySelector('.imp-valor');

            pctSelect.addEventListener('change', function() {
                const tarifa = parseFloat(this.selectedOptions[0].dataset.tarifa) || 0;
                tarifaInput.value = tarifa;
                calcImpuesto();
            });
            baseInput.addEventListener('input', calcImpuesto);
            tarifaInput.addEventListener('input', calcImpuesto);

            function calcImpuesto() {
                const base = parseFloat(baseInput.value) || 0;
                const tarifa = parseFloat(tarifaInput.value) || 0;
                valorInput.value = (base * tarifa / 100).toFixed(2);
            }
        }

        let retCounters = {};
        function addRetencion(dsIdx, dsDiv) {
            if (!retCounters[dsIdx]) retCounters[dsIdx] = 0;
            const idx = retCounters[dsIdx]++;
            const tbody = dsDiv.querySelector(`.retenciones-body[data-ds-idx="${dsIdx}"]`);
            const tr = document.createElement('tr');
            tr.className = 'retencion-row';
            tr.innerHTML = `
                <td class="px-3 py-2">
                    <select name="doc_sustentos[${dsIdx}][retenciones][${idx}][codigo]" class="w-full border-gray-300 rounded-md shadow-sm text-sm impuesto-tipo">
                        <option value="1">Renta</option>
                        <option value="2">IVA</option>
                        <option value="6">ISD</option>
                    </select>
                </td>
                <td class="px-3 py-2">
                    <div class="flex gap-1">
                        <input type="text" name="doc_sustentos[${dsIdx}][retenciones][${idx}][codigo_retencion]" class="w-full border-gray-300 rounded-md shadow-sm text-sm codigo-retencion-input" required>
                        <button type="button" class="buscar-codigo bg-indigo-100 text-indigo-700 px-2 rounded hover:bg-indigo-200 text-sm flex-shrink-0" title="Buscar codigo">&#128269;</button>
                    </div>
                </td>
                <td class="px-3 py-2"><input type="number" name="doc_sustentos[${dsIdx}][retenciones][${idx}][porcentaje_retener]" class="w-full border-gray-300 rounded-md shadow-sm text-sm porcentaje" value="0" min="0" step="any" required></td>
                <td class="px-3 py-2"><input type="number" name="doc_sustentos[${dsIdx}][retenciones][${idx}][base_imponible]" class="w-full border-gray-300 rounded-md shadow-sm text-sm base" value="0.00" min="0" step="any" required></td>
                <td class="px-3 py-2 text-right">
                    <span class="valor-text">$0.00</span>
                    <input type="hidden" name="doc_sustentos[${dsIdx}][retenciones][${idx}][valor_retenido]" class="valor-retenido-hidden" value="0.00">
                </td>
                <td class="px-3 py-2"><button type="button" class="text-red-500 hover:text-red-700 remove-ret">&times;</button></td>
            `;
            tbody.appendChild(tr);

            tr.querySelectorAll('.base, .porcentaje').forEach(el => {
                el.addEventListener('input', function() {
                    const base = parseFloat(tr.querySelector('.base').value) || 0;
                    const pct = parseFloat(tr.querySelector('.porcentaje').value) || 0;
                    const val = (base * pct / 100).toFixed(2);
                    tr.querySelector('.valor-text').textContent = '$' + val;
                    tr.querySelector('.valor-retenido-hidden').value = val;
                });
            });
            tr.querySelector('.remove-ret').addEventListener('click', function() { tr.remove(); });
            tr.querySelector('.buscar-codigo').addEventListener('click', function() {
                activeCodigoInput = tr.querySelector('.codigo-retencion-input');
                activeRow = tr;
                openModal();
            });
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
                        tr.addEventListener('click', function() { selectCodigo(codigo); });
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
            if (activeCodigoInput) activeCodigoInput.value = codigo.codigo;
            if (activeRow) {
                const porcentajeInput = activeRow.querySelector('.porcentaje');
                if (porcentajeInput) porcentajeInput.value = codigo.porcentaje;
                const tipoSelect = activeRow.querySelector('.impuesto-tipo');
                const tipoReverseMap = {'RENTA': '1', 'IVA': '2', 'ISD': '6'};
                if (tipoSelect && tipoReverseMap[codigo.tipo]) tipoSelect.value = tipoReverseMap[codigo.tipo];
                // Trigger recalc
                const baseInput = activeRow.querySelector('.base');
                if (baseInput) baseInput.dispatchEvent(new Event('input'));
            }
            closeModal();
        }

        document.getElementById('agregar-doc-sustento').addEventListener('click', addDocSustento);
        document.getElementById('modal-close').addEventListener('click', closeModal);
        document.getElementById('modal-overlay').addEventListener('click', closeModal);
        document.getElementById('modal-buscar').addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => { modalPage = 1; loadCodigos(); }, 300);
        });
        document.getElementById('modal-tipo-filter').addEventListener('change', function() { modalPage = 1; loadCodigos(); });
        document.getElementById('modal-prev').addEventListener('click', function() { if (modalPage > 1) { modalPage--; loadCodigos(); } });
        document.getElementById('modal-next').addEventListener('click', function() { modalPage++; loadCodigos(); });
        document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeModal(); });

        // Init: add first doc sustento
        addDocSustento();

    </script>
    @endpush
</x-emisor-layout>
