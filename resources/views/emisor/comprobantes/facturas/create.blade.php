<x-emisor-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Nueva Factura</h2>
            <a href="{{ route('emisor.comprobantes.facturas.index') }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-100 text-sm font-medium transition-colors duration-150">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Volver
            </a>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('emisor.comprobantes.facturas.store') }}" id="factura-form"
          x-data="{ conGuia: false, conReembolso: false, reembolsos: [], reembolsoIdx: 0 }">
        @csrf
        @include('emisor.comprobantes.partials.validation-errors')

        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 mb-6">
            <div class="flex items-center justify-between mb-5">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Datos Generales</h3>
                </div>
                <div class="flex items-center gap-3 text-sm">
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Fecha Emision</label>
                    <input type="date" name="fecha_emision" value="{{ old('fecha_emision', date('Y-m-d')) }}" class="w-full border-gray-200 rounded-lg shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500" required>
                    @error('fecha_emision') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <x-cliente-search />
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Forma de Pago</label>
                    <select name="forma_pago" class="w-full border-gray-200 rounded-lg shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="01" {{ old('forma_pago', '01') == '01' ? 'selected' : '' }}>Sin utilizacion del sistema financiero</option>
                        <option value="15" {{ old('forma_pago') == '15' ? 'selected' : '' }}>Compensacion de deudas</option>
                        <option value="16" {{ old('forma_pago') == '16' ? 'selected' : '' }}>Tarjeta de debito</option>
                        <option value="17" {{ old('forma_pago') == '17' ? 'selected' : '' }}>Dinero electronico</option>
                        <option value="18" {{ old('forma_pago') == '18' ? 'selected' : '' }}>Tarjeta prepago</option>
                        <option value="19" {{ old('forma_pago') == '19' ? 'selected' : '' }}>Tarjeta de credito</option>
                        <option value="20" {{ old('forma_pago') == '20' ? 'selected' : '' }}>Otros con utilizacion del sistema financiero</option>
                        <option value="21" {{ old('forma_pago') == '21' ? 'selected' : '' }}>Endoso de titulos</option>
                    </select>
                    @error('forma_pago') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Plazo (dias)</label>
                    <input type="number" name="plazo" value="{{ old('plazo') }}" class="w-full border-gray-200 rounded-lg shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500" min="0" placeholder="0">
                    @error('plazo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Checkboxes for special options --}}
            <div class="flex flex-wrap gap-6 mt-5 pt-5 border-t border-gray-100">
                <label class="inline-flex items-center cursor-pointer group">
                    <input type="checkbox" x-model="conGuia" name="con_guia" value="1" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                    <span class="ml-2 text-sm font-medium text-gray-600 group-hover:text-gray-900 transition-colors">Factura con Guia</span>
                </label>
                <label class="inline-flex items-center cursor-pointer group">
                    <input type="checkbox" x-model="conReembolso" name="es_reembolso" value="1" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                    <span class="ml-2 text-sm font-medium text-gray-600 group-hover:text-gray-900 transition-colors">Factura con Reembolso</span>
                </label>
            </div>
        </div>

        {{-- Factura con Guia section --}}
        <div x-show="conGuia" x-cloak class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 mb-6">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-8 h-8 bg-indigo-50 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">Datos Sustitutivos Guia</h3>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Fecha Inicio Transporte *</label>
                    <input type="date" name="guia_fecha_ini_transporte" class="w-full border-gray-200 rounded-lg shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('guia_fecha_ini_transporte') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Fecha Fin Transporte *</label>
                    <input type="date" name="guia_fecha_fin_transporte" class="w-full border-gray-200 rounded-lg shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('guia_fecha_fin_transporte') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Direccion Partida *</label>
                    <input type="text" name="guia_dir_partida" class="w-full border-gray-200 rounded-lg shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Direccion de origen">
                    @error('guia_dir_partida') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Direccion Llegada *</label>
                    <input type="text" name="guia_dir_llegada" class="w-full border-gray-200 rounded-lg shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Direccion de destino">
                    @error('guia_dir_llegada') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <x-transportista-search prefix="guia_" />
            </div>
        </div>

        {{-- Factura con Reembolso section --}}
        <div x-show="conReembolso" x-cloak class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 mb-6">
            <div class="flex justify-between items-center mb-5">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-emerald-50 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Reembolso</h3>
                </div>
                <button type="button" @click="reembolsos.push({tipo_proveedor: '01', identificacion: '', estab: '', pto: '', secuencial: '', fecha: '', num_autorizacion: '', base_0: 0, base_15: 0, iva: 0}); reembolsoIdx++" class="inline-flex items-center gap-1 bg-emerald-600 text-white px-4 py-1.5 rounded-lg text-sm font-medium hover:bg-emerald-700 transition-colors duration-150">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Nuevo
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50/80 border-b border-gray-100">
                            <th class="px-2 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tipo Proveedor</th>
                            <th class="px-2 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Identificacion</th>
                            <th class="px-2 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-16">Estb.</th>
                            <th class="px-2 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-16">Pto</th>
                            <th class="px-2 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-24">Secuencial</th>
                            <th class="px-2 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Fecha</th>
                            <th class="px-2 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Num. Autorizacion</th>
                            <th class="px-2 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-24">Base 0%</th>
                            <th class="px-2 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-24">Base 15%</th>
                            <th class="px-2 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-20">IVA</th>
                            <th class="px-2 py-2.5 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider w-20">Total</th>
                            <th class="px-2 py-2.5 w-10"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(r, idx) in reembolsos" :key="idx">
                            <tr class="border-b border-gray-50 hover:bg-gray-50/50">
                                <td class="px-2 py-1.5">
                                    <select :name="'reembolsos['+idx+'][tipo_proveedor]'" x-model="r.tipo_proveedor" class="w-full border-gray-200 rounded-lg shadow-sm text-xs focus:border-blue-500 focus:ring-blue-500">
                                        <option value="01">PERSONA NATURAL</option>
                                        <option value="02">SOCIEDAD</option>
                                    </select>
                                </td>
                                <td class="px-2 py-1.5"><input type="text" :name="'reembolsos['+idx+'][identificacion_proveedor]'" x-model="r.identificacion" class="w-full border-gray-200 rounded-lg shadow-sm text-xs focus:border-blue-500 focus:ring-blue-500" required></td>
                                <td class="px-2 py-1.5"><input type="text" :name="'reembolsos['+idx+'][estab_doc_reembolso]'" x-model="r.estab" class="w-full border-gray-200 rounded-lg shadow-sm text-xs focus:border-blue-500 focus:ring-blue-500" maxlength="3"></td>
                                <td class="px-2 py-1.5"><input type="text" :name="'reembolsos['+idx+'][pto_emision_doc_reembolso]'" x-model="r.pto" class="w-full border-gray-200 rounded-lg shadow-sm text-xs focus:border-blue-500 focus:ring-blue-500" maxlength="3"></td>
                                <td class="px-2 py-1.5"><input type="text" :name="'reembolsos['+idx+'][secuencial_doc_reembolso]'" x-model="r.secuencial" class="w-full border-gray-200 rounded-lg shadow-sm text-xs focus:border-blue-500 focus:ring-blue-500" maxlength="9"></td>
                                <td class="px-2 py-1.5"><input type="date" :name="'reembolsos['+idx+'][fecha_emision_doc_reembolso]'" x-model="r.fecha" class="w-full border-gray-200 rounded-lg shadow-sm text-xs focus:border-blue-500 focus:ring-blue-500"></td>
                                <td class="px-2 py-1.5"><input type="text" :name="'reembolsos['+idx+'][numero_autorizacion_doc_reembolso]'" x-model="r.num_autorizacion" class="w-full border-gray-200 rounded-lg shadow-sm text-xs focus:border-blue-500 focus:ring-blue-500"></td>
                                <td class="px-2 py-1.5"><input type="number" :name="'reembolsos['+idx+'][base_0]'" x-model.number="r.base_0" step="any" min="0" class="w-full border-gray-200 rounded-lg shadow-sm text-xs focus:border-blue-500 focus:ring-blue-500"></td>
                                <td class="px-2 py-1.5"><input type="number" :name="'reembolsos['+idx+'][base_15]'" x-model.number="r.base_15" step="any" min="0" class="w-full border-gray-200 rounded-lg shadow-sm text-xs focus:border-blue-500 focus:ring-blue-500"></td>
                                <td class="px-2 py-1.5"><input type="number" :name="'reembolsos['+idx+'][iva]'" x-model.number="r.iva" step="any" min="0" class="w-full border-gray-200 rounded-lg shadow-sm text-xs focus:border-blue-500 focus:ring-blue-500"></td>
                                <td class="px-2 py-1.5 text-right text-xs font-semibold text-gray-700" x-text="'$' + ((r.base_0 || 0) + (r.base_15 || 0) + (r.iva || 0)).toFixed(2)"></td>
                                <td class="px-2 py-1.5"><button type="button" @click="reembolsos.splice(idx, 1)" class="text-red-400 hover:text-red-600 transition-colors">&times;</button></td>
                            </tr>
                        </template>
                        <tr x-show="reembolsos.length === 0">
                            <td colspan="12" class="px-2 py-8 text-center text-gray-400 text-sm">Haga clic en "+ Nuevo" para agregar reembolsos</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        @include('emisor.comprobantes.partials.productos-table')

        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 mb-6">
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Observaciones</label>
            <textarea name="observaciones" rows="2" class="w-full border-gray-200 rounded-lg shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">{{ old('observaciones') }}</textarea>
            @error('observaciones') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="flex justify-end">
            <button type="submit" class="inline-flex items-center gap-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white px-8 py-2.5 rounded-xl hover:from-blue-700 hover:to-blue-800 font-medium shadow-sm shadow-blue-500/25 transition-all duration-150">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Guardar Factura
            </button>
        </div>
    </form>

</x-emisor-layout>
