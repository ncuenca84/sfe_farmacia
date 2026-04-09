<x-emisor-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Establecimientos</h2>
            <a href="{{ route('emisor.configuracion.establecimientos.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm">Nuevo Establecimiento</a>
        </div>
    </x-slot>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Codigo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                    @if($tieneMultiplesUnidades)
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Linea de negocio</th>
                    @endif
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Direccion</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Inventario</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Activo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($establecimientos as $est)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-mono">{{ $est->codigo }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $est->nombre ?? '-' }}</td>
                    @if($tieneMultiplesUnidades)
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $est->unidadNegocio?->nombre ?? '-' }}</td>
                    @endif
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $est->direccion }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($est->maneja_inventario)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Si</span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">No</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($est->activo)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Activo</span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Inactivo</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <a href="{{ route('emisor.configuracion.establecimientos.edit', $est) }}" class="text-blue-600 hover:text-blue-900">Editar</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ $tieneMultiplesUnidades ? 7 : 6 }}" class="px-6 py-4 text-center text-sm text-gray-500">No hay establecimientos registrados.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 border-t">
            {{ $establecimientos->links() }}
        </div>
    </div>
</x-emisor-layout>
