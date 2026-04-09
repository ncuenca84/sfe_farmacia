<x-admin-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Editar Firma Electrónica</h2>
            <a href="{{ route('admin.crm.firmas-electronicas.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-md text-sm hover:bg-gray-600">Volver</a>
        </div>
    </x-slot>

    <div class="max-w-4xl">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <form method="POST" action="{{ route('admin.crm.firmas-electronicas.update', $firma) }}" enctype="multipart/form-data">
                @csrf @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
                    <div>
                        <label for="identificacion" class="block text-sm font-medium text-gray-700 mb-1">Identificación (Cédula/RUC) *</label>
                        <input type="text" name="identificacion" id="identificacion" value="{{ old('identificacion', $firma->identificacion) }}" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" maxlength="20">
                        @error('identificacion') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="correo" class="block text-sm font-medium text-gray-700 mb-1">Correo</label>
                        <input type="email" name="correo" id="correo" value="{{ old('correo', $firma->correo) }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                    </div>
                    <div>
                        <label for="nombres" class="block text-sm font-medium text-gray-700 mb-1">Nombres *</label>
                        <input type="text" name="nombres" id="nombres" value="{{ old('nombres', $firma->nombres) }}" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                    </div>
                    <div>
                        <label for="apellidos" class="block text-sm font-medium text-gray-700 mb-1">Apellidos *</label>
                        <input type="text" name="apellidos" id="apellidos" value="{{ old('apellidos', $firma->apellidos) }}" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                    </div>
                    <div>
                        <label for="celular" class="block text-sm font-medium text-gray-700 mb-1">Celular</label>
                        <input type="text" name="celular" id="celular" value="{{ old('celular', $firma->celular) }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" maxlength="20">
                    </div>
                    <div>
                        <label for="emisor_id" class="block text-sm font-medium text-gray-700 mb-1">Asociar a Emisor</label>
                        <select name="emisor_id" id="emisor_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                            <option value="">-- Sin asociar --</option>
                            @foreach($emisores as $emisor)
                            <option value="{{ $emisor->id }}" {{ old('emisor_id', $firma->emisor_id) == $emisor->id ? 'selected' : '' }}>{{ $emisor->razon_social }} ({{ $emisor->ruc }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="fecha_inicio" class="block text-sm font-medium text-gray-700 mb-1">Fecha de Creación</label>
                        <input type="date" name="fecha_inicio" id="fecha_inicio" value="{{ old('fecha_inicio', $firma->fecha_inicio?->format('Y-m-d')) }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                    </div>
                    <div>
                        <label for="fecha_fin" class="block text-sm font-medium text-gray-700 mb-1">Fecha de Expiración</label>
                        <input type="date" name="fecha_fin" id="fecha_fin" value="{{ old('fecha_fin', $firma->fecha_fin?->format('Y-m-d')) }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
                    <div>
                        <label for="archivo_p12" class="block text-sm font-medium text-gray-700 mb-1">Archivo .p12</label>
                        @if($firma->archivo_p12)
                        <p class="text-xs text-green-600 mb-1">Archivo actual: {{ basename($firma->archivo_p12) }}</p>
                        @endif
                        <input type="file" name="archivo_p12" id="archivo_p12" accept=".p12" class="w-full text-sm border border-gray-300 rounded-md px-3 py-2">
                        <p class="text-xs text-gray-400 mt-1">Dejar vacío para mantener el archivo actual.</p>
                    </div>
                    <div>
                        <label for="password_p12" class="block text-sm font-medium text-gray-700 mb-1">Contraseña del .p12</label>
                        <input type="password" name="password_p12" id="password_p12" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" placeholder="Dejar vacío para no cambiar">
                    </div>
                </div>

                <div class="mb-6">
                    <label for="observaciones" class="block text-sm font-medium text-gray-700 mb-1">Observaciones</label>
                    <textarea name="observaciones" id="observaciones" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">{{ old('observaciones', $firma->observaciones) }}</textarea>
                </div>

                @if($firma->emisor_cn || $firma->organizacion)
                <div class="bg-gray-50 rounded-lg p-4 mb-6 text-sm text-gray-600">
                    <p class="font-medium text-gray-700 mb-1">Datos del certificado:</p>
                    @if($firma->emisor_cn)<p>CN: {{ $firma->emisor_cn }}</p>@endif
                    @if($firma->serial_number)<p>Serial: {{ $firma->serial_number }}</p>@endif
                    @if($firma->organizacion)<p>Organización: {{ $firma->organizacion }}</p>@endif
                </div>
                @endif

                <div class="flex justify-end gap-3">
                    <a href="{{ route('admin.crm.firmas-electronicas.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">Cancelar</a>
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md text-sm hover:bg-blue-700 font-medium">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
