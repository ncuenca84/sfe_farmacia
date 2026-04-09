<x-admin-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center space-x-2">
                <a href="{{ route('admin.emisores.index') }}" class="text-indigo-600 hover:text-indigo-800">&larr; Emisores</a>
                <span class="text-gray-400">/</span>
                <h2 class="text-xl font-semibold text-gray-800">{{ $emisor->razon_social }}</h2>
            </div>
            <a href="{{ route('admin.emisores.edit', $emisor) }}" class="bg-yellow-500 text-white px-4 py-2 rounded-md text-sm hover:bg-yellow-600">Editar</a>
        </div>
    </x-slot>

    <!-- Datos Generales -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Datos Generales</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div>
                <span class="text-sm font-medium text-gray-500">RUC</span>
                <p class="text-gray-900">{{ $emisor->ruc }}</p>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-500">Razon Social</span>
                <p class="text-gray-900">{{ $emisor->razon_social }}</p>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-500">Nombre Comercial</span>
                <p class="text-gray-900">{{ $emisor->nombre_comercial ?? '-' }}</p>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-500">Direccion Matriz</span>
                <p class="text-gray-900">{{ $emisor->direccion_matriz }}</p>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-500">Ambiente</span>
                <p class="text-gray-900">{{ $emisor->ambiente == 1 ? 'Pruebas' : 'Produccion' }}</p>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-500">Obligado Contabilidad</span>
                <p class="text-gray-900">{{ $emisor->obligado_contabilidad ? 'Si' : 'No' }}</p>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-500">Contribuyente Especial</span>
                <p class="text-gray-900">{{ $emisor->contribuyente_especial ?? '-' }}</p>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-500">Agente de Retencion</span>
                <p class="text-gray-900">{{ $emisor->agente_retencion ?? '-' }}</p>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-500">Regimen</span>
                <p class="text-gray-900">{{ $emisor->regimen?->nombre() ?? '-' }}</p>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-500">Codigo Numerico</span>
                <p class="text-gray-900">{{ $emisor->codigo_numerico }}</p>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-500">Activo</span>
                <p>
                    @if($emisor->activo)
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Si</span>
                    @else
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">No</span>
                    @endif
                </p>
            </div>
        </div>
    </div>

    <!-- Suscripcion Activa -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Suscripcion Activa</h3>
        @if($emisor->suscripcionActiva)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <span class="text-sm font-medium text-gray-500">Plan</span>
                    <p class="text-gray-900">{{ $emisor->suscripcionActiva->plan->nombre }}</p>
                </div>
                <div>
                    <span class="text-sm font-medium text-gray-500">Fecha Inicio</span>
                    <p class="text-gray-900">{{ $emisor->suscripcionActiva->fecha_inicio->format('d/m/Y') }}</p>
                </div>
                <div>
                    <span class="text-sm font-medium text-gray-500">Fecha Fin</span>
                    <p class="text-gray-900">{{ $emisor->suscripcionActiva->fecha_fin->format('d/m/Y') }}</p>
                </div>
                <div>
                    <span class="text-sm font-medium text-gray-500">Comprobantes Usados</span>
                    <p class="text-gray-900">{{ $emisor->suscripcionActiva->comprobantes_emitidos }} / {{ $emisor->suscripcionActiva->plan->cant_comprobante == 0 ? 'Ilimitado' : $emisor->suscripcionActiva->plan->cant_comprobante }}</p>
                </div>
            </div>
        @else
            <p class="text-gray-500">No tiene suscripcion activa.</p>
        @endif
    </div>

    <!-- Establecimientos -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Establecimientos</h3>
        @forelse($emisor->establecimientos as $establecimiento)
            <div class="mb-4 border rounded-lg p-4">
                <div class="flex justify-between items-center mb-2">
                    <h4 class="font-medium text-gray-800">{{ $establecimiento->codigo }} - {{ $establecimiento->nombre }}</h4>
                    <span class="text-sm text-gray-500">{{ $establecimiento->direccion }}</span>
                </div>
                @if($establecimiento->ptoEmisiones->count())
                    <div class="ml-4">
                        <span class="text-sm font-medium text-gray-500">Puntos de Emision:</span>
                        <ul class="list-disc list-inside text-sm text-gray-700 mt-1">
                            @foreach($establecimiento->ptoEmisiones as $ptoEmision)
                                <li>{{ $ptoEmision->codigo }} - {{ $ptoEmision->descripcion ?? 'Sin descripcion' }}</li>
                            @endforeach
                        </ul>
                    </div>
                @else
                    <p class="ml-4 text-sm text-gray-500">Sin puntos de emision.</p>
                @endif
            </div>
        @empty
            <p class="text-gray-500">No tiene establecimientos registrados.</p>
        @endforelse
    </div>

    <!-- Usuarios -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Usuarios</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rol</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($emisor->users as $user)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $user->username }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $user->nombre }} {{ $user->apellido }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $user->email }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $user->rol ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">No hay usuarios registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin-layout>
