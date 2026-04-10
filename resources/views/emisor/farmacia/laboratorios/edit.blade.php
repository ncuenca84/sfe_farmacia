<x-emisor-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Editar Laboratorio</h2>
            <a href="{{ route('emisor.farmacia.laboratorios.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 text-sm">Volver</a>
        </div>
    </x-slot>

    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('emisor.farmacia.laboratorios.update', $laboratorio) }}">
            @csrf @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                    <input type="text" name="nombre" value="{{ old('nombre', $laboratorio->nombre) }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm" required>
                    @error('nombre') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pais</label>
                    <input type="text" name="pais" value="{{ old('pais', $laboratorio->pais) }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                    @error('pais') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="flex items-center">
                    <label class="flex items-center">
                        <input type="hidden" name="activo" value="0">
                        <input type="checkbox" name="activo" value="1" {{ old('activo', $laboratorio->activo) ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600 shadow-sm">
                        <span class="ml-2 text-sm text-gray-700">Activo</span>
                    </label>
                </div>
            </div>
            <div class="flex justify-end mt-6">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Actualizar Laboratorio</button>
            </div>
        </form>
    </div>
</x-emisor-layout>
