<x-emisor-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Punto de Venta</h2>
    </x-slot>

    <div x-data="posApp()" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Panel izquierdo: Búsqueda de productos -->
        <div class="lg:col-span-2 space-y-4">
            <!-- Buscador -->
            <div class="bg-white rounded-lg shadow p-4">
                <div class="relative">
                    <input type="text" x-model="busqueda" @input.debounce.300ms="buscarProductos()"
                        @keydown.escape="resultados = []"
                        placeholder="Buscar por nombre, codigo o principio activo..."
                        class="w-full border-gray-300 rounded-lg shadow-sm text-sm pl-10 py-3" autofocus>
                    <svg class="w-5 h-5 text-gray-400 absolute left-3 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>

                <!-- Resultados de búsqueda -->
                <div x-show="resultados.length > 0" x-cloak class="mt-2 border rounded-lg divide-y max-h-64 overflow-y-auto">
                    <template x-for="prod in resultados" :key="prod.id">
                        <button @click="agregarProducto(prod)" class="w-full text-left px-4 py-3 hover:bg-blue-50 flex justify-between items-center">
                            <div>
                                <p class="text-sm font-medium text-gray-900" x-text="prod.nombre"></p>
                                <p class="text-xs text-gray-500">
                                    <span x-show="prod.codigo_principal" x-text="prod.codigo_principal + ' | '"></span>
                                    <span x-show="prod.principio_activo" x-text="prod.principio_activo"></span>
                                    <span x-show="prod.concentracion" x-text="' ' + prod.concentracion"></span>
                                    <span x-show="prod.presentacion" x-text="' - ' + prod.presentacion.nombre"></span>
                                </p>
                                <template x-if="prod.tipo_venta === 'requiere_receta'">
                                    <span class="inline-flex px-1.5 py-0.5 text-[10px] font-medium rounded bg-yellow-100 text-yellow-700">Receta</span>
                                </template>
                                <template x-if="prod.tipo_venta === 'controlado'">
                                    <span class="inline-flex px-1.5 py-0.5 text-[10px] font-medium rounded bg-red-100 text-red-700">Controlado</span>
                                </template>
                            </div>
                            <span class="text-sm font-bold text-gray-900" x-text="'$' + Number(prod.precio_unitario).toFixed(2)"></span>
                        </button>
                    </template>
                </div>
            </div>

            <!-- Tabla del carrito -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Producto</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase w-24">Cant.</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase w-28">Precio</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase w-24">Desc.</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase w-28">Subtotal</th>
                            <th class="px-4 py-3 w-12"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <template x-for="(item, index) in carrito" :key="index">
                            <tr>
                                <td class="px-4 py-2">
                                    <p class="text-sm font-medium text-gray-900" x-text="item.descripcion"></p>
                                    <p class="text-xs text-gray-400" x-text="item.codigo_principal || ''"></p>
                                </td>
                                <td class="px-4 py-2">
                                    <input type="number" x-model.number="item.cantidad" @input="recalcular()" min="0.0001" step="any" class="w-20 border-gray-300 rounded text-sm text-center">
                                </td>
                                <td class="px-4 py-2">
                                    <input type="number" x-model.number="item.precio_unitario" @input="recalcular()" min="0" step="any" class="w-24 border-gray-300 rounded text-sm text-right">
                                </td>
                                <td class="px-4 py-2">
                                    <input type="number" x-model.number="item.descuento" @input="recalcular()" min="0" step="any" class="w-20 border-gray-300 rounded text-sm text-right">
                                </td>
                                <td class="px-4 py-2 text-right text-sm font-medium text-gray-900" x-text="'$' + subtotalItem(item).toFixed(2)"></td>
                                <td class="px-4 py-2">
                                    <button @click="eliminarItem(index)" class="text-red-500 hover:text-red-700">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="carrito.length === 0">
                            <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-400">Busca y agrega productos para empezar</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Panel derecho: Resumen y pago -->
        <div class="space-y-4">
            <!-- Cliente -->
            <div class="bg-white rounded-lg shadow p-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Cliente</label>
                <select x-model="clienteId" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                    @if($consumidorFinal)
                        <option value="{{ $consumidorFinal->id }}">Consumidor Final (9999999999999)</option>
                    @endif
                </select>
                <div class="mt-2">
                    <input type="text" x-model="buscarCliente" @input.debounce.300ms="buscarClientes()" placeholder="Buscar otro cliente..." class="w-full border-gray-300 rounded-md shadow-sm text-xs">
                    <div x-show="clientesResultados.length > 0" class="mt-1 border rounded divide-y max-h-32 overflow-y-auto text-xs">
                        <template x-for="cli in clientesResultados" :key="cli.id">
                            <button @click="seleccionarCliente(cli)" class="w-full text-left px-3 py-2 hover:bg-blue-50" x-text="cli.razon_social + ' (' + cli.identificacion + ')'"></button>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Forma de pago -->
            <div class="bg-white rounded-lg shadow p-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Forma de Pago</label>
                <select x-model="formaPago" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                    <option value="01">Efectivo</option>
                    <option value="16">Tarjeta Debito</option>
                    <option value="19">Tarjeta Credito</option>
                    <option value="17">Dinero Electronico</option>
                    <option value="20">Transferencia</option>
                </select>
            </div>

            <!-- Totales -->
            <div class="bg-white rounded-lg shadow p-4 space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Subtotal</span>
                    <span class="font-medium" x-text="'$' + subtotal.toFixed(2)"></span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Descuento</span>
                    <span class="font-medium text-red-500" x-text="'-$' + totalDescuento.toFixed(2)"></span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">IVA</span>
                    <span class="font-medium" x-text="'$' + totalIva.toFixed(2)"></span>
                </div>
                <div class="border-t pt-2 flex justify-between">
                    <span class="text-lg font-bold text-gray-900">TOTAL</span>
                    <span class="text-lg font-bold text-blue-600" x-text="'$' + total.toFixed(2)"></span>
                </div>
            </div>

            <!-- Monto recibido -->
            <div class="bg-white rounded-lg shadow p-4" x-show="formaPago === '01'">
                <label class="block text-sm font-medium text-gray-700 mb-2">Recibido</label>
                <input type="number" x-model.number="montoRecibido" min="0" step="any" class="w-full border-gray-300 rounded-md shadow-sm text-lg font-bold text-right">
                <div class="mt-2 flex justify-between text-sm">
                    <span class="text-gray-500">Cambio</span>
                    <span class="font-bold text-lg" :class="cambio >= 0 ? 'text-green-600' : 'text-red-600'" x-text="'$' + cambio.toFixed(2)"></span>
                </div>
            </div>

            <!-- Botones -->
            <form method="POST" action="{{ route('emisor.farmacia.pos.facturar') }}" @submit.prevent="facturar($event)">
                @csrf
                <input type="hidden" name="cliente_id" :value="clienteId">
                <input type="hidden" name="forma_pago" :value="formaPago">
                <template x-for="(item, i) in carrito" :key="i">
                    <div>
                        <input type="hidden" :name="'detalles['+i+'][codigo_principal]'" :value="item.codigo_principal">
                        <input type="hidden" :name="'detalles['+i+'][descripcion]'" :value="item.descripcion">
                        <input type="hidden" :name="'detalles['+i+'][cantidad]'" :value="item.cantidad">
                        <input type="hidden" :name="'detalles['+i+'][precio_unitario]'" :value="item.precio_unitario">
                        <input type="hidden" :name="'detalles['+i+'][descuento]'" :value="item.descuento || 0">
                        <input type="hidden" :name="'detalles['+i+'][impuesto_iva_id]'" :value="item.impuesto_iva_id">
                    </div>
                </template>

                <button type="submit" :disabled="carrito.length === 0 || procesando"
                    class="w-full bg-blue-600 text-white py-3 rounded-lg font-bold text-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span x-show="!procesando">Facturar</span>
                    <span x-show="procesando">Procesando...</span>
                </button>

                <button type="submit" name="imprimir_ticket" value="1" :disabled="carrito.length === 0 || procesando"
                    class="w-full mt-2 bg-gray-700 text-white py-2 rounded-lg font-medium text-sm hover:bg-gray-800 disabled:opacity-50 disabled:cursor-not-allowed">
                    Facturar + Imprimir Ticket
                </button>
            </form>

            <button @click="limpiar()" class="w-full bg-red-50 text-red-600 py-2 rounded-lg text-sm font-medium hover:bg-red-100">
                Limpiar Venta
            </button>
        </div>
    </div>

    @push('scripts')
    <script>
    function posApp() {
        return {
            busqueda: '',
            resultados: [],
            carrito: [],
            clienteId: '{{ $consumidorFinal?->id ?? '' }}',
            buscarCliente: '',
            clientesResultados: [],
            formaPago: '01',
            montoRecibido: 0,
            procesando: false,
            subtotal: 0,
            totalDescuento: 0,
            totalIva: 0,
            total: 0,

            // IVA rates map from server
            ivaRates: {
                @foreach($ivas as $iva)
                    {{ $iva->id }}: {{ $iva->tarifa ?? 0 }},
                @endforeach
            },

            get cambio() {
                return this.montoRecibido - this.total;
            },

            async buscarProductos() {
                if (this.busqueda.length < 2) { this.resultados = []; return; }
                const res = await fetch(`{{ route('emisor.farmacia.pos.buscar-producto') }}?q=${encodeURIComponent(this.busqueda)}`);
                this.resultados = await res.json();
            },

            async buscarClientes() {
                if (this.buscarCliente.length < 2) { this.clientesResultados = []; return; }
                const res = await fetch(`{{ route('emisor.api.clientes.buscar') }}?q=${encodeURIComponent(this.buscarCliente)}`);
                const data = await res.json();
                this.clientesResultados = data.data || data;
            },

            seleccionarCliente(cli) {
                this.clienteId = cli.id;
                this.buscarCliente = cli.razon_social + ' (' + cli.identificacion + ')';
                this.clientesResultados = [];
            },

            agregarProducto(prod) {
                const existe = this.carrito.find(i => i.producto_id === prod.id);
                if (existe) {
                    existe.cantidad++;
                } else {
                    this.carrito.push({
                        producto_id: prod.id,
                        codigo_principal: prod.codigo_principal || '',
                        descripcion: prod.nombre,
                        cantidad: 1,
                        precio_unitario: Number(prod.precio_unitario),
                        descuento: 0,
                        impuesto_iva_id: prod.impuesto_iva_id,
                    });
                }
                this.busqueda = '';
                this.resultados = [];
                this.recalcular();
            },

            eliminarItem(index) {
                this.carrito.splice(index, 1);
                this.recalcular();
            },

            subtotalItem(item) {
                return (item.cantidad * item.precio_unitario) - (item.descuento || 0);
            },

            recalcular() {
                let sub = 0, desc = 0, iva = 0;
                for (const item of this.carrito) {
                    const lineaSub = item.cantidad * item.precio_unitario;
                    const lineaDesc = item.descuento || 0;
                    const base = lineaSub - lineaDesc;
                    const tarifa = this.ivaRates[item.impuesto_iva_id] || 0;
                    sub += lineaSub;
                    desc += lineaDesc;
                    iva += base * (tarifa / 100);
                }
                this.subtotal = sub;
                this.totalDescuento = desc;
                this.totalIva = iva;
                this.total = sub - desc + iva;
            },

            facturar(event) {
                if (this.carrito.length === 0) return;
                this.procesando = true;
                event.target.submit();
            },

            limpiar() {
                this.carrito = [];
                this.busqueda = '';
                this.resultados = [];
                this.montoRecibido = 0;
                this.subtotal = 0;
                this.totalDescuento = 0;
                this.totalIva = 0;
                this.total = 0;
            }
        }
    }
    </script>
    @endpush
</x-emisor-layout>
