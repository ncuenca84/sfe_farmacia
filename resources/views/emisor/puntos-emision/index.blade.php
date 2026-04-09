<x-emisor-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Puntos de Emision</h2>
            <a href="{{ route('emisor.configuracion.puntos-emision.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm">Nuevo Punto de Emision</a>
        </div>
    </x-slot>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Establecimiento</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Codigo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Descripcion</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Activo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($ptosEmision as $pto)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $pto->establecimiento->codigo ?? 'N/A' }} - {{ $pto->establecimiento->nombre ?? $pto->establecimiento->direccion ?? '' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-mono">{{ $pto->codigo }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $pto->nombre ?? '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($pto->activo)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Activo</span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Inactivo</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <a href="{{ route('emisor.configuracion.puntos-emision.edit', $pto) }}" class="text-blue-600 hover:text-blue-900">Editar</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">No hay puntos de emision registrados.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 border-t">
            {{ $ptosEmision->links() }}
        </div>
    </div>
</x-emisor-layout>
