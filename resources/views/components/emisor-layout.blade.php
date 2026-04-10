<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $nombreSitio ?? config('app.name', 'SistemSFE') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Inter', sans-serif; }
        .sidebar-link { transition: all 0.15s ease; }
        .sidebar-link:hover { transform: translateX(2px); }
        .sidebar-link.active { background: linear-gradient(135deg, rgba(255,255,255,0.15), rgba(255,255,255,0.05)); border-left: 3px solid #60a5fa; }
        .sidebar-scrollbar::-webkit-scrollbar { width: 4px; }
        .sidebar-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .sidebar-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.15); border-radius: 4px; }
        .sidebar-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.25); }

        /* Responsive sidebar */
        .app-sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 40;
            width: 16rem;
            transform: translateX(-100%);
            transition: transform 0.2s ease;
        }
        .app-sidebar.sidebar-open {
            transform: translateX(0);
        }
        @media (min-width: 768px) {
            .app-sidebar {
                position: static;
                transform: none !important;
                z-index: auto;
                min-height: 100vh;
            }
        }
        .mobile-overlay {
            display: none;
        }
        .mobile-overlay.sidebar-open {
            display: block;
        }
        @media (min-width: 768px) {
            .mobile-overlay {
                display: none !important;
            }
            .mobile-hamburger {
                display: none !important;
            }
        }
    </style>
    @stack('styles')
