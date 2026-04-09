<x-admin-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">CRM - Clientes</h2>
        </div>
    </x-slot>

    {{-- Busqueda --}}
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form method="GET" action="{{ route('admin.crm.clientes') }}" class="flex gap-4">
            <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar por RUC, razon social o celular..." class="flex-1 border-gray-300 rounded-md shadow-sm text-sm">
            <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded hover:bg-gray-700 text-sm">Buscar</button>
            @if(request('buscar'))
                <a href="{{ route('admin.crm.clientes') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300 text-sm">Limpiar</a>
            @endif
        </form>
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">RUC</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Razon Social</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Direccion</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Celular</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Correo</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha de Inicio</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Plan</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tiempo restante</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Comprobantes</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($emisores as $emisor)
                        @php
                            $suscripcion = $emisor->suscripcionActiva;
                            $correo = $emisor->mail_from_address ?? $emisor->users->first()?->email ?? '';
                            $diasRestantes = $suscripcion ? $suscripcion->diasRestantes() : 0;
                            $compRestantes = $suscripcion ? $suscripcion->comprobantesRestantes() : 0;
                            $planVencido = $suscripcion && $suscripcion->fecha_fin && $suscripcion->fecha_fin->isPast();
                        @endphp
                        <tr>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ $emisor->ruc }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900 max-w-[200px]">{{ $emisor->razon_social }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500 max-w-[160px]">{{ $emisor->direccion_matriz ?? '-' }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ $emisor->celular ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500 max-w-[180px] truncate">{{ $correo ?: '-' }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{ $suscripcion?->fecha_inicio?->format('Y-m-d') ?? '-' }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                @if($suscripcion)
                                    {{ $suscripcion->plan->nombre ?? '-' }}
                                    ({{ $suscripcion->plan->cant_comprobante ?? '?' }})
                                @else
                                    <span class="text-gray-400">Sin plan</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm">
                                @if($planVencido)
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Plan expirado</span>
                                @elseif($suscripcion)
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $diasRestantes <= 15 ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' }}">{{ $diasRestantes }} dias</span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm">
                                @if($suscripcion)
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $compRestantes > 20 ? 'bg-green-100 text-green-800' : ($compRestantes > 0 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                        {{ $compRestantes === PHP_INT_MAX ? 'Ilimitado' : $compRestantes . ' Disponible' }}
                                    </span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm">
                                <div style="display:flex; gap:6px;">
                                    <a href="{{ route('admin.emisores.edit', $emisor) }}" style="display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:6px; background:#3b82f6; color:white;" title="Editar">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </a>
                                    @if($emisor->celular)
                                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $emisor->celular) }}" target="_blank" style="display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:6px; background:#25d366; color:white;" title="WhatsApp">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                        </a>
                                    @else
                                        <span style="display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:6px; background:#d1d5db; color:white; cursor:not-allowed;" title="Sin celular registrado">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-4 text-center text-sm text-gray-500">No hay clientes registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $emisores->links() }}
        </div>
    </div>
</x-admin-layout>
