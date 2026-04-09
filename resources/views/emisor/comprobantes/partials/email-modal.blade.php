@props(['action', 'clienteEmail' => ''])

<div x-data="{ showEmailModal: false, emailInput: '{{ $clienteEmail }}', sending: false }" class="inline">
    <button type="button" @click="showEmailModal = true" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 text-sm">Enviar Email</button>

    <div x-show="showEmailModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" @keydown.escape.window="showEmailModal = false">
        <div class="fixed inset-0 bg-black bg-opacity-50" @click="showEmailModal = false"></div>
        <div class="bg-white rounded-lg shadow-xl w-full max-w-lg mx-4 relative z-10">
            <div class="flex justify-between items-center px-6 py-4 border-b">
                <h3 class="text-lg font-medium text-gray-900">Enviar Email</h3>
                <button type="button" @click="showEmailModal = false" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
            </div>
            <div class="px-6 py-4">
                <div class="bg-blue-50 border border-blue-200 rounded p-3 mb-4 text-sm text-blue-800">
                    Ingrese el email al cual desea enviar el documento. Para mas de un email separarlo por coma (,) ejemplo: email1@gmail.com,email2@gmail.com
                </div>
                <form method="POST" action="{{ $action }}" @submit="sending = true">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="text" name="emails" x-model="emailInput" class="w-full border-gray-300 rounded-md shadow-sm text-sm" placeholder="Email separados por coma" required>
                    </div>
                    <div class="flex justify-start">
                        <button type="submit" :disabled="sending" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm disabled:opacity-50">
                            <span x-show="!sending">Enviar</span>
                            <span x-show="sending">Enviando...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
