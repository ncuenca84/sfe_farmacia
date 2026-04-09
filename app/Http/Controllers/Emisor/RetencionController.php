<?php

namespace App\Http\Controllers\Emisor;

use App\Enums\Ambiente;
use App\Http\Controllers\Controller;
use App\Models\Retencion;
use App\Services\ComprobantesService;
use App\Services\ComprobanteEmailService;
use App\Services\PdfService;
use App\Services\SuscripcionService;
use App\Services\ValidacionSriService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RetencionController extends Controller
{
    public function __construct(
        private SuscripcionService $suscripcionService,
        private PdfService $pdfService,
        private ComprobanteEmailService $emailService
    ) {}

    public function index(Request $request): View
    {
        $emisor = auth()->user()->emisor;

        $query = Retencion::where('emisor_id', $emisor->id)
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

        $retenciones = $query->orderByDesc('fecha_emision')->orderByDesc('secuencial')->paginate(50);
        return view('emisor.comprobantes.retenciones.index', compact('retenciones'));
    }

    public function create(Request $request): View
    {
        $emisor = auth()->user()->emisor;

        $compra = null;
        $clienteProveedor = null;
        if ($request->filled('from_compra')) {
            $compra = \App\Models\Compra::where('id', $request->from_compra)
                ->where('emisor_id', $emisor->id)
                ->first();

            if ($compra) {
                $clienteProveedor = $emisor->clientes()
                    ->where('identificacion', $compra->ruc_proveedor)
                    ->first();

                if (!$clienteProveedor) {
                    $clienteProveedor = $emisor->clientes()->create([
                        'tipo_identificacion' => '04',
                        'identificacion' => $compra->ruc_proveedor,
                        'razon_social' => $compra->razon_social_proveedor,
                        'email' => 'proveedor@pendiente.ec',
                        'direccion' => 'N/A',
                    ]);
                }
            }
        }

        return view('emisor.comprobantes.retenciones.create', compact('compra', 'clienteProveedor'));
    }

    public function store(Request $request): RedirectResponse
    {
        $emisor = auth()->user()->emisor;

        $this->suscripcionService->verificarEIncrementar($emisor);

        $validated = $request->validate([
            'cliente_id' => "required|exists:clientes,id,emisor_id,{$emisor->id}",
            'fecha_emision' => 'required|date',
            'tipo_doc_sustento' => 'required|string|max:2',
            'num_doc_sustento' => 'required|string|max:20',
            'fecha_emision_doc_sustento' => 'required|date',
            'impuestos' => 'required|array|min:1',
            'impuestos.*.codigo' => 'required|string',
            'impuestos.*.codigo_retencion' => 'required|string',
            'impuestos.*.base_imponible' => 'required|numeric|min:0',
            'impuestos.*.porcentaje_retener' => 'required|numeric|min:0',
            'impuestos.*.valor_retenido' => 'required|numeric|min:0',
        ]);

        $establecimiento = auth()->user()->establecimientosActivos()->first();
        abort_unless($establecimiento, 403, 'No tiene un establecimiento asignado.');
        $ptoEmision = $establecimiento->ptoEmisiones->first();
        abort_unless($ptoEmision, 403, 'No tiene un punto de emisión asignado.');
        $validated['establecimiento_id'] = $establecimiento->id;
        $validated['pto_emision_id'] = $ptoEmision->id;

        // Validar retención duplicada sobre el mismo documento sustento
        $retencionExistente = \App\Models\Retencion::where('emisor_id', $emisor->id)
            ->where('num_doc_sustento', $validated['num_doc_sustento'])
            ->whereNotIn('estado', ['ANULADA'])
            ->first();

        if ($retencionExistente) {
            return back()->withInput()->with('error',
                "Ya existe una retención (#{$retencionExistente->secuencial}) para el documento {$validated['num_doc_sustento']}. "
                . "Estado: {$retencionExistente->estado}. Si necesita crear otra, primero anule la existente."
            );
        }

        // Validaciones SRI antes de generar XML
        $cliente = \App\Models\Cliente::findOrFail($validated['cliente_id']);
        app(ValidacionSriService::class)->validarRetencion($emisor, $cliente, $validated);

        $ret = app(ComprobantesService::class)->crearRetencion($emisor, $validated);


        return redirect()->route('emisor.comprobantes.retenciones.show', $ret)
            ->with('success', 'Retención guardada correctamente.');
    }

    public function show(Retencion $retencion): View
    {
        $this->autorizarAcceso($retencion);
        $retencion->load(['impuestosRetencion', 'cliente', 'emisor', 'establecimiento', 'ptoEmision', 'camposAdicionales', 'mensajes']);

        return view('emisor.comprobantes.retenciones.show', compact('retencion'));
    }

    public function edit(Retencion $retencion): View
    {
        $this->autorizarAcceso($retencion);

        if ($retencion->estado !== 'CREADA') {
            abort(403, 'Solo se pueden editar retenciones en estado CREADA.');
        }

        $emisor = auth()->user()->emisor;
        $retencion->load(['impuestosRetencion', 'cliente']);

        return view('emisor.comprobantes.retenciones.edit', compact('retencion'));
    }

    public function update(Request $request, Retencion $retencion): RedirectResponse
    {
        $this->autorizarAcceso($retencion);

        if ($retencion->estado !== 'CREADA') {
            return back()->with('error', 'Solo se pueden editar retenciones en estado CREADA.');
        }

        $emisor = auth()->user()->emisor;

        $validated = $request->validate([
            'cliente_id' => "required|exists:clientes,id,emisor_id,{$emisor->id}",
            'fecha_emision' => 'required|date',
            'tipo_doc_sustento' => 'required|string|max:2',
            'num_doc_sustento' => 'required|string|max:20',
            'fecha_emision_doc_sustento' => 'required|date',
            'impuestos' => 'required|array|min:1',
            'impuestos.*.codigo' => 'required|string',
            'impuestos.*.codigo_retencion' => 'required|string',
            'impuestos.*.base_imponible' => 'required|numeric|min:0',
            'impuestos.*.porcentaje_retener' => 'required|numeric|min:0',
            'impuestos.*.valor_retenido' => 'required|numeric|min:0',
        ]);

        $retencion->update([
            'cliente_id' => $validated['cliente_id'],
            'fecha_emision' => $validated['fecha_emision'],
            'cod_doc_sustento' => $validated['tipo_doc_sustento'],
            'num_doc_sustento' => $validated['num_doc_sustento'],
            'fecha_emision_doc_sustento' => $validated['fecha_emision_doc_sustento'],
            'clave_acceso' => null,
        ]);

        $retencion->impuestosRetencion()->delete();

        foreach ($validated['impuestos'] as $imp) {
            \App\Models\RetencionImpuesto::create([
                'retencion_id' => $retencion->id,
                'codigo_impuesto' => $imp['codigo'],
                'codigo_retencion' => $imp['codigo_retencion'],
                'base_imponible' => $imp['base_imponible'],
                'porcentaje_retener' => $imp['porcentaje_retener'],
                'valor_retenido' => $imp['valor_retenido'],
                'cod_doc_sustento' => $validated['tipo_doc_sustento'],
                'num_doc_sustento' => $validated['num_doc_sustento'],
                'fecha_emision_doc_sustento' => $validated['fecha_emision_doc_sustento'],
            ]);
        }

        return redirect()->route('emisor.comprobantes.retenciones.show', $retencion)
            ->with('success', 'Retención actualizada correctamente.');
    }

    public function destroy(Retencion $retencion): RedirectResponse
    {
        $this->autorizarAcceso($retencion);
        if ($retencion->emisor->ambiente !== Ambiente::PRUEBAS) {
            return back()->with('error', 'Solo se pueden eliminar comprobantes emitidos en ambiente de pruebas.');
        }
        $retencion->impuestosRetencion()->delete();
        $retencion->delete();

        $this->suscripcionService->decrementarContador($retencion->emisor);

        return redirect()->route('emisor.comprobantes.retenciones.index')
            ->with('success', 'Retención eliminada.');
    }

    public function procesar(Retencion $retencion): RedirectResponse
    {
        $this->autorizarAcceso($retencion);

        if (in_array($retencion->estado, ['AUTORIZADO', 'PROCESANDOSE'])) {
            return back()->with('info', 'Este comprobante ya fue enviado al SRI.');
        }

        app(\App\Services\SriService::class)->procesarComprobante($retencion, '07');
        $retencion->refresh();
        if ($retencion->estado === 'AUTORIZADO') {
            $retencion->load(['impuestosRetencion', 'cliente', 'emisor', 'establecimiento', 'ptoEmision', 'camposAdicionales']);
            app(\App\Services\ComprobanteEmailService::class)->enviarAutomatico($retencion, 'retencion', 'pdf.retencion', 'ret');
            return back()->with('success', 'Retención autorizada por el SRI. Email enviado al cliente.');
        }
        if ($retencion->estado === 'PROCESANDOSE') {
            return back()->with('info', $retencion->motivo_rechazo ?? 'El comprobante está siendo procesado por el SRI. Consulte el estado en unos minutos.');
        }
        return back()->with('error', 'Retención no autorizada por el SRI. Revise el detalle abajo.');
    }

    public function xml(Retencion $retencion)
    {
        $this->autorizarAcceso($retencion);
        if (!$retencion->xml_path || !file_exists($retencion->xml_path)) {
            return back()->with('error', 'XML no disponible.');
        }
        return response()->download($retencion->xml_path);
    }

    public function consultarEstado(Retencion $retencion): RedirectResponse
    {
        $this->autorizarAcceso($retencion);
        if (!$retencion->clave_acceso) {
            return back()->with('error', 'La retención no tiene clave de acceso.');
        }
        $estado = app(\App\Services\SriService::class)->consultarYActualizar($retencion);
        if ($estado === 'AUTORIZADO') {
            $retencion->load(['impuestosRetencion', 'cliente', 'emisor', 'establecimiento', 'ptoEmision', 'camposAdicionales']);
            app(\App\Services\ComprobanteEmailService::class)->enviarAutomatico($retencion, 'retencion', 'pdf.retencion', 'ret');
        }
        return match ($estado) {
            'AUTORIZADO' => back()->with('success', 'Retención autorizada por el SRI. Email enviado al cliente.'),
            'NO AUTORIZADO' => back()->with('error', 'Retención no autorizada por el SRI. Revise el detalle abajo.'),
            default => back()->with('info', 'El comprobante sigue siendo procesado por el SRI. Intente nuevamente en unos minutos.'),
        };
    }

    public function pdf(Retencion $retencion)
    {
        $this->autorizarAcceso($retencion);
        return $this->pdfService->retencion($retencion);
    }

    public function pdfPos(Retencion $retencion)
    {
        $this->autorizarAcceso($retencion);
        return $this->pdfService->retencionPos($retencion);
    }

    public function anular(Retencion $retencion): RedirectResponse
    {
        $this->autorizarAcceso($retencion);
        $retencion->update(['estado' => 'ANULADA']);
        return back()->with('success', 'Retención anulada internamente. Recuerde completar la anulación en el portal del SRI.');
    }

    public function clonar(Retencion $retencion): RedirectResponse
    {
        $this->autorizarAcceso($retencion);
        $retencion->load(['impuestosRetencion']);

        $emisor = auth()->user()->emisor;
        $this->suscripcionService->verificarEIncrementar($emisor);

        $data = [
            'establecimiento_id' => $retencion->establecimiento_id,
            'pto_emision_id' => $retencion->pto_emision_id,
            'cliente_id' => $retencion->cliente_id,
            'fecha_emision' => now()->toDateString(),
            'tipo_doc_sustento' => $retencion->cod_doc_sustento,
            'num_doc_sustento' => $retencion->num_doc_sustento,
            'fecha_emision_doc_sustento' => $retencion->fecha_emision_doc_sustento,
            'impuestos' => $retencion->impuestosRetencion->map(fn ($i) => [
                'codigo' => $i->codigo_impuesto,
                'codigo_retencion' => $i->codigo_retencion,
                'base_imponible' => $i->base_imponible,
                'porcentaje_retener' => $i->porcentaje_retener,
                'valor_retenido' => $i->valor_retenido,
            ])->toArray(),
        ];

        $nueva = app(ComprobantesService::class)->crearRetencion($emisor, $data);

        return redirect()->route('emisor.comprobantes.retenciones.show', $nueva)
            ->with('success', 'Retención duplicada correctamente.');
    }

    public function email(Request $request, \App\Models\Retencion $retencion): \Illuminate\Http\RedirectResponse
    {
        $this->autorizarAcceso($retencion);
        $retencion->load(['impuestosRetencion', 'emisor', 'establecimiento', 'ptoEmision', 'camposAdicionales']);
        return $this->emailService->enviar($request, $retencion, 'retencion', 'pdf.retencion', 'ret');
    }

    private function autorizarAcceso(Retencion $retencion): void
    {
        $user = auth()->user();
        if (!$user->esAdmin() && $retencion->emisor_id !== $user->emisor_id) {
            abort(403);
        }
    }
}
