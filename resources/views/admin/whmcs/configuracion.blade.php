<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">WHMCS - Configuracion</h2>
    </x-slot>

    <!-- Estado Actual -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Configuracion Actual</h3>
        @if($config)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <span class="text-sm font-medium text-gray-500">WHMCS URL</span>
                    <p class="text-gray-900">{{ $config->whmcs_url ?? 'No configurado' }}</p>
                </div>
                <div>
                    <span class="text-sm font-medium text-gray-500">API Key</span>
                    <p class="text-gray-900 font-mono">
                        @if($config->api_key)
                            {{ Str::mask($config->api_key, '*', 8) }}
                        @else
                            No generada
                        @endif
                    </p>
                </div>
            </div>
        @else
            <p class="text-gray-500">No hay configuracion WHMCS registrada.</p>
        @endif
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Formulario URL -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Actualizar URL</h3>
            <form method="POST" action="{{ route('admin.whmcs.configuracion.guardar') }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <x-input-label for="whmcs_url" value="WHMCS URL" />
                        <x-text-input id="whmcs_url" name="whmcs_url" type="url" class="mt-1 block w-full" :value="old('whmcs_url', $config?->whmcs_url)" placeholder="https://whmcs.example.com" required />
                        <x-input-error :messages="$errors->get('whmcs_url')" class="mt-2" />
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-md text-sm">Guardar URL</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Generar API Key -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">API Key</h3>
            <p class="text-sm text-gray-500 mb-4">Regenerar la API key invalidara la key anterior. Los servicios WHMCS necesitaran la nueva key para comunicarse.</p>
            <form method="POST" action="{{ route('admin.whmcs.generar-api-key') }}">
                @csrf
                <div class="flex justify-end">
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-md text-sm" onclick="return confirm('Esta seguro de regenerar la API Key? La key anterior dejara de funcionar.')">Regenerar API Key</button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
