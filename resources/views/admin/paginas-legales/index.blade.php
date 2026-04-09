<x-admin-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Páginas Legales</h2>
            <a href="{{ route('admin.paginas-legales.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm hover:bg-blue-700">Nueva Página</a>
        </div>
    </x-slot>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Título</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Slug</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Última Actualización</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($paginas as $pagina)
                <tr>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $pagina->titulo }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $pagina->slug }}</td>
                    <td class="px-6 py-4">
                        @if($pagina->activo)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Activo</span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Inactivo</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $pagina->updated_at->format('d/m/Y H:i') }}</td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('legal.show', $pagina->slug) }}" target="_blank" class="text-gray-500 hover:text-gray-700 text-sm">Ver</a>
                            <a href="{{ route('admin.paginas-legales.edit', $pagina) }}" class="text-blue-600 hover:text-blue-800 text-sm">Editar</a>
                            <form method="POST" action="{{ route('admin.paginas-legales.destroy', $pagina) }}" onsubmit="return confirm('¿Eliminar esta página?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 text-sm">Eliminar</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">No hay páginas legales registradas.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-admin-layout>
