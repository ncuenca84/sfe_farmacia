<x-admin-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Emisores</h2>
            <a href="{{ route('admin.emisores.create') }}" style="background-color:#4f46e5; color:white; font-weight:500; padding:8px 16px; border-radius:6px; font-size:14px; text-decoration:none; display:inline-block;">Nuevo Emisor</a>
        </div>
    </x-slot>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">RUC</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Razon Social</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Plan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado Suscripcion</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Activo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($emisores as $emisor)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $emisor->ruc }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $emisor->razon_social }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $emisor->suscripcionActiva?->plan?->nombre ?? 'Sin plan' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($emisor->suscripcionActiva)
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Activa</span>
                                @else
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Sin suscripcion</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($emisor->activo)
                                    <span class="text-green-600 font-medium">Si</span>
                                @else
                                    <span class="text-red-600 font-medium">No</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div style="display:flex; gap:6px; flex-wrap:wrap;">
                                    <a href="{{ route('admin.emisores.show', $emisor) }}" style="display:inline-flex; align-items:center; padding:4px 10px; border-radius:4px; font-size:12px; font-weight:500; background:#eef2ff; color:#4338ca; text-decoration:none; border:1px solid #c7d2fe;">Ver</a>
                                    <a href="{{ route('admin.emisores.edit', $emisor) }}" style="display:inline-flex; align-items:center; padding:4px 10px; border-radius:4px; font-size:12px; font-weight:500; background:#fefce8; color:#a16207; text-decoration:none; border:1px solid #fde68a;">Editar</a>
                                    <form method="POST" action="{{ route('admin.emisores.impersonar', $emisor) }}" class="inline">
                                        @csrf
                                        <button type="submit" style="display:inline-flex; align-items:center; padding:4px 10px; border-radius:4px; font-size:12px; font-weight:500; background:#f0fdf4; color:#166534; text-decoration:none; border:1px solid #bbf7d0; cursor:pointer;">Acceder</button>
                                    </form>
                                    @if($emisor->activo)
                                        <form method="POST" action="{{ route('admin.emisores.destroy', $emisor) }}" class="inline" onsubmit="return confirm('¿Desactivar el emisor {{ $emisor->razon_social }}? No podra emitir comprobantes hasta que sea reactivado.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" style="display:inline-flex; align-items:center; padding:4px 10px; border-radius:4px; font-size:12px; font-weight:500; background:#fff7ed; color:#c2410c; border:1px solid #fed7aa; cursor:pointer;">Desactivar</button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('admin.emisores.activar', $emisor) }}" class="inline" onsubmit="return confirm('¿Reactivar el emisor {{ $emisor->razon_social }}?')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" style="display:inline-flex; align-items:center; padding:4px 10px; border-radius:4px; font-size:12px; font-weight:500; background:#f0fdf4; color:#15803d; border:1px solid #bbf7d0; cursor:pointer;">Activar</button>
                                        </form>
                                    @endif
                                    <button type="button"
                                        data-emisor-id="{{ $emisor->id }}"
                                        data-emisor-ruc="{{ $emisor->ruc }}"
                                        data-emisor-nombre="{{ $emisor->razon_social }}"
                                        onclick="abrirModalEliminar(this.dataset.emisorId, this.dataset.emisorRuc, this.dataset.emisorNombre)"
                                        style="display:inline-flex; align-items:center; padding:4px 10px; border-radius:4px; font-size:12px; font-weight:500; background:#fef2f2; color:#991b1b; border:1px solid #fecaca; cursor:pointer;">
                                        Eliminar
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">No hay emisores registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $emisores->links() }}
        </div>
    </div>

    {{-- Modal de confirmacion para eliminar emisor --}}
    <div id="modal-eliminar" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.5);">
        <div style="background:white; border-radius:12px; max-width:480px; width:90%; padding:28px; box-shadow:0 20px 60px rgba(0,0,0,0.3); margin:auto; position:relative; top:50%; transform:translateY(-50%);">
            <div style="text-align:center; margin-bottom:16px;">
                <div style="display:inline-flex; align-items:center; justify-content:center; width:56px; height:56px; border-radius:50%; background:#fef2f2;">
                    <svg style="width:32px; height:32px; color:#dc2626;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                </div>
            </div>

            <h3 style="font-size:18px; font-weight:700; color:#111827; text-align:center; margin-bottom:8px;">Eliminar Emisor Permanentemente</h3>

            <p style="font-size:14px; color:#6b7280; text-align:center; margin-bottom:4px;">
                Esta a punto de eliminar al emisor:
            </p>
            <p style="font-size:15px; font-weight:600; color:#111827; text-align:center; margin-bottom:16px;" id="modal-emisor-nombre"></p>

            <div style="background:#fef2f2; border:1px solid #fecaca; border-radius:8px; padding:12px; margin-bottom:20px;">
                <p style="font-size:13px; color:#991b1b; font-weight:600; margin-bottom:6px;">ADVERTENCIA: Este proceso es IRREVERSIBLE</p>
                <p style="font-size:12px; color:#b91c1c; margin:0;">Se eliminaran permanentemente:</p>
                <ul style="font-size:12px; color:#b91c1c; margin:6px 0 0 16px; padding:0; list-style:disc;">
                    <li>Todas las facturas emitidas</li>
                    <li>Notas de credito y debito</li>
                    <li>Retenciones</li>
                    <li>Guias de remision</li>
                    <li>Liquidaciones de compra</li>
                    <li>Proformas</li>
                    <li>Clientes, productos y usuarios</li>
                    <li>Suscripciones y establecimientos</li>
                </ul>
            </div>

            <label style="display:block; font-size:13px; font-weight:600; color:#374151; margin-bottom:6px;">
                Escriba el RUC <strong id="modal-ruc-display" style="color:#dc2626;"></strong> para confirmar:
            </label>
            <input type="text" id="modal-ruc-input"
                style="width:100%; padding:10px 12px; border:2px solid #d1d5db; border-radius:8px; font-size:15px; letter-spacing:1px; text-align:center; box-sizing:border-box; outline:none; font-family:monospace;"
                placeholder="Ingrese el RUC aqui"
                autocomplete="off">
            <p id="modal-ruc-error" style="display:none; color:#dc2626; font-size:12px; margin-top:4px; text-align:center;">El RUC ingresado no coincide.</p>

            <div style="display:flex; gap:12px; margin-top:20px;">
                <button type="button" onclick="cerrarModalEliminar()"
                    style="flex:1; padding:10px; border:1px solid #d1d5db; border-radius:8px; background:white; color:#374151; font-size:14px; font-weight:500; cursor:pointer;">
                    Cancelar
                </button>
                <button type="button" id="btn-confirmar-eliminar" onclick="confirmarEliminar()"
                    style="flex:1; padding:10px; border:none; border-radius:8px; background:#d1d5db; color:white; font-size:14px; font-weight:600; cursor:not-allowed; opacity:0.5;"
                    disabled>
                    Eliminar Permanentemente
                </button>
            </div>

            <form id="form-eliminar" method="POST" style="display:none;">
                @csrf
                <input type="hidden" name="ruc_confirmacion" id="form-ruc-confirmacion">
            </form>
        </div>
    </div>

    <script>
        var _eliminarEmisorId = '';
        var _eliminarRuc = '';

        function abrirModalEliminar(id, ruc, nombre) {
            _eliminarEmisorId = id;
            _eliminarRuc = ruc;
            document.getElementById('modal-emisor-nombre').textContent = ruc + ' - ' + nombre;
            document.getElementById('modal-ruc-display').textContent = ruc;
            document.getElementById('modal-ruc-input').value = '';
            document.getElementById('modal-ruc-error').style.display = 'none';
            var btn = document.getElementById('btn-confirmar-eliminar');
            btn.disabled = true;
            btn.style.background = '#d1d5db';
            btn.style.cursor = 'not-allowed';
            btn.style.opacity = '0.5';
            document.getElementById('modal-eliminar').style.display = 'block';
            setTimeout(function() { document.getElementById('modal-ruc-input').focus(); }, 100);
        }

        function cerrarModalEliminar() {
            document.getElementById('modal-eliminar').style.display = 'none';
        }

        document.getElementById('modal-ruc-input').addEventListener('input', function() {
            var match = this.value.trim() === _eliminarRuc;
            var btn = document.getElementById('btn-confirmar-eliminar');
            btn.disabled = !match;
            btn.style.background = match ? '#dc2626' : '#d1d5db';
            btn.style.cursor = match ? 'pointer' : 'not-allowed';
            btn.style.opacity = match ? '1' : '0.5';
            document.getElementById('modal-ruc-error').style.display = 'none';
        });

        document.getElementById('modal-ruc-input').addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && this.value.trim() === _eliminarRuc) {
                confirmarEliminar();
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') cerrarModalEliminar();
        });

        document.getElementById('modal-eliminar').addEventListener('click', function(e) {
            if (e.target === this) cerrarModalEliminar();
        });

        function confirmarEliminar() {
            if (document.getElementById('modal-ruc-input').value.trim() !== _eliminarRuc) {
                document.getElementById('modal-ruc-error').style.display = 'block';
                return;
            }
            var form = document.getElementById('form-eliminar');
            form.action = '{{ url("admin/emisores") }}/' + _eliminarEmisorId + '/eliminar';
            document.getElementById('form-ruc-confirmacion').value = _eliminarRuc;
            form.submit();
        }
    </script>
</x-admin-layout>
