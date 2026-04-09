<x-emisor-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Cliente: {{ $cliente->razon_social }}</h2>
            <a href="{{ route('emisor.configuracion.clientes.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 text-sm">Volver</a>
        </div>
    </x-slot>

    <div class="bg-white rounded-lg shadow p-6">
        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div><dt class="text-gray-500 font-medium">Tipo Identificacion:</dt><dd class="text-gray-900 mt-1">{{ $cliente->tipo_identificacion }}</dd></div>
            <div><dt class="text-gray-500 font-medium">Identificacion:</dt><dd class="text-gray-900 mt-1">{{ $cliente->identificacion }}</dd></div>
            <div><dt class="text-gray-500 font-medium">Razon Social:</dt><dd class="text-gray-900 mt-1">{{ $cliente->razon_social }}</dd></div>
            <div><dt class="text-gray-500 font-medium">Email:</dt><dd class="text-gray-900 mt-1">{{ $cliente->email ?? '-' }}</dd></div>
            <div><dt class="text-gray-500 font-medium">Telefono:</dt><dd class="text-gray-900 mt-1">{{ $cliente->telefono ?? '-' }}</dd></div>
            <div><dt class="text-gray-500 font-medium">Direccion:</dt><dd class="text-gray-900 mt-1">{{ $cliente->direccion ?? '-' }}</dd></div>
        </dl>
        <div class="mt-6">
            <a href="{{ route('emisor.configuracion.clientes.edit', $cliente) }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm">Editar</a>
        </div>
    </div>
</x-emisor-layout>
