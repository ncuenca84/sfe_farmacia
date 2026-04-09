<x-emisor-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-2">
            <a href="{{ route('emisor.codigos-retencion.index') }}" class="text-indigo-600 hover:text-indigo-800">&larr; Codigos de Retencion</a>
            <span class="text-gray-400">/</span>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Nuevo Codigo de Retencion</h2>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('emisor.codigos-retencion.store') }}" class="max-w-2xl">
        @csrf

        <div class="bg-white shadow rounded-lg p-6 space-y-6">
            <div>
                <label for="tipo" class="block text-sm font-medium text-gray-700">Tipo</label>
                <select id="tipo" name="tipo" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                    <option value="">Seleccione</option>
                    <option value="RENTA" {{ old('tipo') == 'RENTA' ? 'selected' : '' }}>RENTA</option>
                    <option value="IVA" {{ old('tipo') == 'IVA' ? 'selected' : '' }}>IVA</option>
                    <option value="ISD" {{ old('tipo') == 'ISD' ? 'selected' : '' }}>ISD</option>
                </select>
                @error('tipo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="codigo" class="block text-sm font-medium text-gray-700">Codigo</label>
                <input id="codigo" name="codigo" type="text" value="{{ old('codigo') }}" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                @error('codigo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="descripcion" class="block text-sm font-medium text-gray-700">Descripcion</label>
                <textarea id="descripcion" name="descripcion" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>{{ old('descripcion') }}</textarea>
                @error('descripcion') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="porcentaje" class="block text-sm font-medium text-gray-700">Porcentaje</label>
                <input id="porcentaje" name="porcentaje" type="number" step="0.01" value="{{ old('porcentaje') }}" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                @error('porcentaje') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex justify-end">
                <a href="{{ route('emisor.codigos-retencion.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md text-sm hover:bg-gray-400 mr-2">Cancelar</a>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-6 rounded-md text-sm">Crear Codigo</button>
            </div>
        </div>
    </form>
</x-emisor-layout>
