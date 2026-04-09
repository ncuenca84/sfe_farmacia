<x-emisor-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Impuestos</h2>
    </x-slot>

    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded text-sm mb-4">
            Los impuestos son administrados por el sistema. Estos valores se aplican automaticamente a los comprobantes electronicos.
        </div>

        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Codigo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Tarifa</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($impuestos as $impuesto)
                <tr>
                    <td class="px-6 py-4 text-sm text-gray-900">{{ $impuesto->codigo ?? $impuesto->id }}</td>
                    <td class="px-6 py-4 text-sm text-gray-900">{{ $impuesto->nombre }}</td>
                    <td class="px-6 py-4 text-sm text-gray-900 text-right">{{ $impuesto->tarifa ?? 0 }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-emisor-layout>
