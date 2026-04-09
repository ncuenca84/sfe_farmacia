<x-emisor-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Producto: {{ $producto->nombre }}</h2>
            <a href="{{ route('emisor.configuracion.productos.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 text-sm">Volver</a>
        </div>
    </x-slot>

    <div class="bg-white rounded-lg shadow p-6">
        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div><dt class="text-gray-500 font-medium">Codigo Principal:</dt><dd class="text-gray-900 mt-1">{{ $producto->codigo_principal ?? '-' }}</dd></div>
            <div><dt class="text-gray-500 font-medium">Codigo Auxiliar:</dt><dd class="text-gray-900 mt-1">{{ $producto->codigo_auxiliar ?? '-' }}</dd></div>
            <div class="md:col-span-2"><dt class="text-gray-500 font-medium">Nombre / Descripcion:</dt><dd class="text-gray-900 mt-1">{{ $producto->nombre }}</dd></div>
            <div><dt class="text-gray-500 font-medium">Precio Unitario:</dt><dd class="text-gray-900 mt-1">${{ number_format($producto->precio_unitario ?? 0, 2) }}</dd></div>
            <div><dt class="text-gray-500 font-medium">IVA:</dt><dd class="text-gray-900 mt-1">{{ $producto->impuestoIva->nombre ?? $producto->impuesto_iva_id ?? '-' }}</dd></div>
        </dl>
        <div class="mt-6">
            <a href="{{ route('emisor.configuracion.productos.edit', $producto) }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm">Editar</a>
        </div>
    </div>
</x-emisor-layout>
