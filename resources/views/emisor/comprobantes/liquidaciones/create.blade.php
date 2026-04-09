<x-emisor-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Nueva Liquidacion de Compra</h2>
            <a href="{{ route('emisor.comprobantes.liquidaciones.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 text-sm">Volver</a>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('emisor.comprobantes.liquidaciones.store') }}" id="liquidacion-form">
        @csrf
        @include('emisor.comprobantes.partials.validation-errors')

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Datos Generales</h3>
                <div class="flex items-center gap-3 text-sm">
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Emision</label>
                    <input type="date" name="fecha_emision" value="{{ old('fecha_emision', date('Y-m-d')) }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm" required>
                    @error('fecha_emision') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <x-cliente-search label="Proveedor" />
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Forma de Pago</label>
                    <select name="forma_pago" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
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
            </div>
        </div>

        @include('emisor.comprobantes.partials.productos-table')

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-1">Observaciones</label>
            <textarea name="observaciones" rows="2" class="w-full border-gray-300 rounded-md shadow-sm text-sm">{{ old('observaciones') }}</textarea>
            @error('observaciones') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="flex justify-end">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Guardar Liquidacion</button>
        </div>
    </form>

</x-emisor-layout>
