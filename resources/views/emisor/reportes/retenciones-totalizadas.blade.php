<x-emisor-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Reporte de Retenciones Totalizadas</h2>
    </x-slot>

    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form method="GET" action="{{ route('emisor.reportes.retenciones-totalizadas') }}">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Desde</label>
                    <input type="date" name="desde" value="{{ request('desde') }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Hasta</label>
                    <input type="date" name="hasta" value="{{ request('hasta') }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Buscar Sujeto</label>
                    <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Nombre o RUC/CI" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm">Filtrar</button>
                    <a href="{{ route('emisor.reportes.export-retenciones-totalizadas', request()->query()) }}" class="bg-green-600 text-white px-3 py-2 rounded hover:bg-green-700 text-sm flex items-center" title="Exportar Excel">
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

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Numero</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sujeto</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">RUC/CI</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Doc. Sustento</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total Retenido</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @php $pTotal = 0; @endphp
                    @forelse($retenciones as $ret)
                    @php $retenido = $ret->impuestosRetencion->sum('valor_retenido'); $pTotal += $retenido; @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $ret->fecha_emision->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 font-mono">
                            {{ $ret->establecimiento->codigo ?? '000' }}-{{ $ret->ptoEmision->codigo ?? '000' }}-{{ str_pad($ret->secuencial ?? 0, 9, '0', STR_PAD_LEFT) }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $ret->cliente->razon_social ?? 'N/A' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $ret->cliente->identificacion ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500 font-mono">{{ $ret->num_doc_sustento ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right font-mono font-semibold">${{ number_format($retenido, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">No se encontraron retenciones.</td>
                    </tr>
                    @endforelse
                </tbody>
                @if($retenciones->count())
                <tfoot class="bg-gray-100">
                    <tr class="font-semibold text-sm">
                        <td colspan="5" class="px-4 py-3 text-right text-gray-700">Subtotal pagina:</td>
                        <td class="px-4 py-3 text-right font-mono">${{ number_format($pTotal, 2) }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
        <div class="px-4 py-3 border-t">
            {{ $retenciones->withQueryString()->links() }}
        </div>
    </div>
</x-emisor-layout>
