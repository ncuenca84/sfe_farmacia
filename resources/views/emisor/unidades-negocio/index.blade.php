<x-emisor-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Lineas de Negocio</h2>
            <a href="{{ route('emisor.configuracion.unidades-negocio.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm">Nueva Linea</a>
        </div>
    </x-slot>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Establecimientos</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Usuarios</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($unidades as $unidad)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">{{ $unidad->nombre }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $unidad->establecimientos_count }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $unidad->users_count }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($unidad->activo)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Activa</span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Inactiva</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <a href="{{ route('emisor.configuracion.unidades-negocio.edit', $unidad) }}" class="text-blue-600 hover:text-blue-900">Editar</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4 bg-blue-50 border border-blue-200 rounded-lg p-4 text-sm text-blue-800">
        <p class="font-medium mb-1">Como usar las lineas de negocio:</p>
        <ol class="list-decimal list-inside space-y-1 text-blue-700">
            <li>Cree una linea de negocio por cada giro o actividad diferente.</li>
            <li>Asigne cada establecimiento a su linea de negocio (Configuracion > Establecimientos > Editar).</li>
            <li>Asigne cada usuario a su linea de negocio (Configuracion > Usuarios > Editar). Los usuarios sin linea asignada ven todo.</li>
        </ol>
    </div>
</x-emisor-layout>
