<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-2">
            <a href="{{ route('admin.impuesto-ices.index') }}" class="text-indigo-600 hover:text-indigo-800">&larr; ICE</a>
            <span class="text-gray-400">/</span>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Nuevo Impuesto ICE</h2>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('admin.impuesto-ices.store') }}" class="max-w-2xl">
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
                <x-input-label for="tarifa" value="Tarifa" />
                <x-text-input id="tarifa" name="tarifa" type="number" step="0.01" class="mt-1 block w-full" :value="old('tarifa')" required />
                <x-input-error :messages="$errors->get('tarifa')" class="mt-2" />
            </div>

            <div class="flex justify-end">
                <a href="{{ route('admin.impuesto-ices.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md text-sm hover:bg-gray-400 mr-2">Cancelar</a>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-6 rounded-md text-sm">Crear ICE</button>
            </div>
        </div>
    </form>
</x-admin-layout>
