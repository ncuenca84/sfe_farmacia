<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.establecimientos.index') }}" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <h2 class="text-xl font-semibold text-gray-800">Nuevo Establecimiento</h2>
        </div>
    </x-slot>

    <div class="max-w-2xl">
        <form method="POST" action="{{ route('admin.establecimientos.store') }}">
            @csrf
            <div class="bg-white rounded-lg shadow p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Emisor *</label>
                    <select name="emisor_id" required class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                        <option value="">Seleccione un emisor</option>
                        @foreach($emisores as $emisor)
                        <option value="{{ $emisor->id }}" {{ old('emisor_id') == $emisor->id ? 'selected' : '' }}>{{ $emisor->ruc }} - {{ $emisor->razon_social }}</option>
                        @endforeach
                    </select>
                    @error('emisor_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Codigo *</label>
                        <input type="text" name="codigo" value="{{ old('codigo') }}" required maxlength="3" placeholder="001" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                        @error('codigo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                        <input type="text" name="nombre" value="{{ old('nombre') }}" placeholder="Sucursal principal" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Dirección *</label>
                    <input type="text" name="direccion" value="{{ old('direccion') }}" required class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                    @error('direccion') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="flex items-center gap-6">
                    <label class="inline-flex items-center">
                        <input type="hidden" name="activo" value="0">
                        <input type="checkbox" name="activo" value="1" checked class="rounded border-gray-300 text-indigo-600 shadow-sm">
                        <span class="ml-2 text-sm text-gray-700">Activo</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="hidden" name="maneja_inventario" value="0">
                        <input type="checkbox" name="maneja_inventario" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm">
                        <span class="ml-2 text-sm text-gray-700">Maneja inventario</span>
                    </label>
                </div>
            </div>
            <div class="flex justify-end mt-4 space-x-3">
                <a href="{{ route('admin.establecimientos.index') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 text-sm font-medium">Cancelar</a>
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 text-sm font-medium">Crear</button>
            </div>
        </form>
    </div>
</x-admin-layout>
