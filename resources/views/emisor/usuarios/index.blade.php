<x-emisor-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Usuarios</h2>
            <a href="{{ route('emisor.configuracion.usuarios.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm">Nuevo Usuario</a>
        </div>
    </x-slot>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Usuario</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rol</th>
                    @if($tieneMultiplesUnidades)
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Linea de negocio</th>
                    @endif
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($usuarios as $usuario)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $usuario->username }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $usuario->nombre_completo }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $usuario->email }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $usuario->rol->descripcion ?? $usuario->rol->nombre }}</td>
                    @if($tieneMultiplesUnidades)
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $usuario->unidadNegocio?->nombre ?? 'Todas' }}</td>
                    @endif
                    <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                        <a href="{{ route('emisor.configuracion.usuarios.show', $usuario) }}" class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700">Ver</a>
                        <a href="{{ route('emisor.configuracion.usuarios.edit', $usuario) }}" class="inline-flex items-center px-3 py-1.5 bg-gray-600 text-white text-xs font-medium rounded hover:bg-gray-700">Editar</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ $tieneMultiplesUnidades ? 6 : 5 }}" class="px-6 py-4 text-center text-sm text-gray-500">No hay usuarios registrados.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 border-t">
            {{ $usuarios->links() }}
        </div>
    </div>
</x-emisor-layout>
