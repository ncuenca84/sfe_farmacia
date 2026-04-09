<x-emisor-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Registrar Compra desde XML</h2>
            <a href="{{ route('emisor.compras.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 text-sm">Volver</a>
        </div>
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <p class="text-sm text-blue-800">
                <strong>Instrucciones:</strong> Suba el archivo XML de la factura de compra autorizada por el SRI.
                El sistema extraera automaticamente los datos del proveedor, productos y totales.
            </p>
        </div>

        @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6 text-sm">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
        @endif

        <form method="POST" action="{{ route('emisor.compras.preview') }}" enctype="multipart/form-data">
            @csrf
            <div class="bg-white rounded-lg shadow p-8">
                <div class="flex flex-col items-center justify-center" x-data="{ fileName: '' }">
                    <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    <label class="cursor-pointer">
                        <span class="inline-flex items-center gap-2 bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 text-sm font-medium transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            Seleccionar archivo XML
                        </span>
                        <input type="file" name="xml_file" accept=".xml" class="hidden" required
                               @change="fileName = $event.target.files[0]?.name || ''">
                    </label>
                    <p class="mt-3 text-sm text-gray-500" x-show="fileName" x-text="fileName"></p>
                    <p class="mt-2 text-xs text-gray-400">Solo archivos .xml (max 2MB)</p>
                </div>

                <div class="mt-6 flex justify-center">
                    <button type="submit" class="inline-flex items-center gap-2 bg-green-600 text-white px-8 py-3 rounded-xl hover:bg-green-700 text-sm font-medium shadow-sm transition-all duration-150">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        Previsualizar
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-emisor-layout>
