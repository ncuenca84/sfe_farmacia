<x-emisor-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Editar Cliente: {{ $cliente->razon_social }}</h2>
            <a href="{{ route('emisor.configuracion.clientes.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 text-sm">Volver</a>
        </div>
    </x-slot>

    <div class="bg-white rounded-lg shadow p-6" x-data="consultaCliente()">
        <form method="POST" action="{{ route('emisor.configuracion.clientes.update', $cliente) }}">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Identificacion</label>
                    <select name="tipo_identificacion" x-model="tipoIdentificacion" class="w-full border-gray-300 rounded-md shadow-sm text-sm" required>
                        <option value="04" {{ old('tipo_identificacion', $cliente->tipo_identificacion) == '04' ? 'selected' : '' }}>04 - RUC</option>
                        <option value="05" {{ old('tipo_identificacion', $cliente->tipo_identificacion) == '05' ? 'selected' : '' }}>05 - Cedula</option>
                        <option value="06" {{ old('tipo_identificacion', $cliente->tipo_identificacion) == '06' ? 'selected' : '' }}>06 - Pasaporte</option>
                        <option value="07" {{ old('tipo_identificacion', $cliente->tipo_identificacion) == '07' ? 'selected' : '' }}>07 - Consumidor Final</option>
                        <option value="08" {{ old('tipo_identificacion', $cliente->tipo_identificacion) == '08' ? 'selected' : '' }}>08 - Exterior</option>
                    </select>
                    @error('tipo_identificacion') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Identificacion</label>
                    <div class="flex gap-2">
                        <input type="text" name="identificacion" x-model="identificacion" value="{{ old('identificacion', $cliente->identificacion) }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm" required>
                        <button type="button" @click="consultar()" :disabled="consultando || !puedeConsultar" class="bg-green-600 text-white px-3 py-2 rounded hover:bg-green-700 text-sm whitespace-nowrap disabled:opacity-50 disabled:cursor-not-allowed" title="Consultar datos en el SRI">
                            <span x-show="!consultando">Consultar</span>
                            <span x-show="consultando">Consultando...</span>
                        </button>
                    </div>
                    @error('identificacion') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    <p x-show="mensaje" x-text="mensaje" :class="error ? 'text-red-500' : 'text-green-600'" class="text-xs mt-1"></p>
                    <p x-show="tipoIdentificacion === '05' && !mensaje" class="text-xs text-gray-400 mt-1">Solo funciona si la persona tiene RUC registrado en el SRI</p>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Razon Social</label>
                    <input type="text" name="razon_social" x-model="razonSocial" value="{{ old('razon_social', $cliente->razon_social) }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm" required>
                    @error('razon_social') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Direccion</label>
                    <input type="text" name="direccion" x-model="direccion" value="{{ old('direccion', $cliente->direccion) }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                    @error('direccion') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email(s)</label>
                    <input type="text" name="email" value="{{ old('email', $cliente->email) }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm" placeholder="email1@ejemplo.com, email2@ejemplo.com">
                    <p class="text-xs text-gray-500 mt-1">Para multiples emails, separar con coma (,)</p>
                    @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Telefono</label>
                    <input type="text" name="telefono" value="{{ old('telefono', $cliente->telefono) }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                    @error('telefono') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex justify-end mt-6">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Actualizar Cliente</button>
            </div>
        </form>
    </div>

    <script>
    function consultaCliente() {
        return {
            tipoIdentificacion: '{{ old('tipo_identificacion', $cliente->tipo_identificacion instanceof \BackedEnum ? $cliente->tipo_identificacion->value : $cliente->tipo_identificacion) }}',
            identificacion: '{{ old('identificacion', $cliente->identificacion) }}',
            razonSocial: '{{ old('razon_social', $cliente->razon_social) }}',
            direccion: '{{ old('direccion', $cliente->direccion) }}',
            consultando: false,
            mensaje: '',
            error: false,

            get puedeConsultar() {
                return (this.tipoIdentificacion === '04' || this.tipoIdentificacion === '05')
                    && this.identificacion.length >= 10;
            },

            async consultar() {
                if (!this.puedeConsultar) return;

                this.consultando = true;
                this.mensaje = '';
                this.error = false;

                try {
                    const response = await fetch(`/emisor/api/clientes/consultar/${this.identificacion}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        this.error = true;
                        this.mensaje = data.error || 'No se encontraron datos.';
                        return;
                    }

                    if (data.razon_social) this.razonSocial = data.razon_social;
                    if (data.direccion) this.direccion = data.direccion;
                    this.mensaje = 'Datos cargados: ' + (data.razon_social || 'Contribuyente encontrado');
                } catch (e) {
                    this.error = true;
                    this.mensaje = 'Error de conexion al consultar el SRI.';
                } finally {
                    this.consultando = false;
                }
            }
        }
    }
    </script>
</x-emisor-layout>
