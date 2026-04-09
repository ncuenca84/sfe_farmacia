<x-emisor-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Categorias de Productos</h2>
            <a href="{{ route('emisor.farmacia.categorias.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm">Nueva Categoria</a>
        </div>
    </x-slot>

    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form method="GET" action="{{ route('emisor.farmacia.categorias.index') }}" class="flex gap-4">
            <div class="flex-1">
                <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar categoria..." class="w-full border-gray-300 rounded-md shadow-sm text-sm">
            </div>
            <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded hover:bg-gray-700 text-sm">Buscar</button>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Productos</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Estado</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($categorias as $categoria)
                <tr>
                    <td class="px-6 py-4 text-sm text-gray-900">{{ $categoria->nombre }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500 text-center">{{ $categoria->productos_count }}</td>
                    <td class="px-6 py-4 text-center">
                        <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full {{ $categoria->activo ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $categoria->activo ? 'Activa' : 'Inactiva' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                        <a href="{{ route('emisor.farmacia.categorias.edit', $categoria) }}" class="inline-flex items-center px-3 py-1.5 bg-gray-600 text-white text-xs font-medium rounded hover:bg-gray-700">Editar</a>
                        @if($categoria->productos_count === 0)
                        <form method="POST" action="{{ route('emisor.farmacia.categorias.destroy', $categoria) }}" class="inline" onsubmit="return confirm('Eliminar esta categoria?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-red-600 text-white text-xs font-medium rounded hover:bg-red-700">Eliminar</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">No se encontraron categorias.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 border-t">
            {{ $categorias->withQueryString()->links() }}
        </div>
    </div>
</x-emisor-layout>
