@once
<script>
    var _ivaOptions = @json($ivas ?? []);
    var _productoSearchUrl = "{{ route('emisor.api.productos.buscar') }}";
    var _productoStoreUrl = "{{ route('emisor.api.productos.store') }}";

    document.addEventListener('alpine:init', () => {
        Alpine.data('productosSection', () => ({
            ivaOptions: _ivaOptions,
            productos: [],
            totales: { subtotal: 0, descuento: 0, iva: 0, total: 0 },
            showCreateForm: false,
            showProductModal: false,
            prodSearchQuery: '',
            prodSearchResults: [],
            prodTotalResults: 0,
            prodCurrentPage: 1,
            prodLastPage: 1,
            prodPerPage: 5,
            prodSearching: false,
            creating: false,
            createError: '',
            newProd: { codigo_principal: '', nombre: '', precio_unitario: 0, impuesto_iva_id: _ivaOptions.length ? _ivaOptions[0].id : '' },

            addProducto(data) {
                this.productos.push({
                    codigo: data?.codigo_principal || '',
                    descripcion: data?.nombre || '',
                    cantidad: 1,
                    precio: data ? Number(data.precio_unitario || 0) : 0,
                    descuento: 0,
                    descuento_tipo: '$',
                    descuento_input: 0,
                    impuesto_iva_id: data?.impuesto_iva_id || (_ivaOptions.length ? _ivaOptions[0].id : ''),
                    subtotal: 0,
                });
                this.calcularTotales();
            },

            openProductModal() {
                this.showProductModal = true;
                this.showCreateForm = false;
                this.prodSearchQuery = '';
                this.prodCurrentPage = 1;
                this.fetchProductos();
            },

            closeProductModal() {
                this.showProductModal = false;
            },

            async fetchProductos() {
                this.prodSearching = true;
                try {
                    let url = _productoSearchUrl + '?per_page=' + this.prodPerPage + '&page=' + this.prodCurrentPage;
                    if (this.prodSearchQuery) url += '&q=' + encodeURIComponent(this.prodSearchQuery);
                    const resp = await fetch(url);
                    const data = await resp.json();
                    this.prodSearchResults = data.data;
                    this.prodTotalResults = data.total;
                    this.prodLastPage = data.last_page;
                    this.prodCurrentPage = data.current_page;
                } catch(e) { this.prodSearchResults = []; }
                this.prodSearching = false;
            },

            prodSearch() {
                this.prodCurrentPage = 1;
                this.fetchProductos();
            },

            prodGoToPage(page) {
                if (page < 1 || page > this.prodLastPage) return;
                this.prodCurrentPage = page;
                this.fetchProductos();
            },

            selectProductFromModal(result) {
                this.addProducto(result);
            },

            get prodVisiblePages() {
                let pages = [];
                let start = Math.max(1, this.prodCurrentPage - 2);
                let end = Math.min(this.prodLastPage, this.prodCurrentPage + 2);
                for (let i = start; i <= end; i++) pages.push(i);
                return pages;
            },

            get prodFromRecord() {
                return this.prodTotalResults === 0 ? 0 : (this.prodCurrentPage - 1) * this.prodPerPage + 1;
            },

            get prodToRecord() {
                return Math.min(this.prodCurrentPage * this.prodPerPage, this.prodTotalResults);
            },

            removeProducto(idx) {
                this.productos.splice(idx, 1);
                this.calcularTotales();
            },

            calcularTotales() {
                const r2 = v => Math.round((v + Number.EPSILON) * 100) / 100;
                let subtotal = 0, descuento = 0, iva = 0;
                this.productos.forEach(prod => {
                    const linea = (prod.cantidad || 0) * (prod.precio || 0);
                    let desc = 0;
                    if (prod.descuento_tipo === '%') {
                        desc = r2(linea * (prod.descuento_input || 0) / 100);
                    } else {
                        desc = prod.descuento_input || 0;
                    }
                    prod.descuento = desc;
                    const base = r2(linea - desc);
                    const ivaObj = _ivaOptions.find(i => String(i.id) === String(prod.impuesto_iva_id));
                    const tarifa = ivaObj ? parseFloat(ivaObj.tarifa) || 0 : 0;
                    const ivaLinea = r2(base * tarifa / 100);
                    prod.subtotal = base;
                    subtotal += base;
                    descuento += desc;
                    iva += ivaLinea;
                });
                this.totales = { subtotal: r2(subtotal), descuento: r2(descuento), iva: r2(iva), total: r2(subtotal + iva) };
            },

            async createProducto() {
                if (!this.newProd.nombre) { this.createError = 'El nombre es obligatorio.'; return; }
                this.creating = true;
                this.createError = '';
                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
                    const resp = await fetch(_productoStoreUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                        body: JSON.stringify(this.newProd),
                    });
                    if (!resp.ok) {
                        const err = await resp.json();
                        this.createError = err.message || Object.values(err.errors || {}).flat().join(', ') || 'Error al crear.';
                        this.creating = false;
                        return;
                    }
                    const producto = await resp.json();
                    this.addProducto(producto);
                    this.showCreateForm = false;
                    this.newProd = { codigo_principal: '', nombre: '', precio_unitario: 0, impuesto_iva_id: _ivaOptions.length ? _ivaOptions[0].id : '' };
                } catch(e) { this.createError = 'Error de conexion.'; }
                this.creating = false;
            },
        }));
    });
