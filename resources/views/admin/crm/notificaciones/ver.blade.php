<x-admin-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Detalle de Notificación</h2>
            <a href="{{ route('admin.crm.notificaciones') }}" class="bg-gray-500 text-white px-4 py-2 rounded-md text-sm hover:bg-gray-600">Volver</a>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ $notificacion->asunto }}</h3>
            <div class="flex gap-3 mb-4 text-sm text-gray-500">
                <span>Tipo: <strong>{{ $notificacion->tipo }}</strong></span>
                <span>Destinatarios: <strong>{{ $notificacion->destinatarios }}</strong></span>
                <span>Por: <strong>{{ $notificacion->creadoPor?->nombre_completo ?? 'Sistema' }}</strong></span>
            </div>
            <div class="border border-gray-200 rounded-lg p-4 bg-gray-50 prose prose-sm max-w-none">
                {!! $notificacion->mensaje !!}
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h4 class="font-semibold text-gray-800 mb-3">Resumen</h4>
            <dl class="space-y-3 text-sm">
                <div>
                    <dt class="text-gray-500">Estado</dt>
                    <dd><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $notificacion->estado === 'ENVIADA' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">{{ $notificacion->estado }}</span></dd>
                </div>
                <div>
                    <dt class="text-gray-500">Enviada</dt>
                    <dd class="text-gray-900 font-medium">{{ $notificacion->enviada_at?->format('d/m/Y H:i') ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Enviados exitosamente</dt>
                    <dd class="text-green-600 font-bold text-lg">{{ $notificacion->enviados }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Fallidos</dt>
                    <dd class="text-red-600 font-bold text-lg">{{ $notificacion->fallidos }}</dd>
                </div>
            </dl>
        </div>
    </div>

    {{-- Detalle de envíos --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-200">
            <h3 class="font-semibold text-gray-800">Detalle de Envíos</h3>
        </div>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Emisor</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Error</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($notificacion->historialEmails as $email)
                <tr>
                    <td class="px-5 py-3 text-sm text-gray-900">{{ $email->emisor->razon_social ?? '-' }}</td>
                    <td class="px-5 py-3 text-sm text-gray-500">{{ $email->email_destino }}</td>
                    <td class="px-5 py-3">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $email->estado === 'ENVIADO' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">{{ $email->estado }}</span>
                    </td>
                    <td class="px-5 py-3 text-xs text-red-500">{{ $email->error ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-5 py-6 text-center text-gray-400">Sin registros de envío.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-admin-layout>
