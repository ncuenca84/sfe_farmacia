<x-emisor-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Reporte de Comprobantes</h2>
    </x-slot>

    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form method="GET" action="{{ route('emisor.reportes.comprobantes') }}">
            <div class="grid grid-cols-1 md:grid-cols-6 gap-4 items-end">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                    <select name="tipo" class="w-full border-gray-300 rounded-md shadow-sm text-sm" onchange="this.form.submit()">
                        <option value="facturas" {{ $tipo == 'facturas' ? 'selected' : '' }}>Facturas</option>
                        <option value="notas-credito" {{ $tipo == 'notas-credito' ? 'selected' : '' }}>Notas de Credito</option>
                        <option value="notas-debito" {{ $tipo == 'notas-debito' ? 'selected' : '' }}>Notas de Debito</option>
                        <option value="retenciones" {{ $tipo == 'retenciones' ? 'selected' : '' }}>Retenciones</option>
                        <option value="guias" {{ $tipo == 'guias' ? 'selected' : '' }}>Guias de Remision</option>
                        <option value="liquidaciones" {{ $tipo == 'liquidaciones' ? 'selected' : '' }}>Liquidaciones de Compra</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                    <select name="estado" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                        <option value="">Todos</option>
                        @foreach(['CREADA', 'AUTORIZADO', 'NO AUTORIZADO', 'PROCESANDOSE', 'ANULADA'] as $estado)
                        <option value="{{ $estado }}" {{ request('estado') == $estado ? 'selected' : '' }}>{{ $estado }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Desde</label>
                    <input type="date" name="desde" value="{{ request('desde') }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Hasta</label>
                    <input type="date" name="hasta" value="{{ request('hasta') }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ $tipo === 'guias' ? 'Buscar Transportista' : 'Buscar Cliente' }}</label>
                    <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="{{ $tipo === 'guias' ? 'Nombre o RUC transportista' : 'Nombre o RUC/CI' }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm">Filtrar</button>
                    <a href="{{ route('emisor.reportes.export-comprobantes', request()->query()) }}" class="bg-green-600 text-white px-3 py-2 rounded hover:bg-green-700 text-sm flex items-center" title="Exportar Excel">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- Resumen global --}}
    @if($totales && $totales->cantidad > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4 flex items-center">
            <div class="bg-blue-100 rounded-full p-3 mr-4">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <div>
                <p class="text-sm text-gray-500">Total Comprobantes</p>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($totales->cantidad) }}</p>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4 flex items-center">
            <div class="bg-green-100 rounded-full p-3 mr-4">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-sm text-gray-500">Monto Total</p>
                <p class="text-2xl font-bold text-gray-900">${{ number_format($totales->total, 2) }}</p>
            </div>
        </div>
    </div>
    @endif

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Numero</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ $tipo === 'guias' ? 'Transportista' : 'Cliente/Sujeto' }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ $tipo === 'guias' ? 'RUC Transportista' : 'RUC/CI' }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                        @if(!in_array($tipo, ['guias', 'retenciones']))
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($comprobantes as $comp)
                    @php
                        $colors = ['CREADA' => 'gray', 'AUTORIZADO' => 'green', 'NO AUTORIZADO' => 'red', 'PROCESANDOSE' => 'blue', 'ANULADA' => 'yellow'];
                        $color = $colors[$comp->estado] ?? 'gray';
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $comp->fecha_emision->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 font-mono">
                            {{ $comp->establecimiento->codigo ?? '---' }}-{{ $comp->ptoEmision->codigo ?? '---' }}-{{ str_pad($comp->secuencial, 9, '0', STR_PAD_LEFT) }}
                        </td>
                        @if($tipo === 'guias')
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $comp->razon_social_transportista ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $comp->ruc_transportista ?? '-' }}</td>
                        @else
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $comp->cliente->razon_social ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $comp->cliente->identificacion ?? '-' }}</td>
                        @endif
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-800">{{ $comp->estado }}</span>
                        </td>
                        @if(!in_array($tipo, ['guias', 'retenciones']))
                        <td class="px-4 py-3 text-sm text-gray-900 text-right font-mono">${{ number_format($comp->importe_total ?? 0, 2) }}</td>
                        @endif
                    </tr>
                    @empty
                    <tr><td colspan="{{ !in_array($tipo, ['guias', 'retenciones']) ? 6 : 5 }}" class="px-4 py-8 text-center text-sm text-gray-500">No se encontraron comprobantes con los filtros seleccionados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t">{{ $comprobantes->withQueryString()->links() }}</div>
    </div>
</x-emisor-layout>
