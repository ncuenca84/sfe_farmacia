<x-admin-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Gestión de Firmas Electrónicas</h2>
            <a href="{{ route('admin.crm.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-md text-sm hover:bg-gray-600">Volver al CRM</a>
        </div>
    </x-slot>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Emisor</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">RUC</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Firma</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vigencia</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                    <th class="px-5 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($emisores as $emisor)
                @php
                    $firmaEstado = 'sin-firma';
                    $firmaColor = 'gray';
                    $firmaTexto = 'Sin firma';
                    if ($emisor->firma_path) {
                        if ($emisor->firma_vigencia) {
                            if ($emisor->firma_vigencia < now()) {
                                $firmaEstado = 'vencida';
                                $firmaColor = 'red';
                                $firmaTexto = 'Vencida';
                            } elseif ($emisor->firma_vigencia < now()->addDays(30)) {
                                $firmaEstado = 'por-vencer';
                                $firmaColor = 'yellow';
                                $firmaTexto = 'Por vencer';
                            } else {
                                $firmaEstado = 'vigente';
                                $firmaColor = 'green';
                                $firmaTexto = 'Vigente';
                            }
                        } else {
                            $firmaEstado = 'sin-fecha';
                            $firmaColor = 'blue';
                            $firmaTexto = 'Sin fecha';
                        }
                    }
                @endphp
                <tr>
                    <td class="px-5 py-3 text-sm font-medium text-gray-900">{{ $emisor->razon_social }}</td>
                    <td class="px-5 py-3 text-sm text-gray-500">{{ $emisor->ruc }}</td>
                    <td class="px-5 py-3 text-sm text-gray-500">
                        @if($emisor->firma_path)
                            <span class="text-green-600">Cargada</span>
                        @else
                            <span class="text-red-500">No cargada</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-sm text-gray-500">
                        {{ $emisor->firma_vigencia?->format('d/m/Y') ?? '-' }}
                    </td>
                    <td class="px-5 py-3">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $firmaColor }}-100 text-{{ $firmaColor }}-800">{{ $firmaTexto }}</span>
                    </td>
                    <td class="px-5 py-3 text-right">
                        <a href="{{ route('admin.crm.emisor-historial', $emisor) }}" class="text-blue-600 hover:text-blue-800 text-sm">Historial</a>
                        <a href="{{ route('admin.emisores.edit', $emisor) }}" class="text-gray-600 hover:text-gray-800 text-sm ml-2">Editar</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="px-5 py-3 border-t border-gray-200">
            {{ $emisores->links() }}
        </div>
    </div>
</x-admin-layout>
