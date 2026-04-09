<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Configuración del Sitio</h2>
    </x-slot>

    <div class="max-w-2xl space-y-6">
        {{-- Nombre del sistema --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-3">Nombre del Sistema</h3>
            <form method="POST" action="{{ route('admin.configuracion-sitio.guardar-general') }}">
                @csrf
                <div class="flex items-end gap-3">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                        <input type="text" name="nombre_sitio" value="{{ old('nombre_sitio', $config['nombre_sitio']) }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm" placeholder="Opcional">
                        <p class="text-xs text-gray-400 mt-1">Se muestra en el login, sidebar y título del navegador. Si se deja vacío se usa el valor de APP_NAME del .env.</p>
                    </div>
                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 text-sm font-medium whitespace-nowrap">Guardar</button>
                </div>
                @error('nombre_sitio') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </form>
        </div>

        {{-- Logo --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-3">Logo del Sitio</h3>
            <p class="text-sm text-gray-500 mb-4">Se muestra en la pantalla de login y en la barra lateral del panel admin.</p>

            @if($logoExists)
            <div class="mb-4">
                <p class="text-sm font-medium text-gray-700 mb-2">Logo actual:</p>
                <div class="flex items-center gap-4">
                    <div class="bg-gray-100 rounded-lg p-4 inline-block">
                        <img src="{{ route('site.logo') }}?v={{ time() }}" alt="Logo" class="h-16 max-w-[200px] object-contain">
                    </div>
                    <form method="POST" action="{{ route('admin.configuracion-sitio.eliminar-logo') }}" onsubmit="return confirm('¿Eliminar el logo?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">Eliminar logo</button>
                    </form>
                </div>
            </div>
            @endif

            <form method="POST" action="{{ route('admin.configuracion-sitio.guardar-logo') }}" enctype="multipart/form-data">
                @csrf
                <div class="flex items-end gap-3">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ $logoExists ? 'Cambiar logo' : 'Subir logo' }}</label>
                        <input type="file" name="logo" accept="image/*" required class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                        <p class="text-xs text-gray-400 mt-1">PNG, JPG, SVG o WebP. Max 2MB. Recomendado: fondo transparente.</p>
                    </div>
                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 text-sm font-medium whitespace-nowrap">Guardar Logo</button>
                </div>
                @error('logo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </form>
        </div>

        {{-- Configuración SMTP --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-1">Correo SMTP (Notificaciones del Sistema)</h3>
            <p class="text-sm text-gray-500 mb-4">Configuración para enviar notificaciones generales: suscripciones, recordatorios de pago, CRM. Cada emisor tiene su propia configuracion SMTP para el envío de comprobantes.</p>

            <form method="POST" action="{{ route('admin.configuracion-sitio.guardar-mail') }}">
                @csrf
                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Servidor SMTP</label>
                            <input type="text" name="mail_host" value="{{ old('mail_host', $config['mail_host']) }}" placeholder="smtp.gmail.com" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                            @error('mail_host') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Puerto</label>
                            <input type="number" name="mail_port" value="{{ old('mail_port', $config['mail_port']) }}" placeholder="587" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                            @error('mail_port') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Usuario</label>
                            <input type="text" name="mail_username" value="{{ old('mail_username', $config['mail_username']) }}" placeholder="usuario@dominio.com" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                            @error('mail_username') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
                            <input type="password" name="mail_password" placeholder="Dejar vacío para mantener actual" class="w-full border-gray-300 rounded-md shadow-sm text-sm" autocomplete="new-password">
                            @error('mail_password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Encriptacion</label>
                        <select name="mail_encryption" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                            <option value="tls" {{ old('mail_encryption', $config['mail_encryption']) === 'tls' ? 'selected' : '' }}>TLS (puerto 587)</option>
                            <option value="ssl" {{ old('mail_encryption', $config['mail_encryption']) === 'ssl' ? 'selected' : '' }}>SSL (puerto 465)</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email remitente</label>
                            <input type="email" name="mail_from_address" value="{{ old('mail_from_address', $config['mail_from_address']) }}" placeholder="notificaciones@dominio.com" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                            @error('mail_from_address') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre remitente</label>
                            <input type="text" name="mail_from_name" value="{{ old('mail_from_name', $config['mail_from_name']) }}" placeholder="Mi Sistema" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                            @error('mail_from_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
                <div class="flex justify-end mt-4">
                    <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 text-sm font-medium">Guardar Correo</button>
                </div>
            </form>
        </div>

        {{-- Probar correo --}}
        <div class="bg-gray-50 rounded-lg border border-gray-200 p-6">
            <h3 class="text-base font-medium text-gray-700 mb-3">Probar Envio de Correo</h3>
            <form method="POST" action="{{ route('admin.configuracion-sitio.probar-mail') }}">
                @csrf
                <div class="flex items-end gap-3">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email de destino</label>
                        <input type="email" name="email_prueba" required placeholder="tu-email@dominio.com" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                    </div>
                    <button type="submit" class="bg-emerald-600 text-white px-4 py-2 rounded-lg hover:bg-emerald-700 text-sm font-medium whitespace-nowrap">Enviar Prueba</button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
