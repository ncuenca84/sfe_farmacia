@props(['prefix' => '', 'initialRuc' => '', 'initialRazonSocial' => '', 'initialPlaca' => ''])

@once
<script>
    var _transportistaSearchUrl = "{{ route('emisor.api.transportistas.buscar') }}";
    var _transportistaStoreUrl = "{{ route('emisor.api.transportistas.store') }}";

    document.addEventListener('alpine:init', () => {
        Alpine.data('transportistaSearch', () => ({
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
            selectedRuc: '',
            selectedRazonSocial: '',
            selectedPlaca: '',
            selectedDisplay: '',
            creating: false,
            createError: '',
            newTransportista: {
                tipo_identificacion: '04',
                identificacion: '',
                razon_social: '',
                placa: '',
                email: '',
                telefono: '',
            },

            openModal() {
                this.showModal = true;
                this.showCreateForm = false;
                this.query = '';
                this.currentPage = 1;
                this.fetchTransportistas();
            },

            closeModal() {
                this.showModal = false;
            },

            async fetchTransportistas() {
                this.loading = true;
                try {
                    let url = _transportistaSearchUrl + '?per_page=' + this.perPage + '&page=' + this.currentPage;
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

            searchTransportistas() {
                this.currentPage = 1;
                this.fetchTransportistas();
            },

            goToPage(page) {
                if (page < 1 || page > this.lastPage) return;
                this.currentPage = page;
                this.fetchTransportistas();
            },

            selectTransportista(t) {
                this.selectedId = t.id;
                this.selectedRuc = t.identificacion;
                this.selectedRazonSocial = t.razon_social;
                this.selectedPlaca = t.placa || '';
                this.selectedDisplay = t.identificacion + ' - ' + t.razon_social;
                this.closeModal();
            },

            clearSelection() {
                this.selectedId = '';
                this.selectedRuc = '';
                this.selectedRazonSocial = '';
                this.selectedPlaca = '';
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

            async createTransportista() {
                if (!this.newTransportista.identificacion || !this.newTransportista.razon_social) {
                    this.createError = 'Identificacion y razon social son obligatorios.';
                    return;
                }
                this.creating = true;
                this.createError = '';
                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
                    const resp = await fetch(_transportistaStoreUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(this.newTransportista),
                    });
                    if (!resp.ok) {
                        const err = await resp.json();
                        this.createError = err.message || Object.values(err.errors || {}).flat().join(', ') || 'Error al crear transportista.';
                        this.creating = false;
                        return;
                    }
                    const transportista = await resp.json();
                    this.selectTransportista(transportista);
                    this.showCreateForm = false;
                    this.newTransportista = { tipo_identificacion: '04', identificacion: '', razon_social: '', placa: '', email: '', telefono: '' };
                } catch (e) {
                    this.createError = 'Error de conexion.';
                }
                this.creating = false;
            }
        }));
    });
</script>
@endonce

<div class="md:col-span-3" x-data="transportistaSearch" @if($initialRuc) x-init="selectedRuc = '{{ $initialRuc }}'; selectedRazonSocial = '{{ addslashes($initialRazonSocial) }}'; selectedPlaca = '{{ addslashes($initialPlaca) }}'; selectedId = 'prefilled'; selectedDisplay = '{{ $initialRuc }} - {{ addslashes($initialRazonSocial) }}';" @endif>
    <label class="block text-sm font-medium text-gray-700 mb-1">Transportista</label>

    <input type="hidden" name="{{ $prefix }}ruc_transportista" :value="selectedRuc">
    <input type="hidden" name="{{ $prefix }}razon_social_transportista" :value="selectedRazonSocial">
    <input type="hidden" name="{{ $prefix }}placa" :value="selectedPlaca">

    {{-- Selected display --}}
    <div x-show="selectedId" class="flex items-center justify-between bg-blue-50 border border-blue-200 rounded px-3 py-2 text-sm mb-2">
        <span class="text-blue-800" x-text="selectedDisplay + (selectedPlaca ? ' | Placa: ' + selectedPlaca : '')"></span>
        <button type="button" @click="clearSelection()" class="text-red-500 hover:text-red-700 text-xs font-bold">&times;</button>
    </div>

    {{-- Action buttons --}}
    <div class="flex gap-2" x-show="!showCreateForm">
        <button type="button" @click="openModal()" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm">Buscar Transportista</button>
        <button type="button" @click="showCreateForm = true" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 text-sm">+ Nuevo Transportista</button>
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
                <h3 class="text-lg font-medium text-gray-900">Seleccione el Transportista</h3>
                <button type="button" @click="closeModal()" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
            </div>

            {{-- Controls --}}
            <div class="flex items-center justify-between px-6 py-3">
                <div class="flex items-center gap-2 text-sm">
                    <span class="text-gray-600">Mostrar</span>
                    <select x-model="perPage" @change="searchTransportistas()" class="border-gray-300 rounded-md shadow-sm text-sm py-1">
                        <option value="5">5</option>
                        <option value="10">10</option>
                        <option value="25">25</option>
                    </select>
                </div>
                <div class="flex items-center gap-2 text-sm">
                    <span class="text-gray-600">Buscar:</span>
                    <input type="text" x-model="query" @input.debounce.300ms="searchTransportistas()" class="border-gray-300 rounded-md shadow-sm text-sm py-1" autocomplete="off">
                </div>
            </div>

            {{-- Table --}}
            <div class="px-6 overflow-y-auto flex-1">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Identificacion</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Razon Social</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Placa</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <template x-for="t in results" :key="t.id">
                            <tr @click="selectTransportista(t)" class="hover:bg-blue-50 cursor-pointer">
                                <td class="px-4 py-2 text-sm text-gray-900" x-text="t.identificacion"></td>
                                <td class="px-4 py-2 text-sm text-gray-900" x-text="t.razon_social"></td>
                                <td class="px-4 py-2 text-sm text-gray-900" x-text="t.placa || '-'"></td>
                            </tr>
                        </template>
                        <tr x-show="results.length === 0 && !loading">
                            <td colspan="3" class="px-4 py-4 text-center text-sm text-gray-400">No se encontraron resultados.</td>
                        </tr>
                        <tr x-show="loading">
                            <td colspan="3" class="px-4 py-4 text-center text-sm text-gray-400">Buscando...</td>
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
                    <button type="button" @click="goToPage(currentPage + 1)" :disabled="currentPage >= lastPage" class="px-2 py-1 border rounded text-xs disabled:opacity-50">Siguiente</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Create form --}}
    <div x-show="showCreateForm" x-cloak class="border border-gray-200 rounded-lg p-4 bg-gray-50 space-y-3 mt-1">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Tipo Identificacion</label>
                <select x-model="newTransportista.tipo_identificacion" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                    <option value="04">RUC</option>
                    <option value="05">Cedula</option>
                    <option value="06">Pasaporte</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Identificacion (RUC/Cedula)</label>
                <input type="text" x-model="newTransportista.identificacion" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Razon Social / Nombre</label>
                <input type="text" x-model="newTransportista.razon_social" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Placa</label>
                <input type="text" x-model="newTransportista.placa" class="w-full border-gray-300 rounded-md shadow-sm text-sm" placeholder="ABC-1234">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Email</label>
                <input type="email" x-model="newTransportista.email" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Telefono</label>
                <input type="text" x-model="newTransportista.telefono" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
            </div>
        </div>
        <div class="flex items-center justify-between">
            <p x-show="createError" x-text="createError" class="text-red-500 text-xs"></p>
            <button type="button" @click="createTransportista()" :disabled="creating"
                    class="bg-green-600 text-white px-4 py-1.5 rounded hover:bg-green-700 text-sm disabled:opacity-50 ml-auto">
                <span x-show="!creating">Crear y seleccionar</span>
                <span x-show="creating">Creando...</span>
            </button>
        </div>
    </div>

    @error($prefix . 'ruc_transportista') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
</div>
