<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.usuarios.index') }}" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <h2 class="text-xl font-semibold text-gray-800">Editar Usuario: {{ $usuario->username }}</h2>
        </div>
    </x-slot>

    <div class="max-w-2xl">
        <form method="POST" action="{{ route('admin.usuarios.update', $usuario) }}">
            @csrf
            @method('PUT')

            <div class="bg-white rounded-lg shadow p-6 space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Username *</label>
                        <input type="text" name="username" value="{{ old('username', $usuario->username) }}" required class="w-full border-gray-300 rounded-md shadow-sm text-sm @error('username') border-red-500 @enderror">
                        @error('username') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                        <input type="email" name="email" value="{{ old('email', $usuario->email) }}" required class="w-full border-gray-300 rounded-md shadow-sm text-sm @error('email') border-red-500 @enderror">
                        @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
                        <input type="text" name="nombre" value="{{ old('nombre', $usuario->nombre) }}" required class="w-full border-gray-300 rounded-md shadow-sm text-sm @error('nombre') border-red-500 @enderror">
                        @error('nombre') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Apellido *</label>
                        <input type="text" name="apellido" value="{{ old('apellido', $usuario->apellido) }}" required class="w-full border-gray-300 rounded-md shadow-sm text-sm @error('apellido') border-red-500 @enderror">
                        @error('apellido') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nueva Contraseña</label>
                        <input type="password" name="password" class="w-full border-gray-300 rounded-md shadow-sm text-sm @error('password') border-red-500 @enderror" placeholder="Dejar vacío para no cambiar">
                        @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar Contraseña</label>
                        <input type="password" name="password_confirmation" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Rol *</label>
                        <select name="rol_id" required class="w-full border-gray-300 rounded-md shadow-sm text-sm @error('rol_id') border-red-500 @enderror">
                            @foreach($roles as $rol)
                            <option value="{{ $rol->id }}" {{ old('rol_id', $usuario->rol_id) == $rol->id ? 'selected' : '' }}>{{ $rol->nombre }}</option>
                            @endforeach
                        </select>
                        @error('rol_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Emisor</label>
                        <select name="emisor_id" class="w-full border-gray-300 rounded-md shadow-sm text-sm @error('emisor_id') border-red-500 @enderror">
                            <option value="">Sin emisor (Admin global)</option>
                            @foreach($emisores as $emisor)
                            <option value="{{ $emisor->id }}" {{ old('emisor_id', $usuario->emisor_id) == $emisor->id ? 'selected' : '' }}>{{ $emisor->ruc }} - {{ $emisor->razon_social }}</option>
                            @endforeach
                        </select>
                        @error('emisor_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex items-center">
                    <input type="hidden" name="activo" value="0">
                    <input type="checkbox" name="activo" value="1" id="activo" {{ old('activo', $usuario->activo) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm">
                    <label for="activo" class="ml-2 text-sm text-gray-700">Usuario activo</label>
                </div>

                @if($usuario->created_at)
                <div class="pt-4 border-t text-xs text-gray-400">
                    Creado: {{ $usuario->created_at->format('d/m/Y H:i') }}
                    @if($usuario->updated_at)
                    | Actualizado: {{ $usuario->updated_at->format('d/m/Y H:i') }}
                    @endif
                </div>
                @endif
            </div>

            <div class="flex justify-end mt-4 space-x-3">
                <a href="{{ route('admin.usuarios.index') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 text-sm font-medium">Cancelar</a>
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 text-sm font-medium">Guardar Cambios</button>
            </div>
        </form>
    </div>
</x-admin-layout>
