<?php

namespace App\Http\Controllers\Emisor;

use App\Enums\Ambiente;
use App\Http\Controllers\Controller;
use App\Models\ImpuestoIva;
use App\Models\LiquidacionCompra;
use App\Services\ComprobantesService;
use App\Services\ComprobanteEmailService;
use App\Services\PdfService;
use App\Services\SuscripcionService;
use App\Services\ValidacionSriService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LiquidacionCompraController extends Controller
{
    public function __construct(
        private SuscripcionService $suscripcionService,
        private PdfService $pdfService,
        private ComprobanteEmailService $emailService
    ) {}

    public function index(Request $request): View
    {
        $emisor = auth()->user()->emisor;

        $query = LiquidacionCompra::where('emisor_id', $emisor->id)
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

        $liquidaciones = $query->orderByDesc('fecha_emision')->orderByDesc('secuencial')->paginate(50);
        return view('emisor.comprobantes.liquidaciones.index', compact('liquidaciones'));
    }

    public function create(): View
    {
        $emisor = auth()->user()->emisor;

        $ivas = ImpuestoIva::activos()->get();

        return view('emisor.comprobantes.liquidaciones.create', compact('ivas'));
    }

    public function store(Request $request): RedirectResponse
    {
        $emisor = auth()->user()->emisor;

        $this->suscripcionService->verificarEIncrementar($emisor);

        $validated = $request->validate([
            'cliente_id' => "required|exists:clientes,id,emisor_id," . (auth()->user()->emisor_id),
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

        // Validaciones SRI antes de generar XML
        $cliente = \App\Models\Cliente::findOrFail($validated['cliente_id']);
        app(ValidacionSriService::class)->validarLiquidacion($emisor, $cliente, $validated);

        $liq = app(ComprobantesService::class)->crearLiquidacion($emisor, $validated);


        return redirect()->route('emisor.comprobantes.liquidaciones.show', $liq)
            ->with('success', 'Liquidación de compra guardada correctamente.');
    }

    public function show(LiquidacionCompra $liquidacion): View
    {
        $this->autorizarAcceso($liquidacion);
        $liquidacion->load(['detalles.impuestos', 'cliente', 'emisor', 'establecimiento', 'ptoEmision', 'camposAdicionales', 'mensajes']);

        return view('emisor.comprobantes.liquidaciones.show', compact('liquidacion'));
    }

    public function edit(LiquidacionCompra $liquidacion): View
    {
        $this->autorizarAcceso($liquidacion);
        abort_unless(in_array($liquidacion->estado, ['CREADA', 'NO AUTORIZADO', 'DEVUELTA']), 403, 'Solo se pueden editar liquidaciones en estado CREADA o NO AUTORIZADO.');

        $emisor = auth()->user()->emisor;
        $ivas = ImpuestoIva::activos()->get();
        $liquidacion->load(['detalles.impuestos', 'cliente']);

        return view('emisor.comprobantes.liquidaciones.edit', compact('liquidacion', 'ivas'));
    }

    public function update(Request $request, LiquidacionCompra $liquidacion): RedirectResponse
    {
        $this->autorizarAcceso($liquidacion);
        abort_unless(in_array($liquidacion->estado, ['CREADA', 'NO AUTORIZADO', 'DEVUELTA']), 403, 'Solo se pueden editar liquidaciones en estado CREADA o NO AUTORIZADO.');

        if ($liquidacion->estado !== 'CREADA') {
            $liquidacion->update(['estado' => 'CREADA', 'motivo_rechazo' => null]);
        }

        $validated = $request->validate([
            'cliente_id' => "required|exists:clientes,id,emisor_id," . (auth()->user()->emisor_id),
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

        app(ComprobantesService::class)->actualizarLiquidacion($liquidacion, $validated);

        return redirect()->route('emisor.comprobantes.liquidaciones.show', $liquidacion)
            ->with('success', 'Liquidacion actualizada correctamente.');
    }

    public function destroy(LiquidacionCompra $liquidacion): RedirectResponse
    {
        $this->autorizarAcceso($liquidacion);
        if ($liquidacion->emisor->ambiente !== Ambiente::PRUEBAS) {
            return back()->with('error', 'Solo se pueden eliminar comprobantes emitidos en ambiente de pruebas.');
        }
        $liquidacion->detalles()->each(fn ($d) => $d->impuestos()->delete());
        $liquidacion->detalles()->delete();
        $liquidacion->delete();

        $this->suscripcionService->decrementarContador($liquidacion->emisor);

        return redirect()->route('emisor.comprobantes.liquidaciones.index')
            ->with('success', 'Liquidación de compra eliminada.');
    }

    public function procesar(LiquidacionCompra $liquidacion): RedirectResponse
    {
        $this->autorizarAcceso($liquidacion);

        if (in_array($liquidacion->estado, ['AUTORIZADO', 'PROCESANDOSE'])) {
            return back()->with('info', 'Este comprobante ya fue enviado al SRI.');
        }

        app(\App\Services\SriService::class)->procesarComprobante($liquidacion, '03');
        $liquidacion->refresh();
        if ($liquidacion->estado === 'AUTORIZADO') {
            app(\App\Services\InventarioService::class)->procesarLiquidacionAutorizada($liquidacion);
            $liquidacion->load(['cliente', 'emisor', 'detalles.impuestos', 'establecimiento', 'ptoEmision', 'camposAdicionales']);
            app(\App\Services\ComprobanteEmailService::class)->enviarAutomatico($liquidacion, 'liquidacion', 'pdf.liquidacion', 'liq');
            return back()->with('success', 'Liquidación de compra autorizada por el SRI. Email enviado al cliente.');
        }
        if ($liquidacion->estado === 'PROCESANDOSE') {
            return back()->with('info', $liquidacion->motivo_rechazo ?? 'El comprobante está siendo procesado por el SRI. Consulte el estado en unos minutos.');
        }
        return back()->with('error', 'Liquidación de compra no autorizada por el SRI. Revise el detalle abajo.');
    }

    public function xml(LiquidacionCompra $liquidacion)
    {
        $this->autorizarAcceso($liquidacion);
        if (!$liquidacion->xml_path || !file_exists($liquidacion->xml_path)) {
            return back()->with('error', 'XML no disponible.');
        }
        return response()->download($liquidacion->xml_path);
    }

    public function consultarEstado(LiquidacionCompra $liquidacion): RedirectResponse
    {
        $this->autorizarAcceso($liquidacion);
        if (!$liquidacion->clave_acceso) {
            return back()->with('error', 'La liquidación de compra no tiene clave de acceso.');
        }
        $estado = app(\App\Services\SriService::class)->consultarYActualizar($liquidacion);

        if ($estado === 'AUTORIZADO') {
            app(\App\Services\InventarioService::class)->procesarLiquidacionAutorizada($liquidacion);
            $liquidacion->load(['cliente', 'emisor', 'detalles.impuestos', 'establecimiento', 'ptoEmision', 'camposAdicionales']);
            app(\App\Services\ComprobanteEmailService::class)->enviarAutomatico($liquidacion, 'liquidacion', 'pdf.liquidacion', 'liq');
        }

        return match ($estado) {
            'AUTORIZADO' => back()->with('success', 'Liquidación de compra autorizada por el SRI. Email enviado al cliente.'),
            'NO AUTORIZADO' => back()->with('error', 'Liquidación de compra no autorizada por el SRI. Revise el detalle abajo.'),
            default => back()->with('info', 'El comprobante sigue siendo procesado por el SRI. Intente nuevamente en unos minutos.'),
        };
    }

    public function pdf(LiquidacionCompra $liquidacion)
    {
        $this->autorizarAcceso($liquidacion);
        return $this->pdfService->liquidacion($liquidacion);
    }

    public function pdfPos(LiquidacionCompra $liquidacion)
    {
        $this->autorizarAcceso($liquidacion);
        return $this->pdfService->liquidacionPos($liquidacion);
    }

    public function anular(LiquidacionCompra $liquidacion): RedirectResponse
    {
        $this->autorizarAcceso($liquidacion);
        $liquidacion->update(['estado' => 'ANULADA']);
        return back()->with('success', 'Liquidación anulada internamente. Recuerde completar la anulación en el portal del SRI.');
    }

    public function clonar(LiquidacionCompra $liquidacion): RedirectResponse
    {
        $this->autorizarAcceso($liquidacion);
        $liquidacion->load(['detalles.impuestos']);

        $emisor = auth()->user()->emisor;
        $this->suscripcionService->verificarEIncrementar($emisor);

        $data = [
            'establecimiento_id' => $liquidacion->establecimiento_id,
            'pto_emision_id' => $liquidacion->pto_emision_id,
            'cliente_id' => $liquidacion->cliente_id,
            'fecha_emision' => now()->toDateString(),
            'forma_pago' => $liquidacion->forma_pago,
            'observaciones' => $liquidacion->observaciones,
            'detalles' => $liquidacion->detalles->map(fn ($d) => [
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

        $nueva = app(ComprobantesService::class)->crearLiquidacion($emisor, $data);

        return redirect()->route('emisor.comprobantes.liquidaciones.edit', $nueva)
            ->with('success', 'Liquidación duplicada correctamente.');
    }

    public function email(Request $request, \App\Models\LiquidacionCompra $liquidacion): \Illuminate\Http\RedirectResponse
    {
        $this->autorizarAcceso($liquidacion);
        $liquidacion->load(['cliente', 'emisor', 'detalles.impuestos', 'establecimiento', 'ptoEmision', 'camposAdicionales']);
        return $this->emailService->enviar($request, $liquidacion, 'liquidacion', 'pdf.liquidacion', 'liq');
    }

    private function autorizarAcceso(LiquidacionCompra $liquidacion): void
    {
        $user = auth()->user();
        if (!$user->esAdmin() && $liquidacion->emisor_id !== $user->emisor_id) {
            abort(403);
        }
    }
}
