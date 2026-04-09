<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">CRM - Panel de Gestión de Clientes</h2>
    </x-slot>

    {{-- Estadísticas --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total Clientes</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $totalClientes }}</p>
                </div>
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Activos</p>
                    <p class="text-2xl font-bold text-green-600">{{ $clientesActivos }}</p>
                </div>
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Inactivos</p>
                    <p class="text-2xl font-bold text-red-600">{{ $clientesInactivos }}</p>
                </div>
                <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Suscripciones por vencer --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="font-semibold text-gray-800">Suscripciones por Vencer (15 días)</h3>
                <a href="{{ route('admin.crm.suscripciones', ['estado' => 'activas']) }}" class="text-sm text-blue-600 hover:text-blue-800">Ver todas</a>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($porVencer as $sus)
                <div class="px-5 py-3 flex justify-between items-center">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $sus->emisor->razon_social }}</p>
                        <p class="text-xs text-gray-500">{{ $sus->plan->nombre }} - Vence: {{ $sus->fecha_fin->format('d/m/Y') }}</p>
                    </div>
                    <span class="text-xs font-semibold px-2 py-1 rounded-full {{ $sus->diasRestantes() <= 5 ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700' }}">{{ $sus->diasRestantes() }} días</span>
                </div>
                @empty
                <p class="px-5 py-4 text-sm text-gray-400">No hay suscripciones por vencer.</p>
                @endforelse
            </div>
        </div>

        {{-- Firmas electrónicas --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="font-semibold text-gray-800">Alertas de Firmas Electrónicas</h3>
                <a href="{{ route('admin.crm.firmas') }}" class="text-sm text-blue-600 hover:text-blue-800">Ver todas</a>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach($firmasVencidas->take(3) as $emisor)
                <div class="px-5 py-3 flex justify-between items-center">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $emisor->razon_social }}</p>
                        <p class="text-xs text-gray-500">Venció: {{ $emisor->firma_vigencia->format('d/m/Y') }}</p>
                    </div>
                    <span class="text-xs font-semibold px-2 py-1 rounded-full bg-red-100 text-red-700">Vencida</span>
                </div>
                @endforeach
                @foreach($firmasPorVencer->take(3) as $emisor)
                <div class="px-5 py-3 flex justify-between items-center">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $emisor->razon_social }}</p>
                        <p class="text-xs text-gray-500">Vence: {{ $emisor->firma_vigencia->format('d/m/Y') }}</p>
                    </div>
                    <span class="text-xs font-semibold px-2 py-1 rounded-full bg-yellow-100 text-yellow-700">Por vencer</span>
                </div>
                @endforeach
                @foreach($sinFirma->take(3) as $emisor)
                <div class="px-5 py-3 flex justify-between items-center">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $emisor->razon_social }}</p>
                        <p class="text-xs text-gray-500">RUC: {{ $emisor->ruc }}</p>
                    </div>
                    <span class="text-xs font-semibold px-2 py-1 rounded-full bg-gray-100 text-gray-700">Sin firma</span>
                </div>
                @endforeach
                @if($firmasVencidas->isEmpty() && $firmasPorVencer->isEmpty() && $sinFirma->isEmpty())
                <p class="px-5 py-4 text-sm text-gray-400">Todas las firmas están al día.</p>
                @endif
            </div>
        </div>

        {{-- Últimas notificaciones --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden lg:col-span-2">
            <div class="px-5 py-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="font-semibold text-gray-800">Últimas Notificaciones Enviadas</h3>
                <div class="flex gap-2">
                    <a href="{{ route('admin.crm.notificaciones.crear') }}" class="bg-blue-600 text-white px-3 py-1.5 rounded-md text-sm hover:bg-blue-700">Nueva Notificación</a>
                    <a href="{{ route('admin.crm.notificaciones') }}" class="text-sm text-blue-600 hover:text-blue-800 py-1.5">Ver todas</a>
                </div>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($ultimasNotificaciones as $notif)
                <a href="{{ route('admin.crm.notificaciones.ver', $notif) }}" class="px-5 py-3 flex justify-between items-center hover:bg-gray-50">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $notif->asunto }}</p>
                        <p class="text-xs text-gray-500">{{ $notif->tipo }} - {{ $notif->enviada_at?->format('d/m/Y H:i') ?? 'Pendiente' }} - Por: {{ $notif->creadoPor?->nombre_completo ?? 'Sistema' }}</p>
                    </div>
                    <div class="text-right">
                        <span class="text-xs font-semibold px-2 py-1 rounded-full {{ $notif->estado === 'ENVIADA' ? 'bg-green-100 text-green-700' : ($notif->estado === 'FALLIDA' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">{{ $notif->estado }}</span>
                        @if($notif->estado === 'ENVIADA')
                        <p class="text-xs text-gray-400 mt-1">{{ $notif->enviados }} enviados / {{ $notif->fallidos }} fallidos</p>
                        @endif
                    </div>
                </a>
                @empty
                <p class="px-5 py-4 text-sm text-gray-400">No hay notificaciones enviadas aún.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-admin-layout>
