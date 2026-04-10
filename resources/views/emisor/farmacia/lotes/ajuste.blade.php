<x-emisor-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">Ajustar Lote: {{ $lote->numero_lote }}</h2>
                <p class="text-sm text-gray-500 mt-1">{{ $lote->producto->nombre }} - {{ $lote->establecimiento->nombre }}</p>
            </div>
            <a href="{{ route('emisor.farmacia.lotes.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 text-sm">Volver</a>
        </div>
    </x-slot>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="grid grid-cols-3 gap-4 mb-6 pb-6 border-b">
            <div>
                <p class="text-xs text-gray-500">Cantidad Actual</p>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($lote->cantidad_actual, 2) }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Cantidad Inicial</p>
                <p class="text-lg text-gray-500">{{ number_format($lote->cantidad_inicial, 2) }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Vencimiento</p>
                <p class="text-lg {{ $lote->estaVencido() ? 'text-red-600 font-bold' : 'text-gray-500' }}">
                    {{ $lote->fecha_vencimiento ? $lote->fecha_vencimiento->format('d/m/Y') : '-' }}
                </p>
            </div>
        </div>

        <form method="POST" action="{{ route('emisor.farmacia.lotes.guardar-ajuste', $lote) }}">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nueva Cantidad</label>
                    <input type="number" name="cantidad_actual" value="{{ old('cantidad_actual', $lote->cantidad_actual) }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm" step="any" min="0" required>
                    @error('cantidad_actual') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Motivo del Ajuste</label>
                    <input type="text" name="descripcion" value="{{ old('descripcion') }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm" placeholder="Ej: Conteo fisico, merma, rotura...">
                    @error('descripcion') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="flex justify-end mt-6">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Guardar Ajuste</button>
            </div>
        </form>
    </div>
</x-emisor-layout>
