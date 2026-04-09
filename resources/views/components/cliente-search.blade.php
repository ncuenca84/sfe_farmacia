@props(['label' => 'Cliente', 'selectedId' => null, 'selectedDisplay' => null])

@once
<script>
    var _clienteSearchUrl = "{{ route('emisor.api.clientes.buscar') }}";
    var _clienteStoreUrl = "{{ route('emisor.api.clientes.store') }}";
    var _clienteConsultarUrl = "{{ route('emisor.api.clientes.consultar', ['identificacion' => '__ID__']) }}";

    document.addEventListener('alpine:init', () => {
        Alpine.data('clienteSearch', () => ({
            showModal: false,
            showCreateForm: false,
            query: '',
            results: [],
            totalResults: 0,
            currentPage: 1,
            lastPage: 1,
            perPage: 5,
            loading: false,
            selectedId: '',
            selectedDisplay: '',
            creating: false,
            createError: '',
            consultando: false,
            consultaMensaje: '',
            consultaError: false,
            newCliente: {
                tipo_identificacion: '05',
                identificacion: '',
                razon_social: '',
                direccion: '',
                email: '',
                telefono: '',
            },

            init() {
                const el = this.$el;
                const initId = el.dataset.initialId;
                const initDisplay = el.dataset.initialDisplay;
                if (initId) {
                    this.selectedId = initId;
                    this.selectedDisplay = initDisplay || '';
                }
            },

            openModal() {
                this.showModal = true;
                this.showCreateForm = false;
                this.query = '';
                this.currentPage = 1;
                this.fetchClientes();
            },

            closeModal() {
                this.showModal = false;
            },

            async fetchClientes() {
                this.loading = true;
                try {
                    let url = _clienteSearchUrl + '?per_page=' + this.perPage + '&page=' + this.currentPage;
                    if (this.query) url += '&q=' + encodeURIComponent(this.query);
                    const resp = await fetch(url);
                    const data = await resp.json();
                    this.results = data.data;
                    this.totalResults = data.total;
                    this.lastPage = data.last_page;
                    this.currentPage = data.current_page;
                } catch (e) {
                    this.results = [];
                }
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
                this.selectedId = cliente.id;
                this.selectedDisplay = cliente.identificacion + ' - ' + cliente.razon_social;
                this.closeModal();
            },

            clearSelection() {
                this.selectedId = '';
                this.selectedDisplay = '';
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
            },

            get puedeConsultarSri() {
                return (this.newCliente.tipo_identificacion === '04' || this.newCliente.tipo_identificacion === '05')
                    && this.newCliente.identificacion.length >= 10;
            },

            async consultarSri() {
                if (!this.puedeConsultarSri) return;
                this.consultando = true;
                this.consultaMensaje = '';
                this.consultaError = false;
                try {
                    const url = _clienteConsultarUrl.replace('__ID__', this.newCliente.identificacion);
                    const resp = await fetch(url, {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const data = await resp.json();
                    if (!resp.ok) {
                        this.consultaError = true;
                        this.consultaMensaje = data.error || 'No se encontraron datos.';
                        return;
                    }
                    if (data.razon_social) this.newCliente.razon_social = data.razon_social;
                    if (data.direccion) this.newCliente.direccion = data.direccion;
                    this.consultaMensaje = 'Datos cargados: ' + (data.razon_social || 'Contribuyente encontrado');
                } catch (e) {
                    this.consultaError = true;
                    this.consultaMensaje = 'Error de conexion al consultar el SRI.';
                } finally {
                    this.consultando = false;
                }
            },

            async createCliente() {
                if (!this.newCliente.identificacion || !this.newCliente.razon_social) {
                    this.createError = 'Identificacion y razon social son obligatorios.';
                    return;
                }
                this.creating = true;
                this.createError = '';
                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
                    const resp = await fetch(_clienteStoreUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(this.newCliente),
                    });
                    if (!resp.ok) {
                        const err = await resp.json();
                        this.createError = err.message || Object.values(err.errors || {}).flat().join(', ') || 'Error al crear cliente.';
                        this.creating = false;
                        return;
                    }
                    const cliente = await resp.json();
                    this.selectCliente(cliente);
                    this.showCreateForm = false;
                    this.newCliente = { tipo_identificacion: '05', identificacion: '', razon_social: '', direccion: '', email: '', telefono: '' };
                    this.consultaMensaje = '';
                    this.consultaError = false;
                } catch (e) {
                    this.createError = 'Error de conexion.';
                }
                this.creating = false;
            }
        }));
    });
</script>
@endonce

