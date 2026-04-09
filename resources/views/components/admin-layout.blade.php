<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin - {{ $nombreSitio ?? config('app.name', 'SistemSFE') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Inter', sans-serif; }
        .sidebar-link { transition: all 0.15s ease; }
        .sidebar-link:hover { transform: translateX(2px); }
        .sidebar-link.active { background: linear-gradient(135deg, rgba(255,255,255,0.15), rgba(255,255,255,0.05)); border-left: 3px solid #f59e0b; }
        .sidebar-scrollbar::-webkit-scrollbar { width: 4px; }
        .sidebar-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .sidebar-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.15); border-radius: 4px; }
    </style>
</head>
<body class="antialiased">
    <div class="min-h-screen bg-gray-50 flex">
        <!-- Sidebar -->
        <aside class="w-64 bg-gradient-to-b from-gray-800 to-gray-900 text-white min-h-screen flex-shrink-0 flex flex-col shadow-xl">
            <!-- Logo -->
            <div class="p-5 border-b border-white/10">
                <div class="flex flex-col items-center text-center">
                    @if(file_exists(storage_path('app/public/site/logo.png')))
                        <img src="{{ route('site.logo') }}" alt="{{ $nombreSitio ?? config('app.name', 'SistemSFE') }}" class="h-10 max-w-[140px] object-contain mb-2">
                    @endif
                    <h1 class="text-base font-bold tracking-tight">{{ $nombreSitio ?? config('app.name', 'SistemSFE') }}</h1>
                    <p class="text-[11px] text-amber-400 font-medium">Panel Administrador</p>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto sidebar-scrollbar py-4 px-3 space-y-1">
                <a href="{{ route('admin.dashboard') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('admin.dashboard') ? 'active text-white' : 'text-gray-300 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    Dashboard
                </a>
                <a href="{{ route('admin.emisores.index') }}" class="sidebar-link flex items-center px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.emisores.*') ? 'active text-white font-medium' : 'text-gray-300 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    Emisores
                </a>
                <a href="{{ route('admin.establecimientos.index') }}" class="sidebar-link flex items-center px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.establecimientos.*') ? 'active text-white font-medium' : 'text-gray-300 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/></svg>
                    Establecimientos
                </a>
                <a href="{{ route('admin.puntos-emision.index') }}" class="sidebar-link flex items-center px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.puntos-emision.*') ? 'active text-white font-medium' : 'text-gray-300 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Puntos de Emisión
                </a>
                <a href="{{ route('admin.planes.index') }}" class="sidebar-link flex items-center px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.planes.*') ? 'active text-white font-medium' : 'text-gray-300 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    Planes
                </a>
                <a href="{{ route('admin.usuarios.index') }}" class="sidebar-link flex items-center px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.usuarios.*') ? 'active text-white font-medium' : 'text-gray-300 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    Usuarios
                </a>

                <a href="{{ route('admin.paginas-legales.index') }}" class="sidebar-link flex items-center px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.paginas-legales.*') ? 'active text-white font-medium' : 'text-gray-300 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Páginas Legales
                </a>

                <div class="pt-4 pb-2 px-3">
                    <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">CRM</p>
                </div>
                <a href="{{ route('admin.crm.index') }}" class="sidebar-link flex items-center px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.crm.index') ? 'active text-white font-medium' : 'text-gray-300 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Panel CRM
                </a>
                <a href="{{ route('admin.crm.clientes') }}" class="sidebar-link flex items-center px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.crm.clientes') ? 'active text-white font-medium' : 'text-gray-300 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    Clientes
                </a>
                <a href="{{ route('admin.crm.suscripciones') }}" class="sidebar-link flex items-center px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.crm.suscripciones') ? 'active text-white font-medium' : 'text-gray-300 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    Suscripciones
                </a>
                <a href="{{ route('admin.crm.firmas-electronicas.index') }}" class="sidebar-link flex items-center px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.crm.firmas-electronicas.*') ? 'active text-white font-medium' : 'text-gray-300 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                    Firmas Electrónicas
                </a>
                <a href="{{ route('admin.crm.notificaciones') }}" class="sidebar-link flex items-center px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.crm.notificaciones*') ? 'active text-white font-medium' : 'text-gray-300 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    Notificaciones
                </a>

                <div class="pt-4 pb-2 px-3">
                    <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Impuestos</p>
                </div>
                <a href="{{ route('admin.impuesto-ivas.index') }}" class="sidebar-link flex items-center px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.impuesto-ivas.*') ? 'active text-white font-medium' : 'text-gray-300 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    IVA
                </a>
                <a href="{{ route('admin.impuesto-ices.index') }}" class="sidebar-link flex items-center px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.impuesto-ices.*') ? 'active text-white font-medium' : 'text-gray-300 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    ICE
                </a>
                <a href="{{ route('admin.impuesto-irbpnrs.index') }}" class="sidebar-link flex items-center px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.impuesto-irbpnrs.*') ? 'active text-white font-medium' : 'text-gray-300 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    IRBPNR
                </a>
                <a href="{{ route('admin.codigos-retencion.index') }}" class="sidebar-link flex items-center px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.codigos-retencion.*') ? 'active text-white font-medium' : 'text-gray-300 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                    Retenciones
                </a>

                <div class="pt-4 pb-2 px-3">
                    <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">WHMCS</p>
                </div>
                <a href="{{ route('admin.whmcs.configuracion') }}" class="sidebar-link flex items-center px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.whmcs.configuracion*') ? 'active text-white font-medium' : 'text-gray-300 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                    Configuración
                </a>
                <a href="{{ route('admin.whmcs.servicios') }}" class="sidebar-link flex items-center px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.whmcs.servicios') ? 'active text-white font-medium' : 'text-gray-300 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/></svg>
                    Servicios
                </a>
                <a href="{{ route('admin.whmcs.planes') }}" class="sidebar-link flex items-center px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.whmcs.planes') ? 'active text-white font-medium' : 'text-gray-300 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                    Mapeo Planes
                </a>

                <div class="pt-4 pb-2 px-3">
                    <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Sistema</p>
                </div>
                <a href="{{ route('admin.configuracion-sitio.index') }}" class="sidebar-link flex items-center px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.configuracion-sitio.*') ? 'active text-white font-medium' : 'text-gray-300 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Config. Sitio
                </a>

                @if(session('impersonar_emisor_id'))
                <div class="mt-4 pt-4 border-t border-white/10">
                    <a href="{{ route('emisor.dashboard') }}" class="sidebar-link flex items-center px-3 py-2 rounded-lg text-sm text-amber-300 hover:text-amber-100 hover:bg-white/5">
                        <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Ver Facturación ({{ \App\Models\Emisor::find(session('impersonar_emisor_id'))->razon_social ?? '' }})
                    </a>
                </div>
                @endif
            </nav>

            <!-- User Section -->
            <div class="p-4 border-t border-white/10 bg-gray-900/50">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 rounded-full bg-amber-500/20 flex items-center justify-center">
                        <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-200 truncate">{{ Auth::user()->nombre_completo }}</p>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-xs text-gray-400 hover:text-red-400 transition-colors">Cerrar sesión</button>
                        </form>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-w-0">
            @isset($header)
            <header class="bg-white border-b border-gray-200">
                <div class="max-w-7xl mx-auto py-5 px-6 sm:px-8">
                    {{ $header }}
                </div>
            </header>
            @endisset

            @if(session('success'))
            <div class="max-w-7xl mx-auto mt-4 px-6 sm:px-8 w-full">
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg flex items-center">
                    <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ session('success') }}
                </div>
            </div>
            @endif

            @if(session('error'))
            <div class="max-w-7xl mx-auto mt-4 px-6 sm:px-8 w-full">
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg flex items-center">
                    <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ session('error') }}
                </div>
            </div>
            @endif

            <main class="flex-1 p-6 sm:p-8">
                {{ $slot }}
            </main>
        </div>
    </div>
@stack('scripts')
</body>
</html>
