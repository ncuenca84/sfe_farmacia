<x-admin-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Firmas Electrónicas</h2>
            <div class="flex gap-2">
                <a href="{{ route('admin.crm.firmas-electronicas.create') }}" class="bg-green-600 text-white px-4 py-2 rounded-md text-sm hover:bg-green-700 font-medium">+ Nuevo</a>
                <a href="{{ route('admin.crm.firmas-electronicas.plantilla') }}" class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm hover:bg-blue-700 font-medium">Descargar Formato</a>
                <a href="{{ route('admin.crm.firmas-electronicas.exportar') }}" class="bg-gray-600 text-white px-4 py-2 rounded-md text-sm hover:bg-gray-700 font-medium">Exportar Excel</a>
            </div>
        </div>
    </x-slot>

    {{-- Importar Excel --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-4">
        <form method="POST" action="{{ route('admin.crm.firmas-electronicas.importar') }}" enctype="multipart/form-data" class="flex items-center gap-4">
            @csrf
            <label class="text-sm font-medium text-gray-700">Importar desde Excel:</label>
            <input type="file" name="archivo_excel" accept=".xlsx,.xls" required class="text-sm border border-gray-300 rounded-md px-3 py-1.5">
            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-md text-sm hover:bg-green-700 font-medium">Cargar</button>
        </form>
    </div>

    {{-- Estadísticas --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
        <div class="bg-white rounded-lg border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
            <p class="text-xs text-gray-500">Total</p>
        </div>
        <a href="{{ route('admin.crm.firmas-electronicas.index', ['estado' => 'vigentes']) }}" class="bg-white rounded-lg border border-gray-200 p-4 text-center hover:bg-green-50 {{ $estado === 'vigentes' ? 'ring-2 ring-green-500' : '' }}">
            <p class="text-2xl font-bold text-green-600">{{ $stats['vigentes'] }}</p>
            <p class="text-xs text-gray-500">Vigentes</p>
        </a>
        <a href="{{ route('admin.crm.firmas-electronicas.index', ['estado' => 'por_vencer']) }}" class="bg-white rounded-lg border border-gray-200 p-4 text-center hover:bg-yellow-50 {{ $estado === 'por_vencer' ? 'ring-2 ring-yellow-500' : '' }}">
            <p class="text-2xl font-bold text-yellow-600">{{ $stats['por_vencer'] }}</p>
            <p class="text-xs text-gray-500">Por vencer (30d)</p>
        </a>
        <a href="{{ route('admin.crm.firmas-electronicas.index', ['estado' => 'vencidas']) }}" class="bg-white rounded-lg border border-gray-200 p-4 text-center hover:bg-red-50 {{ $estado === 'vencidas' ? 'ring-2 ring-red-500' : '' }}">
            <p class="text-2xl font-bold text-red-600">{{ $stats['vencidas'] }}</p>
            <p class="text-xs text-gray-500">Vencidas</p>
        </a>
    </div>

    {{-- Búsqueda --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-4">
        <form method="GET" action="{{ route('admin.crm.firmas-electronicas.index') }}" class="flex items-center gap-3">
            <input type="hidden" name="estado" value="{{ $estado }}">
            <input type="text" name="buscar" value="{{ $buscar }}" placeholder="Buscar por cédula, nombre o correo..." class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm hover:bg-blue-700">Buscar</button>
            @if($buscar || $estado)
            <a href="{{ route('admin.crm.firmas-electronicas.index') }}" class="text-gray-500 hover:text-gray-700 text-sm">Limpiar</a>
            @endif
        </form>
    </div>

    {{-- Tabla --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cédula</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombres</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Apellidos</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Celular</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Correo</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Emisor</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha Creación</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha Expiración</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tiempo Restante</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($firmas as $firma)
                @php
                    $estado_firma = $firma->estadoTexto();
                    $color = match($estado_firma) {
                        'Vigente' => 'green',
                        'Por vencer' => 'yellow',
                        'Vencida' => 'red',
                        default => 'gray',
                    };
                @endphp
                <tr class="{{ $estado_firma === 'Vencida' ? 'bg-red-50' : ($estado_firma === 'Por vencer' ? 'bg-yellow-50' : '') }}">
                    <td class="px-4 py-3 text-sm font-mono text-gray-900">{{ $firma->identificacion }}</td>
                    <td class="px-4 py-3 text-sm text-gray-900">{{ $firma->nombres }}</td>
                    <td class="px-4 py-3 text-sm text-gray-900">{{ $firma->apellidos }}</td>
                    <td class="px-4 py-3 text-sm text-gray-500">{{ $firma->celular ?? '-' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-500">{{ $firma->correo ?? '-' }}</td>
                    <td class="px-4 py-3 text-sm">
                        @if($firma->emisor)
                            <a href="{{ route('admin.emisores.show', $firma->emisor) }}" class="text-blue-600 hover:text-blue-800" title="{{ $firma->emisor->ruc }}">{{ Str::limit($firma->emisor->razon_social, 25) }}</a>
                        @else
                            <span class="text-gray-400">Sin asignar</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-500">{{ $firma->fecha_inicio?->format('Y/m/d') ?? '-' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-500">{{ $firma->fecha_fin?->format('Y/m/d') ?? '-' }}</td>
                    <td class="px-4 py-3">
                        @if($firma->fecha_fin)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-800">
                            {{ $estado_firma === 'Vencida' ? 'Vencida' : $firma->diasRestantes() . ' días' }}
                        </span>
                        @else
                        <span class="text-xs text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('admin.crm.firmas-electronicas.edit', $firma) }}" class="text-blue-600 hover:text-blue-800 text-sm">Editar</a>
                            <form method="POST" action="{{ route('admin.crm.firmas-electronicas.destroy', $firma) }}" onsubmit="return confirm('¿Eliminar esta firma electrónica?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 text-sm">Eliminar</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="px-4 py-8 text-center text-gray-400">No hay firmas electrónicas registradas.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-200">
            {{ $firmas->links() }}
        </div>
    </div>
</x-admin-layout>
