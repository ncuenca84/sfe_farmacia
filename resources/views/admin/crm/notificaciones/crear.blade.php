<x-admin-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Nueva Notificación</h2>
            <a href="{{ route('admin.crm.notificaciones') }}" class="bg-gray-500 text-white px-4 py-2 rounded-md text-sm hover:bg-gray-600">Volver</a>
        </div>
    </x-slot>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 max-w-4xl">
        <form method="POST" action="{{ route('admin.crm.notificaciones.enviar') }}">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="tipo" class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                    <select name="tipo" id="tipo" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        <option value="MANUAL">Manual</option>
                        <option value="PROMOCION">Promoción</option>
                        <option value="ALERTA_PLAN">Alerta de Plan</option>
                        <option value="ALERTA_FIRMA">Alerta de Firma</option>
                        <option value="GENERAL">General</option>
                    </select>
                    @error('tipo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="destinatarios" class="block text-sm font-medium text-gray-700 mb-1">Destinatarios</label>
                    <select name="destinatarios" id="destinatarios" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" onchange="document.getElementById('emisores-select').style.display = this.value === 'SELECCIONADOS' ? 'block' : 'none'">
                        <option value="TODOS">Todos los emisores</option>
                        <option value="ACTIVOS">Solo activos</option>
                        <option value="INACTIVOS">Solo inactivos</option>
                        <option value="VENCIDOS">Con plan vencido</option>
                        <option value="SELECCIONADOS">Seleccionar manualmente</option>
                    </select>
                    @error('destinatarios') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div id="emisores-select" class="mb-6" style="display:none">
                <label class="block text-sm font-medium text-gray-700 mb-1">Seleccionar Emisores</label>
                <div class="border border-gray-300 rounded-md max-h-48 overflow-y-auto p-3 space-y-1">
                    @foreach($emisores as $emisor)
                    <label class="flex items-center text-sm">
                        <input type="checkbox" name="emisor_ids[]" value="{{ $emisor->id }}" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500 mr-2">
                        {{ $emisor->razon_social }} <span class="text-gray-400 ml-1">({{ $emisor->ruc }})</span>
                    </label>
                    @endforeach
                </div>
            </div>

            <div class="mb-6">
                <label for="asunto" class="block text-sm font-medium text-gray-700 mb-1">Asunto</label>
                <input type="text" name="asunto" id="asunto" value="{{ old('asunto') }}" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" placeholder="Asunto del correo electrónico">
                @error('asunto') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="mb-6">
                <label for="mensaje" class="block text-sm font-medium text-gray-700 mb-1">Mensaje (HTML)</label>
                <textarea name="mensaje" id="mensaje" rows="12" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm font-mono" placeholder="<h2>Título</h2><p>Contenido del mensaje...</p>">{{ old('mensaje') }}</textarea>
                <p class="text-xs text-gray-400 mt-1">Puede usar HTML para dar formato al mensaje. El mensaje se enviará dentro de la plantilla de email del sistema.</p>
                @error('mensaje') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.crm.notificaciones') }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">Cancelar</a>
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md text-sm hover:bg-blue-700" onclick="return confirm('¿Enviar esta notificación a los destinatarios seleccionados?')">Enviar Notificación</button>
            </div>
        </form>
    </div>
</x-admin-layout>