</head>
<body class="antialiased">
    @if(session('impersonar_emisor_id'))
    <div style="background:#f59e0b; color:#000; padding:8px 16px; text-align:center; font-size:14px; font-weight:600; position:fixed; top:0; left:0; right:0; z-index:9999; display:flex; align-items:center; justify-content:center; gap:12px;">
        <svg style="width:18px; height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        Modo soporte: {{ auth()->user()->emisor->razon_social ?? '' }}
        <form method="POST" action="{{ route('admin.emisores.dejar-impersonar') }}" style="display:inline;">
            @csrf
            <button type="submit" style="background:#000; color:#f59e0b; padding:4px 14px; border-radius:4px; font-size:12px; font-weight:600; border:none; cursor:pointer; margin-left:8px;">
                Volver al Admin
            </button>
        </form>
    </div>
    <div style="height:40px;"></div>
    @endif
    <div class="min-h-screen bg-gray-50 flex" x-data="{ sidebarOpen: false }">
        <!-- Mobile Overlay -->
        <div
            class="fixed inset-0 z-30 bg-black/50 mobile-overlay"
            :class="sidebarOpen ? 'sidebar-open' : ''"
            @click="sidebarOpen = false"
        ></div>

        <!-- Sidebar -->
        <aside
            class="app-sidebar bg-gradient-to-b from-slate-800 to-slate-900 text-white flex-shrink-0 flex flex-col shadow-xl"
            :class="sidebarOpen ? 'sidebar-open' : ''"
        >
            <!-- Logo -->
            <div class="p-5 border-b border-white/10">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <div>
                        <h1 class="text-base font-bold tracking-tight">{{ $nombreSitio ?? config('app.name', 'SistemSFE') }}</h1>
                        @if(Auth::user()->emisor)
                        <p class="text-[11px] text-slate-400 truncate max-w-[160px]">{{ Auth::user()->emisor->razon_social }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Ambiente Indicator -->
            @if(Auth::user()->emisor)
                @if(Auth::user()->emisor->ambiente->value === '1')
                <div class="mx-3 mt-3 px-3 py-2 rounded-lg bg-yellow-500/20 border border-yellow-500/40 text-center">
                    <span class="text-yellow-300 text-xs font-bold uppercase tracking-wider">AMBIENTE: PRUEBAS</span>
                </div>
                @else
                <div class="mx-3 mt-3 px-3 py-2 rounded-lg bg-emerald-500/20 border border-emerald-500/40 text-center">
                    <span class="text-emerald-300 text-xs font-bold uppercase tracking-wider">AMBIENTE: PRODUCCION</span>
                </div>
                @endif
            @endif

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto sidebar-scrollbar py-4 px-3 space-y-1" @click="if ($event.target.closest('a')) sidebarOpen = false">
                <a href="{{ route('emisor.dashboard') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('emisor.dashboard') ? 'active text-white' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    Dashboard
                </a>

                <div class="pt-4 pb-2 px-3">
                    <p class="text-[10px] font-semibold text-slate-500 uppercase tracking-widest">Comprobantes</p>
                </div>
                <a href="{{ route('emisor.comprobantes.facturas.index') }}" class="sidebar-link flex items-center px-3 py-2 rounded-lg text-sm {{ request()->routeIs('emisor.comprobantes.facturas.*') ? 'active text-white font-medium' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                    Facturas
                </a>
                <a href="{{ route('emisor.comprobantes.notas-credito.index') }}" class="sidebar-link flex items-center px-3 py-2 rounded-lg text-sm {{ request()->routeIs('emisor.comprobantes.notas-credito.*') ? 'active text-white font-medium' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
                    Notas de Credito
                </a>
                <a href="{{ route('emisor.comprobantes.notas-debito.index') }}" class="sidebar-link flex items-center px-3 py-2 rounded-lg text-sm {{ request()->routeIs('emisor.comprobantes.notas-debito.*') ? 'active text-white font-medium' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Notas de Debito
                </a>
                <a href="{{ route('emisor.comprobantes.retenciones.index') }}" class="sidebar-link flex items-center px-3 py-2 rounded-lg text-sm {{ request()->routeIs('emisor.comprobantes.retenciones.*') ? 'active text-white font-medium' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    Retenciones
                </a>
                <a href="{{ route('emisor.comprobantes.retenciones-ats.index') }}" class="sidebar-link flex items-center px-3 py-2 rounded-lg text-sm {{ request()->routeIs('emisor.comprobantes.retenciones-ats.*') ? 'active text-white font-medium' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    Retenciones ATS 2.0
                </a>
                <a href="{{ route('emisor.comprobantes.liquidaciones.index') }}" class="sidebar-link flex items-center px-3 py-2 rounded-lg text-sm {{ request()->routeIs('emisor.comprobantes.liquidaciones.*') ? 'active text-white font-medium' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                    Liquidaciones
                </a>
                <a href="{{ route('emisor.comprobantes.guias.index') }}" class="sidebar-link flex items-center px-3 py-2 rounded-lg text-sm {{ request()->routeIs('emisor.comprobantes.guias.*') ? 'active text-white font-medium' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                    Guias Remision
                </a>
                <a href="{{ route('emisor.comprobantes.proformas.index') }}" class="sidebar-link flex items-center px-3 py-2 rounded-lg text-sm {{ request()->routeIs('emisor.comprobantes.proformas.*') ? 'active text-white font-medium' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    Proformas
                </a>

                <div class="pt-4 pb-2 px-3">
                    <p class="text-[10px] font-semibold text-slate-500 uppercase tracking-widest">Retenciones</p>
                </div>
                <a href="{{ route('emisor.compras.index') }}" class="sidebar-link flex items-center px-3 py-2 rounded-lg text-sm {{ request()->routeIs('emisor.compras.*') ? 'active text-white font-medium' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Comprobantes XML
                </a>

                <div class="pt-4 pb-2 px-3">
                    <p class="text-[10px] font-semibold text-slate-500 uppercase tracking-widest">Farmacia</p>
                </div>
                <a href="{{ route('emisor.farmacia.pos') }}" class="sidebar-link flex items-center px-3 py-2 rounded-lg text-sm {{ request()->routeIs('emisor.farmacia.pos*') ? 'active text-white font-medium' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                    Punto de Venta
                </a>
                <a href="{{ route('emisor.farmacia.dashboard') }}" class="sidebar-link flex items-center px-3 py-2 rounded-lg text-sm {{ request()->routeIs('emisor.farmacia.dashboard') ? 'active text-white font-medium' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                    Panel Farmacia
                </a>
                <a href="{{ route('emisor.farmacia.categorias.index') }}" class="sidebar-link flex items-center px-3 py-2 rounded-lg text-sm {{ request()->routeIs('emisor.farmacia.categorias.*') ? 'active text-white font-medium' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                    Categorias
                </a>
                <a href="{{ route('emisor.farmacia.proveedores.index') }}" class="sidebar-link flex items-center px-3 py-2 rounded-lg text-sm {{ request()->routeIs('emisor.farmacia.proveedores.*') ? 'active text-white font-medium' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Proveedores
                </a>
                <a href="{{ route('emisor.farmacia.presentaciones.index') }}" class="sidebar-link flex items-center px-3 py-2 rounded-lg text-sm {{ request()->routeIs('emisor.farmacia.presentaciones.*') ? 'active text-white font-medium' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    Presentaciones
                </a>
                <a href="{{ route('emisor.farmacia.laboratorios.index') }}" class="sidebar-link flex items-center px-3 py-2 rounded-lg text-sm {{ request()->routeIs('emisor.farmacia.laboratorios.*') ? 'active text-white font-medium' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/></svg>
                    Laboratorios
                </a>
                <a href="{{ route('emisor.farmacia.lotes.index') }}" class="sidebar-link flex items-center px-3 py-2 rounded-lg text-sm {{ request()->routeIs('emisor.farmacia.lotes.*') ? 'active text-white font-medium' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                    Lotes (FEFO)
                </a>
                <a href="{{ route('emisor.farmacia.vencidos') }}" class="sidebar-link flex items-center px-3 py-2 rounded-lg text-sm {{ request()->routeIs('emisor.farmacia.vencidos') ? 'active text-white font-medium' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Vencimientos
                </a>

                @if(Auth::user()->emisor && Auth::user()->establecimientosActivos()->contains('maneja_inventario', true))
                <div class="pt-4 pb-2 px-3">
                    <p class="text-[10px] font-semibold text-slate-500 uppercase tracking-widest">Inventario</p>
                </div>
                <a href="{{ route('emisor.inventario.index') }}" class="sidebar-link flex items-center px-3 py-2 rounded-lg text-sm {{ request()->routeIs('emisor.inventario.index') || request()->routeIs('emisor.inventario.kardex') ? 'active text-white font-medium' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    Stock
                </a>
                <a href="{{ route('emisor.inventario.ajuste') }}" class="sidebar-link flex items-center px-3 py-2 rounded-lg text-sm {{ request()->routeIs('emisor.inventario.ajuste') ? 'active text-white font-medium' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                    Ajustes
                </a>
                <a href="{{ route('emisor.inventario.valorizado') }}" class="sidebar-link flex items-center px-3 py-2 rounded-lg text-sm {{ request()->routeIs('emisor.inventario.valorizado') ? 'active text-white font-medium' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Valorizado
                </a>
                @endif

                <div class="pt-4 pb-2 px-3">
                    <p class="text-[10px] font-semibold text-slate-500 uppercase tracking-widest">Catalogos</p>
                </div>
                <a href="{{ route('emisor.codigos-retencion.index') }}" class="sidebar-link flex items-center px-3 py-2 rounded-lg text-sm {{ request()->routeIs('emisor.codigos-retencion.*') ? 'active text-white font-medium' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                    Codigos Retencion
                </a>

                <div class="pt-4 pb-2 px-3">
                    <p class="text-[10px] font-semibold text-slate-500 uppercase tracking-widest">Reportes</p>
                </div>
                <a href="{{ route('emisor.reportes.comprobantes') }}" class="sidebar-link flex items-center px-3 py-2 rounded-lg text-sm {{ request()->routeIs('emisor.reportes.comprobantes') ? 'active text-white font-medium' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Comprobantes
                </a>
                <a href="{{ route('emisor.reportes.ventas') }}" class="sidebar-link flex items-center px-3 py-2 rounded-lg text-sm {{ request()->routeIs('emisor.reportes.ventas') && !request()->routeIs('emisor.reportes.ventas-detallada') ? 'active text-white font-medium' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    Ventas
                </a>
                <a href="{{ route('emisor.reportes.ventas-detallada') }}" class="sidebar-link flex items-center px-3 py-2 rounded-lg text-sm {{ request()->routeIs('emisor.reportes.ventas-detallada') ? 'active text-white font-medium' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                    Ventas Detalladas
                </a>
                <a href="{{ route('emisor.reportes.retenciones-totalizadas') }}" class="sidebar-link flex items-center px-3 py-2 rounded-lg text-sm {{ request()->routeIs('emisor.reportes.retenciones-totalizadas') ? 'active text-white font-medium' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/></svg>
                    Retenciones Total
                </a>
                <a href="{{ route('emisor.reportes.retenciones-factura') }}" class="sidebar-link flex items-center px-3 py-2 rounded-lg text-sm {{ request()->routeIs('emisor.reportes.retenciones-factura') ? 'active text-white font-medium' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                    Retenciones x Factura
                </a>

                <a href="{{ route('emisor.carga-masiva.index') }}" class="sidebar-link flex items-center px-3 py-2 rounded-lg text-sm mt-1 {{ request()->routeIs('emisor.carga-masiva.*') ? 'active text-white font-medium' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    Carga Masiva
                </a>

                @if(Auth::user()->esAdmin() || Auth::user()->esEmisorAdmin())
                <div class="pt-4 pb-2 px-3">
                    <p class="text-[10px] font-semibold text-slate-500 uppercase tracking-widest">Configuración</p>
                </div>
                <a href="{{ route('emisor.configuracion.emisor') }}" class="sidebar-link flex items-center px-3 py-2 rounded-lg text-sm {{ request()->routeIs('emisor.configuracion.emisor*') ? 'active text-white font-medium' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    Datos Emisor
                </a>
                <a href="{{ route('emisor.configuracion.unidades-negocio.index') }}" class="sidebar-link flex items-center px-3 py-2 rounded-lg text-sm {{ request()->routeIs('emisor.configuracion.unidades-negocio.*') ? 'active text-white font-medium' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    Lineas de Negocio
                </a>
                <a href="{{ route('emisor.configuracion.establecimientos.index') }}" class="sidebar-link flex items-center px-3 py-2 rounded-lg text-sm {{ request()->routeIs('emisor.configuracion.establecimientos.*') || request()->routeIs('emisor.establecimientos.*') ? 'active text-white font-medium' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Establecimientos
                </a>
                <a href="{{ route('emisor.configuracion.puntos-emision.index') }}" class="sidebar-link flex items-center px-3 py-2 rounded-lg text-sm {{ request()->routeIs('emisor.configuracion.puntos-emision.*') || request()->routeIs('emisor.puntos-emision.*') ? 'active text-white font-medium' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2z"/></svg>
                    Puntos Emisión
                </a>
                <a href="{{ route('emisor.configuracion.clientes.index') }}" class="sidebar-link flex items-center px-3 py-2 rounded-lg text-sm {{ request()->routeIs('emisor.configuracion.clientes.*') || request()->routeIs('emisor.clientes.*') ? 'active text-white font-medium' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Clientes
                </a>
                <a href="{{ route('emisor.configuracion.productos.index') }}" class="sidebar-link flex items-center px-3 py-2 rounded-lg text-sm {{ request()->routeIs('emisor.configuracion.productos.*') || request()->routeIs('emisor.productos.*') ? 'active text-white font-medium' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    Productos
                </a>
                <a href="{{ route('emisor.configuracion.usuarios.index') }}" class="sidebar-link flex items-center px-3 py-2 rounded-lg text-sm {{ request()->routeIs('emisor.configuracion.usuarios.*') || request()->routeIs('emisor.usuarios.*') ? 'active text-white font-medium' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    Usuarios
                </a>
                @endif

                @if(Auth::user()->esAdmin())
                <div class="mt-4 pt-4 border-t border-white/10">
                    <a href="{{ route('admin.dashboard') }}" class="sidebar-link flex items-center px-3 py-2 rounded-lg text-sm text-slate-300 hover:text-white hover:bg-white/5">
                        <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Panel Admin
                    </a>
                </div>
                @endif
            </nav>

            <!-- User Section -->
            <div class="p-4 border-t border-white/10 bg-slate-900/50">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 rounded-full bg-blue-500/20 flex items-center justify-center">
                        <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-slate-200 truncate">{{ Auth::user()->nombre_completo }}</p>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-xs text-slate-400 hover:text-red-400 transition-colors">Cerrar sesión</button>
                        </form>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-w-0">
            @isset($header)
            <header class="bg-white border-b border-gray-200">
                <div class="max-w-7xl mx-auto py-5 px-6 sm:px-8 flex items-center">
                    <button @click="sidebarOpen = true" class="mobile-hamburger mr-3 p-1 rounded-md text-gray-500 hover:text-gray-700 hover:bg-gray-100 focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <div class="flex-1">
                        {{ $header }}
                    </div>
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

            @if(session('info'))
            <div class="max-w-7xl mx-auto mt-4 px-6 sm:px-8 w-full">
                <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded-lg flex items-center">
                    <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ session('info') }}
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
