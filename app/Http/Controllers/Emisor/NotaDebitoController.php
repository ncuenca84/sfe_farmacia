<?php

namespace App\Http\Controllers\Emisor;

use App\Enums\Ambiente;
use App\Http\Controllers\Controller;
use App\Models\ImpuestoIva;
use App\Models\NotaDebito;
use App\Services\ComprobantesService;
use App\Services\ComprobanteEmailService;
use App\Services\PdfService;
use App\Services\SuscripcionService;
use App\Services\ValidacionSriService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotaDebitoController extends Controller
{
    public function __construct(
        private SuscripcionService $suscripcionService,
        private PdfService $pdfService,
        private ComprobanteEmailService $emailService
    ) {}

    public function index(Request $request): View
    {
        $emisor = auth()->user()->emisor;

        $query = NotaDebito::where('emisor_id', $emisor->id)
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

        $notasDebito = $query->orderByDesc('fecha_emision')->orderByDesc('secuencial')->paginate(50);
        return view('emisor.comprobantes.notas-debito.index', compact('notasDebito'));
    }

    public function create(): View
    {
        $emisor = auth()->user()->emisor;

        $ivas = ImpuestoIva::activos()->get();

        return view('emisor.comprobantes.notas-debito.create', compact('ivas'));
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
            'observaciones' => 'nullable|string|max:500',
            'motivos' => 'required|array|min:1',
            'motivos.*.razon' => 'required|string|max:300',
            'motivos.*.valor' => 'required|numeric|min:0',
            'motivos.*.impuesto_iva_id' => 'required|exists:impuesto_ivas,id',
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
        app(ValidacionSriService::class)->validarNotaDebito($emisor, $cliente, $validated);

        $nd = app(ComprobantesService::class)->crearNotaDebito($emisor, $validated);


        return redirect()->route('emisor.comprobantes.notas-debito.show', $nd)
            ->with('success', 'Nota de débito guardada correctamente.');
    }

    public function show(NotaDebito $notaDebito): View
    {
        $this->autorizarAcceso($notaDebito);
        $notaDebito->load(['motivos.impuestoIva', 'cliente', 'emisor', 'establecimiento', 'ptoEmision', 'camposAdicionales', 'mensajes']);

        return view('emisor.comprobantes.notas-debito.show', compact('notaDebito'));
    }

    public function edit(NotaDebito $notaDebito): View
    {
        $this->autorizarAcceso($notaDebito);
        abort_unless(in_array($notaDebito->estado, ['CREADA', 'NO AUTORIZADO', 'DEVUELTA']), 403, 'Solo se pueden editar notas de debito en estado CREADA o NO AUTORIZADO.');

        $emisor = auth()->user()->emisor;
        $ivas = ImpuestoIva::activos()->get();
        $notaDebito->load(['motivos', 'cliente']);

        return view('emisor.comprobantes.notas-debito.edit', compact('notaDebito', 'ivas'));
    }

    public function update(Request $request, NotaDebito $notaDebito): RedirectResponse
    {
        $this->autorizarAcceso($notaDebito);
        abort_unless(in_array($notaDebito->estado, ['CREADA', 'NO AUTORIZADO', 'DEVUELTA']), 403, 'Solo se pueden editar notas de debito en estado CREADA o NO AUTORIZADO.');

        if ($notaDebito->estado !== 'CREADA') {
            $notaDebito->update(['estado' => 'CREADA', 'motivo_rechazo' => null]);
        }

        $validated = $request->validate([
            'cliente_id' => "required|exists:clientes,id,emisor_id," . (auth()->user()->emisor_id),
            'fecha_emision' => 'required|date|date_equals:today',
            'cod_doc_modificado' => 'required|string|max:2',
            'num_doc_modificado' => 'required|string|max:20',
            'fecha_emision_doc_sustento' => 'required|date',
            'observaciones' => 'nullable|string|max:500',
            'motivos' => 'required|array|min:1',
            'motivos.*.razon' => 'required|string|max:300',
            'motivos.*.valor' => 'required|numeric|min:0',
            'motivos.*.impuesto_iva_id' => 'required|exists:impuesto_ivas,id',
        ], [
            'fecha_emision.date_equals' => 'La fecha de emision debe ser la fecha de hoy (normativa SRI).',
        ]);

        $establecimiento = auth()->user()->establecimientosActivos()->first();
        abort_unless($establecimiento, 403, 'No tiene un establecimiento asignado.');
        $ptoEmision = $establecimiento->ptoEmisiones->first();
        abort_unless($ptoEmision, 403, 'No tiene un punto de emisión asignado.');
        $validated['establecimiento_id'] = $establecimiento->id;
        $validated['pto_emision_id'] = $ptoEmision->id;

        app(ComprobantesService::class)->actualizarNotaDebito($notaDebito, $validated);

        return redirect()->route('emisor.comprobantes.notas-debito.show', $notaDebito)
            ->with('success', 'Nota de debito actualizada correctamente.');
    }

    public function destroy(NotaDebito $notaDebito): RedirectResponse
    {
        $this->autorizarAcceso($notaDebito);
        if ($notaDebito->emisor->ambiente !== Ambiente::PRUEBAS) {
            return back()->with('error', 'Solo se pueden eliminar comprobantes emitidos en ambiente de pruebas.');
        }
        $notaDebito->motivos()->delete();
        $notaDebito->delete();

        $this->suscripcionService->decrementarContador($notaDebito->emisor);

        return redirect()->route('emisor.comprobantes.notas-debito.index')
            ->with('success', 'Nota de débito eliminada.');
    }

    public function procesar(NotaDebito $notaDebito): RedirectResponse
    {
        $this->autorizarAcceso($notaDebito);

        if (in_array($notaDebito->estado, ['AUTORIZADO', 'PROCESANDOSE'])) {
            return back()->with('info', 'Este comprobante ya fue enviado al SRI.');
        }

        app(\App\Services\SriService::class)->procesarComprobante($notaDebito, '05');
        $notaDebito->refresh();
        if ($notaDebito->estado === 'AUTORIZADO') {
            $notaDebito->load(['cliente', 'emisor', 'motivos.impuestoIva', 'establecimiento', 'ptoEmision', 'camposAdicionales']);
            app(\App\Services\ComprobanteEmailService::class)->enviarAutomatico($notaDebito, 'nota-debito', 'pdf.nota-debito', 'nd');
            return back()->with('success', 'Nota de débito autorizada por el SRI. Email enviado al cliente.');
        }
        if ($notaDebito->estado === 'PROCESANDOSE') {
            return back()->with('info', $notaDebito->motivo_rechazo ?? 'El comprobante está siendo procesado por el SRI. Consulte el estado en unos minutos.');
        }
        return back()->with('error', 'Nota de débito no autorizada por el SRI. Revise el detalle abajo.');
    }

    public function xml(NotaDebito $notaDebito)
    {
        $this->autorizarAcceso($notaDebito);
        if (!$notaDebito->xml_path || !file_exists($notaDebito->xml_path)) {
            return back()->with('error', 'XML no disponible.');
        }
        return response()->download($notaDebito->xml_path);
    }

    public function consultarEstado(NotaDebito $notaDebito): RedirectResponse
    {
        $this->autorizarAcceso($notaDebito);
        if (!$notaDebito->clave_acceso) {
            return back()->with('error', 'La nota de débito no tiene clave de acceso.');
        }
        $estado = app(\App\Services\SriService::class)->consultarYActualizar($notaDebito);
        if ($estado === 'AUTORIZADO') {
            $notaDebito->load(['cliente', 'emisor', 'motivos.impuestoIva', 'establecimiento', 'ptoEmision', 'camposAdicionales']);
            app(\App\Services\ComprobanteEmailService::class)->enviarAutomatico($notaDebito, 'nota-debito', 'pdf.nota-debito', 'nd');
        }
        return match ($estado) {
            'AUTORIZADO' => back()->with('success', 'Nota de débito autorizada por el SRI. Email enviado al cliente.'),
            'NO AUTORIZADO' => back()->with('error', 'Nota de débito no autorizada por el SRI. Revise el detalle abajo.'),
            default => back()->with('info', 'El comprobante sigue siendo procesado por el SRI. Intente nuevamente en unos minutos.'),
        };
    }

    public function pdf(NotaDebito $notaDebito)
    {
        $this->autorizarAcceso($notaDebito);
        return $this->pdfService->notaDebito($notaDebito);
    }

    public function pdfPos(NotaDebito $notaDebito)
    {
        $this->autorizarAcceso($notaDebito);
        return $this->pdfService->notaDebitoPos($notaDebito);
    }

    public function anular(NotaDebito $notaDebito): RedirectResponse
    {
        $this->autorizarAcceso($notaDebito);
        $notaDebito->update(['estado' => 'ANULADA']);
        return back()->with('success', 'Nota de débito anulada internamente. Recuerde completar la anulación en el portal del SRI.');
    }

    public function clonar(NotaDebito $notaDebito): RedirectResponse
    {
        $this->autorizarAcceso($notaDebito);
        $notaDebito->load(['motivos']);

        $emisor = auth()->user()->emisor;
        $this->suscripcionService->verificarEIncrementar($emisor);

        $data = [
            'establecimiento_id' => $notaDebito->establecimiento_id,
            'pto_emision_id' => $notaDebito->pto_emision_id,
            'cliente_id' => $notaDebito->cliente_id,
            'fecha_emision' => now()->toDateString(),
            'cod_doc_modificado' => $notaDebito->cod_doc_modificado,
            'num_doc_modificado' => $notaDebito->num_doc_modificado,
            'fecha_emision_doc_sustento' => $notaDebito->fecha_emision_doc_sustento,
            'observaciones' => $notaDebito->observaciones,
            'motivos' => $notaDebito->motivos->map(fn ($m) => [
                'razon' => $m->razon,
                'valor' => $m->valor,
                'impuesto_iva_id' => $m->impuesto_iva_id,
            ])->toArray(),
        ];

        $nueva = app(ComprobantesService::class)->crearNotaDebito($emisor, $data);

        return redirect()->route('emisor.comprobantes.notas-debito.edit', $nueva)
            ->with('success', 'Nota de débito duplicada correctamente.');
    }

    public function email(Request $request, \App\Models\NotaDebito $notaDebito): \Illuminate\Http\RedirectResponse
    {
        $this->autorizarAcceso($notaDebito);
        $notaDebito->load(['cliente', 'emisor', 'motivos.impuestoIva', 'establecimiento', 'ptoEmision', 'camposAdicionales']);
        return $this->emailService->enviar($request, $notaDebito, 'nota-debito', 'pdf.nota-debito', 'nd');
    }

    private function autorizarAcceso(NotaDebito $notaDebito): void
    {
        $user = auth()->user();
        if (!$user->esAdmin() && $notaDebito->emisor_id !== $user->emisor_id) {
            abort(403);
        }
    }
}
