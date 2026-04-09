<x-admin-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Planes</h2>
            <a href="{{ route('admin.planes.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-md text-sm">Nuevo Plan</a>
        </div>
    </x-slot>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Comprobantes</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Periodo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Precio</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Suscripciones</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Activo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($planes as $plan)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $plan->nombre }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $plan->cant_comprobante == 0 ? 'Ilimitado' : $plan->cant_comprobante }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $plan->tipo_periodo }}{{ $plan->tipo_periodo === 'DIAS' ? ' (' . $plan->dias . ')' : '' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${{ number_format($plan->precio, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $plan->suscripciones_count }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($plan->activo)
                                    <span class="text-green-600 font-medium">Si</span>
                                @else
                                    <span class="text-red-600 font-medium">No</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <a href="{{ route('admin.planes.edit', $plan) }}" class="text-yellow-600 hover:text-yellow-900">Editar</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">No hay planes registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $planes->links() }}
        </div>
    </div>
</x-admin-layout>
