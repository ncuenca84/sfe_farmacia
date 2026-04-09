<x-admin-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Codigos de Retencion</h2>
            <a href="{{ route('admin.codigos-retencion.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-md text-sm">Nuevo Codigo</a>
        </div>
    </x-slot>

    <!-- Filtros -->
    <div class="bg-white shadow rounded-lg p-4 mb-6">
        <form method="GET" action="{{ route('admin.codigos-retencion.index') }}" class="flex flex-wrap items-end gap-4">
            <div>
                <x-input-label for="tipo" value="Tipo" />
                <select id="tipo" name="tipo" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                    <option value="">Todos</option>
                    <option value="RENTA" {{ request('tipo') == 'RENTA' ? 'selected' : '' }}>RENTA</option>
                    <option value="IVA" {{ request('tipo') == 'IVA' ? 'selected' : '' }}>IVA</option>
                    <option value="ISD" {{ request('tipo') == 'ISD' ? 'selected' : '' }}>ISD</option>
                </select>
            </div>
            <div>
                <x-input-label for="buscar" value="Buscar" />
                <x-text-input id="buscar" name="buscar" type="text" class="mt-1 block w-full" :value="request('buscar')" placeholder="Codigo o descripcion..." />
            </div>
            <div>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-md text-sm">Filtrar</button>
                <a href="{{ route('admin.codigos-retencion.index') }}" class="ml-2 text-sm text-gray-600 hover:text-gray-900">Limpiar</a>
            </div>
        </form>
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Codigo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descripcion</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Porcentaje</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Activo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($codigos as $codigo)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                    {{ $codigo->tipo === 'RENTA' ? 'bg-blue-100 text-blue-800' : ($codigo->tipo === 'IVA' ? 'bg-green-100 text-green-800' : 'bg-purple-100 text-purple-800') }}">
                                    {{ $codigo->tipo }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $codigo->codigo }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">{{ Str::limit($codigo->descripcion, 60) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $codigo->porcentaje }}%</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($codigo->activo)
                                    <span class="text-green-600 font-medium">Si</span>
                                @else
                                    <span class="text-red-600 font-medium">No</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <a href="{{ route('admin.codigos-retencion.edit', $codigo) }}" class="text-yellow-600 hover:text-yellow-900">Editar</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">No hay codigos de retencion registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $codigos->links() }}
        </div>
    </div>
</x-admin-layout>
