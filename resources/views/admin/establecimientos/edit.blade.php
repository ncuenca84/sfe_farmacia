<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.establecimientos.index', ['emisor_id' => $establecimiento->emisor_id]) }}" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <h2 class="text-xl font-semibold text-gray-800">Editar Establecimiento: {{ $establecimiento->codigo }} - {{ $establecimiento->emisor->razon_social }}</h2>
        </div>
    </x-slot>

    <div class="max-w-2xl">
        <form method="POST" action="{{ route('admin.establecimientos.update', $establecimiento) }}">
            @csrf
            @method('PUT')
            <div class="bg-white rounded-lg shadow p-6 space-y-4">
                <div class="bg-gray-50 rounded-md p-3 border">
                    <p class="text-sm text-gray-600"><span class="font-medium">Emisor:</span> {{ $establecimiento->emisor->ruc }} - {{ $establecimiento->emisor->razon_social }}</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Codigo *</label>
                        <input type="text" name="codigo" value="{{ old('codigo', $establecimiento->codigo) }}" required maxlength="3" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                        @error('codigo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                        <input type="text" name="nombre" value="{{ old('nombre', $establecimiento->nombre) }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Dirección *</label>
                    <input type="text" name="direccion" value="{{ old('direccion', $establecimiento->direccion) }}" required class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                    @error('direccion') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="flex items-center gap-6">
                    <label class="inline-flex items-center">
                        <input type="hidden" name="activo" value="0">
                        <input type="checkbox" name="activo" value="1" {{ old('activo', $establecimiento->activo) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm">
                        <span class="ml-2 text-sm text-gray-700">Activo</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="hidden" name="maneja_inventario" value="0">
                        <input type="checkbox" name="maneja_inventario" value="1" {{ old('maneja_inventario', $establecimiento->maneja_inventario) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm">
                        <span class="ml-2 text-sm text-gray-700">Maneja inventario</span>
                    </label>
                </div>
            </div>
            <div class="flex justify-end mt-4 space-x-3">
                <a href="{{ route('admin.establecimientos.index', ['emisor_id' => $establecimiento->emisor_id]) }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 text-sm font-medium">Cancelar</a>
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 text-sm font-medium">Guardar Cambios</button>
            </div>
        </form>
    </div>
</x-admin-layout>
