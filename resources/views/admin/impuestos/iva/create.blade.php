<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-2">
            <a href="{{ route('admin.impuesto-ivas.index') }}" class="text-indigo-600 hover:text-indigo-800">&larr; IVA</a>
            <span class="text-gray-400">/</span>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Nuevo Impuesto IVA</h2>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('admin.impuesto-ivas.store') }}" class="max-w-2xl">
        @csrf

        <div class="bg-white shadow rounded-lg p-6 space-y-6">
            <div>
                <x-input-label for="codigo_porcentaje" value="Codigo Porcentaje" />
                <x-text-input id="codigo_porcentaje" name="codigo_porcentaje" type="text" class="mt-1 block w-full" :value="old('codigo_porcentaje')" required />
                <x-input-error :messages="$errors->get('codigo_porcentaje')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="nombre" value="Nombre" />
                <x-text-input id="nombre" name="nombre" type="text" class="mt-1 block w-full" :value="old('nombre')" required />
                <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="tarifa" value="Tarifa (%)" />
                <x-text-input id="tarifa" name="tarifa" type="number" step="0.01" class="mt-1 block w-full" :value="old('tarifa')" required />
                <x-input-error :messages="$errors->get('tarifa')" class="mt-2" />
            </div>

            <div>
                <label class="flex items-center">
                    <input type="checkbox" name="activo" value="1" {{ old('activo', true) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                    <span class="ml-2 text-sm text-gray-700">Activo</span>
                </label>
            </div>

            <div>
                <x-input-label for="fecha_vigencia_desde" value="Vigencia Desde" />
                <x-text-input id="fecha_vigencia_desde" name="fecha_vigencia_desde" type="date" class="mt-1 block w-full" :value="old('fecha_vigencia_desde')" />
                <x-input-error :messages="$errors->get('fecha_vigencia_desde')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="fecha_vigencia_hasta" value="Vigencia Hasta" />
                <x-text-input id="fecha_vigencia_hasta" name="fecha_vigencia_hasta" type="date" class="mt-1 block w-full" :value="old('fecha_vigencia_hasta')" />
                <x-input-error :messages="$errors->get('fecha_vigencia_hasta')" class="mt-2" />
            </div>

            <div class="flex justify-end">
                <a href="{{ route('admin.impuesto-ivas.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md text-sm hover:bg-gray-400 mr-2">Cancelar</a>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-6 rounded-md text-sm">Crear IVA</button>
            </div>
        </div>
    </form>
</x-admin-layout>
