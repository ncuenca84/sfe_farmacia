<x-admin-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">{{ $emisor->razon_social }}</h2>
                <p class="text-sm text-gray-500">RUC: {{ $emisor->ruc }} - Historial CRM</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.emisores.show', $emisor) }}" class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm hover:bg-blue-700">Ver Emisor</a>
                <a href="{{ route('admin.crm.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-md text-sm hover:bg-gray-600">Volver al CRM</a>
            </div>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Info del cliente --}}
        <div class="space-y-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-800 mb-3">Información</h3>
                <dl class="space-y-2 text-sm">
                    <div>
                        <dt class="text-gray-500">Estado</dt>
                        <dd><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $emisor->activo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">{{ $emisor->activo ? 'Activo' : 'Inactivo' }}</span></dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Ambiente</dt>
                        <dd class="text-gray-900">{{ $emisor->ambiente->value == 1 ? 'Pruebas' : 'Producción' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Firma</dt>
                        <dd class="text-gray-900">
                            @if($emisor->firma_path)
                                Cargada
                                @if($emisor->firma_vigencia)
                                    - Vence: {{ $emisor->firma_vigencia->format('d/m/Y') }}
                                    @if($emisor->firma_vigencia < now())
                                        <span class="text-red-500 font-semibold">(VENCIDA)</span>
                                    @endif
                                @endif
                            @else
                                <span class="text-red-500">No cargada</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Email</dt>
                        <dd class="text-gray-900">{{ $emisor->mail_from_address ?? 'No configurado' }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Suscripción activa --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-800 mb-3">Suscripción</h3>
                @if($emisor->suscripcionActiva)
                @php $sus = $emisor->suscripcionActiva; @endphp
                <dl class="space-y-2 text-sm">
                    <div>
                        <dt class="text-gray-500">Plan</dt>
                        <dd class="text-gray-900 font-medium">{{ $sus->plan->nombre }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Período</dt>
                        <dd class="text-gray-900">{{ $sus->fecha_inicio->format('d/m/Y') }} - {{ $sus->fecha_fin->format('d/m/Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Comprobantes</dt>
                        <dd class="text-gray-900">{{ $sus->comprobantes_usados }} / {{ $sus->plan->esIlimitado() ? 'Ilimitado' : $sus->plan->cant_comprobante }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Días restantes</dt>
                        <dd class="text-gray-900 font-medium {{ $sus->diasRestantes() <= 7 ? 'text-red-600' : '' }}">{{ $sus->diasRestantes() }} días</dd>
                    </div>
                </dl>
                @else
                <p class="text-sm text-gray-400">Sin suscripción activa.</p>
                @endif
            </div>

            {{-- Historial de suscripciones --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-800 mb-3">Historial de Planes</h3>
                <div class="space-y-2">
                    @foreach($emisor->suscripciones->sortByDesc('created_at') as $sus)
                    @php
                        $color = match($sus->estado->value) {
                            'ACTIVA' => 'green',
                            'VENCIDA' => 'red',
                            'SUSPENDIDA' => 'yellow',
                            default => 'gray',
                        };
                    @endphp
                    <div class="flex justify-between items-center text-sm border-b border-gray-100 pb-2">
                        <div>
                            <p class="text-gray-900">{{ $sus->plan->nombre }}</p>
                            <p class="text-xs text-gray-500">{{ $sus->fecha_inicio->format('d/m/Y') }} - {{ $sus->fecha_fin->format('d/m/Y') }}</p>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-800">{{ $sus->estado->value }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Notas y comunicaciones --}}
        <div class="lg:col-span-2 space-y-4">
            {{-- Agregar nota --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-800 mb-3">Agregar Nota</h3>
                <form method="POST" action="{{ route('admin.crm.agregar-nota', $emisor) }}">
                    @csrf
                    <textarea name="contenido" rows="3" required placeholder="Escribir nota o comentario sobre este cliente..." class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm mb-2"></textarea>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm hover:bg-blue-700">Guardar Nota</button>
                </form>
            </div>

            {{-- Notas --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-200">
                    <h3 class="font-semibold text-gray-800">Notas</h3>
                </div>
                <div class="divide-y divide-gray-100">
                    @forelse($notas as $nota)
                    <div class="px-5 py-3">
                        <p class="text-sm text-gray-800">{{ $nota->contenido }}</p>
                        <p class="text-xs text-gray-400 mt-1">{{ $nota->creadoPor?->nombre_completo ?? 'Sistema' }} - {{ $nota->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    @empty
                    <p class="px-5 py-4 text-sm text-gray-400">Sin notas registradas.</p>
                    @endforelse
                </div>
            </div>

            {{-- Historial de emails --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-200">
                    <h3 class="font-semibold text-gray-800">Historial de Emails</h3>
                </div>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Asunto</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($historial as $email)
                        <tr>
                            <td class="px-5 py-3 text-sm text-gray-900">{{ $email->asunto }}</td>
                            <td class="px-5 py-3 text-sm text-gray-500">{{ $email->email_destino }}</td>
                            <td class="px-5 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $email->estado === 'ENVIADO' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">{{ $email->estado }}</span>
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-500">{{ $email->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-5 py-6 text-center text-gray-400">Sin emails registrados.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                @if($historial->hasPages())
                <div class="px-5 py-3 border-t border-gray-200">
                    {{ $historial->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</x-admin-layout>
