<x-admin-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Usuarios</h2>
            <a href="{{ route('admin.usuarios.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 text-sm font-medium">
                + Nuevo Usuario
            </a>
        </div>
    </x-slot>

    {{-- Filtros --}}
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form method="GET" action="{{ route('admin.usuarios.index') }}">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                    <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Username, nombre, email..." class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Rol</label>
                    <select name="rol" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                        <option value="">Todos</option>
                        @foreach($roles as $rol)
                        <option value="{{ $rol->nombre }}" {{ request('rol') == $rol->nombre ? 'selected' : '' }}>{{ $rol->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Emisor</label>
                    <select name="emisor_id" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                        <option value="">Todos</option>
                        @foreach($emisores as $emisor)
                        <option value="{{ $emisor->id }}" {{ request('emisor_id') == $emisor->id ? 'selected' : '' }}>{{ $emisor->razon_social }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                    <select name="activo" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                        <option value="">Todos</option>
                        <option value="1" {{ request('activo') === '1' ? 'selected' : '' }}>Activo</option>
                        <option value="0" {{ request('activo') === '0' ? 'selected' : '' }}>Inactivo</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm">Filtrar</button>
                    <a href="{{ route('admin.usuarios.index') }}" class="bg-gray-200 text-gray-700 px-3 py-2 rounded hover:bg-gray-300 text-sm">Limpiar</a>
                </div>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Username</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rol</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Emisor</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($usuarios as $usuario)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm text-gray-900 font-mono">{{ $usuario->username }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $usuario->nombre_completo }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $usuario->email }}</td>
                        <td class="px-4 py-3 text-sm">
                            @php
                                $rolColors = [
                                    'ROLE_ADMIN' => 'purple',
                                    'ROLE_EMISOR_ADMIN' => 'blue',
                                    'ROLE_EMISOR' => 'gray',
                                ];
                                $rc = $rolColors[$usuario->rol->nombre ?? ''] ?? 'gray';
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $rc }}-100 text-{{ $rc }}-800">{{ $usuario->rol->nombre ?? '-' }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $usuario->emisor->razon_social ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm">
                            @if($usuario->activo)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Activo</span>
                            @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Inactivo</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-right space-x-1">
                            <a href="{{ route('admin.usuarios.edit', $usuario) }}" class="inline-flex items-center px-2.5 py-1.5 bg-yellow-50 text-yellow-700 rounded hover:bg-yellow-100 text-xs font-medium">Editar</a>
                            @if($usuario->id !== auth()->id())
                            <form method="POST" action="{{ route('admin.usuarios.destroy', $usuario) }}" class="inline" onsubmit="return confirm('¿Eliminar usuario {{ $usuario->username }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center px-2.5 py-1.5 bg-red-50 text-red-700 rounded hover:bg-red-100 text-xs font-medium">Eliminar</button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500">No se encontraron usuarios.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t">{{ $usuarios->withQueryString()->links() }}</div>
    </div>
</x-admin-layout>
