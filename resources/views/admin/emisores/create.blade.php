<x-admin-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Crear Emisor</h2>
            <a href="{{ route('admin.emisores.index') }}" class="text-gray-600 hover:text-gray-900 text-sm">&larr; Volver</a>
        </div>
    </x-slot>

    <!-- Indicador de pasos -->
    <div class="mb-8">
        <div class="flex items-center justify-center space-x-2">
            <template x-for="(step, index) in steps" :key="index">
                <div class="flex items-center" x-data>
                    <div @click="if(index < currentStep) currentStep = index"
                         :class="{
                            'bg-indigo-600 text-white': currentStep === index,
                            'bg-emerald-500 text-white cursor-pointer': index < currentStep,
                            'bg-gray-200 text-gray-500': index > currentStep
                         }"
                         class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold transition-all duration-200">
                        <template x-if="index < currentStep">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </template>
                        <template x-if="index >= currentStep">
                            <span x-text="index + 1"></span>
                        </template>
                    </div>
                    <span :class="currentStep === index ? 'text-indigo-600 font-semibold' : 'text-gray-400'"
                          class="ml-2 text-sm hidden sm:inline" x-text="step"></span>
                    <template x-if="index < steps.length - 1">
                        <div class="w-8 sm:w-16 h-0.5 mx-2" :class="index < currentStep ? 'bg-emerald-400' : 'bg-gray-200'"></div>
                    </template>
                </div>
            </template>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.emisores.store') }}" enctype="multipart/form-data"
          x-data="wizardEmisor()" x-cloak autocomplete="off">
        @csrf

        <!-- PASO 1: Datos del Emisor + Consulta SRI -->
        <div x-show="currentStep === 0" x-transition>
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Datos del Emisor</h3>

                <!-- Buscador RUC -->
                <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <label class="block text-sm font-medium text-blue-800 mb-2">Buscar contribuyente por RUC</label>
                    <div class="flex gap-3">
                        <input type="text" x-model="rucBusqueda" maxlength="13" placeholder="Ingrese los 13 digitos del RUC"
                               @keyup.enter.prevent="consultarRuc()"
                               class="flex-1 rounded-md border-blue-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        <button type="button" @click="consultarRuc()"
                                :disabled="consultando || rucBusqueda.length !== 13"
                                class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                            <svg x-show="consultando" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <span x-text="consultando ? 'Consultando...' : 'Consultar SRI'"></span>
                        </button>
                    </div>
                    <!-- Resultado consulta -->
                    <div x-show="sriMensaje" class="mt-3 text-sm rounded-md p-3"
                         :class="sriError ? 'bg-red-50 text-red-700' : 'bg-emerald-50 text-emerald-700'">
                        <span x-text="sriMensaje"></span>
                    </div>
                    <div x-show="sriDatos && sriDatos.estado" class="mt-2 text-xs text-blue-600">
                        <span x-show="sriDatos.fuente">Fuente: <span x-text="sriDatos.fuente" class="font-semibold"></span></span>
                        <span x-show="sriDatos.tipo_contribuyente"> | Tipo: <span x-text="sriDatos.tipo_contribuyente"></span></span>
                        <span x-show="sriDatos.estado"> | Estado SRI: <span x-text="sriDatos.estado"></span></span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-input-label for="ruc" value="RUC" />
                        <x-text-input id="ruc" name="ruc" type="text" class="mt-1 block w-full" x-model="form.ruc" required maxlength="13" />
                        <x-input-error :messages="$errors->get('ruc')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="razon_social" value="Razon Social" />
                        <x-text-input id="razon_social" name="razon_social" type="text" class="mt-1 block w-full" x-model="form.razon_social" required />
                        <x-input-error :messages="$errors->get('razon_social')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="nombre_comercial" value="Nombre Comercial" />
                        <x-text-input id="nombre_comercial" name="nombre_comercial" type="text" class="mt-1 block w-full" x-model="form.nombre_comercial" />
                        <x-input-error :messages="$errors->get('nombre_comercial')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="direccion_matriz" value="Direccion Matriz" />
                        <x-text-input id="direccion_matriz" name="direccion_matriz" type="text" class="mt-1 block w-full" x-model="form.direccion_matriz" required />
                        <x-input-error :messages="$errors->get('direccion_matriz')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="ambiente" value="Ambiente" />
                        <select id="ambiente" name="ambiente" x-model="form.ambiente" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="1">Pruebas</option>
                            <option value="2">Produccion</option>
                        </select>
                        <x-input-error :messages="$errors->get('ambiente')" class="mt-2" />
                    </div>
                    <input type="hidden" name="tipo_emision" value="1">
                    <div class="flex items-center gap-6 pt-6">
                        <label class="flex items-center">
                            <input type="checkbox" name="obligado_contabilidad" value="1" x-model="form.obligado_contabilidad" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">Obligado a llevar Contabilidad</span>
                        </label>
                    </div>
                    <div>
                        <x-input-label for="contribuyente_especial" value="Contribuyente Especial (Nro. Resolucion)" />
                        <x-text-input id="contribuyente_especial" name="contribuyente_especial" type="text" class="mt-1 block w-full" x-model="form.contribuyente_especial" />
                        <x-input-error :messages="$errors->get('contribuyente_especial')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="agente_retencion" value="Agente de Retencion (Nro. Resolucion)" />
                        <x-text-input id="agente_retencion" name="agente_retencion" type="text" class="mt-1 block w-full" x-model="form.agente_retencion" />
                        <x-input-error :messages="$errors->get('agente_retencion')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="regimen" value="Regimen" />
                        <select id="regimen" name="regimen" x-model="form.regimen" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            @foreach(\App\Enums\RegimenEmisor::cases() as $regimen)
                                <option value="{{ $regimen->value }}">{{ $regimen->nombre() }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('regimen')" class="mt-2" />
                    </div>
                    <input type="hidden" name="codigo_numerico" x-model="form.codigo_numerico" />
                </div>
            </div>
        </div>

        <!-- PASO 2: Establecimiento + Punto de Emision -->
        <div x-show="currentStep === 1" x-transition>
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Establecimiento Principal</h3>
                <p class="text-sm text-gray-500 mb-4">Se creara automaticamente el establecimiento matriz y su punto de emision.</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-input-label for="estab_codigo" value="Codigo Establecimiento" />
                        <x-text-input id="estab_codigo" name="estab_codigo" type="text" class="mt-1 block w-full" x-model="form.estab_codigo" maxlength="3" />
                        <p class="mt-1 text-xs text-gray-400">Generalmente 001</p>
                    </div>
                    <div>
                        <x-input-label for="estab_nombre" value="Nombre Establecimiento" />
                        <x-text-input id="estab_nombre" name="estab_nombre" type="text" class="mt-1 block w-full" x-model="form.estab_nombre" />
                    </div>
                    <div class="md:col-span-2">
                        <x-input-label for="estab_direccion" value="Direccion Establecimiento" />
                        <x-text-input id="estab_direccion" name="estab_direccion" type="text" class="mt-1 block w-full" x-model="form.estab_direccion" />
                        <p class="mt-1 text-xs text-gray-400">Si es la matriz, se usa la direccion del emisor</p>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow rounded-lg p-6 mt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Punto de Emision</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-input-label for="pto_codigo" value="Codigo Punto de Emision" />
                        <x-text-input id="pto_codigo" name="pto_codigo" type="text" class="mt-1 block w-full" x-model="form.pto_codigo" maxlength="3" />
                        <p class="mt-1 text-xs text-gray-400">Generalmente 001</p>
                    </div>
                    <div>
                        <x-input-label for="pto_nombre" value="Nombre Punto de Emision" />
                        <x-text-input id="pto_nombre" name="pto_nombre" type="text" class="mt-1 block w-full" x-model="form.pto_nombre" />
                    </div>
                </div>
            </div>
        </div>

        <!-- PASO 3: Firma + Correo + Logo -->
        <div x-show="currentStep === 2" x-transition>
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Firma Electronica</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-input-label for="firma" value="Archivo Firma (.p12)" />
                        <input id="firma" name="firma" type="file" accept=".p12" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                        <x-input-error :messages="$errors->get('firma')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="firma_password" value="Contraseña Firma" />
                        <x-text-input id="firma_password" name="firma_password" type="password" class="mt-1 block w-full" autocomplete="new-password" />
                        <x-input-error :messages="$errors->get('firma_password')" class="mt-2" />
                    </div>
                </div>
            </div>

            <div class="bg-white shadow rounded-lg p-6 mt-6" x-data="mailConfig()">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Configuracion de Correo</h3>

                <!-- Autocompletar por dominio -->
                <div class="mb-5 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <label class="block text-sm font-medium text-blue-800 mb-2">Autocompletar por dominio</label>
                    <div class="flex gap-3">
                        <input type="text" x-model="dominio" placeholder="ejemplo: exxalink.com"
                               @keyup.enter.prevent="aplicarDominio()"
                               class="flex-1 rounded-md border-blue-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        <button type="button" @click="aplicarDominio()"
                                :disabled="!dominio"
                                style="background-color: #4f46e5; color: #fff; padding: 8px 16px; border-radius: 6px; border: none; cursor: pointer; font-size: 14px; font-weight: 500;"
                                onmouseover="this.style.backgroundColor='#4338ca'"
                                onmouseout="this.style.backgroundColor='#4f46e5'">
                            Aplicar
                        </button>
                    </div>
                    <p class="text-xs text-blue-600 mt-1">Completa automaticamente: mail.[dominio], puerto 465, comprobantes@[dominio], SSL</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-input-label for="mail_host" value="Host SMTP" />
                        <x-text-input id="mail_host" name="mail_host" type="text" class="mt-1 block w-full" x-model="mailHost" />
                        <x-input-error :messages="$errors->get('mail_host')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="mail_port" value="Puerto" />
                        <x-text-input id="mail_port" name="mail_port" type="text" class="mt-1 block w-full" x-model="mailPort" />
                        <x-input-error :messages="$errors->get('mail_port')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="mail_username" value="Usuario" />
                        <x-text-input id="mail_username" name="mail_username" type="text" class="mt-1 block w-full" x-model="mailUsername" />
                        <x-input-error :messages="$errors->get('mail_username')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="mail_password" value="Contraseña" />
                        <x-text-input id="mail_password" name="mail_password" type="password" class="mt-1 block w-full" autocomplete="new-password" />
                        <x-input-error :messages="$errors->get('mail_password')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="mail_encryption" value="Encriptacion" />
                        <select id="mail_encryption" name="mail_encryption" x-model="mailEncryption" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">Sin encriptacion</option>
                            <option value="ssl">SSL</option>
                            <option value="tls">TLS</option>
                        </select>
                        <x-input-error :messages="$errors->get('mail_encryption')" class="mt-2" />
                    </div>
                </div>
            </div>

            <div class="bg-white shadow rounded-lg p-6 mt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Logo</h3>
                <div>
                    <x-input-label for="logo" value="Logo (imagen)" />
                    <input id="logo" name="logo" type="file" accept="image/*" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                    <x-input-error :messages="$errors->get('logo')" class="mt-2" />
                </div>
            </div>
        </div>

        <!-- PASO 4: Suscripcion + Usuario -->
        <div x-show="currentStep === 3" x-transition>
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Suscripcion</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-input-label for="plan_id" value="Plan" />
                        <select id="plan_id" name="plan_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                            <option value="">Seleccione un plan</option>
                            @foreach($planes as $plan)
                                <option value="{{ $plan->id }}" {{ old('plan_id') == $plan->id ? 'selected' : '' }}>{{ $plan->nombre }} - ${{ $plan->precio }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('plan_id')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="fecha_inicio" value="Fecha Inicio" />
                        <x-text-input id="fecha_inicio" name="fecha_inicio" type="date" class="mt-1 block w-full" :value="old('fecha_inicio', date('Y-m-d'))" required />
                        <x-input-error :messages="$errors->get('fecha_inicio')" class="mt-2" />
                    </div>
                </div>
            </div>

            <div class="bg-white shadow rounded-lg p-6 mt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Usuario Administrador del Emisor</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-input-label for="admin_username" value="Usuario" />
                        <x-text-input id="admin_username" name="admin_username" type="text" class="mt-1 block w-full" :value="old('admin_username')" required />
                        <x-input-error :messages="$errors->get('admin_username')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="admin_nombre" value="Nombre" />
                        <x-text-input id="admin_nombre" name="admin_nombre" type="text" class="mt-1 block w-full" :value="old('admin_nombre')" required />
                        <x-input-error :messages="$errors->get('admin_nombre')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="admin_apellido" value="Apellido" />
                        <x-text-input id="admin_apellido" name="admin_apellido" type="text" class="mt-1 block w-full" :value="old('admin_apellido')" required />
                        <x-input-error :messages="$errors->get('admin_apellido')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="admin_email" value="Email" />
                        <x-text-input id="admin_email" name="admin_email" type="email" class="mt-1 block w-full" :value="old('admin_email')" required />
                        <x-input-error :messages="$errors->get('admin_email')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="admin_password" value="Contraseña" />
                        <x-text-input id="admin_password" name="admin_password" type="password" class="mt-1 block w-full" required autocomplete="new-password" />
                        <x-input-error :messages="$errors->get('admin_password')" class="mt-2" />
                    </div>
                </div>
            </div>

            <!-- Resumen -->
            <div class="bg-amber-50 border border-amber-200 shadow rounded-lg p-6 mt-6">
                <h3 class="text-lg font-semibold text-amber-800 mb-3">Resumen</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-500">RUC:</span>
                        <span class="font-medium ml-1" x-text="form.ruc || '-'"></span>
                    </div>
                    <div>
                        <span class="text-gray-500">Razon Social:</span>
                        <span class="font-medium ml-1" x-text="form.razon_social || '-'"></span>
                    </div>
                    <div>
                        <span class="text-gray-500">Establecimiento:</span>
                        <span class="font-medium ml-1" x-text="form.estab_codigo + ' - ' + form.estab_nombre"></span>
                    </div>
                    <div>
                        <span class="text-gray-500">Punto Emision:</span>
                        <span class="font-medium ml-1" x-text="form.pto_codigo + ' - ' + form.pto_nombre"></span>
                    </div>
                    <div>
                        <span class="text-gray-500">Ambiente:</span>
                        <span class="font-medium ml-1" x-text="form.ambiente === '1' ? 'Pruebas' : 'Produccion'"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botones de navegacion -->
        <div class="flex justify-between mt-6">
            <button type="button" x-show="currentStep > 0" @click="currentStep--"
                    class="px-5 py-2.5 bg-gray-200 text-gray-700 rounded-md text-sm font-medium hover:bg-gray-300 transition-colors">
                &larr; Anterior
            </button>
            <div x-show="currentStep === 0"></div>

            <button type="button" x-show="currentStep < 3" @click="siguientePaso()"
                    class="px-5 py-2.5 bg-indigo-600 text-white rounded-md text-sm font-medium hover:bg-indigo-700 transition-colors">
                Siguiente &rarr;
            </button>

            <button type="submit" x-show="currentStep === 3"
                    style="background-color: #059669; color: #ffffff; padding: 12px 32px; font-size: 16px; font-weight: 700; border-radius: 8px; border: none; cursor: pointer; box-shadow: 0 4px 6px rgba(0,0,0,0.1); display: flex; align-items: center; gap: 8px;"
                    onmouseover="this.style.backgroundColor='#047857'"
                    onmouseout="this.style.backgroundColor='#059669'">
                <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                Finalizar y Crear Emisor
            </button>
        </div>
    </form>

    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('wizardEmisor', () => ({
            currentStep: 0,
            steps: ['Datos Emisor', 'Establecimiento', 'Firma y Correo', 'Plan y Usuario'],
            consultando: false,
            sriMensaje: '',
            sriError: false,
            sriDatos: null,
            rucBusqueda: '',

            form: {
                ruc: '{{ old('ruc', '') }}',
                razon_social: '{{ old('razon_social', '') }}',
                nombre_comercial: '{{ old('nombre_comercial', '') }}',
                direccion_matriz: '{{ old('direccion_matriz', '') }}',
                ambiente: '{{ old('ambiente', '1') }}',
                obligado_contabilidad: {{ old('obligado_contabilidad') ? 'true' : 'false' }},
                contribuyente_especial: '{{ old('contribuyente_especial', '') }}',
                agente_retencion: '{{ old('agente_retencion', '') }}',
                regimen: '{{ old('regimen', 'GENERAL') }}',
                codigo_numerico: '{{ old('codigo_numerico', '00000001') }}',
                estab_codigo: '{{ old('estab_codigo', '001') }}',
                estab_nombre: '{{ old('estab_nombre', 'Matriz') }}',
                estab_direccion: '{{ old('estab_direccion', '') }}',
                pto_codigo: '{{ old('pto_codigo', '001') }}',
                pto_nombre: '{{ old('pto_nombre', 'Punto de Emision 1') }}',
            },

            async consultarRuc() {
                if (this.rucBusqueda.length !== 13) return;

                this.consultando = true;
                this.sriMensaje = '';
                this.sriError = false;
                this.sriDatos = null;

                try {
                    const response = await fetch(`/admin/api/consultar-ruc/${this.rucBusqueda}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        this.sriError = true;
                        this.sriMensaje = data.error || 'Error al consultar el SRI.';
                        return;
                    }

                    // Auto-llenar campos
                    this.sriDatos = data;
                    this.form.ruc = data.ruc || this.rucBusqueda;
                    if (data.razon_social) this.form.razon_social = data.razon_social;
                    if (data.nombre_comercial) this.form.nombre_comercial = data.nombre_comercial;
                    if (data.direccion) this.form.direccion_matriz = data.direccion;
                    if (data.obligado_contabilidad !== undefined) this.form.obligado_contabilidad = data.obligado_contabilidad;
                    if (data.regimen) this.form.regimen = data.regimen;

                    // Auto-llenar direccion del establecimiento
                    if (data.direccion && !this.form.estab_direccion) {
                        this.form.estab_direccion = data.direccion;
                    }

                    this.sriMensaje = 'Datos cargados exitosamente: ' + (data.razon_social || 'Contribuyente encontrado');
                } catch (error) {
                    this.sriError = true;
                    this.sriMensaje = 'Error de conexion al consultar el SRI.';
                } finally {
                    this.consultando = false;
                }
            },

            siguientePaso() {
                if (this.currentStep >= 3) return;

                // Validacion basica por paso
                if (this.currentStep === 0) {
                    if (!this.form.ruc || this.form.ruc.length !== 13) {
                        alert('El RUC debe tener 13 digitos.');
                        return;
                    }
                    if (!this.form.razon_social) {
                        alert('La razon social es obligatoria.');
                        return;
                    }
                }
                if (this.currentStep === 1) {
                    if (!this.form.estab_codigo || !this.form.pto_codigo) {
                        alert('El codigo de establecimiento y punto de emision son obligatorios.');
                        return;
                    }
                }
                this.currentStep++;
            }
        }));

        Alpine.data('mailConfig', () => ({
            dominio: '',
            mailHost: '{{ old('mail_host') }}',
            mailPort: '{{ old('mail_port') }}',
            mailUsername: '{{ old('mail_username') }}',
            mailEncryption: '{{ old('mail_encryption') }}',

            aplicarDominio() {
                if (!this.dominio) return;
                let d = this.dominio.replace(/^https?:\/\//, '').replace(/\/.*$/, '').trim();
                this.mailHost = 'mail.' + d;
                this.mailPort = '465';
                this.mailUsername = 'comprobantes@' + d;
                this.mailEncryption = 'ssl';
            }
        }));
    });
    </script>
</x-admin-layout>
