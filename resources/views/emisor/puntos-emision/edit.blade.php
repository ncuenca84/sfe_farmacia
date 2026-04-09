<x-emisor-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Editar Punto de Emision {{ $puntosEmision->codigo }}</h2>
            <a href="{{ route('emisor.configuracion.puntos-emision.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 text-sm">Volver</a>
        </div>
    </x-slot>

    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('emisor.configuracion.puntos-emision.update', $puntosEmision) }}">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Establecimiento</label>
                    <select name="establecimiento_id" class="w-full border-gray-300 rounded-md shadow-sm text-sm" required>
                        <option value="">Seleccionar...</option>
                        @foreach($establecimientos as $est)
                            <option value="{{ $est->id }}" {{ old('establecimiento_id', $puntosEmision->establecimiento_id) == $est->id ? 'selected' : '' }}>
                                {{ $est->codigo }} - {{ $est->nombre ?? $est->direccion }}
                            </option>
                        @endforeach
                    </select>
                    @error('establecimiento_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Codigo (3 digitos)</label>
                    <input type="text" name="codigo" value="{{ old('codigo', $puntosEmision->codigo) }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm" required maxlength="3">
                    @error('codigo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Descripcion</label>
                    <input type="text" name="nombre" value="{{ old('nombre', $puntosEmision->nombre) }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                    @error('nombre') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="flex items-center">
                        <input type="checkbox" name="activo" value="1" {{ old('activo', $puntosEmision->activo) ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600 shadow-sm">
                        <span class="ml-2 text-sm text-gray-700">Activo</span>
                    </label>
                </div>
            </div>

            <!-- Secuenciales Iniciales -->
            <div class="md:col-span-2 mt-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Secuenciales Iniciales</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Factura</label>
                        <input type="number" name="sec_factura" value="{{ old('sec_factura', $puntosEmision->sec_factura) }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm" min="0">
                        @error('sec_factura') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nota de Credito</label>
                        <input type="number" name="sec_nota_credito" value="{{ old('sec_nota_credito', $puntosEmision->sec_nota_credito) }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm" min="0">
                        @error('sec_nota_credito') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nota de Debito</label>
                        <input type="number" name="sec_nota_debito" value="{{ old('sec_nota_debito', $puntosEmision->sec_nota_debito) }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm" min="0">
                        @error('sec_nota_debito') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Retencion</label>
                        <input type="number" name="sec_retencion" value="{{ old('sec_retencion', $puntosEmision->sec_retencion) }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm" min="0">
                        @error('sec_retencion') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Guia de Remision</label>
                        <input type="number" name="sec_guia" value="{{ old('sec_guia', $puntosEmision->sec_guia) }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm" min="0">
                        @error('sec_guia') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Liquidacion de Compra</label>
                        <input type="number" name="sec_liquidacion" value="{{ old('sec_liquidacion', $puntosEmision->sec_liquidacion) }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm" min="0">
                        @error('sec_liquidacion') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Proforma</label>
                        <input type="number" name="sec_proforma" value="{{ old('sec_proforma', $puntosEmision->sec_proforma) }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm" min="0">
                        @error('sec_proforma') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="flex justify-end mt-6">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Actualizar Punto de Emision</button>
            </div>
        </form>
    </div>
</x-emisor-layout>
