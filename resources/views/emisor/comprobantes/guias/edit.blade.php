<x-emisor-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Editar Guia de Remision</h2>
            <a href="{{ route('emisor.comprobantes.guias.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 text-sm">Volver</a>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('emisor.comprobantes.guias.update', $guia) }}" id="guia-form">
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
                    <input type="date" name="fecha_emision" value="{{ old('fecha_emision', $guia->fecha_emision?->format('Y-m-d')) }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm" required>
                    @error('fecha_emision') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- Direcciones: Partida y Llegada en el mismo bloque --}}
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Direcciones</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Direccion de Partida *</label>
                    <input type="text" name="dir_partida" value="{{ old('dir_partida', $guia->dir_partida) }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm" required placeholder="Direccion de origen">
                    @error('dir_partida') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Direccion de Llegada</label>
                    <input type="text" name="dir_llegada" value="{{ old('dir_llegada', $guia->dir_llegada) }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm" placeholder="Direccion de destino">
                    @error('dir_llegada') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- Transportista section with search --}}
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Transportista</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <x-transportista-search />
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Inicio Transporte *</label>
                    <input type="date" name="fecha_inicio_transporte" value="{{ old('fecha_inicio_transporte', $guia->fecha_ini_transporte?->format('Y-m-d')) }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm" required>
                    @error('fecha_inicio_transporte') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Fin Transporte *</label>
                    <input type="date" name="fecha_fin_transporte" value="{{ old('fecha_fin_transporte', $guia->fecha_fin_transporte?->format('Y-m-d')) }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm" required>
                    @error('fecha_fin_transporte') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- Destinatarios with client search --}}
        <div class="bg-white rounded-lg shadow p-6 mb-6" x-data="destinatariosManager()" @guia-product-selected.window="let d = destinatarios[$event.detail.idx]; if(d) d.productos.push({codigo: $event.detail.producto.codigo_principal || '', descripcion: $event.detail.producto.nombre || '', cantidad: 1})">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Destinatarios</h3>
                <button type="button" @click="addDestinatario()" class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700 text-sm">Agregar destinatario</button>
            </div>
            <div id="destinatarios-container">
                <template x-for="(dest, idx) in destinatarios" :key="idx">
                    <div class="border rounded p-4 mb-4">
                        <div class="flex justify-between items-center mb-3">
                            <h4 class="text-sm font-medium text-gray-700" x-text="'Destinatario #' + (idx + 1)"></h4>
                            <button type="button" @click="destinatarios.splice(idx, 1)" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 text-sm">Eliminar</button>
                        </div>

                        {{-- Client search --}}
                        <div class="mb-3">
                            {{-- Selected client display --}}
                            <div x-show="dest.selectedDisplay" class="mb-2 flex items-center justify-between bg-blue-50 border border-blue-200 rounded px-3 py-2 text-sm">
                                <span class="text-blue-800" x-text="dest.selectedDisplay"></span>
                                <button type="button" @click="clearClienteSelection(dest)" class="text-red-500 hover:text-red-700 text-xs font-bold">&times;</button>
                            </div>

                            {{-- Action buttons --}}
                            <div class="flex items-center gap-2 mb-2" x-show="!dest.showCreateForm">
                                <button type="button" @click="openClienteModal(idx)" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm">Buscar Cliente</button>
                                <button type="button" @click="dest.showCreateForm = true" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 text-sm">+ Nuevo Cliente</button>
                            </div>
                            <div x-show="dest.showCreateForm" class="mb-2">
                                <button type="button" @click="dest.showCreateForm = false" class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500 text-sm">Cancelar</button>
                            </div>

                            {{-- Inline create form --}}
                            <div x-show="dest.showCreateForm" x-cloak class="border border-gray-200 rounded-lg p-4 bg-gray-50 space-y-3">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Tipo Identificacion</label>
                                        <select x-model="dest.newCliente.tipo_identificacion" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                                            <option value="04">RUC</option>
                                            <option value="05">Cedula</option>
                                            <option value="06">Pasaporte</option>
                                            <option value="07">Consumidor Final</option>
                                            <option value="08">Identificacion del exterior</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Identificacion</label>
                                        <input type="text" x-model="dest.newCliente.identificacion" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Razon Social / Nombre</label>
                                        <input type="text" x-model="dest.newCliente.razon_social" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Direccion</label>
                                        <input type="text" x-model="dest.newCliente.direccion" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Email</label>
                                        <input type="email" x-model="dest.newCliente.email" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Telefono</label>
                                        <input type="text" x-model="dest.newCliente.telefono" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                                    </div>
                                </div>
                                <div class="flex items-center justify-between">
                                    <p x-show="dest.createError" x-text="dest.createError" class="text-red-500 text-xs"></p>
                                    <button type="button" @click="createCliente(dest)" :disabled="dest.creating"
                                            class="bg-green-600 text-white px-4 py-1.5 rounded hover:bg-green-700 text-sm disabled:opacity-50 ml-auto">
                                        <span x-show="!dest.creating">Crear y seleccionar</span>
                                        <span x-show="dest.creating">Creando...</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Identificacion *</label>
                                <input type="text" :name="'destinatarios['+idx+'][identificacion]'" x-model="dest.identificacion" class="w-full border-gray-300 rounded-md shadow-sm text-sm bg-gray-50" readonly required>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Razon Social *</label>
                                <input type="text" :name="'destinatarios['+idx+'][razon_social]'" x-model="dest.razon_social" class="w-full border-gray-300 rounded-md shadow-sm text-sm bg-gray-50" readonly required>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Direccion *</label>
                                <input type="text" :name="'destinatarios['+idx+'][direccion]'" x-model="dest.direccion" class="w-full border-gray-300 rounded-md shadow-sm text-sm" required>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Motivo Traslado *</label>
                                <input type="text" :name="'destinatarios['+idx+'][motivo_traslado]'" x-model="dest.motivo_traslado" class="w-full border-gray-300 rounded-md shadow-sm text-sm" required>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Doc. Aduanero Unico</label>
                                <input type="text" :name="'destinatarios['+idx+'][doc_aduanero_unico]'" x-model="dest.doc_aduanero_unico" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Cod. Estab. Destino</label>
                                <input type="text" :name="'destinatarios['+idx+'][cod_establecimiento_destino]'" x-model="dest.cod_establecimiento_destino" class="w-full border-gray-300 rounded-md shadow-sm text-sm" maxlength="3">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Ruta</label>
                                <input type="text" :name="'destinatarios['+idx+'][ruta]'" x-model="dest.ruta" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                            </div>
                        </div>

                        {{-- Products section per destinatario --}}
                        <div class="mt-4 border-t pt-4">
                            <div class="flex justify-between items-center mb-2">
                                <h5 class="text-xs font-semibold text-blue-700 uppercase">Productos</h5>
                                <div class="flex gap-2">
                                    <button type="button" @click="openProductModal(idx)" class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 text-xs">Buscar Producto</button>
                                    <button type="button" @click="dest.showCreateProd = !dest.showCreateProd" class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700 text-xs">
                                        <span x-show="!dest.showCreateProd">+ Nuevo Producto</span>
                                        <span x-show="dest.showCreateProd">Cancelar</span>
                                    </button>
                                </div>
                            </div>

                            {{-- Inline create product form --}}
                            <div x-show="dest.showCreateProd" x-cloak class="border border-gray-200 rounded-lg p-3 bg-gray-50 mb-3">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Codigo</label>
                                        <input type="text" x-model="dest.newProd.codigo_principal" class="w-full border-gray-300 rounded-md shadow-sm text-xs">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Nombre / Descripcion</label>
                                        <input type="text" x-model="dest.newProd.nombre" class="w-full border-gray-300 rounded-md shadow-sm text-xs">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Precio</label>
                                        <input type="number" x-model="dest.newProd.precio_unitario" class="w-full border-gray-300 rounded-md shadow-sm text-xs" step="any" min="0">
                                    </div>
                                </div>
                                <div class="flex items-center justify-between mt-2">
                                    <p x-show="dest.createProdError" x-text="dest.createProdError" class="text-red-500 text-xs"></p>
                                    <button type="button" @click="createProducto(dest)" :disabled="dest.creatingProd"
                                            class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700 text-xs disabled:opacity-50 ml-auto">
                                        <span x-show="!dest.creatingProd">Crear y agregar</span>
                                        <span x-show="dest.creatingProd">Creando...</span>
                                    </button>
                                </div>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 text-sm">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-2 py-1 text-left text-xs font-medium text-gray-500 uppercase w-24">Cantidad</th>
                                            <th class="px-2 py-1 text-left text-xs font-medium text-gray-500 uppercase w-32">Codigo</th>
                                            <th class="px-2 py-1 text-left text-xs font-medium text-gray-500 uppercase">Descripcion</th>
                                            <th class="px-2 py-1 w-10"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="(prod, pIdx) in dest.productos" :key="pIdx">
                                            <tr class="border-b border-gray-100">
                                                <td class="px-2 py-1"><input type="number" :name="'destinatarios['+idx+'][productos]['+pIdx+'][cantidad]'" x-model.number="prod.cantidad" class="w-full border-gray-300 rounded-md shadow-sm text-xs" min="1" step="any"></td>
                                                <td class="px-2 py-1"><input type="text" :name="'destinatarios['+idx+'][productos]['+pIdx+'][codigo_principal]'" x-model="prod.codigo" class="w-full border-gray-300 rounded-md shadow-sm text-xs"></td>
                                                <td class="px-2 py-1"><input type="text" :name="'destinatarios['+idx+'][productos]['+pIdx+'][descripcion]'" x-model="prod.descripcion" class="w-full border-gray-300 rounded-md shadow-sm text-xs" required></td>
                                                <td class="px-2 py-1"><button type="button" @click="dest.productos.splice(pIdx, 1)" class="text-red-500 hover:text-red-700">&times;</button></td>
                                            </tr>
                                        </template>
                                        <tr x-show="dest.productos.length === 0">
                                            <td colspan="4" class="px-2 py-3 text-center text-gray-400 text-xs">Use "Buscar Producto" o "+ Nuevo Producto" para agregar</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </template>
                <div x-show="destinatarios.length === 0" class="text-center text-gray-400 py-4 text-sm">
                    Haga clic en "Agregar destinatario" para comenzar
                </div>
            </div>
        </div>

        {{-- Client Search Modal for Destinatarios --}}
        <div x-data="destClienteModal()" @open-dest-cliente-modal.window="openModal($event.detail.idx)" @keydown.escape.window="closeModal()" x-show="show" x-cloak class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-black bg-opacity-50" @click="closeModal()"></div>
            <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl mx-4 relative z-10 max-h-[90vh] flex flex-col">
                <div class="flex justify-between items-center px-6 py-4 border-b">
                    <h3 class="text-lg font-medium text-gray-900">Seleccione el Cliente</h3>
                    <button type="button" @click="closeModal()" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
                </div>
                <div class="flex items-center justify-between px-6 py-3">
                    <div class="flex items-center gap-2 text-sm">
                        <span class="text-gray-600">Mostrar</span>
                        <select x-model="perPage" @change="searchClientes()" class="border-gray-300 rounded-md shadow-sm text-sm py-1">
                            <option value="5">5</option>
                            <option value="10">10</option>
                            <option value="25">25</option>
                        </select>
                    </div>
                    <div class="flex items-center gap-2 text-sm">
                        <span class="text-gray-600">Buscar:</span>
                        <input type="text" x-model="query" @input.debounce.300ms="searchClientes()" class="border-gray-300 rounded-md shadow-sm text-sm py-1" autocomplete="off">
                    </div>
                </div>
                <div class="px-6 overflow-y-auto flex-1">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Identificacion</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Razon Social</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <template x-for="cliente in results" :key="cliente.id">
                                <tr @click="selectCliente(cliente)" class="hover:bg-blue-50 cursor-pointer">
                                    <td class="px-4 py-2 text-sm text-gray-900" x-text="cliente.identificacion"></td>
                                    <td class="px-4 py-2 text-sm text-gray-900" x-text="cliente.razon_social"></td>
                                </tr>
                            </template>
                            <tr x-show="results.length === 0 && !loading">
                                <td colspan="2" class="px-4 py-4 text-center text-sm text-gray-400">No se encontraron resultados.</td>
                            </tr>
                            <tr x-show="loading">
                                <td colspan="2" class="px-4 py-4 text-center text-sm text-gray-400">Buscando...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-3 border-t flex items-center justify-between text-sm">
                    <span class="text-gray-600">Mostrando del <span x-text="fromRecord"></span> al <span x-text="toRecord"></span> de <span x-text="totalResults"></span> registros</span>
                    <div class="flex gap-1">
                        <button type="button" @click="goToPage(currentPage - 1)" :disabled="currentPage <= 1" class="px-2 py-1 border rounded text-xs disabled:opacity-50">Anterior</button>
                        <template x-for="p in visiblePages" :key="p">
                            <button type="button" @click="goToPage(p)" class="px-2 py-1 border rounded text-xs" :class="p === currentPage ? 'bg-blue-600 text-white' : 'hover:bg-gray-100'" x-text="p"></button>
                        </template>
                        <button type="button" @click="goToPage(currentPage + 1)" :disabled="currentPage >= lastPage" class="px-2 py-1 border rounded text-xs disabled:opacity-50">Siguiente</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Product Search Modal for Destinatarios --}}
        <div x-data="{ show: false }" @open-guia-product-modal.window="show = true" @close-guia-product-modal.window="show = false" @keydown.escape.window="show = false" x-show="show" x-cloak class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-black bg-opacity-50" @click="show = false"></div>
            <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl mx-4 relative z-10 max-h-[90vh] flex flex-col">
                <div class="flex justify-between items-center px-6 py-4 border-b">
                    <h3 class="text-lg font-medium text-gray-900">Seleccione el Producto</h3>
                    <button type="button" @click="show = false" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
                </div>
                <div class="flex items-center justify-between px-6 py-3">
                    <div class="flex items-center gap-2 text-sm">
                        <span class="text-gray-600">Mostrar</span>
                        <select id="guia-prod-per-page" onchange="guiaProdSearch()" class="border-gray-300 rounded-md shadow-sm text-sm py-1">
                            <option value="5">5</option>
                            <option value="10">10</option>
                            <option value="25">25</option>
                        </select>
                    </div>
                    <div class="flex items-center gap-2 text-sm">
                        <span class="text-gray-600">Buscar:</span>
                        <input type="text" id="guia-prod-search" oninput="clearTimeout(window._gpst); window._gpst = setTimeout(guiaProdSearch, 300)" class="border-gray-300 rounded-md shadow-sm text-sm py-1" autocomplete="off">
                    </div>
                </div>
                <div class="px-6 overflow-y-auto flex-1">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Codigo</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Descripcion</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Precio</th>
                            </tr>
                        </thead>
                        <tbody id="guia-prod-results" class="divide-y divide-gray-200"></tbody>
                    </table>
                </div>
                <div class="px-6 py-3 border-t flex items-center justify-between text-sm">
                    <span id="guia-prod-info" class="text-gray-600"></span>
                    <div id="guia-prod-pagination" class="flex gap-1"></div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-1">Observaciones</label>
            <textarea name="observaciones" rows="2" class="w-full border-gray-300 rounded-md shadow-sm text-sm">{{ old('observaciones', $guia->observaciones) }}</textarea>
            @error('observaciones') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="flex justify-end">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Actualizar Guia de Remision</button>
        </div>
    </form>

    @push('scripts')
    <script>
        var _destClienteSearchUrl = "{{ route('emisor.api.clientes.buscar') }}";
        var _destClienteStoreUrl = "{{ route('emisor.api.clientes.store') }}";
        var _guiaProductoSearchUrl = "{{ route('emisor.api.productos.buscar') }}";
        var _guiaProductoStoreUrl = "{{ route('emisor.api.productos.store') }}";
        var _guiaProdCurrentPage = 1;
        var _guiaProdLastPage = 1;
        var _guiaProdTotal = 0;
        var _guiaProdTargetIdx = 0;

        async function guiaProdSearch() {
            _guiaProdCurrentPage = 1;
            await guiaProdFetch();
        }

        async function guiaProdFetch() {
            var perPage = document.getElementById('guia-prod-per-page').value;
            var q = document.getElementById('guia-prod-search').value;
            var url = _guiaProductoSearchUrl + '?per_page=' + perPage + '&page=' + _guiaProdCurrentPage;
            if (q) url += '&q=' + encodeURIComponent(q);
            try {
                var resp = await fetch(url);
                var data = await resp.json();
                _guiaProdLastPage = data.last_page;
                _guiaProdTotal = data.total;
                var tbody = document.getElementById('guia-prod-results');
                tbody.innerHTML = '';
                if (data.data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="3" class="px-4 py-4 text-center text-sm text-gray-400">No se encontraron productos.</td></tr>';
                } else {
                    data.data.forEach(function(p) {
                        var tr = document.createElement('tr');
                        tr.className = 'hover:bg-blue-50 cursor-pointer';
                        var td1 = document.createElement('td');
                        td1.className = 'px-4 py-2 text-sm text-gray-900';
                        td1.textContent = p.codigo_principal || '-';
                        var td2 = document.createElement('td');
                        td2.className = 'px-4 py-2 text-sm text-gray-900';
                        td2.textContent = p.nombre;
                        var td3 = document.createElement('td');
                        td3.className = 'px-4 py-2 text-sm text-gray-900 text-right';
                        td3.textContent = '$' + Number(p.precio_unitario || 0).toFixed(2);
                        tr.appendChild(td1);
                        tr.appendChild(td2);
                        tr.appendChild(td3);
                        tr.addEventListener('click', function() {
                            window.dispatchEvent(new CustomEvent('guia-product-selected', { detail: { producto: p, idx: _guiaProdTargetIdx } }));
                            window.dispatchEvent(new CustomEvent('close-guia-product-modal'));
                        });
                        tbody.appendChild(tr);
                    });
                }
                var from = _guiaProdTotal === 0 ? 0 : (_guiaProdCurrentPage - 1) * perPage + 1;
                var to = Math.min(_guiaProdCurrentPage * perPage, _guiaProdTotal);
                document.getElementById('guia-prod-info').textContent = 'Mostrando del ' + from + ' al ' + to + ' de ' + _guiaProdTotal + ' registros';
                var pagDiv = document.getElementById('guia-prod-pagination');
                pagDiv.innerHTML = '';
                var prevBtn = document.createElement('button');
                prevBtn.type = 'button';
                prevBtn.className = 'px-2 py-1 border rounded text-xs' + (_guiaProdCurrentPage <= 1 ? ' opacity-50' : '');
                prevBtn.textContent = 'Anterior';
                prevBtn.disabled = _guiaProdCurrentPage <= 1;
                prevBtn.onclick = function() { _guiaProdCurrentPage--; guiaProdFetch(); };
                pagDiv.appendChild(prevBtn);
                var nextBtn = document.createElement('button');
                nextBtn.type = 'button';
                nextBtn.className = 'px-2 py-1 border rounded text-xs' + (_guiaProdCurrentPage >= _guiaProdLastPage ? ' opacity-50' : '');
                nextBtn.textContent = 'Siguiente';
                nextBtn.disabled = _guiaProdCurrentPage >= _guiaProdLastPage;
                nextBtn.onclick = function() { _guiaProdCurrentPage++; guiaProdFetch(); };
                pagDiv.appendChild(nextBtn);
            } catch(e) {}
        }

        var _destClienteTargetIdx = 0;

        function destClienteModal() {
            return {
                show: false,
                query: '',
                results: [],
                totalResults: 0,
                currentPage: 1,
                lastPage: 1,
                perPage: 5,
                loading: false,

                openModal(idx) {
                    _destClienteTargetIdx = idx;
                    this.show = true;
                    this.query = '';
                    this.currentPage = 1;
                    this.fetchClientes();
                },

                closeModal() {
                    this.show = false;
                },

                async fetchClientes() {
                    this.loading = true;
                    try {
                        let url = _destClienteSearchUrl + '?per_page=' + this.perPage + '&page=' + this.currentPage;
                        if (this.query) url += '&q=' + encodeURIComponent(this.query);
                        const resp = await fetch(url);
                        const data = await resp.json();
                        this.results = data.data;
                        this.totalResults = data.total;
                        this.lastPage = data.last_page;
                        this.currentPage = data.current_page;
                    } catch (e) { this.results = []; }
                    this.loading = false;
                },

                searchClientes() {
                    this.currentPage = 1;
                    this.fetchClientes();
                },

                goToPage(page) {
                    if (page < 1 || page > this.lastPage) return;
                    this.currentPage = page;
                    this.fetchClientes();
                },

                selectCliente(cliente) {
                    window.dispatchEvent(new CustomEvent('dest-cliente-selected', {
                        detail: { cliente: cliente, idx: _destClienteTargetIdx }
                    }));
                    this.closeModal();
                },

                get visiblePages() {
                    let pages = [];
                    let start = Math.max(1, this.currentPage - 2);
                    let end = Math.min(this.lastPage, this.currentPage + 2);
                    for (let i = start; i <= end; i++) pages.push(i);
                    return pages;
                },

                get fromRecord() {
                    return this.totalResults === 0 ? 0 : (this.currentPage - 1) * this.perPage + 1;
                },

                get toRecord() {
                    return Math.min(this.currentPage * this.perPage, this.totalResults);
                }
            };
        }

        // Build grouped destinatarios from existing detalles
        var _existingDestinatarios = (function() {
            var rawDetalles = @json($guia->detalles);
            var groups = [];
            var seen = {};

            rawDetalles.forEach(function(d) {
                var key = [
                    d.identificacion_destinatario,
                    d.razon_social_destinatario,
                    d.dir_destinatario,
                    d.motivo_traslado
                ].join('||');

                if (!seen[key]) {
                    seen[key] = groups.length;
                    groups.push({
                        identificacion: d.identificacion_destinatario || '',
                        razon_social: d.razon_social_destinatario || '',
                        direccion: d.dir_destinatario || '',
                        motivo_traslado: d.motivo_traslado || '',
                        doc_aduanero_unico: d.doc_aduanero_unico || '',
                        cod_establecimiento_destino: d.cod_establecimiento_destino || '',
                        ruta: d.ruta || '',
                        productos: [],
                        // UI state
                        selectedDisplay: d.identificacion_destinatario
                            ? (d.identificacion_destinatario + ' - ' + d.razon_social_destinatario)
                            : '',
                        showCreateForm: false,
                        creating: false,
                        createError: '',
                        newCliente: { tipo_identificacion: '05', identificacion: '', razon_social: '', direccion: '', email: '', telefono: '' },
                        showCreateProd: false, creatingProd: false, createProdError: '',
                        newProd: { codigo_principal: '', nombre: '', precio_unitario: 0 }
                    });
                }

                if (d.descripcion) {
                    groups[seen[key]].productos.push({
                        codigo: d.codigo_principal || '',
                        descripcion: d.descripcion || '',
                        cantidad: parseFloat(d.cantidad) || 1
                    });
                }
            });

            return groups;
        })();

        function newDestinatario() {
            return {
                identificacion: '', razon_social: '', direccion: '', motivo_traslado: '',
                doc_aduanero_unico: '', cod_establecimiento_destino: '', ruta: '', productos: [],
                selectedDisplay: '', showCreateForm: false,
                creating: false, createError: '',
                newCliente: { tipo_identificacion: '05', identificacion: '', razon_social: '', direccion: '', email: '', telefono: '' },
                showCreateProd: false, creatingProd: false, createProdError: '',
                newProd: { codigo_principal: '', nombre: '', precio_unitario: 0 }
            };
        }

        function destinatariosManager() {
            return {
                destinatarios: _existingDestinatarios.length > 0 ? _existingDestinatarios : [newDestinatario()],
                init() {
                    window.addEventListener('dest-cliente-selected', (e) => {
                        const dest = this.destinatarios[e.detail.idx];
                        if (dest) {
                            dest.identificacion = e.detail.cliente.identificacion;
                            dest.razon_social = e.detail.cliente.razon_social;
                            dest.direccion = e.detail.cliente.direccion || '';
                            dest.selectedDisplay = e.detail.cliente.identificacion + ' - ' + e.detail.cliente.razon_social;
                        }
                    });
                },
                addDestinatario() {
                    this.destinatarios.push(newDestinatario());
                },
                openClienteModal(idx) {
                    window.dispatchEvent(new CustomEvent('open-dest-cliente-modal', { detail: { idx: idx } }));
                },
                openProductModal(idx) {
                    _guiaProdTargetIdx = idx;
                    document.getElementById('guia-prod-search').value = '';
                    _guiaProdCurrentPage = 1;
                    window.dispatchEvent(new CustomEvent('open-guia-product-modal'));
                    guiaProdFetch();
                },
                async createProducto(dest) {
                    if (!dest.newProd.nombre) { dest.createProdError = 'El nombre es obligatorio.'; return; }
                    dest.creatingProd = true;
                    dest.createProdError = '';
                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
                        const resp = await fetch(_guiaProductoStoreUrl, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                            body: JSON.stringify(dest.newProd),
                        });
                        if (!resp.ok) {
                            const err = await resp.json();
                            dest.createProdError = err.message || Object.values(err.errors || {}).flat().join(', ') || 'Error al crear.';
                            dest.creatingProd = false;
                            return;
                        }
                        const producto = await resp.json();
                        dest.productos.push({ codigo: producto.codigo_principal || '', descripcion: producto.nombre || '', cantidad: 1 });
                        dest.showCreateProd = false;
                        dest.newProd = { codigo_principal: '', nombre: '', precio_unitario: 0 };
                    } catch(e) { dest.createProdError = 'Error de conexion.'; }
                    dest.creatingProd = false;
                },
                clearClienteSelection(dest) {
                    dest.identificacion = '';
                    dest.razon_social = '';
                    dest.direccion = '';
                    dest.selectedDisplay = '';
                },
                async createCliente(dest) {
                    if (!dest.newCliente.identificacion || !dest.newCliente.razon_social) {
                        dest.createError = 'Identificacion y razon social son obligatorios.';
                        return;
                    }
                    dest.creating = true;
                    dest.createError = '';
                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
                        const resp = await fetch(_destClienteStoreUrl, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                            body: JSON.stringify(dest.newCliente),
                        });
                        if (!resp.ok) {
                            const err = await resp.json();
                            dest.createError = err.message || Object.values(err.errors || {}).flat().join(', ') || 'Error al crear cliente.';
                            dest.creating = false;
                            return;
                        }
                        const cliente = await resp.json();
                        dest.identificacion = cliente.identificacion;
                        dest.razon_social = cliente.razon_social;
                        dest.direccion = cliente.direccion || '';
                        dest.selectedDisplay = cliente.identificacion + ' - ' + cliente.razon_social;
                        dest.showCreateForm = false;
                        dest.newCliente = { tipo_identificacion: '05', identificacion: '', razon_social: '', direccion: '', email: '', telefono: '' };
                    } catch (e) { dest.createError = 'Error de conexion.'; }
                    dest.creating = false;
                }
            };
        }

        // Pre-fill transportista search display from existing guia data
        @php
            $prefillRuc = old('ruc_transportista', $guia->ruc_transportista);
            $prefillRazonSocial = old('razon_social_transportista', $guia->razon_social_transportista);
            $prefillPlaca = old('placa', $guia->placa);
        @endphp
        @if($prefillRuc)
        document.addEventListener('alpine:initialized', () => {
            // Find the transportistaSearch Alpine component instance and pre-fill it
            const transportistaEl = document.querySelector('[x-data="transportistaSearch"]');
            if (transportistaEl && transportistaEl._x_dataStack) {
                const alpineData = transportistaEl._x_dataStack[0];
                if (alpineData) {
                    alpineData.selectedRuc = @js($prefillRuc);
                    alpineData.selectedRazonSocial = @js($prefillRazonSocial);
                    alpineData.selectedPlaca = @js($prefillPlaca);
                    alpineData.selectedDisplay = @js($prefillRuc) + ' - ' + @js($prefillRazonSocial);
                    alpineData.selectedId = 'prefilled';
                }
            }
        });
        @endif
    </script>
    @endpush
</x-emisor-layout>
