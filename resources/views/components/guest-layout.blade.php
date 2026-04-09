<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $nombreSitio ?? config('app.name', 'SistemSFE') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased" style="font-family: 'Inter', sans-serif;">
        <div class="min-h-screen flex">
            {{-- Left panel - branding --}}
            <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-slate-800 via-slate-900 to-blue-900 relative overflow-hidden">
                <div class="absolute inset-0">
                    <div class="absolute top-0 right-0 w-96 h-96 bg-blue-500/10 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                    <div class="absolute bottom-0 left-0 w-80 h-80 bg-blue-400/10 rounded-full translate-y-1/3 -translate-x-1/3"></div>
                    <div class="absolute top-1/2 left-1/2 w-64 h-64 bg-blue-500/5 rounded-full -translate-x-1/2 -translate-y-1/2"></div>
                </div>
                <div class="relative z-10 flex flex-col justify-center items-center w-full px-12">
                    <div class="mb-8">
                        @if(file_exists(storage_path('app/public/site/logo.png')))
                            <img src="{{ route('site.logo') }}" alt="{{ $nombreSitio ?? config('app.name', 'SistemSFE') }}" class="h-20 max-w-[200px] object-contain">
                        @else
                        <div class="w-20 h-20 bg-gradient-to-br from-blue-400 to-blue-600 rounded-2xl flex items-center justify-center shadow-lg shadow-blue-500/25">
                            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                        </div>
                        @endif
                    </div>
                    <h1 class="text-4xl font-bold text-white mb-4 text-center">{{ $nombreSitio ?? config('app.name', 'SistemSFE') }}</h1>
                    <p class="text-blue-200/80 text-lg text-center max-w-md leading-relaxed">Sistema de Facturacion Electronica</p>
                    <div class="mt-12 grid grid-cols-2 gap-6 text-center">
                        <div class="bg-white/5 backdrop-blur-sm rounded-xl p-4 border border-white/10">
                            <div class="text-2xl font-bold text-white">100%</div>
                            <div class="text-xs text-blue-200/60 mt-1">Compatible SRI</div>
                        </div>
                        <div class="bg-white/5 backdrop-blur-sm rounded-xl p-4 border border-white/10">
                            <div class="text-2xl font-bold text-white">24/7</div>
                            <div class="text-xs text-blue-200/60 mt-1">Disponibilidad</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right panel - form --}}
            <div class="w-full lg:w-1/2 flex flex-col justify-center items-center px-6 sm:px-12 bg-gray-50">
                {{-- Mobile logo --}}
                <div class="lg:hidden mb-8">
                    @if(file_exists(storage_path('app/public/site/logo.png')))
                        <img src="{{ route('site.logo') }}" alt="{{ $nombreSitio ?? config('app.name', 'SistemSFE') }}" class="h-16 max-w-[160px] object-contain">
                    @else
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-700 rounded-2xl flex items-center justify-center shadow-lg shadow-blue-500/25">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                    </div>
                    @endif
                </div>

                <div class="w-full max-w-md">
                    <div class="bg-white rounded-2xl shadow-xl shadow-gray-200/50 border border-gray-100 p-8">
                        {{ $slot }}
                    </div>
                    <p class="text-center text-xs text-gray-400 mt-6">&copy; {{ date('Y') }} {{ $nombreSitio ?? config('app.name', 'SistemSFE') }}. Todos los derechos reservados.</p>
                </div>
            </div>
        </div>
    </body>
</html>
