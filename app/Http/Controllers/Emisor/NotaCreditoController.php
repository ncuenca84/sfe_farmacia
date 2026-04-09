<?php

namespace App\Http\Controllers\Emisor;

use App\Enums\Ambiente;
use App\Http\Controllers\Controller;
use App\Models\ImpuestoIva;
use App\Models\NotaCredito;
use App\Services\ComprobantesService;
use App\Services\ComprobanteEmailService;
use App\Services\PdfService;
use App\Services\SuscripcionService;
use App\Services\ValidacionSriService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotaCreditoController extends Controller
{
    public function __construct(
        private SuscripcionService $suscripcionService,
        private PdfService $pdfService,
        private ComprobanteEmailService $emailService
    ) {}

    public function index(Request $request): View
    {
        $emisor = auth()->user()->emisor;

        $query = NotaCredito::where('emisor_id', $emisor->id)
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

        $notasCredito = $query->orderByDesc('fecha_emision')->orderByDesc('secuencial')->paginate(50);
        return view('emisor.comprobantes.notas-credito.index', compact('notasCredito'));
    }

    public function create(): View
    {
        $emisor = auth()->user()->emisor;

        $ivas = ImpuestoIva::activos()->get();

        return view('emisor.comprobantes.notas-credito.create', compact('ivas'));
    }

    public function store(Request $request): RedirectResponse
    {
        $emisor = auth()->user()->emisor;

        $this->suscripcionService->verificarEIncrementar($emisor);

        $validated = $request->validate([
            'cliente_id' => "required|exists:clientes,id,emisor_id," . (auth()->user()->emisor_id),
            'fecha_emision' => 'required|date|date_equals:today',
            'cod_doc_modificado' => 'required|string|max:2',
            'num_doc_modificado' => 'required|string|max:20',
            'fecha_emision_doc_sustento' => 'required|date',
            'motivo' => 'required|string|max:300',
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
        app(ValidacionSriService::class)->validarNotaCredito($emisor, $cliente, $validated);

        $nc = app(ComprobantesService::class)->crearNotaCredito($emisor, $validated);


        return redirect()->route('emisor.comprobantes.notas-credito.show', $nc)
            ->with('success', 'Nota de crédito guardada correctamente.');
    }

    public function show(NotaCredito $notaCredito): View
    {
        $this->autorizarAcceso($notaCredito);
        $notaCredito->load(['detalles.impuestos', 'cliente', 'emisor', 'establecimiento', 'ptoEmision', 'camposAdicionales', 'mensajes']);

        return view('emisor.comprobantes.notas-credito.show', compact('notaCredito'));
    }

    public function edit(NotaCredito $notaCredito): View
    {
        $this->autorizarAcceso($notaCredito);
        abort_unless(in_array($notaCredito->estado, ['CREADA', 'NO AUTORIZADO', 'DEVUELTA']), 403, 'Solo se pueden editar notas de credito en estado CREADA o NO AUTORIZADO.');

        $emisor = auth()->user()->emisor;
        $ivas = ImpuestoIva::activos()->get();
        $notaCredito->load(['detalles.impuestos', 'cliente']);

        return view('emisor.comprobantes.notas-credito.edit', compact('notaCredito', 'ivas'));
    }

    public function update(Request $request, NotaCredito $notaCredito): RedirectResponse
    {
        $this->autorizarAcceso($notaCredito);
        abort_unless(in_array($notaCredito->estado, ['CREADA', 'NO AUTORIZADO', 'DEVUELTA']), 403, 'Solo se pueden editar notas de credito en estado CREADA o NO AUTORIZADO.');

        // Si viene de NO AUTORIZADO, resetear estado para permitir reenvío
        if ($notaCredito->estado !== 'CREADA') {
            $notaCredito->update(['estado' => 'CREADA', 'motivo_rechazo' => null]);
        }

        $validated = $request->validate([
            'cliente_id' => "required|exists:clientes,id,emisor_id," . (auth()->user()->emisor_id),
            'fecha_emision' => 'required|date|date_equals:today',
            'cod_doc_modificado' => 'required|string|max:2',
            'num_doc_modificado' => 'required|string|max:20',
            'fecha_emision_doc_sustento' => 'required|date',
            'motivo' => 'required|string|max:300',
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

        app(ComprobantesService::class)->actualizarNotaCredito($notaCredito, $validated);

        return redirect()->route('emisor.comprobantes.notas-credito.show', $notaCredito)
            ->with('success', 'Nota de credito actualizada correctamente.');
    }

    public function destroy(NotaCredito $notaCredito): RedirectResponse
    {
        $this->autorizarAcceso($notaCredito);
        if ($notaCredito->emisor->ambiente !== Ambiente::PRUEBAS) {
            return back()->with('error', 'Solo se pueden eliminar comprobantes emitidos en ambiente de pruebas.');
        }
        $notaCredito->detalles()->each(fn ($d) => $d->impuestos()->delete());
        $notaCredito->detalles()->delete();
        $notaCredito->delete();

        $this->suscripcionService->decrementarContador($notaCredito->emisor);

        return redirect()->route('emisor.comprobantes.notas-credito.index')
            ->with('success', 'Nota de crédito eliminada.');
    }

    public function procesar(NotaCredito $notaCredito): RedirectResponse
    {
        $this->autorizarAcceso($notaCredito);

        if (in_array($notaCredito->estado, ['AUTORIZADO', 'PROCESANDOSE'])) {
            return back()->with('info', 'Este comprobante ya fue enviado al SRI.');
        }

        app(\App\Services\SriService::class)->procesarComprobante($notaCredito, '04');
        $notaCredito->refresh();
        if ($notaCredito->estado === 'AUTORIZADO') {
            app(\App\Services\InventarioService::class)->procesarNotaCreditoAutorizada($notaCredito);
            $notaCredito->load(['cliente', 'emisor', 'detalles.impuestos', 'establecimiento', 'ptoEmision', 'camposAdicionales']);
            app(\App\Services\ComprobanteEmailService::class)->enviarAutomatico($notaCredito, 'nota-credito', 'pdf.nota-credito', 'nc');
            return back()->with('success', 'Nota de crédito autorizada por el SRI. Email enviado al cliente.');
        }
        if ($notaCredito->estado === 'PROCESANDOSE') {
            return back()->with('info', $notaCredito->motivo_rechazo ?? 'El comprobante está siendo procesado por el SRI. Consulte el estado en unos minutos.');
        }
        return back()->with('error', 'Nota de crédito no autorizada por el SRI. Revise el detalle abajo.');
    }

    public function xml(NotaCredito $notaCredito)
    {
        $this->autorizarAcceso($notaCredito);
        if (!$notaCredito->xml_path || !file_exists($notaCredito->xml_path)) {
            return back()->with('error', 'XML no disponible.');
        }
        return response()->download($notaCredito->xml_path);
    }

    public function consultarEstado(NotaCredito $notaCredito): RedirectResponse
    {
        $this->autorizarAcceso($notaCredito);
        if (!$notaCredito->clave_acceso) {
            return back()->with('error', 'La nota de crédito no tiene clave de acceso.');
        }
        $estado = app(\App\Services\SriService::class)->consultarYActualizar($notaCredito);

        if ($estado === 'AUTORIZADO') {
            app(\App\Services\InventarioService::class)->procesarNotaCreditoAutorizada($notaCredito);
            $notaCredito->load(['cliente', 'emisor', 'detalles.impuestos', 'establecimiento', 'ptoEmision', 'camposAdicionales']);
            app(\App\Services\ComprobanteEmailService::class)->enviarAutomatico($notaCredito, 'nota-credito', 'pdf.nota-credito', 'nc');
        }

        return match ($estado) {
            'AUTORIZADO' => back()->with('success', 'Nota de crédito autorizada por el SRI. Email enviado al cliente.'),
            'NO AUTORIZADO' => back()->with('error', 'Nota de crédito no autorizada por el SRI. Revise el detalle abajo.'),
            default => back()->with('info', 'El comprobante sigue siendo procesado por el SRI. Intente nuevamente en unos minutos.'),
        };
    }

    public function pdf(NotaCredito $notaCredito)
    {
        $this->autorizarAcceso($notaCredito);
        return $this->pdfService->notaCredito($notaCredito);
    }

    public function pdfPos(NotaCredito $notaCredito)
    {
        $this->autorizarAcceso($notaCredito);
        return $this->pdfService->notaCreditoPos($notaCredito);
    }

    public function anular(NotaCredito $notaCredito): RedirectResponse
    {
        $this->autorizarAcceso($notaCredito);
        $notaCredito->update(['estado' => 'ANULADA']);
        return back()->with('success', 'Nota de crédito anulada internamente. Recuerde completar la anulación en el portal del SRI.');
    }

    public function clonar(NotaCredito $notaCredito): RedirectResponse
    {
        $this->autorizarAcceso($notaCredito);
        $notaCredito->load(['detalles.impuestos']);

        $emisor = auth()->user()->emisor;
        $this->suscripcionService->verificarEIncrementar($emisor);

        $data = [
            'establecimiento_id' => $notaCredito->establecimiento_id,
            'pto_emision_id' => $notaCredito->pto_emision_id,
            'cliente_id' => $notaCredito->cliente_id,
            'fecha_emision' => now()->toDateString(),
            'cod_doc_modificado' => $notaCredito->cod_doc_modificado,
            'num_doc_modificado' => $notaCredito->num_doc_modificado,
            'fecha_emision_doc_sustento' => $notaCredito->fecha_emision_doc_sustento,
            'motivo' => $notaCredito->motivo,
            'observaciones' => $notaCredito->observaciones,
            'detalles' => $notaCredito->detalles->map(fn ($d) => [
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

        $nueva = app(ComprobantesService::class)->crearNotaCredito($emisor, $data);

        return redirect()->route('emisor.comprobantes.notas-credito.edit', $nueva)
            ->with('success', 'Nota de crédito duplicada correctamente.');
    }

    public function email(Request $request, \App\Models\NotaCredito $notaCredito): \Illuminate\Http\RedirectResponse
    {
        $this->autorizarAcceso($notaCredito);
        $notaCredito->load(['cliente', 'emisor', 'detalles.impuestos', 'establecimiento', 'ptoEmision', 'camposAdicionales']);
        return $this->emailService->enviar($request, $notaCredito, 'nota-credito', 'pdf.nota-credito', 'nc');
    }

    private function autorizarAcceso(NotaCredito $notaCredito): void
    {
        $user = auth()->user();
        if (!$user->esAdmin() && $notaCredito->emisor_id !== $user->emisor_id) {
            abort(403);
        }
    }
}
