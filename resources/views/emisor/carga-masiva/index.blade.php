<x-emisor-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Carga Masiva</h2>
    </x-slot>

    {{-- Plantillas de descarga --}}
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-5 mb-6">
        <h3 class="text-sm font-semibold text-blue-800 mb-3">Descargar Plantillas Excel</h3>
        <p class="text-xs text-blue-700 mb-3">Descargue la plantilla correspondiente, llene los datos con el formato indicado y suba el archivo.</p>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('emisor.carga-masiva.plantilla', 'clientes') }}" class="inline-flex items-center gap-2 bg-white border border-blue-300 text-blue-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-100 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Plantilla Clientes
            </a>
            <a href="{{ route('emisor.carga-masiva.plantilla', 'productos') }}" class="inline-flex items-center gap-2 bg-white border border-green-300 text-green-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-green-100 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Plantilla Productos
            </a>
            <a href="{{ route('emisor.carga-masiva.plantilla', 'facturas') }}" class="inline-flex items-center gap-2 bg-white border border-amber-300 text-amber-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-amber-100 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Plantilla Facturas
            </a>
        </div>
    </div>

    {{-- Errores detallados --}}
    @if(session('errores_detalle'))
    <div class="bg-red-50 border border-red-200 rounded-lg p-5 mb-6">
        <h3 class="text-sm font-semibold text-red-800 mb-2">Errores encontrados en la carga:</h3>
        <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
            @foreach(session('errores_detalle') as $err)
                <li><strong>Fila {{ $err['fila'] }}:</strong> {{ $err['mensaje'] }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Formulario de carga --}}
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Cargar Archivo</h3>
        <form method="POST" action="{{ route('emisor.carga-masiva.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Carga</label>
                    <select name="tipo" class="w-full border-gray-300 rounded-md shadow-sm text-sm" required>
                        <option value="">Seleccionar...</option>
                        <option value="clientes" {{ old('tipo') == 'clientes' ? 'selected' : '' }}>Clientes</option>
                        <option value="productos" {{ old('tipo') == 'productos' ? 'selected' : '' }}>Productos</option>
                        <option value="facturas" {{ old('tipo') == 'facturas' ? 'selected' : '' }}>Facturas</option>
                    </select>
                    @error('tipo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Archivo Excel (XLSX, CSV)</label>
                    <input type="file" name="archivo" accept=".xlsx,.csv,.xls" class="w-full border-gray-300 rounded-md shadow-sm text-sm" required>
                    @error('archivo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="flex items-end">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm w-full">Cargar</button>
                </div>
            </div>
        </form>
    </div>

    {{-- Historial de cargas --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b">
            <h3 class="text-lg font-medium text-gray-900">Historial de Cargas</h3>
        </div>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Archivo</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Procesados</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Errores</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($cargas as $carga)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ ucfirst($carga->tipo ?? '') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $carga->archivo_nombre ?? '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">{{ $carga->total_registros ?? 0 }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">{{ $carga->procesados ?? 0 }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right {{ ($carga->errores ?? 0) > 0 ? 'text-red-600' : 'text-gray-900' }}">{{ $carga->errores ?? 0 }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @php
                            $estadoColors = ['pendiente' => 'yellow', 'procesando' => 'blue', 'completado' => 'green', 'error' => 'red'];
                            $estadoColor = $estadoColors[$carga->estado] ?? 'gray';
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $estadoColor }}-100 text-{{ $estadoColor }}-800">{{ ucfirst($carga->estado ?? 'pendiente') }}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $carga->created_at ? $carga->created_at->format('d/m/Y H:i') : '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">No hay cargas registradas.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 border-t">
            {{ $cargas->links() }}
        </div>
    </div>
</x-emisor-layout>
