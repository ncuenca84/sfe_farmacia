<x-admin-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Gestión de Suscripciones</h2>
            <a href="{{ route('admin.crm.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-md text-sm hover:bg-gray-600">Volver al CRM</a>
        </div>
    </x-slot>

    {{-- Filtros --}}
    <div class="flex gap-2 mb-4">
        @foreach(['todas' => 'Todas', 'activas' => 'Activas', 'vencidas' => 'Vencidas', 'suspendidas' => 'Suspendidas'] as $key => $label)
        <a href="{{ route('admin.crm.suscripciones', ['estado' => $key]) }}"
           class="px-4 py-2 rounded-md text-sm font-medium {{ $filtro === $key ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50' }}">
            {{ $label }}
        </a>
        @endforeach
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Emisor</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Plan</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Inicio</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fin</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Comprobantes</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                    <th class="px-5 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($suscripciones as $sus)
                @php
                    $estadoColor = match($sus->estado->value) {
                        'ACTIVA' => 'green',
                        'VENCIDA' => 'red',
                        'SUSPENDIDA' => 'yellow',
                        default => 'gray',
                    };
                @endphp
                <tr>
                    <td class="px-5 py-3">
                        <p class="text-sm font-medium text-gray-900">{{ $sus->emisor->razon_social }}</p>
                        <p class="text-xs text-gray-500">{{ $sus->emisor->ruc }}</p>
                    </td>
                    <td class="px-5 py-3 text-sm text-gray-700">{{ $sus->plan->nombre }}</td>
                    <td class="px-5 py-3 text-sm text-gray-500">{{ $sus->fecha_inicio->format('d/m/Y') }}</td>
                    <td class="px-5 py-3 text-sm text-gray-500">{{ $sus->fecha_fin->format('d/m/Y') }}</td>
                    <td class="px-5 py-3 text-sm text-gray-500">
                        {{ $sus->comprobantes_usados }} / {{ $sus->plan->esIlimitado() ? 'Ilimitado' : $sus->plan->cant_comprobante }}
                    </td>
                    <td class="px-5 py-3">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $estadoColor }}-100 text-{{ $estadoColor }}-800">{{ $sus->estado->value }}</span>
                    </td>
                    <td class="px-5 py-3 text-right">
                        <div class="flex justify-end gap-1">
                            @if($sus->estado->value === 'ACTIVA')
                            <form method="POST" action="{{ route('admin.crm.suspender', $sus) }}" onsubmit="var m = prompt('Motivo de la suspensión:'); if(!m) return false; this.querySelector('[name=motivo]').value = m; return true;">
                                @csrf
                                <input type="hidden" name="motivo" value="">
                                <button type="submit" class="text-red-600 hover:text-red-800 text-sm">Suspender</button>
                            </form>
                            @endif
                            @if($sus->estado->value === 'SUSPENDIDA')
                            <form method="POST" action="{{ route('admin.crm.reactivar', $sus) }}">
                                @csrf
                                <button type="submit" class="text-green-600 hover:text-green-800 text-sm">Reactivar</button>
                            </form>
                            @endif
                            <a href="{{ route('admin.crm.emisor-historial', $sus->emisor) }}" class="text-blue-600 hover:text-blue-800 text-sm ml-2">Historial</a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-5 py-8 text-center text-gray-400">No hay suscripciones para este filtro.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-5 py-3 border-t border-gray-200">
            {{ $suscripciones->links() }}
        </div>
    </div>
</x-admin-layout>
