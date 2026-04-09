<x-admin-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Nueva Página Legal</h2>
            <a href="{{ route('admin.paginas-legales.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-md text-sm hover:bg-gray-600">Volver</a>
        </div>
    </x-slot>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 max-w-4xl">
        <form method="POST" action="{{ route('admin.paginas-legales.store') }}">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="titulo" class="block text-sm font-medium text-gray-700 mb-1">Título</label>
                    <input type="text" name="titulo" id="titulo" value="{{ old('titulo') }}" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                    @error('titulo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="slug" class="block text-sm font-medium text-gray-700 mb-1">Slug (URL)</label>
                    <input type="text" name="slug" id="slug" value="{{ old('slug') }}" required placeholder="ejemplo: terminos-y-condiciones" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                    <p class="text-xs text-gray-400 mt-1">Solo letras minúsculas, números y guiones</p>
                    @error('slug') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mb-6">
                <label for="contenido" class="block text-sm font-medium text-gray-700 mb-1">Contenido (HTML)</label>
                <textarea name="contenido" id="contenido" rows="20" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm font-mono">{{ old('contenido') }}</textarea>
                @error('contenido') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="mb-6">
                <label class="inline-flex items-center">
                    <input type="checkbox" name="activo" value="1" {{ old('activo', true) ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                    <span class="ml-2 text-sm text-gray-700">Activo</span>
                </label>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md text-sm hover:bg-blue-700">Guardar</button>
            </div>
        </form>
    </div>
</x-admin-layout>