<div class="md:col-span-2" x-data="clienteSearch" @if($selectedId) data-initial-id="{{ $selectedId }}" data-initial-display="{{ $selectedDisplay }}" @endif>
    <label class="block text-sm font-medium text-gray-700 mb-1">{{ $label }}</label>

    <input type="hidden" name="cliente_id" :value="selectedId">

    {{-- Selected client display --}}
    <div x-show="selectedId" class="flex items-center justify-between bg-blue-50 border border-blue-200 rounded px-3 py-2 text-sm mb-2">
        <span class="text-blue-800" x-text="selectedDisplay"></span>
        <button type="button" @click="clearSelection()" class="text-red-500 hover:text-red-700 text-xs font-bold">&times;</button>
    </div>

    {{-- Action buttons --}}
    <div class="flex gap-2" x-show="!showCreateForm">
        <button type="button" @click="openModal()" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm">Buscar {{ $label }}</button>
        <button type="button" @click="showCreateForm = true" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 text-sm">+ Nuevo {{ $label }}</button>
    </div>
    <div x-show="showCreateForm">
        <button type="button" @click="showCreateForm = false" class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500 text-sm mb-2">Cancelar</button>
    </div>

    {{-- Modal --}}
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" @keydown.escape.window="closeModal()">
        <div class="fixed inset-0 bg-black bg-opacity-50" @click="closeModal()"></div>
        <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl mx-4 relative z-10 max-h-[90vh] flex flex-col">
            {{-- Header --}}
            <div class="flex justify-between items-center px-6 py-4 border-b">
                <h3 class="text-lg font-medium text-gray-900">Seleccione el {{ $label }}</h3>
                <button type="button" @click="closeModal()" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
            </div>

            {{-- Controls --}}
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

            {{-- Table --}}
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

            {{-- Footer: pagination --}}
            <div class="px-6 py-3 border-t flex items-center justify-between text-sm">
                <span class="text-gray-600">Mostrando del <span x-text="fromRecord"></span> al <span x-text="toRecord"></span> de <span x-text="totalResults"></span> registros</span>
                <div class="flex gap-1">
                    <button type="button" @click="goToPage(currentPage - 1)" :disabled="currentPage <= 1" class="px-2 py-1 border rounded text-xs disabled:opacity-50">Anterior</button>
                    <template x-for="p in visiblePages" :key="p">
                        <button type="button" @click="goToPage(p)" class="px-2 py-1 border rounded text-xs" :class="p === currentPage ? 'bg-blue-600 text-white' : 'hover:bg-gray-100'" x-text="p"></button>
                    </template>
                    <template x-if="lastPage > currentPage + 2">
                        <span class="px-1 py-1 text-xs text-gray-400">...</span>
                    </template>
                    <template x-if="lastPage > currentPage + 2">
                        <button type="button" @click="goToPage(lastPage)" class="px-2 py-1 border rounded text-xs hover:bg-gray-100" x-text="lastPage"></button>
                    </template>
                    <button type="button" @click="goToPage(currentPage + 1)" :disabled="currentPage >= lastPage" class="px-2 py-1 border rounded text-xs disabled:opacity-50">Siguiente</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Create form --}}
    <div x-show="showCreateForm" x-cloak class="border border-gray-200 rounded-lg p-4 bg-gray-50 space-y-3 mt-1">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Tipo Identificacion</label>
                <select x-model="newCliente.tipo_identificacion" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                    <option value="04">RUC</option>
                    <option value="05">Cedula</option>
                    <option value="06">Pasaporte</option>
                    <option value="07">Consumidor Final</option>
                    <option value="08">Identificacion del exterior</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Identificacion</label>
                <div class="flex gap-1">
                    <input type="text" x-model="newCliente.identificacion" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                    <button type="button" @click="consultarSri()" :disabled="consultando || !puedeConsultarSri"
                            class="bg-green-600 text-white px-2 py-1 rounded hover:bg-green-700 text-xs whitespace-nowrap disabled:opacity-50 disabled:cursor-not-allowed" title="Consultar datos en el SRI">
                        <span x-show="!consultando">Consultar</span>
                        <span x-show="consultando">...</span>
                    </button>
                </div>
                <p x-show="consultaMensaje" x-text="consultaMensaje" :class="consultaError ? 'text-red-500' : 'text-green-600'" class="text-xs mt-1"></p>
                <p x-show="newCliente.tipo_identificacion === '05' && !consultaMensaje" class="text-xs text-gray-400 mt-0.5">Solo funciona si tiene RUC en el SRI</p>
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-medium text-gray-600 mb-1">Razon Social / Nombre</label>
                <input type="text" x-model="newCliente.razon_social" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Direccion</label>
                <input type="text" x-model="newCliente.direccion" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Email</label>
                <input type="text" x-model="newCliente.email" class="w-full border-gray-300 rounded-md shadow-sm text-sm" placeholder="email1@ejemplo.com, email2@ejemplo.com">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Telefono</label>
                <input type="text" x-model="newCliente.telefono" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
            </div>
        </div>
        <div class="flex items-center justify-between">
            <p x-show="createError" x-text="createError" class="text-red-500 text-xs"></p>
            <button type="button" @click="createCliente()" :disabled="creating"
                    class="bg-green-600 text-white px-4 py-1.5 rounded hover:bg-green-700 text-sm disabled:opacity-50 ml-auto">
                <span x-show="!creating">Crear y seleccionar</span>
                <span x-show="creating">Creando...</span>
            </button>
        </div>
    </div>

    @error('cliente_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
</div>
