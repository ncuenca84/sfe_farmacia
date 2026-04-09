<x-emisor-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Ajuste de Inventario</h2>
            <a href="{{ route('emisor.inventario.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700 text-sm">Volver</a>
        </div>
    </x-slot>

    <div class="bg-white rounded-lg shadow p-6 max-w-2xl" x-data="ajusteForm()">
        <form method="POST" action="{{ route('emisor.inventario.guardar-ajuste') }}">
            @csrf
            <div class="grid grid-cols-1 gap-4">
                <div>
                    @if($establecimientos->count() > 1)
                    <label class="block text-sm font-medium text-gray-700 mb-1">Establecimiento</label>
                    <select name="establecimiento_id" required class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                        <option value="">Seleccione...</option>
                        @foreach($establecimientos as $est)
                            <option value="{{ $est->id }}" {{ old('establecimiento_id') == $est->id ? 'selected' : '' }}>{{ $est->codigo }} - {{ $est->nombre }}</option>
                        @endforeach
                    </select>
                    @else
                    <input type="hidden" name="establecimiento_id" value="{{ $establecimientos->first()?->id }}">
                    @endif
                    @error('establecimiento_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Producto</label>
                    <div class="flex gap-2">
                        <input type="text" x-model="productoNombre" readonly class="w-full border-gray-300 rounded-md shadow-sm text-sm bg-gray-50" placeholder="Seleccione un producto...">
                        <input type="hidden" name="producto_id" x-model="productoId">
                        <button type="button" @click="openProductModal()" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm whitespace-nowrap">Buscar Producto</button>
                    </div>
                    @error('producto_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cantidad (positiva para entrada, negativa para salida)</label>
                    <input type="number" name="cantidad" step="any" value="{{ old('cantidad') }}" required class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                    @error('cantidad') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Costo Unitario (opcional, para actualizar costo promedio)</label>
                    <input type="number" name="costo_unitario" step="any" min="0" value="{{ old('costo_unitario') }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm" placeholder="0.00">
                    @error('costo_unitario') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Descripcion / Motivo</label>
                    <textarea name="descripcion" rows="2" required class="w-full border-gray-300 rounded-md shadow-sm text-sm" placeholder="Motivo del ajuste...">{{ old('descripcion') }}</textarea>
                    @error('descripcion') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="pt-2">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 text-sm font-medium">Registrar Ajuste</button>
                </div>
            </div>
        </form>

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
                                <tr @click="selectProduct(result)" class="hover:bg-blue-50 cursor-pointer">
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
    </div>

    @push('styles')
    <style>[x-cloak] { display: none !important; }</style>
    @endpush

    <script>
        function ajusteForm() {
            var _searchUrl = "{{ route('emisor.api.productos.buscar') }}";
            return {
                productoId: '',
                productoNombre: '',
                showProductModal: false,
                prodSearchQuery: '',
                prodSearchResults: [],
                prodTotalResults: 0,
                prodCurrentPage: 1,
                prodLastPage: 1,
                prodPerPage: 5,
                prodSearching: false,

                openProductModal() {
                    this.showProductModal = true;
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
                        let url = _searchUrl + '?per_page=' + this.prodPerPage + '&page=' + this.prodCurrentPage;
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

                selectProduct(result) {
                    this.productoId = result.id;
                    this.productoNombre = (result.codigo_principal || '') + ' - ' + result.nombre;
                    this.closeProductModal();
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
            }
        }
    </script>
</x-emisor-layout>
