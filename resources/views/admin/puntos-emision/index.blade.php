<x-admin-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Puntos de Emisión</h2>
            <a href="{{ route('admin.puntos-emision.create', ['emisor_id' => request('emisor_id')]) }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 text-sm font-medium">+ Nuevo Punto de Emisión</a>
        </div>
    </x-slot>

    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form method="GET" action="{{ route('admin.puntos-emision.index') }}">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Emisor</label>
                    <select name="emisor_id" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                        <option value="">Todos</option>
                        @foreach($emisores as $emisor)
                        <option value="{{ $emisor->id }}" {{ request('emisor_id') == $emisor->id ? 'selected' : '' }}>{{ $emisor->ruc }} - {{ $emisor->razon_social }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm">Filtrar</button>
                    <a href="{{ route('admin.puntos-emision.index') }}" class="bg-gray-200 text-gray-700 px-3 py-2 rounded hover:bg-gray-300 text-sm">Limpiar</a>
                </div>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Emisor</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Establecimiento</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Codigo</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Sec. Factura</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Sec. NC</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Sec. Ret</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($ptosEmision as $pto)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $pto->establecimiento->emisor->razon_social ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 font-mono">{{ $pto->establecimiento->codigo ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 font-mono font-medium">{{ $pto->codigo }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $pto->nombre ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500 text-center">{{ $pto->sec_factura }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500 text-center">{{ $pto->sec_nota_credito }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500 text-center">{{ $pto->sec_retencion }}</td>
                        <td class="px-4 py-3 text-sm">
                            @if($pto->activo)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Activo</span>
                            @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Inactivo</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-right space-x-1">
                            <a href="{{ route('admin.puntos-emision.edit', $pto) }}" class="inline-flex items-center px-2.5 py-1.5 bg-yellow-50 text-yellow-700 rounded hover:bg-yellow-100 text-xs font-medium">Editar</a>
                            <form method="POST" action="{{ route('admin.puntos-emision.destroy', $pto) }}" class="inline" onsubmit="return confirm('¿Eliminar punto de emisión {{ $pto->codigo }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center px-2.5 py-1.5 bg-red-50 text-red-700 rounded hover:bg-red-100 text-xs font-medium">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-4 py-8 text-center text-sm text-gray-500">No se encontraron puntos de emisión.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t">{{ $ptosEmision->withQueryString()->links() }}</div>
    </div>
</x-admin-layout>
