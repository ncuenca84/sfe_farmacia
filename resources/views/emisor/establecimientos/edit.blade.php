<x-emisor-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Editar Establecimiento {{ $establecimiento->codigo }}</h2>
            <a href="{{ route('emisor.configuracion.establecimientos.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 text-sm">Volver</a>
        </div>
    </x-slot>

    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('emisor.configuracion.establecimientos.update', $establecimiento) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Codigo (3 digitos)</label>
                    <input type="text" name="codigo" value="{{ old('codigo', $establecimiento->codigo) }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm" required maxlength="3">
                    @error('codigo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                    <input type="text" name="nombre" value="{{ old('nombre', $establecimiento->nombre) }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                    @error('nombre') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Direccion</label>
                    <input type="text" name="direccion" value="{{ old('direccion', $establecimiento->direccion) }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm" required>
                    @error('direccion') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Linea de negocio --}}
                @if($unidadesNegocio->count() > 1)
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Linea de negocio</label>
                    @if(auth()->user()->unidad_negocio_id)
                        <input type="text" value="{{ $unidadesNegocio->firstWhere('id', auth()->user()->unidad_negocio_id)?->nombre ?? '-' }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm bg-gray-100" disabled>
                    @else
                        <select name="unidad_negocio_id" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                            <option value="">-- Sin asignar --</option>
                            @foreach($unidadesNegocio as $unidad)
                                <option value="{{ $unidad->id }}" {{ old('unidad_negocio_id', $establecimiento->unidad_negocio_id) == $unidad->id ? 'selected' : '' }}>{{ $unidad->nombre }}</option>
                            @endforeach
                        </select>
                    @endif
                    @error('unidad_negocio_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                @endif

                {{-- Logo del establecimiento --}}
                <div class="md:col-span-2" x-data="{ preview: {{ $establecimiento->logo_path ? '\'exists\'' : 'null' }}, removelogo: false }">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Logo del establecimiento <span class="text-gray-400 font-normal">(opcional)</span></label>

                    @if($establecimiento->logo_path && file_exists($establecimiento->logo_path))
                        <div class="mb-3 flex items-center gap-3" x-show="!removelogo">
                            <img src="data:{{ mime_content_type($establecimiento->logo_path) }};base64,{{ base64_encode(file_get_contents($establecimiento->logo_path)) }}" class="h-16 max-w-[200px] object-contain border rounded p-1 bg-white">
                            <button type="button" @click="removelogo = true" class="text-red-500 hover:text-red-700 text-sm underline">Quitar logo</button>
                        </div>
                        <input type="hidden" name="quitar_logo" :value="removelogo ? '1' : '0'">
                    @endif

                    <div class="flex items-center gap-4">
                        <div class="flex-1">
                            <input type="file" name="logo" accept="image/*"
                                class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            <p class="text-xs text-gray-400 mt-1">Imagen PNG o JPG, max 2MB. Si no se sube logo, se usara el del emisor en los comprobantes.</p>
                        </div>
                    </div>
                    @error('logo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="flex gap-6">
                    <label class="flex items-center">
                        <input type="checkbox" name="activo" value="1" {{ old('activo', $establecimiento->activo) ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600 shadow-sm">
                        <span class="ml-2 text-sm text-gray-700">Activo</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="maneja_inventario" value="1" {{ old('maneja_inventario', $establecimiento->maneja_inventario) ? 'checked' : '' }} class="rounded border-gray-300 text-green-600 shadow-sm">
                        <span class="ml-2 text-sm text-gray-700">Maneja Inventario</span>
                    </label>
                </div>
            </div>

            <div class="flex justify-end mt-6">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Actualizar Establecimiento</button>
            </div>
        </form>
    </div>
</x-emisor-layout>
