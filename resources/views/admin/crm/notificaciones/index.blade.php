<x-admin-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Notificaciones</h2>
            <div class="flex gap-2">
                <a href="{{ route('admin.crm.notificaciones.crear') }}" class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm hover:bg-blue-700">Nueva Notificación</a>
                <a href="{{ route('admin.crm.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-md text-sm hover:bg-gray-600">Volver al CRM</a>
            </div>
        </div>
    </x-slot>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Asunto</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Destinatarios</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Resultado</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                    <th class="px-5 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($notificaciones as $notif)
                <tr>
                    <td class="px-5 py-3">
                        <p class="text-sm font-medium text-gray-900">{{ $notif->asunto }}</p>
                        <p class="text-xs text-gray-500">Por: {{ $notif->creadoPor?->nombre_completo ?? 'Sistema' }}</p>
                    </td>
                    <td class="px-5 py-3 text-sm text-gray-500">{{ $notif->tipo }}</td>
                    <td class="px-5 py-3 text-sm text-gray-500">{{ $notif->destinatarios }}</td>
                    <td class="px-5 py-3">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $notif->estado === 'ENVIADA' ? 'bg-green-100 text-green-800' : ($notif->estado === 'FALLIDA' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">{{ $notif->estado }}</span>
                        @if($notif->estado === 'ENVIADA')
                        <p class="text-xs text-gray-400 mt-1">{{ $notif->enviados }}/{{ $notif->enviados + $notif->fallidos }}</p>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-sm text-gray-500">{{ $notif->enviada_at?->format('d/m/Y H:i') ?? $notif->created_at->format('d/m/Y H:i') }}</td>
                    <td class="px-5 py-3 text-right">
                        <a href="{{ route('admin.crm.notificaciones.ver', $notif) }}" class="text-blue-600 hover:text-blue-800 text-sm">Ver detalle</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-5 py-8 text-center text-gray-400">No hay notificaciones registradas.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-5 py-3 border-t border-gray-200">
            {{ $notificaciones->links() }}
        </div>
    </div>
</x-admin-layout>