</script>
@endonce

<div class="bg-white rounded-lg shadow p-6 mb-6" x-data="productosSection">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-medium text-gray-900">Productos</h3>
        <div class="flex gap-2">
            <button type="button" @click="openProductModal()" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm">Buscar Producto</button>
            <button type="button" @click="showCreateForm = !showCreateForm" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 text-sm">
                <span x-show="!showCreateForm">+ Nuevo Producto</span>
                <span x-show="showCreateForm">Cancelar</span>
            </button>
        </div>
    </div>

    {{-- Product Search Modal --}}
    <div x-show="showProductModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" @keydown.escape.window="closeProductModal()">
        <div class="fixed inset-0 bg-black bg-opacity-50" @click="closeProductModal()"></div>
        <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl mx-4 relative z-10 max-h-[90vh] flex flex-col">
            <div class="flex justify-between items-center px-6 py-4 border-b">
                <h3 class="text-lg font-medium text-gray-900">Seleccione el Producto</h3>
                <button type="button" @click="closeProductModal()" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
            </div>
            <div class="flex items-center justify-between px-6 py-3">
                <div class="flex items-center gap-2 text-sm">
                    <span class="text-gray-600">Mostrar</span>
                    <select x-model="prodPerPage" @change="prodSearch()" class="border-gray-300 rounded-md shadow-sm text-sm py-1">
                        <option value="5">5</option>
                        <option value="10">10</option>
                        <option value="25">25</option>
                    </select>
                </div>
                <div class="flex items-center gap-2 text-sm">
                    <span class="text-gray-600">Buscar:</span>
                    <input type="text" x-model="prodSearchQuery" @input.debounce.300ms="prodSearch()" class="border-gray-300 rounded-md shadow-sm text-sm py-1" autocomplete="off">
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
                    <tbody class="divide-y divide-gray-200">
                        <template x-for="result in prodSearchResults" :key="result.id">
                            <tr @click="selectProductFromModal(result)" class="hover:bg-blue-50 cursor-pointer">
                                <td class="px-4 py-2 text-sm text-gray-900" x-text="result.codigo_principal || '-'"></td>
                                <td class="px-4 py-2 text-sm text-gray-900" x-text="result.nombre"></td>
                                <td class="px-4 py-2 text-sm text-gray-900 text-right" x-text="'$' + Number(result.precio_unitario || 0).toFixed(2)"></td>
                            </tr>
                        </template>
                        <tr x-show="prodSearchResults.length === 0 && !prodSearching">
                            <td colspan="3" class="px-4 py-4 text-center text-sm text-gray-400">No se encontraron productos.</td>
                        </tr>
                        <tr x-show="prodSearching">
                            <td colspan="3" class="px-4 py-4 text-center text-sm text-gray-400">Buscando...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-3 border-t flex items-center justify-between text-sm">
                <span class="text-gray-600">Mostrando del <span x-text="prodFromRecord"></span> al <span x-text="prodToRecord"></span> de <span x-text="prodTotalResults"></span> registros</span>
                <div class="flex gap-1">
                    <button type="button" @click="prodGoToPage(prodCurrentPage - 1)" :disabled="prodCurrentPage <= 1" class="px-2 py-1 border rounded text-xs disabled:opacity-50">Anterior</button>
                    <template x-for="p in prodVisiblePages" :key="p">
                        <button type="button" @click="prodGoToPage(p)" class="px-2 py-1 border rounded text-xs" :class="p === prodCurrentPage ? 'bg-blue-600 text-white' : 'hover:bg-gray-100'" x-text="p"></button>
                    </template>
                    <template x-if="prodLastPage > prodCurrentPage + 2">
                        <span class="px-1 py-1 text-xs text-gray-400">...</span>
                    </template>
                    <template x-if="prodLastPage > prodCurrentPage + 2">
                        <button type="button" @click="prodGoToPage(prodLastPage)" class="px-2 py-1 border rounded text-xs hover:bg-gray-100" x-text="prodLastPage"></button>
                    </template>
                    <button type="button" @click="prodGoToPage(prodCurrentPage + 1)" :disabled="prodCurrentPage >= prodLastPage" class="px-2 py-1 border rounded text-xs disabled:opacity-50">Siguiente</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Inline create product form --}}
    <div x-show="showCreateForm" x-cloak class="border border-gray-200 rounded-lg p-4 bg-gray-50 mb-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Codigo</label>
                <input type="text" x-model="newProd.codigo_principal" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Nombre / Descripcion</label>
                <input type="text" x-model="newProd.nombre" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Precio</label>
                <input type="number" x-model="newProd.precio_unitario" class="w-full border-gray-300 rounded-md shadow-sm text-sm" step="any" min="0">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">IVA</label>
                <select x-model="newProd.impuesto_iva_id" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                    <template x-for="iva in ivaOptions" :key="iva.id">
                        <option :value="iva.id" x-text="iva.nombre + ' (' + (iva.tarifa || 0) + '%)'"></option>
                    </template>
                </select>
            </div>
        </div>
        <div class="flex items-center justify-between mt-3">
            <p x-show="createError" x-text="createError" class="text-red-500 text-xs"></p>
            <button type="button" @click="createProducto()" :disabled="creating" class="bg-green-600 text-white px-4 py-1.5 rounded hover:bg-green-700 text-sm disabled:opacity-50 ml-auto">
                <span x-show="!creating">Crear y agregar</span>
                <span x-show="creating">Creando...</span>
            </button>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase w-32">Codigo</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Descripcion</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase w-24">Cantidad</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase w-28">Precio</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase w-28">Descuento</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase w-36">IVA</th>
                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase w-28">Total</th>
                    <th class="px-3 py-2 w-10"></th>
                </tr>
            </thead>
            <tbody>
                <template x-for="(prod, idx) in productos" :key="idx">
                    <tr class="border-b border-gray-100">
                        <td class="px-3 py-2">
                            <input type="text" :name="'detalles['+idx+'][codigo_principal]'" x-model="prod.codigo" class="w-full border-gray-300 rounded-md shadow-sm text-sm bg-gray-50" readonly>
                        </td>
                        <td class="px-3 py-2"><input type="text" :name="'detalles['+idx+'][descripcion]'" x-model="prod.descripcion" class="w-full border-gray-300 rounded-md shadow-sm text-sm" required></td>
                        <td class="px-3 py-2"><input type="number" :name="'detalles['+idx+'][cantidad]'" x-model.number="prod.cantidad" @input="calcularTotales()" class="w-full border-gray-300 rounded-md shadow-sm text-sm" min="0.01" step="any" required></td>
                        <td class="px-3 py-2"><input type="number" :name="'detalles['+idx+'][precio_unitario]'" x-model.number="prod.precio" @input="calcularTotales()" class="w-full border-gray-300 rounded-md shadow-sm text-sm" min="0" step="any" required></td>
                        <td class="px-3 py-2">
                            <div class="flex items-center gap-1">
                                <input type="number" x-model.number="prod.descuento_input" @input="calcularTotales()" class="w-full min-w-0 border-gray-300 rounded-md shadow-sm text-sm" min="0" step="any" :max="prod.descuento_tipo === '%' ? 100 : undefined">
                                <button type="button" @click="prod.descuento_tipo = prod.descuento_tipo === '$' ? '%' : '$'; calcularTotales()" class="shrink-0 w-8 h-8 rounded border border-gray-300 text-xs font-bold hover:bg-gray-100" :class="prod.descuento_tipo === '%' ? 'bg-blue-50 border-blue-300 text-blue-700' : 'bg-gray-50'" x-text="prod.descuento_tipo"></button>
                                <input type="hidden" :name="'detalles['+idx+'][descuento]'" :value="prod.descuento">
                            </div>
                        </td>
                        <td class="px-3 py-2">
                            <select :name="'detalles['+idx+'][impuesto_iva_id]'" x-model="prod.impuesto_iva_id" @change="calcularTotales()" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                                <template x-for="iva in ivaOptions" :key="iva.id">
                                    <option :value="iva.id" x-text="iva.nombre" :selected="prod.impuesto_iva_id == iva.id"></option>
                                </template>
                            </select>
                        </td>
                        <td class="px-3 py-2 text-right text-sm font-medium" x-text="'$' + prod.subtotal.toFixed(2)"></td>
                        <td class="px-3 py-2"><button type="button" @click="removeProducto(idx)" class="text-red-500 hover:text-red-700">&times;</button></td>
                    </tr>
                </template>
                <tr x-show="productos.length === 0">
                    <td colspan="8" class="px-3 py-4 text-center text-gray-400 text-sm">Use "Buscar producto" o "+ Nuevo producto" para agregar productos</td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="mt-4 flex justify-end">
        <div class="w-72 space-y-1">
            <div class="flex justify-between text-sm"><span class="text-gray-600">Subtotal sin impuesto:</span><span class="font-medium" x-text="'$' + totales.subtotal.toFixed(2)">$0.00</span></div>
            <div class="flex justify-between text-sm"><span class="text-gray-600">Descuento:</span><span class="font-medium" x-text="'$' + totales.descuento.toFixed(2)">$0.00</span></div>
            <div class="flex justify-between text-sm"><span class="text-gray-600">IVA:</span><span class="font-medium" x-text="'$' + totales.iva.toFixed(2)">$0.00</span></div>
            <div class="flex justify-between text-sm font-bold border-t pt-2"><span>VALOR TOTAL:</span><span x-text="'$' + totales.total.toFixed(2)">$0.00</span></div>
        </div>
    </div>
</div>
