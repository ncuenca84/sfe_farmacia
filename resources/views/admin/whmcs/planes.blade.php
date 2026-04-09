<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">WHMCS - Mapeo de Planes</h2>
    </x-slot>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Plan Nombre</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Precio</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">WHMCS Package ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($planes as $plan)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $plan->nombre }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${{ number_format($plan->precio, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm" colspan="2">
                                <form method="POST" action="{{ route('admin.whmcs.planes.mapear', $plan) }}" class="flex items-center space-x-2">
                                    @csrf
                                    <x-text-input name="whmcs_package_id" type="text" class="block w-32 text-sm" :value="$plan->whmcs_package_id" placeholder="Package ID" />
                                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-1.5 px-3 rounded-md text-sm">Guardar</button>
                                </form>
                                <x-input-error :messages="$errors->get('whmcs_package_id')" class="mt-1" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">No hay planes registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin-layout>
