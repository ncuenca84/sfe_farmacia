<x-emisor-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Clientes</h2>
            <a href="{{ route('emisor.configuracion.clientes.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm">Nuevo Cliente</a>
        </div>
    </x-slot>

    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form method="GET" action="{{ route('emisor.configuracion.clientes.index') }}" class="flex gap-4">
            <div class="flex-1">
                <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar por identificacion, razon social, email..." class="w-full border-gray-300 rounded-md shadow-sm text-sm">
            </div>
            <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded hover:bg-gray-700 text-sm">Buscar</button>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Identificacion</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Razon Social</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Telefono</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($clientes as $cliente)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $cliente->identificacion }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $cliente->razon_social }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $cliente->email ?? '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $cliente->telefono ?? '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                        <a href="{{ route('emisor.configuracion.clientes.show', $cliente) }}" class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700">Ver</a>
                        <a href="{{ route('emisor.configuracion.clientes.edit', $cliente) }}" class="inline-flex items-center px-3 py-1.5 bg-gray-600 text-white text-xs font-medium rounded hover:bg-gray-700">Editar</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">No se encontraron clientes.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 border-t">
            {{ $clientes->withQueryString()->links() }}
        </div>
    </div>
</x-emisor-layout>
