<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.puntos-emision.index') }}" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <h2 class="text-xl font-semibold text-gray-800">Nuevo Punto de Emisión</h2>
        </div>
    </x-slot>

    <div class="max-w-2xl">
        <form method="POST" action="{{ route('admin.puntos-emision.store') }}">
            @csrf
            <div class="bg-white rounded-lg shadow p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Emisor *</label>
                    <select id="emisor_select" class="w-full border-gray-300 rounded-md shadow-sm text-sm" onchange="cargarEstablecimientos(this.value)">
                        <option value="">Seleccione un emisor</option>
                        @foreach($emisores as $emisor)
                        <option value="{{ $emisor->id }}" {{ request('emisor_id') == $emisor->id ? 'selected' : '' }}>{{ $emisor->ruc }} - {{ $emisor->razon_social }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Establecimiento *</label>
                    <select name="establecimiento_id" id="establecimiento_select" required class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                        <option value="">Seleccione un establecimiento</option>
                        @foreach($establecimientos as $est)
                        <option value="{{ $est->id }}" {{ old('establecimiento_id') == $est->id ? 'selected' : '' }}>{{ $est->codigo }} - {{ $est->nombre ?? $est->direccion }}</option>
                        @endforeach
                    </select>
                    @error('establecimiento_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Codigo *</label>
                        <input type="text" name="codigo" value="{{ old('codigo') }}" required maxlength="3" placeholder="001" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                        @error('codigo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                        <input type="text" name="nombre" value="{{ old('nombre') }}" placeholder="Caja 1" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                    </div>
                </div>
                <div class="flex items-center">
                    <input type="hidden" name="activo" value="0">
                    <input type="checkbox" name="activo" value="1" checked class="rounded border-gray-300 text-indigo-600 shadow-sm">
                    <span class="ml-2 text-sm text-gray-700">Activo</span>
                </div>
            </div>
            <div class="flex justify-end mt-4 space-x-3">
                <a href="{{ route('admin.puntos-emision.index') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 text-sm font-medium">Cancelar</a>
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 text-sm font-medium">Crear</button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
    function cargarEstablecimientos(emisorId) {
        const select = document.getElementById('establecimiento_select');
        select.innerHTML = '<option value="">Cargando...</option>';
        if (!emisorId) { select.innerHTML = '<option value="">Seleccione un emisor primero</option>'; return; }
        window.location.href = '{{ route("admin.puntos-emision.create") }}?emisor_id=' + emisorId;
    }
    </script>
    @endpush
</x-admin-layout>
