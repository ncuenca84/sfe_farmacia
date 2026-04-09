<x-emisor-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Configuracion del Emisor</h2>
    </x-slot>

    <form method="POST" action="{{ route('emisor.configuracion.emisor.update') }}" enctype="multipart/form-data" autocomplete="off">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Datos del Emisor</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Razon Social</label>
                    <input type="text" name="razon_social" value="{{ old('razon_social', $emisor->razon_social) }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm" required>
                    @error('razon_social') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre Comercial</label>
                    <input type="text" name="nombre_comercial" value="{{ old('nombre_comercial', $emisor->nombre_comercial) }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                    @error('nombre_comercial') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Direccion Matriz</label>
                    <input type="text" name="direccion_matriz" value="{{ old('direccion_matriz', $emisor->direccion_matriz) }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm" required>
                    @error('direccion_matriz') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Obligado a llevar Contabilidad</label>
                    <select name="obligado_contabilidad" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                        <option value="1" {{ old('obligado_contabilidad', $emisor->obligado_contabilidad) ? 'selected' : '' }}>SI</option>
                        <option value="0" {{ !old('obligado_contabilidad', $emisor->obligado_contabilidad) ? 'selected' : '' }}>NO</option>
                    </select>
                    @error('obligado_contabilidad') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Contribuyente Especial</label>
                    <input type="text" name="contribuyente_especial" value="{{ old('contribuyente_especial', $emisor->contribuyente_especial) }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm" placeholder="Numero de resolucion (opcional)">
                    @error('contribuyente_especial') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Agente de Retencion</label>
                    <input type="text" name="agente_retencion" value="{{ old('agente_retencion', $emisor->agente_retencion) }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm" placeholder="Numero de resolucion (opcional)" autocomplete="off">
                    @error('agente_retencion') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Regimen</label>
                    <select name="regimen" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                        @foreach(\App\Enums\RegimenEmisor::cases() as $regimen)
                            <option value="{{ $regimen->value }}" {{ old('regimen', $emisor->regimen?->value) === $regimen->value ? 'selected' : '' }}>{{ $regimen->nombre() }}</option>
                        @endforeach
                    </select>
                    @error('regimen') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ambiente</label>
                    <select name="ambiente" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                        @foreach(\App\Enums\Ambiente::cases() as $amb)
                            <option value="{{ $amb->value }}" {{ old('ambiente', $emisor->ambiente?->value) === $amb->value ? 'selected' : '' }}>{{ $amb->value }} - {{ $amb->nombre() }}</option>
                        @endforeach
                    </select>
                    @error('ambiente') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Archivos</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Logo</label>
                    <input type="file" name="logo" accept="image/*" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                    @if($emisor->logo)
                        <p class="text-xs text-gray-500 mt-1">Logo actual cargado.</p>
                    @endif
                    @error('logo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Certificado Digital (.p12)</label>
                    <input type="file" name="certificado_p12" accept=".p12,.pfx" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                    @if($emisor->certificado_p12)
                        <p class="text-xs text-gray-500 mt-1">Certificado actual cargado.</p>
                    @endif
                    @error('certificado_p12') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    <div class="mt-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Clave del Certificado</label>
                        <input type="password" name="clave_certificado" class="w-full border-gray-300 rounded-md shadow-sm text-sm" placeholder="Dejar vacio para no cambiar" autocomplete="new-password">
                        @error('clave_certificado') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Configuración de Email</h3>
            <p class="text-sm text-gray-500 mb-4">Configuración SMTP para el envío de comprobantes electrónicos por correo.</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Host SMTP</label>
                    <input type="text" name="mail_host" value="{{ old('mail_host', $emisor->mail_host) }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm" placeholder="smtp.gmail.com">
                    @error('mail_host') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Puerto SMTP</label>
                    <input type="number" name="mail_port" value="{{ old('mail_port', $emisor->mail_port) }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm" placeholder="465">
                    @error('mail_port') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Usuario SMTP</label>
                    <input type="text" name="mail_username" value="{{ old('mail_username', $emisor->mail_username) }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm" placeholder="usuario@ejemplo.com">
                    @error('mail_username') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Contraseña SMTP</label>
                    <input type="password" name="mail_password" class="w-full border-gray-300 rounded-md shadow-sm text-sm" placeholder="Dejar vacío para no cambiar" autocomplete="new-password">
                    @if($emisor->mail_password)
                        <p class="text-xs text-gray-500 mt-1">Contraseña configurada. Dejar vacío para mantener la actual.</p>
                    @endif
                    @error('mail_password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Encriptación</label>
                    <select name="mail_encryption" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                        <option value="">-- Sin encriptación --</option>
                        <option value="ssl" {{ old('mail_encryption', $emisor->mail_encryption) == 'ssl' ? 'selected' : '' }}>SSL</option>
                        <option value="tls" {{ old('mail_encryption', $emisor->mail_encryption) == 'tls' ? 'selected' : '' }}>TLS</option>
                    </select>
                    @error('mail_encryption') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email Remitente</label>
                    <input type="email" name="mail_from_address" value="{{ old('mail_from_address', $emisor->mail_from_address) }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm" placeholder="facturacion@ejemplo.com">
                    @error('mail_from_address') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre Remitente</label>
                    <input type="text" name="mail_from_name" value="{{ old('mail_from_name', $emisor->mail_from_name) }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm" placeholder="Mi Empresa S.A.">
                    @error('mail_from_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Guardar Cambios</button>
        </div>
    </form>

    @if($emisor->ambiente === \App\Enums\Ambiente::PRUEBAS)
    <div class="bg-yellow-50 border border-yellow-300 rounded-lg shadow p-6 mt-6">
        <h3 class="text-lg font-medium text-yellow-800 mb-2">Ambiente de Pruebas</h3>
        <p class="text-sm text-yellow-700 mb-4">Esta opcion permite eliminar todos los comprobantes generados en ambiente de pruebas (facturas, notas de credito, notas de debito, retenciones, guias y liquidaciones).</p>
        <form method="POST" action="{{ route('emisor.configuracion.eliminar-comprobantes-prueba') }}" onsubmit="return confirm('¿Está seguro? Se eliminarán TODOS los comprobantes de prueba. Esta acción no se puede deshacer.')">
            @csrf
            @method('DELETE')
            <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 text-sm">Eliminar todos los comprobantes de prueba</button>
        </form>
    </div>
    @endif
</x-emisor-layout>
