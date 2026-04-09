<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-2">
            <a href="{{ route('admin.planes.index') }}" class="text-indigo-600 hover:text-indigo-800">&larr; Planes</a>
            <span class="text-gray-400">/</span>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Nuevo Plan</h2>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('admin.planes.store') }}" class="max-w-2xl">
        @csrf

        <div class="bg-white shadow rounded-lg p-6 space-y-6">
            <div>
                <x-input-label for="nombre" value="Nombre" />
                <x-text-input id="nombre" name="nombre" type="text" class="mt-1 block w-full" :value="old('nombre')" required />
                <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="cant_comprobante" value="Cantidad de Comprobantes (0 = Ilimitado)" />
                <x-text-input id="cant_comprobante" name="cant_comprobante" type="number" min="0" class="mt-1 block w-full" :value="old('cant_comprobante', 0)" required />
                <x-input-error :messages="$errors->get('cant_comprobante')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="tipo_periodo" value="Tipo de Periodo" />
                <select id="tipo_periodo" name="tipo_periodo" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required onchange="document.getElementById('dias_container').style.display = this.value === 'DIAS' ? 'block' : 'none'">
                    <option value="MENSUAL" {{ old('tipo_periodo') == 'MENSUAL' ? 'selected' : '' }}>MENSUAL</option>
                    <option value="ANUAL" {{ old('tipo_periodo') == 'ANUAL' ? 'selected' : '' }}>ANUAL</option>
                    <option value="DIAS" {{ old('tipo_periodo') == 'DIAS' ? 'selected' : '' }}>DIAS</option>
                </select>
                <x-input-error :messages="$errors->get('tipo_periodo')" class="mt-2" />
            </div>

            <div id="dias_container" style="{{ old('tipo_periodo') == 'DIAS' ? '' : 'display: none;' }}">
                <x-input-label for="dias" value="Dias" />
                <x-text-input id="dias" name="dias" type="number" min="1" class="mt-1 block w-full" :value="old('dias')" />
                <x-input-error :messages="$errors->get('dias')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="precio" value="Precio" />
                <x-text-input id="precio" name="precio" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('precio')" required />
                <x-input-error :messages="$errors->get('precio')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="observaciones" value="Observaciones" />
                <textarea id="observaciones" name="observaciones" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('observaciones') }}</textarea>
                <x-input-error :messages="$errors->get('observaciones')" class="mt-2" />
            </div>

            <div class="flex justify-end">
                <a href="{{ route('admin.planes.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md text-sm hover:bg-gray-400 mr-2">Cancelar</a>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-6 rounded-md text-sm">Crear Plan</button>
            </div>
        </div>
    </form>
</x-admin-layout>
