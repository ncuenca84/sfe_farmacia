<x-emisor-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Nuevo Usuario</h2>
            <a href="{{ route('emisor.configuracion.usuarios.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 text-sm">Volver</a>
        </div>
    </x-slot>

    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('emisor.configuracion.usuarios.store') }}">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Usuario (login)</label>
                    <input type="text" name="username" value="{{ old('username') }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm" required>
                    @error('username') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm" required>
                    @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                    <input type="text" name="nombre" value="{{ old('nombre') }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm" required>
                    @error('nombre') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Apellido</label>
                    <input type="text" name="apellido" value="{{ old('apellido') }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm" required>
                    @error('apellido') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
                    <input type="password" name="password" class="w-full border-gray-300 rounded-md shadow-sm text-sm" required>
                    @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar Contraseña</label>
                    <input type="password" name="password_confirmation" class="w-full border-gray-300 rounded-md shadow-sm text-sm" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Rol</label>
                    <select name="rol_id" class="w-full border-gray-300 rounded-md shadow-sm text-sm" required>
                        <option value="">Seleccionar...</option>
                        @foreach($roles as $rol)
                            <option value="{{ $rol->id }}" {{ old('rol_id') == $rol->id ? 'selected' : '' }}>{{ $rol->descripcion }}</option>
                        @endforeach
                    </select>
                    @error('rol_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                @if($unidadesNegocio->count() > 1)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Linea de negocio</label>
                    <select name="unidad_negocio_id" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                        <option value="">-- Todas (sin restriccion) --</option>
                        @foreach($unidadesNegocio as $unidad)
                            <option value="{{ $unidad->id }}" {{ old('unidad_negocio_id') == $unidad->id ? 'selected' : '' }}>{{ $unidad->nombre }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-400 mt-1">Si se asigna, el usuario solo vera datos de esa linea de negocio.</p>
                    @error('unidad_negocio_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                @endif
            </div>

            <div class="flex justify-end mt-6">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Crear Usuario</button>
            </div>
        </form>
    </div>
</x-emisor-layout>
