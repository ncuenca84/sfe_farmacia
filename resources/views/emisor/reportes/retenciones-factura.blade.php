<x-emisor-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Reporte de Retenciones por Factura</h2>
    </x-slot>

    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form method="GET" action="{{ route('emisor.reportes.retenciones-factura') }}">
            <div class="grid grid-cols-1 md:grid-cols-6 gap-4 items-end">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Desde</label>
                    <input type="date" name="desde" value="{{ request('desde') }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Hasta</label>
                    <input type="date" name="hasta" value="{{ request('hasta') }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">No. Factura</label>
                    <input type="text" name="factura" value="{{ request('factura') }}" placeholder="001-001-000000001" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Buscar Sujeto</label>
                    <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Nombre o RUC/CI" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm">Filtrar</button>
                    <a href="{{ route('emisor.reportes.export-retenciones-factura', request()->query()) }}" class="bg-green-600 text-white px-3 py-2 rounded hover:bg-green-700 text-sm flex items-center" title="Exportar Excel">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- Resumen global --}}
    @if($totalComprobantes > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-sm text-gray-500">Total Retenciones</p>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($totalComprobantes) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-sm text-gray-500">Total Retenido</p>
            <p class="text-2xl font-bold text-red-700">${{ number_format($totalRetenidoGlobal, 2) }}</p>
        </div>
    </div>
    @endif

    @forelse($retenciones as $ret)
    <div class="bg-white rounded-lg shadow overflow-hidden mb-4">
        <div class="px-4 py-3 bg-gray-50 border-b flex flex-wrap justify-between items-center gap-2">
            <div class="flex items-center gap-4 text-sm">
                <span class="font-medium text-gray-900 font-mono">
                    {{ $ret->establecimiento->codigo ?? '000' }}-{{ $ret->ptoEmision->codigo ?? '000' }}-{{ str_pad($ret->secuencial ?? 0, 9, '0', STR_PAD_LEFT) }}
                </span>
                <span class="text-gray-500">{{ $ret->fecha_emision->format('d/m/Y') }}</span>
                <span class="text-gray-700">{{ $ret->cliente->razon_social ?? 'N/A' }}</span>
                <span class="text-gray-400">{{ $ret->cliente->identificacion ?? '' }}</span>
            </div>
            <div class="text-sm">
                <span class="text-gray-500">Doc. Sustento: <span class="font-mono">{{ $ret->num_doc_sustento ?? '-' }}</span></span>
                <span class="font-bold text-gray-900 ml-3">Total: ${{ number_format($ret->impuestosRetencion->sum('valor_retenido'), 2) }}</span>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Cod. Impuesto</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Cod. Retencion</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Base Imponible</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">% Retencion</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Valor Retenido</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($ret->impuestosRetencion as $imp)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 text-sm text-gray-900">
                            @if($imp->codigo_impuesto == '1') Renta
                            @elseif($imp->codigo_impuesto == '2') IVA
                            @elseif($imp->codigo_impuesto == '6') ISD
                            @else {{ $imp->codigo_impuesto }}
                            @endif
                        </td>
                        <td class="px-4 py-2 text-sm text-gray-900 font-mono">{{ $imp->codigo_retencion }}</td>
                        <td class="px-4 py-2 text-sm text-gray-900 text-right font-mono">${{ number_format($imp->base_imponible, 2) }}</td>
                        <td class="px-4 py-2 text-sm text-gray-900 text-right font-mono">{{ number_format($imp->porcentaje_retener, 2) }}%</td>
                        <td class="px-4 py-2 text-sm text-gray-900 text-right font-mono font-semibold">${{ number_format($imp->valor_retenido, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @empty
    <div class="bg-white rounded-lg shadow p-8 text-center text-sm text-gray-500">
        No se encontraron retenciones.
    </div>
    @endforelse

    <div class="mt-4">
        {{ $retenciones->withQueryString()->links() }}
    </div>
</x-emisor-layout>
