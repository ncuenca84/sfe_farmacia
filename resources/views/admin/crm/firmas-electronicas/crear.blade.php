<x-admin-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Nueva Firma Electrónica</h2>
            <a href="{{ route('admin.crm.firmas-electronicas.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-md text-sm hover:bg-gray-600">Volver</a>
        </div>
    </x-slot>

    <div class="max-w-4xl space-y-6">
        {{-- Lectura automática del .p12 --}}
        <div class="bg-blue-50 rounded-xl border border-blue-200 p-6">
            <h3 class="text-base font-semibold text-blue-900 mb-1">Lectura Automática del Certificado .p12</h3>
            <p class="text-sm text-blue-700 mb-4">Suba el archivo .p12 con su contraseña y los datos se llenarán automáticamente.</p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-blue-800 mb-1">Archivo .p12</label>
                    <input type="file" id="p12_auto" accept=".p12" class="w-full text-sm border border-blue-300 rounded-md px-3 py-2 bg-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-blue-800 mb-1">Contraseña</label>
                    <input type="password" id="p12_password_auto" class="w-full rounded-md border-blue-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" placeholder="Contraseña del certificado">
                </div>
                <div class="flex items-end">
                    <button type="button" id="btn_leer_p12" class="bg-blue-600 text-white px-6 py-2 rounded-md text-sm hover:bg-blue-700 font-medium w-full">
                        Leer Certificado
                    </button>
                </div>
            </div>
            <div id="p12_resultado" class="mt-3 hidden">
                <p class="text-sm font-medium text-green-700" id="p12_exito"></p>
                <p class="text-sm font-medium text-red-600" id="p12_error"></p>
            </div>
        </div>

        {{-- Formulario manual --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-base font-semibold text-gray-800 mb-4">Datos de la Firma Electrónica</h3>
            <form method="POST" action="{{ route('admin.crm.firmas-electronicas.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
                    <div>
                        <label for="identificacion" class="block text-sm font-medium text-gray-700 mb-1">Identificación (Cédula/RUC) *</label>
                        <input type="text" name="identificacion" id="identificacion" value="{{ old('identificacion') }}" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" maxlength="20">
                        @error('identificacion') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="correo" class="block text-sm font-medium text-gray-700 mb-1">Correo</label>
                        <input type="email" name="correo" id="correo" value="{{ old('correo') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        @error('correo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="nombres" class="block text-sm font-medium text-gray-700 mb-1">Nombres *</label>
                        <input type="text" name="nombres" id="nombres" value="{{ old('nombres') }}" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        @error('nombres') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="apellidos" class="block text-sm font-medium text-gray-700 mb-1">Apellidos *</label>
                        <input type="text" name="apellidos" id="apellidos" value="{{ old('apellidos') }}" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        @error('apellidos') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="celular" class="block text-sm font-medium text-gray-700 mb-1">Celular</label>
                        <input type="text" name="celular" id="celular" value="{{ old('celular') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" maxlength="20">
                        @error('celular') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="emisor_id" class="block text-sm font-medium text-gray-700 mb-1">Asociar a Emisor</label>
                        <select name="emisor_id" id="emisor_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                            <option value="">-- Sin asociar --</option>
                            @foreach($emisores as $emisor)
                            <option value="{{ $emisor->id }}">{{ $emisor->razon_social }} ({{ $emisor->ruc }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="fecha_inicio" class="block text-sm font-medium text-gray-700 mb-1">Fecha de Creación</label>
                        <input type="date" name="fecha_inicio" id="fecha_inicio" value="{{ old('fecha_inicio') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        @error('fecha_inicio') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="fecha_fin" class="block text-sm font-medium text-gray-700 mb-1">Fecha de Expiración</label>
                        <input type="date" name="fecha_fin" id="fecha_fin" value="{{ old('fecha_fin') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        @error('fecha_fin') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
                    <div>
                        <label for="archivo_p12" class="block text-sm font-medium text-gray-700 mb-1">Archivo .p12</label>
                        <input type="file" name="archivo_p12" id="archivo_p12" accept=".p12" class="w-full text-sm border border-gray-300 rounded-md px-3 py-2">
                        @error('archivo_p12') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="password_p12" class="block text-sm font-medium text-gray-700 mb-1">Contraseña del .p12</label>
                        <input type="password" name="password_p12" id="password_p12" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        @error('password_p12') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mb-6">
                    <label for="observaciones" class="block text-sm font-medium text-gray-700 mb-1">Observaciones</label>
                    <textarea name="observaciones" id="observaciones" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">{{ old('observaciones') }}</textarea>
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('admin.crm.firmas-electronicas.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">Cancelar</a>
                    <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-md text-sm hover:bg-green-700 font-medium">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    document.getElementById('btn_leer_p12').addEventListener('click', async function() {
        const archivo = document.getElementById('p12_auto').files[0];
        const password = document.getElementById('p12_password_auto').value;
        const resultado = document.getElementById('p12_resultado');
        const exito = document.getElementById('p12_exito');
        const error = document.getElementById('p12_error');

        if (!archivo || !password) {
            resultado.classList.remove('hidden');
            error.textContent = 'Seleccione el archivo .p12 e ingrese la contraseña.';
            exito.textContent = '';
            return;
        }

        this.disabled = true;
        this.textContent = 'Leyendo...';

        const formData = new FormData();
        formData.append('archivo', archivo);
        formData.append('password', password);
        formData.append('_token', '{{ csrf_token() }}');

        try {
            const response = await fetch('{{ route("admin.crm.firmas-electronicas.leer-p12") }}', {
                method: 'POST',
                body: formData,
            });
            const data = await response.json();

            resultado.classList.remove('hidden');

            if (data.success) {
                const d = data.datos;
                document.getElementById('identificacion').value = d.identificacion || '';
                document.getElementById('nombres').value = d.nombres || '';
                document.getElementById('apellidos').value = d.apellidos || '';
                document.getElementById('correo').value = d.correo || '';
                document.getElementById('fecha_inicio').value = d.fecha_inicio || '';
                document.getElementById('fecha_fin').value = d.fecha_fin || '';

                exito.textContent = 'Datos extraídos correctamente del certificado. CN: ' + (d.emisor_cn || '') + ' | Org: ' + (d.organizacion || '');
                error.textContent = '';

                // Copiar archivo al input del formulario
                const dt = new DataTransfer();
                dt.items.add(archivo);
                document.getElementById('archivo_p12').files = dt.files;
                document.getElementById('password_p12').value = password;
            } else {
                error.textContent = data.error || 'Error al leer el certificado.';
                exito.textContent = '';
            }
        } catch (e) {
            resultado.classList.remove('hidden');
            error.textContent = 'Error de conexión. Intente de nuevo.';
            exito.textContent = '';
        }

        this.disabled = false;
        this.textContent = 'Leer Certificado';
    });
    </script>
    @endpush
</x-admin-layout>
