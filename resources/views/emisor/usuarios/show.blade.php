<x-emisor-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Usuario: {{ $usuario->nombre_completo }}</h2>
            <a href="{{ route('emisor.configuracion.usuarios.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 text-sm">Volver</a>
        </div>
    </x-slot>

    <div class="bg-white rounded-lg shadow p-6">
        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div><dt class="text-gray-500 font-medium">Usuario:</dt><dd class="text-gray-900 mt-1">{{ $usuario->username }}</dd></div>
            <div><dt class="text-gray-500 font-medium">Nombre:</dt><dd class="text-gray-900 mt-1">{{ $usuario->nombre_completo }}</dd></div>
            <div><dt class="text-gray-500 font-medium">Email:</dt><dd class="text-gray-900 mt-1">{{ $usuario->email }}</dd></div>
            <div><dt class="text-gray-500 font-medium">Rol:</dt><dd class="text-gray-900 mt-1">{{ $usuario->rol->descripcion ?? $usuario->rol->nombre }}</dd></div>
            <div><dt class="text-gray-500 font-medium">Creado:</dt><dd class="text-gray-900 mt-1">{{ $usuario->created_at ? $usuario->created_at->format('d/m/Y H:i') : 'N/A' }}</dd></div>
        </dl>
        <div class="mt-6">
            <a href="{{ route('emisor.configuracion.usuarios.edit', $usuario) }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm">Editar</a>
        </div>
    </div>
</x-emisor-layout>
