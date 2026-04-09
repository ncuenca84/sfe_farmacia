<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pagina->titulo }} - {{ config('app.name', 'SistemSFE') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Inter', sans-serif; }
        .legal-content h2 { font-size: 1.5rem; font-weight: 700; margin-bottom: 0.75rem; margin-top: 1.5rem; color: #1f2937; }
        .legal-content h3 { font-size: 1.25rem; font-weight: 600; margin-bottom: 0.5rem; margin-top: 1.25rem; color: #374151; }
        .legal-content h4 { font-size: 1.1rem; font-weight: 600; margin-bottom: 0.5rem; margin-top: 1rem; color: #4b5563; }
        .legal-content p { margin-bottom: 0.75rem; line-height: 1.7; color: #4b5563; }
        .legal-content ul { list-style: disc; margin-left: 1.5rem; margin-bottom: 0.75rem; }
        .legal-content ul li { margin-bottom: 0.35rem; color: #4b5563; line-height: 1.6; }
        .legal-content strong { font-weight: 600; color: #374151; }
    </style>
</head>
<body class="antialiased bg-gray-50">
    <div class="min-h-screen">
        <header class="bg-white border-b border-gray-200 shadow-sm">
            <div class="max-w-4xl mx-auto px-6 py-4 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-blue-700 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                    </div>
                    <span class="text-lg font-bold text-gray-800">SistemSFE</span>
                </div>
                <a href="{{ route('login') }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">Volver al login</a>
            </div>
        </header>

        <main class="max-w-4xl mx-auto px-6 py-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
                <h1 class="text-2xl font-bold text-gray-900 mb-6 pb-4 border-b border-gray-200">{{ $pagina->titulo }}</h1>
                <div class="legal-content">
                    {!! $pagina->contenido !!}
                </div>
            </div>
            <p class="text-center text-xs text-gray-400 mt-6">&copy; {{ date('Y') }} SistemSFE. Todos los derechos reservados.</p>
        </main>
    </div>
</body>
</html>
