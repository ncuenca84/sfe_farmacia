<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-2">
            <a href="{{ route('admin.emisores.index') }}" class="text-indigo-600 hover:text-indigo-800">&larr; Emisores</a>
            <span class="text-gray-400">/</span>
            <h2 class="text-xl font-semibold text-gray-800">Editar: {{ $emisor->razon_social }}</h2>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('admin.emisores.update', $emisor) }}" enctype="multipart/form-data" class="space-y-8" autocomplete="off">
        @csrf
        @method('PUT')

        <!-- Datos del Emisor -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Datos del Emisor</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <x-input-label for="ruc" value="RUC" />
                    <x-text-input id="ruc" name="ruc" type="text" class="mt-1 block w-full" :value="old('ruc', $emisor->ruc)" required />
                    <x-input-error :messages="$errors->get('ruc')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="razon_social" value="Razon Social" />
                    <x-text-input id="razon_social" name="razon_social" type="text" class="mt-1 block w-full" :value="old('razon_social', $emisor->razon_social)" required />
                    <x-input-error :messages="$errors->get('razon_social')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="nombre_comercial" value="Nombre Comercial" />
                    <x-text-input id="nombre_comercial" name="nombre_comercial" type="text" class="mt-1 block w-full" :value="old('nombre_comercial', $emisor->nombre_comercial)" />
                    <x-input-error :messages="$errors->get('nombre_comercial')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="direccion_matriz" value="Direccion Matriz" />
                    <x-text-input id="direccion_matriz" name="direccion_matriz" type="text" class="mt-1 block w-full" :value="old('direccion_matriz', $emisor->direccion_matriz)" required />
                    <x-input-error :messages="$errors->get('direccion_matriz')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="celular" value="Celular" />
                    <x-text-input id="celular" name="celular" type="text" class="mt-1 block w-full" :value="old('celular', $emisor->celular)" placeholder="0991234567" />
                    <x-input-error :messages="$errors->get('celular')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="ambiente" value="Ambiente" />
                    <select id="ambiente" name="ambiente" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        <option value="1" {{ old('ambiente', $emisor->ambiente) == '1' ? 'selected' : '' }}>Pruebas</option>
                        <option value="2" {{ old('ambiente', $emisor->ambiente) == '2' ? 'selected' : '' }}>Produccion</option>
                    </select>
                    <x-input-error :messages="$errors->get('ambiente')" class="mt-2" />
                </div>
                <input type="hidden" name="tipo_emision" value="1">
                <div class="flex items-center space-x-6 col-span-2">
                    <label class="flex items-center">
                        <input type="checkbox" name="obligado_contabilidad" value="1" {{ old('obligado_contabilidad', $emisor->obligado_contabilidad) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-gray-700">Obligado a llevar Contabilidad</span>
                    </label>
                </div>
                <div>
                    <x-input-label for="regimen" value="Regimen" />
                    <select id="regimen" name="regimen" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        @foreach(\App\Enums\RegimenEmisor::cases() as $regimen)
                            <option value="{{ $regimen->value }}" {{ old('regimen', $emisor->regimen?->value) === $regimen->value ? 'selected' : '' }}>{{ $regimen->nombre() }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('regimen')" class="mt-2" />
                    <label class="flex items-center">
                        <input type="checkbox" name="activo" value="1" {{ old('activo', $emisor->activo) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-gray-700">Activo</span>
                    </label>
                </div>
                <div>
                    <x-input-label for="contribuyente_especial" value="Contribuyente Especial (Nro. Resolucion)" />
                    <x-text-input id="contribuyente_especial" name="contribuyente_especial" type="text" class="mt-1 block w-full" :value="old('contribuyente_especial', $emisor->contribuyente_especial)" />
                    <x-input-error :messages="$errors->get('contribuyente_especial')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="agente_retencion" value="Agente de Retencion (Nro. Resolucion)" />
                    <x-text-input id="agente_retencion" name="agente_retencion" type="text" class="mt-1 block w-full" :value="old('agente_retencion', $emisor->agente_retencion)" />
                    <x-input-error :messages="$errors->get('agente_retencion')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="codigo_numerico" value="Codigo Numerico" />
                    <x-text-input id="codigo_numerico" name="codigo_numerico" type="text" class="mt-1 block w-full" :value="old('codigo_numerico', $emisor->codigo_numerico)" />
                    <x-input-error :messages="$errors->get('codigo_numerico')" class="mt-2" />
                </div>
            </div>
        </div>

        <!-- Firma Electronica -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Firma Electronica</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <x-input-label for="firma" value="Archivo Firma (.p12) - Dejar vacío para mantener actual" />
                    <input id="firma" name="firma" type="file" accept=".p12" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                    <x-input-error :messages="$errors->get('firma')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="firma_password" value="Contraseña Firma - Dejar vacío para mantener actual" />
                    <x-text-input id="firma_password" name="firma_password" type="password" class="mt-1 block w-full" autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('firma_password')" class="mt-2" />
                </div>
            </div>
        </div>

        <!-- Configuracion Email -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Configuracion de Email</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <x-input-label for="mail_host" value="Host SMTP" />
                    <x-text-input id="mail_host" name="mail_host" type="text" class="mt-1 block w-full" :value="old('mail_host', $emisor->mail_host)" />
                    <x-input-error :messages="$errors->get('mail_host')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="mail_port" value="Puerto SMTP" />
                    <x-text-input id="mail_port" name="mail_port" type="text" class="mt-1 block w-full" :value="old('mail_port', $emisor->mail_port)" />
                    <x-input-error :messages="$errors->get('mail_port')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="mail_username" value="Usuario SMTP" />
                    <x-text-input id="mail_username" name="mail_username" type="text" class="mt-1 block w-full" :value="old('mail_username', $emisor->mail_username)" />
                    <x-input-error :messages="$errors->get('mail_username')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="mail_password" value="Contraseña SMTP - Dejar vacío para mantener actual" />
                    <x-text-input id="mail_password" name="mail_password" type="password" class="mt-1 block w-full" autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('mail_password')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="mail_encryption" value="Encriptacion" />
                    <select id="mail_encryption" name="mail_encryption" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        <option value="ssl" {{ old('mail_encryption', $emisor->mail_encryption) == 'ssl' ? 'selected' : '' }}>SSL</option>
                        <option value="tls" {{ old('mail_encryption', $emisor->mail_encryption) == 'tls' ? 'selected' : '' }}>TLS</option>
                    </select>
                    <x-input-error :messages="$errors->get('mail_encryption')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="mail_from_address" value="Email Remitente" />
                    <x-text-input id="mail_from_address" name="mail_from_address" type="email" class="mt-1 block w-full" :value="old('mail_from_address', $emisor->mail_from_address)" />
                    <x-input-error :messages="$errors->get('mail_from_address')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="mail_from_name" value="Nombre Remitente" />
                    <x-text-input id="mail_from_name" name="mail_from_name" type="text" class="mt-1 block w-full" :value="old('mail_from_name', $emisor->mail_from_name)" />
                    <x-input-error :messages="$errors->get('mail_from_name')" class="mt-2" />
                </div>
            </div>
        </div>

        <!-- Logo -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Logo</h3>
            <div>
                @if($emisor->logo)
                    <div class="mb-4">
                        <img src="{{ asset('storage/' . $emisor->logo) }}" alt="Logo actual" class="h-20">
                    </div>
                @endif
                <x-input-label for="logo" value="Logo (imagen) - Dejar vacío para mantener actual" />
                <input id="logo" name="logo" type="file" accept="image/*" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                <x-input-error :messages="$errors->get('logo')" class="mt-2" />
            </div>
        </div>

        <!-- Suscripcion -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Suscripcion</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <x-input-label for="plan_id" value="Plan" />
                    <select id="plan_id" name="plan_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        <option value="">Seleccione un plan</option>
                        @foreach($planes as $plan)
                            <option value="{{ $plan->id }}" {{ old('plan_id', $emisor->suscripcionActiva?->plan_id) == $plan->id ? 'selected' : '' }}>{{ $plan->nombre }} - ${{ $plan->precio }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('plan_id')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="fecha_inicio" value="Fecha Inicio" />
                    <x-text-input id="fecha_inicio" name="fecha_inicio" type="date" class="mt-1 block w-full" :value="old('fecha_inicio', $emisor->suscripcionActiva?->fecha_inicio?->format('Y-m-d'))" />
                    <x-input-error :messages="$errors->get('fecha_inicio')" class="mt-2" />
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <a href="{{ route('admin.emisores.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md text-sm hover:bg-gray-400 mr-2">Cancelar</a>
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm hover:bg-indigo-700">Actualizar Emisor</button>
        </div>
    </form>
</x-admin-layout>
