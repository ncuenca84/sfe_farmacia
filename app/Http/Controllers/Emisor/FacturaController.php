<?php

namespace App\Http\Controllers\Emisor;

use App\Enums\Ambiente;
use App\Http\Controllers\Controller;
use App\Models\Factura;
use App\Models\ImpuestoIva;
use App\Services\ClaveAccesoService;
use App\Services\ComprobanteEmailService;
use App\Services\PdfService;
use App\Services\SriService;
use App\Services\SuscripcionService;
use App\Services\ValidacionSriService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FacturaController extends Controller
{
    public function __construct(
        private SuscripcionService $suscripcionService,
        private ClaveAccesoService $claveAccesoService,
        private PdfService $pdfService,
        private ComprobanteEmailService $emailService,
    ) {}

    public function index(Request $request): View
    {
        $emisor = auth()->user()->emisor;

        $query = Factura::where('emisor_id', $emisor->id)
            ->with(['cliente', 'establecimiento', 'ptoEmision']);

        if (auth()->user()->unidad_negocio_id) {
            $query->whereHas('establecimiento', fn ($q) => $q->where('unidad_negocio_id', auth()->user()->unidad_negocio_id));
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }
        if ($request->filled('desde')) {
            $query->where('fecha_emision', '>=', $request->desde);
        }
        if ($request->filled('hasta')) {
            $query->where('fecha_emision', '<=', $request->hasta);
        }
        if ($request->filled('buscar')) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('cliente', fn ($c) => $c->where('razon_social', 'like', "%{$request->buscar}%")
                    ->orWhere('identificacion', 'like', "%{$request->buscar}%"));
            });
        }

        $facturas = $query->orderByDesc('fecha_emision')->orderByDesc('secuencial')->paginate(50);
        return view('emisor.comprobantes.facturas.index', compact('facturas'));
    }

    public function create(): View
    {
        $emisor = auth()->user()->emisor;
        $ivas = ImpuestoIva::activos()->get();

        return view('emisor.comprobantes.facturas.create', compact('ivas'));
    }

    public function store(Request $request): RedirectResponse
    {
        $emisor = auth()->user()->emisor;

        // Verificar límite e incrementar contador atómicamente
        $this->suscripcionService->verificarEIncrementar($emisor);

        $rules = [
            'cliente_id' => "required|exists:clientes,id,emisor_id,{$emisor->id}",
            'fecha_emision' => 'required|date|date_equals:today',
            'forma_pago' => 'required|string|max:2',
            // Detalles
            'observaciones' => 'nullable|string|max:500',
            'detalles' => 'required|array|min:1',
            'detalles.*.descripcion' => 'required|string|max:300',
            'detalles.*.cantidad' => 'required|numeric|min:0.0001',
            'detalles.*.precio_unitario' => 'required|numeric|min:0',
            'detalles.*.descuento' => 'nullable|numeric|min:0',
            'detalles.*.codigo_principal' => 'nullable|string|max:50',
            'detalles.*.impuesto_iva_id' => 'required|exists:impuesto_ivas,id',
        ];

        // Guía fields
        if ($request->boolean('con_guia')) {
            $rules += [
                'guia_dir_partida' => 'required|string|max:300',
                'guia_dir_llegada' => 'required|string|max:300',
                'guia_ruc_transportista' => 'required|string|max:13',
                'guia_razon_social_transportista' => 'required|string|max:300',
                'guia_placa' => 'nullable|string|max:20',
                'guia_fecha_ini_transporte' => 'required|date',
                'guia_fecha_fin_transporte' => 'required|date',
            ];
        }

        // Reembolso fields
        if ($request->boolean('es_reembolso')) {
            $rules += [
                'reembolsos' => 'required|array|min:1',
                'reembolsos.*.identificacion_proveedor' => 'required|string|max:20',
                'reembolsos.*.tipo_proveedor' => 'required|string|max:2',
                'reembolsos.*.estab_doc_reembolso' => 'required|string|max:3',
                'reembolsos.*.pto_emision_doc_reembolso' => 'required|string|max:3',
                'reembolsos.*.secuencial_doc_reembolso' => 'required|string|max:9',
                'reembolsos.*.fecha_emision_doc_reembolso' => 'required|date',
                'reembolsos.*.numero_autorizacion_doc_reembolso' => 'nullable|string|max:49',
                'reembolsos.*.base_0' => 'nullable|numeric|min:0',
                'reembolsos.*.base_15' => 'nullable|numeric|min:0',
                'reembolsos.*.iva' => 'nullable|numeric|min:0',
            ];
        }

        $validated = $request->validate($rules, [
            'fecha_emision.date_equals' => 'La fecha de emision debe ser la fecha de hoy (normativa SRI).',
        ]);

        $establecimiento = auth()->user()->establecimientosActivos()->first();
        abort_unless($establecimiento, 403, 'No tiene un establecimiento asignado.');
        $ptoEmision = $establecimiento->ptoEmisiones->first();
        abort_unless($ptoEmision, 403, 'No tiene un punto de emisión asignado.');
        $validated['establecimiento_id'] = $establecimiento->id;
        $validated['pto_emision_id'] = $ptoEmision->id;

        // Validaciones SRI antes de generar XML
        $cliente = \App\Models\Cliente::findOrFail($validated['cliente_id']);
        app(ValidacionSriService::class)->validarFactura($emisor, $cliente, $validated);

        // Crear factura y procesar con SRI en un solo paso
        $factura = app(\App\Services\FacturaService::class)->crear($emisor, $validated);

        return redirect()->route('emisor.comprobantes.facturas.show', $factura)
            ->with('success', 'Factura guardada correctamente.');
    }

    public function edit(Factura $factura): View
    {
        $this->authorize('update', $factura);
        abort_unless(in_array($factura->estado, ['CREADA', 'NO AUTORIZADO', 'DEVUELTA']), 403, 'Solo se pueden editar facturas en estado CREADA o NO AUTORIZADO.');

        $emisor = auth()->user()->emisor;
        $ivas = ImpuestoIva::activos()->get();
        $factura->load(['detalles.impuestos', 'cliente']);

        return view('emisor.comprobantes.facturas.edit', compact('factura', 'ivas'));
    }

    public function update(Request $request, Factura $factura): RedirectResponse
    {
        $this->authorize('update', $factura);
        abort_unless(in_array($factura->estado, ['CREADA', 'NO AUTORIZADO', 'DEVUELTA']), 403, 'Solo se pueden editar facturas en estado CREADA o NO AUTORIZADO.');

        if ($factura->estado !== 'CREADA') {
            $factura->update(['estado' => 'CREADA', 'motivo_rechazo' => null]);
        }

        $emisorId = $factura->emisor_id;
        $validated = $request->validate([
            'cliente_id' => "required|exists:clientes,id,emisor_id,{$emisorId}",
            'fecha_emision' => 'required|date|date_equals:today',
            'forma_pago' => 'required|string|max:2',
            'observaciones' => 'nullable|string|max:500',
            'detalles' => 'required|array|min:1',
            'detalles.*.descripcion' => 'required|string|max:300',
            'detalles.*.cantidad' => 'required|numeric|min:0.0001',
            'detalles.*.precio_unitario' => 'required|numeric|min:0',
            'detalles.*.descuento' => 'nullable|numeric|min:0',
            'detalles.*.codigo_principal' => 'nullable|string|max:50',
            'detalles.*.impuesto_iva_id' => 'required|exists:impuesto_ivas,id',
        ], [
            'fecha_emision.date_equals' => 'La fecha de emision debe ser la fecha de hoy (normativa SRI).',
        ]);

        $establecimiento = auth()->user()->establecimientosActivos()->first();
        abort_unless($establecimiento, 403, 'No tiene un establecimiento asignado.');
        $ptoEmision = $establecimiento->ptoEmisiones->first();
        abort_unless($ptoEmision, 403, 'No tiene un punto de emisión asignado.');
        $validated['establecimiento_id'] = $establecimiento->id;
        $validated['pto_emision_id'] = $ptoEmision->id;

        app(\App\Services\FacturaService::class)->actualizar($factura, $validated);

        return redirect()->route('emisor.comprobantes.facturas.show', $factura)
            ->with('success', 'Factura actualizada correctamente.');
    }

    public function show(Factura $factura): View
    {
        $this->authorize('view', $factura);
        $factura->load([
            'detalles.impuestos', 'cliente', 'emisor',
            'establecimiento', 'ptoEmision',
            'camposAdicionales', 'reembolsos', 'infoGuia', 'mensajes',
        ]);

        return view('emisor.comprobantes.facturas.show', compact('factura'));
    }

    public function pdf(Factura $factura)
    {
        $this->authorize('view', $factura);
        return $this->pdfService->factura($factura);
    }

    public function pdfPos(Factura $factura)
    {
        $this->authorize('view', $factura);
        return $this->pdfService->facturaPos($factura);
    }

    public function xml(Factura $factura)
    {
        $this->authorize('view', $factura);

        if (!$factura->xml_path || !file_exists($factura->xml_path)) {
            return back()->with('error', 'XML no disponible.');
        }

        return response()->download($factura->xml_path);
    }

    public function procesar(Factura $factura): RedirectResponse
    {
        $this->authorize('update', $factura);

        if (in_array($factura->estado, ['AUTORIZADO', 'PROCESANDOSE'])) {
            return back()->with('info', 'Este comprobante ya fue enviado al SRI.');
        }

        app(SriService::class)->procesarFactura($factura);

        $factura->refresh();

        if ($factura->estado === 'AUTORIZADO') {
            app(\App\Services\InventarioService::class)->procesarFacturaAutorizada($factura);
            $factura->load(['cliente', 'emisor', 'detalles.impuestos', 'establecimiento', 'ptoEmision', 'camposAdicionales']);
            app(\App\Services\ComprobanteEmailService::class)->enviarAutomatico($factura, 'factura', 'pdf.factura', 'factura');
            return back()->with('success', 'Factura autorizada por el SRI. Email enviado al cliente.');
        }

        if ($factura->estado === 'PROCESANDOSE') {
            return back()->with('info', $factura->motivo_rechazo ?? 'El comprobante está siendo procesado por el SRI. Consulte el estado en unos minutos.');
        }

        return back()->with('error', 'Factura no autorizada por el SRI. Revise el detalle abajo.');
    }

    public function consultarEstado(Factura $factura): RedirectResponse
    {
        $this->authorize('view', $factura);

        if (!$factura->clave_acceso) {
            return back()->with('error', 'La factura no tiene clave de acceso.');
        }

        $estado = app(SriService::class)->consultarYActualizar($factura);

        if ($estado === 'AUTORIZADO') {
            app(\App\Services\InventarioService::class)->procesarFacturaAutorizada($factura);
            $factura->load(['cliente', 'emisor', 'detalles.impuestos', 'establecimiento', 'ptoEmision', 'camposAdicionales']);
            app(\App\Services\ComprobanteEmailService::class)->enviarAutomatico($factura, 'factura', 'pdf.factura', 'factura');
        }

        return match ($estado) {
            'AUTORIZADO' => back()->with('success', 'Factura autorizada por el SRI. Email enviado al cliente.'),
            'NO AUTORIZADO' => back()->with('error', 'Factura no autorizada por el SRI. Revise el detalle abajo.'),
            default => back()->with('info', 'El comprobante sigue siendo procesado por el SRI. Intente nuevamente en unos minutos.'),
        };
    }

    public function email(Request $request, Factura $factura): RedirectResponse
    {
        $this->authorize('view', $factura);
        $factura->load(['cliente', 'emisor', 'detalles.impuestos', 'establecimiento', 'ptoEmision', 'camposAdicionales']);

        return $this->emailService->enviar($request, $factura, 'factura', 'pdf.factura', 'factura');
    }

    public function destroy(Factura $factura): RedirectResponse
    {
        $this->authorize('update', $factura);

        if ($factura->emisor->ambiente !== Ambiente::PRUEBAS) {
            return back()->with('error', 'Solo se pueden eliminar comprobantes emitidos en ambiente de pruebas.');
        }

        $factura->detalles()->each(fn ($d) => $d->impuestos()->delete());
        $factura->detalles()->delete();
        $factura->delete();

        $this->suscripcionService->decrementarContador($factura->emisor);

        return redirect()->route('emisor.comprobantes.facturas.index')
            ->with('success', 'Factura eliminada correctamente (ambiente de pruebas).');
    }

    public function anular(Factura $factura): RedirectResponse
    {
        $this->authorize('update', $factura);
        $factura->update(['estado' => 'ANULADA']);
        return back()->with('success', 'Factura anulada internamente. Recuerde completar la anulación en el portal del SRI.');
    }

    public function clonar(Factura $factura): RedirectResponse
    {
        $this->authorize('view', $factura);
        $factura->load(['detalles.impuestos']);

        $emisor = auth()->user()->emisor;
        $this->suscripcionService->verificarEIncrementar($emisor);

        $data = [
            'establecimiento_id' => $factura->establecimiento_id,
            'pto_emision_id' => $factura->pto_emision_id,
            'cliente_id' => $factura->cliente_id,
            'fecha_emision' => now()->toDateString(),
            'forma_pago' => $factura->forma_pago,
            'observaciones' => $factura->observaciones,
            'detalles' => $factura->detalles->map(fn ($d) => [
                'codigo_principal' => $d->codigo_principal,
                'descripcion' => $d->descripcion,
                'cantidad' => $d->cantidad,
                'precio_unitario' => $d->precio_unitario,
                'descuento' => $d->descuento ?? 0,
                'impuesto_iva_id' => $d->impuestos->where('codigo', '2')->first()?->codigo_porcentaje
                    ? \App\Models\ImpuestoIva::where('codigo_porcentaje', $d->impuestos->where('codigo', '2')->first()->codigo_porcentaje)->first()?->id
                    : \App\Models\ImpuestoIva::first()?->id,
            ])->toArray(),
        ];

        $nueva = app(\App\Services\FacturaService::class)->crear($emisor, $data);

        return redirect()->route('emisor.comprobantes.facturas.edit', $nueva)
            ->with('success', 'Factura duplicada correctamente. Puede editarla antes de enviarla al SRI.');
    }

    private function authorize(string $ability, Factura $factura): void
    {
        $user = auth()->user();
        if (!$user->esAdmin() && $factura->emisor_id !== $user->emisor_id) {
            abort(403);
        }
    }
}
