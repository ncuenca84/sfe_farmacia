<x-emisor-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Establecimiento {{ $establecimiento->codigo }}</h2>
            <a href="{{ route('emisor.configuracion.establecimientos.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 text-sm">Volver</a>
        </div>
    </x-slot>

    <div class="bg-white rounded-lg shadow p-6">
        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div><dt class="text-gray-500 font-medium">Codigo:</dt><dd class="text-gray-900 mt-1">{{ $establecimiento->codigo }}</dd></div>
            <div><dt class="text-gray-500 font-medium">Nombre:</dt><dd class="text-gray-900 mt-1">{{ $establecimiento->nombre ?? '-' }}</dd></div>
            @if($establecimiento->unidadNegocio)
            <div><dt class="text-gray-500 font-medium">Linea de negocio:</dt><dd class="text-gray-900 mt-1">{{ $establecimiento->unidadNegocio->nombre }}</dd></div>
            @endif
            <div class="md:col-span-2"><dt class="text-gray-500 font-medium">Direccion:</dt><dd class="text-gray-900 mt-1">{{ $establecimiento->direccion }}</dd></div>
            <div>
                <dt class="text-gray-500 font-medium">Estado:</dt>
                <dd class="mt-1">
                    @if($establecimiento->activo)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Activo</span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Inactivo</span>
                    @endif
                </dd>
            </div>
        </dl>
        <div class="mt-6">
            <a href="{{ route('emisor.configuracion.establecimientos.edit', $establecimiento) }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm">Editar</a>
        </div>
    </div>
</x-emisor-layout>
