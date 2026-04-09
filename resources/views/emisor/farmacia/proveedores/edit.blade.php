<x-emisor-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Editar Proveedor</h2>
            <a href="{{ route('emisor.farmacia.proveedores.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 text-sm">Volver</a>
        </div>
    </x-slot>

    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('emisor.farmacia.proveedores.update', $proveedor) }}">
            @csrf @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Identificacion (RUC/CI)</label>
                    <input type="text" name="identificacion" value="{{ old('identificacion', $proveedor->identificacion) }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                    @error('identificacion') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre / Razon Social</label>
                    <input type="text" name="nombre" value="{{ old('nombre', $proveedor->nombre) }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm" required>
                    @error('nombre') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Direccion</label>
                    <input type="text" name="direccion" value="{{ old('direccion', $proveedor->direccion) }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                    @error('direccion') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Telefono</label>
                    <input type="text" name="telefono" value="{{ old('telefono', $proveedor->telefono) }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                    @error('telefono') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email', $proveedor->email) }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                    @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Persona de Contacto</label>
                    <input type="text" name="contacto" value="{{ old('contacto', $proveedor->contacto) }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                    @error('contacto') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="flex items-center mt-6">
                    <label class="flex items-center">
                        <input type="hidden" name="activo" value="0">
                        <input type="checkbox" name="activo" value="1" {{ old('activo', $proveedor->activo) ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600 shadow-sm">
                        <span class="ml-2 text-sm text-gray-700">Activo</span>
                    </label>
                </div>
            </div>
            <div class="flex justify-end mt-6">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Actualizar Proveedor</button>
            </div>
        </form>
    </div>
</x-emisor-layout>
