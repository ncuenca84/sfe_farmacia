<x-emisor-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Nueva Nota de Credito</h2>
            <a href="{{ route('emisor.comprobantes.notas-credito.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 text-sm">Volver</a>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('emisor.comprobantes.notas-credito.store') }}" id="nc-form">
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
                <x-cliente-search />
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Motivo</label>
                    <input type="text" name="motivo" value="{{ old('motivo') }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm" required placeholder="Motivo de la nota de credito">
                    @error('motivo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Documento Modificado</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo Documento</label>
                    <select name="cod_doc_modificado" class="w-full border-gray-300 rounded-md shadow-sm text-sm" required>
                        <option value="01" {{ old('cod_doc_modificado') == '01' ? 'selected' : '' }}>01 - Factura</option>
                    </select>
                    @error('cod_doc_modificado') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Numero Doc. Modificado</label>
                    <input type="text" name="num_doc_modificado" value="{{ old('num_doc_modificado', '001-001-000000001') }}" placeholder="001-001-000000001" class="w-full border-gray-300 rounded-md shadow-sm text-sm" required>
                    <p class="text-xs text-amber-600 mt-1">* Valor referencial. Verifique y ajuste segun el documento real.</p>
                    @error('num_doc_modificado') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Emision Doc. Sustento</label>
                    <input type="date" name="fecha_emision_doc_sustento" value="{{ old('fecha_emision_doc_sustento') }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm" required>
                    @error('fecha_emision_doc_sustento') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
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
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Guardar Nota de Credito</button>
        </div>
    </form>

</x-emisor-layout>
