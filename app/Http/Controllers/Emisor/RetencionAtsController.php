<?php

namespace App\Http\Controllers\Emisor;

use App\Http\Controllers\Controller;
use App\Models\DesgloceRetencion;
use App\Models\DocSustentoRetencion;
use App\Models\ImpuestoDocSustento;
use App\Models\PtoEmision;
use App\Models\RetencionAts;
use App\Services\ComprobanteEmailService;
use App\Services\PdfService;
use App\Services\SuscripcionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RetencionAtsController extends Controller
{
    public function __construct(
        private SuscripcionService $suscripcionService,
        private PdfService $pdfService,
        private ComprobanteEmailService $emailService,
    ) {}

    public function index(Request $request): View
    {
        $emisor = auth()->user()->emisor;

        $query = RetencionAts::where('emisor_id', $emisor->id)
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
                    ->orWhere('identificacion', 'like', "%{$request->buscar}%"))
                  ->orWhere('periodo_fiscal', 'like', "%{$request->buscar}%");
            });
        }

        $retencionesAts = $query->orderByDesc('fecha_emision')->orderByDesc('id')->paginate(50);
        return view('emisor.comprobantes.retenciones-ats.index', compact('retencionesAts'));
    }

    public function create(): View
    {
        $emisor = auth()->user()->emisor;
        $codigosRetencion = \App\Models\CodigoRetencion::activos()->orderBy('tipo')->orderBy('codigo')->get();

        return view('emisor.comprobantes.retenciones-ats.create', compact('codigosRetencion'));
    }

    public function store(Request $request): RedirectResponse
    {
        $emisor = auth()->user()->emisor;
        $this->suscripcionService->verificarEIncrementar($emisor);

        $validated = $request->validate([
            'cliente_id' => "required|exists:clientes,id,emisor_id,{$emisor->id}",
            'fecha_emision' => 'required|date',
            'periodo_fiscal' => 'required|string|max:7',
            'parte_rel' => 'required|in:SI,NO',
            // Documentos sustento (array)
            'doc_sustentos' => 'required|array|min:1',
            'doc_sustentos.*.cod_sustento' => 'required|string|max:2',
            'doc_sustentos.*.cod_doc_sustento' => 'required|string|max:2',
            'doc_sustentos.*.num_doc_sustento' => 'required|string|max:20',
            'doc_sustentos.*.fecha_emision_doc_sustento' => 'required|date',
            'doc_sustentos.*.fecha_registro_contable' => 'nullable|date',
            'doc_sustentos.*.num_aut_doc_sustento' => 'nullable|string|max:49',
            'doc_sustentos.*.pago_loc_ext' => 'required|string|max:2',
            'doc_sustentos.*.total_sin_impuestos' => 'required|numeric|min:0',
            'doc_sustentos.*.importe_total' => 'required|numeric|min:0',
            'doc_sustentos.*.forma_pago' => 'required|string|max:2',
            'doc_sustentos.*.impuestos_doc' => 'nullable|array',
            'doc_sustentos.*.impuestos_doc.*.codigo_impuesto' => 'required|string',
            'doc_sustentos.*.impuestos_doc.*.codigo_porcentaje' => 'required|string',
            'doc_sustentos.*.impuestos_doc.*.base_imponible' => 'required|numeric|min:0',
            'doc_sustentos.*.impuestos_doc.*.tarifa' => 'required|numeric|min:0',
            'doc_sustentos.*.impuestos_doc.*.valor_impuesto' => 'required|numeric|min:0',
            'doc_sustentos.*.retenciones' => 'required|array|min:1',
            'doc_sustentos.*.retenciones.*.codigo' => 'required|string',
            'doc_sustentos.*.retenciones.*.codigo_retencion' => 'required|string',
            'doc_sustentos.*.retenciones.*.base_imponible' => 'required|numeric|min:0',
            'doc_sustentos.*.retenciones.*.porcentaje_retener' => 'required|numeric|min:0',
            'doc_sustentos.*.retenciones.*.valor_retenido' => 'required|numeric|min:0',
        ]);

        $establecimiento = auth()->user()->establecimientosActivos()->first();
        abort_unless($establecimiento, 403, 'No tiene un establecimiento asignado.');
        $ptoEmision = $establecimiento->ptoEmisiones->first();
        abort_unless($ptoEmision, 403, 'No tiene un punto de emisión asignado.');
        $validated['establecimiento_id'] = $establecimiento->id;
        $validated['pto_emision_id'] = $ptoEmision->id;

        $retencionAts = DB::transaction(function () use ($emisor, $validated) {
            $ptoEmision = PtoEmision::findOrFail($validated['pto_emision_id']);
            $secuencial = $ptoEmision->siguienteSecuencial('retencion_ats');

            $retencionAts = RetencionAts::create([
                'emisor_id' => $emisor->id,
                'establecimiento_id' => $validated['establecimiento_id'],
                'pto_emision_id' => $validated['pto_emision_id'],
                'cliente_id' => $validated['cliente_id'],
                'secuencial' => $secuencial,
                'fecha_emision' => $validated['fecha_emision'],
                'periodo_fiscal' => $validated['periodo_fiscal'],
                'parte_rel' => $validated['parte_rel'],
                'estado' => 'CREADA',
                'user_id' => auth()->id(),
            ]);

            $this->guardarDocSustentos($retencionAts, $validated['doc_sustentos']);

            return $retencionAts;
        });


        return redirect()->route('emisor.comprobantes.retenciones-ats.show', $retencionAts)
            ->with('success', 'Retención ATS registrada correctamente.');
    }

    public function show(RetencionAts $retencionAt): View
    {
        $this->autorizarAcceso($retencionAt);
        $retencionAt->load(['cliente', 'emisor', 'establecimiento', 'ptoEmision', 'docSustentos.desgloses', 'docSustentos.impuestos', 'mensajes']);

        return view('emisor.comprobantes.retenciones-ats.show', compact('retencionAt'));
    }

    public function edit(RetencionAts $retencionAt): View
    {
        $this->autorizarAcceso($retencionAt);
        abort_unless(in_array($retencionAt->estado, ['CREADA', 'NO AUTORIZADO', 'DEVUELTA']), 403, 'Solo se pueden editar retenciones ATS en estado CREADA o NO AUTORIZADO.');

        $retencionAt->load(['cliente', 'establecimiento', 'ptoEmision', 'docSustentos.desgloses', 'docSustentos.impuestos']);
        $emisor = auth()->user()->emisor;
        $codigosRetencion = \App\Models\CodigoRetencion::activos()->orderBy('tipo')->orderBy('codigo')->get();

        return view('emisor.comprobantes.retenciones-ats.edit', compact('retencionAt', 'codigosRetencion'));
    }

    public function update(Request $request, RetencionAts $retencionAt): RedirectResponse
    {
        $this->autorizarAcceso($retencionAt);
        abort_unless(in_array($retencionAt->estado, ['CREADA', 'NO AUTORIZADO', 'DEVUELTA']), 403, 'Solo se pueden editar retenciones ATS en estado CREADA o NO AUTORIZADO.');

        if ($retencionAt->estado !== 'CREADA') {
            $retencionAt->update(['estado' => 'CREADA', 'motivo_rechazo' => null]);
        }

        $emisor = auth()->user()->emisor;

        $validated = $request->validate([
            'cliente_id' => "required|exists:clientes,id,emisor_id,{$emisor->id}",
            'fecha_emision' => 'required|date',
            'periodo_fiscal' => 'required|string|max:7',
            'parte_rel' => 'required|in:SI,NO',
            'doc_sustentos' => 'required|array|min:1',
            'doc_sustentos.*.cod_sustento' => 'required|string|max:2',
            'doc_sustentos.*.cod_doc_sustento' => 'required|string|max:2',
            'doc_sustentos.*.num_doc_sustento' => 'required|string|max:20',
            'doc_sustentos.*.fecha_emision_doc_sustento' => 'required|date',
            'doc_sustentos.*.fecha_registro_contable' => 'nullable|date',
            'doc_sustentos.*.num_aut_doc_sustento' => 'nullable|string|max:49',
            'doc_sustentos.*.pago_loc_ext' => 'required|string|max:2',
            'doc_sustentos.*.total_sin_impuestos' => 'required|numeric|min:0',
            'doc_sustentos.*.importe_total' => 'required|numeric|min:0',
            'doc_sustentos.*.forma_pago' => 'required|string|max:2',
            'doc_sustentos.*.impuestos_doc' => 'nullable|array',
            'doc_sustentos.*.impuestos_doc.*.codigo_impuesto' => 'required|string',
            'doc_sustentos.*.impuestos_doc.*.codigo_porcentaje' => 'required|string',
            'doc_sustentos.*.impuestos_doc.*.base_imponible' => 'required|numeric|min:0',
            'doc_sustentos.*.impuestos_doc.*.tarifa' => 'required|numeric|min:0',
            'doc_sustentos.*.impuestos_doc.*.valor_impuesto' => 'required|numeric|min:0',
            'doc_sustentos.*.retenciones' => 'required|array|min:1',
            'doc_sustentos.*.retenciones.*.codigo' => 'required|string',
            'doc_sustentos.*.retenciones.*.codigo_retencion' => 'required|string',
            'doc_sustentos.*.retenciones.*.base_imponible' => 'required|numeric|min:0',
            'doc_sustentos.*.retenciones.*.porcentaje_retener' => 'required|numeric|min:0',
            'doc_sustentos.*.retenciones.*.valor_retenido' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($retencionAt, $validated) {
            $retencionAt->update([
                'cliente_id' => $validated['cliente_id'],
                'fecha_emision' => $validated['fecha_emision'],
                'periodo_fiscal' => $validated['periodo_fiscal'],
                'parte_rel' => $validated['parte_rel'],
            ]);

            // Eliminar doc sustentos existentes (cascade elimina desgloses e impuestos)
            $retencionAt->docSustentos()->delete();

            $this->guardarDocSustentos($retencionAt, $validated['doc_sustentos']);
        });

        return redirect()->route('emisor.comprobantes.retenciones-ats.show', $retencionAt)
            ->with('success', 'Retención ATS actualizada correctamente.');
    }

    public function destroy(RetencionAts $retencionAt): RedirectResponse
    {
        $this->autorizarAcceso($retencionAt);
        $retencionAt->delete();

        $this->suscripcionService->decrementarContador($retencionAt->emisor);

        return redirect()->route('emisor.comprobantes.retenciones-ats.index')
            ->with('success', 'Retención ATS eliminada.');
    }

    public function procesar(RetencionAts $retencionAt): RedirectResponse
    {
        $this->autorizarAcceso($retencionAt);

        if (in_array($retencionAt->estado, ['AUTORIZADO', 'PROCESANDOSE'])) {
            return back()->with('info', 'Este comprobante ya fue enviado al SRI.');
        }

        app(\App\Services\SriService::class)->procesarComprobante($retencionAt, '07');
        $retencionAt->refresh();

        if ($retencionAt->estado === 'AUTORIZADO') {
            $retencionAt->load(['docSustentos.desgloses', 'docSustentos.impuestos', 'cliente', 'emisor', 'establecimiento', 'ptoEmision', 'camposAdicionales']);
            app(\App\Services\ComprobanteEmailService::class)->enviarAutomatico($retencionAt, 'retencion-ats', 'pdf.retencion-ats', 'ret');
            return back()->with('success', 'Retención ATS autorizada por el SRI. Email enviado al cliente.');
        }
        if ($retencionAt->estado === 'PROCESANDOSE') {
            return back()->with('info', $retencionAt->motivo_rechazo ?? 'El comprobante está siendo procesado por el SRI.');
        }
        return back()->with('error', 'Retención ATS no autorizada por el SRI. Revise el detalle abajo.');
    }

    public function xml(RetencionAts $retencionAt)
    {
        $this->autorizarAcceso($retencionAt);
        if (!$retencionAt->xml_path || !file_exists($retencionAt->xml_path)) {
            return back()->with('error', 'XML no disponible.');
        }
        return response()->download($retencionAt->xml_path);
    }

    public function consultarEstado(RetencionAts $retencionAt): RedirectResponse
    {
        $this->autorizarAcceso($retencionAt);
        if (!$retencionAt->clave_acceso) {
            return back()->with('error', 'La retención ATS no tiene clave de acceso.');
        }
        $estado = app(\App\Services\SriService::class)->consultarYActualizar($retencionAt);
        if ($estado === 'AUTORIZADO') {
            $retencionAt->load(['docSustentos.desgloses', 'docSustentos.impuestos', 'cliente', 'emisor', 'establecimiento', 'ptoEmision', 'camposAdicionales']);
            app(\App\Services\ComprobanteEmailService::class)->enviarAutomatico($retencionAt, 'retencion-ats', 'pdf.retencion-ats', 'ret');
        }
        return match ($estado) {
            'AUTORIZADO' => back()->with('success', 'Retención ATS autorizada por el SRI. Email enviado al cliente.'),
            'NO AUTORIZADO' => back()->with('error', 'Retención ATS no autorizada por el SRI. Revise el detalle abajo.'),
            default => back()->with('info', 'El comprobante sigue siendo procesado por el SRI.'),
        };
    }

    public function pdf(RetencionAts $retencionAt)
    {
        $this->autorizarAcceso($retencionAt);
        return $this->pdfService->retencionAts($retencionAt);
    }

    public function pdfPos(RetencionAts $retencionAt)
    {
        $this->autorizarAcceso($retencionAt);
        return $this->pdfService->retencionAtsPos($retencionAt);
    }

    public function clonar(RetencionAts $retencionAt): RedirectResponse
    {
        $this->autorizarAcceso($retencionAt);
        $retencionAt->load(['docSustentos.desgloses', 'docSustentos.impuestos']);

        $emisor = auth()->user()->emisor;
        $this->suscripcionService->verificarEIncrementar($emisor);

        $docSustentosData = $retencionAt->docSustentos->map(function ($ds) {
            return [
                'cod_sustento' => $ds->cod_sustento,
                'cod_doc_sustento' => $ds->cod_doc_sustento,
                'num_doc_sustento' => $ds->num_doc_sustento,
                'fecha_emision_doc_sustento' => $ds->fecha_emision_doc_sustento->toDateString(),
                'fecha_registro_contable' => $ds->fecha_registro_contable?->toDateString(),
                'num_aut_doc_sustento' => $ds->num_aut_doc_sustento,
                'pago_loc_ext' => $ds->pago_loc_ext,
                'total_sin_impuestos' => $ds->total_sin_impuestos,
                'importe_total' => $ds->importe_total,
                'forma_pago' => $ds->forma_pago,
                'impuestos_doc' => $ds->impuestos->map(fn ($imp) => [
                    'codigo_impuesto' => $imp->codigo_impuesto,
                    'codigo_porcentaje' => $imp->codigo_porcentaje,
                    'base_imponible' => $imp->base_imponible,
                    'tarifa' => $imp->tarifa,
                    'valor_impuesto' => $imp->valor_impuesto,
                ])->toArray(),
                'retenciones' => $ds->desgloses->map(fn ($d) => [
                    'codigo' => $d->codigo_impuesto,
                    'codigo_retencion' => $d->codigo_retencion,
                    'base_imponible' => $d->base_imponible,
                    'porcentaje_retener' => $d->porcentaje_retener,
                    'valor_retenido' => $d->valor_retenido,
                ])->toArray(),
            ];
        })->toArray();

        $nueva = DB::transaction(function () use ($emisor, $retencionAt, $docSustentosData) {
            $ptoEmision = PtoEmision::findOrFail($retencionAt->pto_emision_id);
            $secuencial = $ptoEmision->siguienteSecuencial('retencion_ats');

            $nueva = RetencionAts::create([
                'emisor_id' => $emisor->id,
                'establecimiento_id' => $retencionAt->establecimiento_id,
                'pto_emision_id' => $retencionAt->pto_emision_id,
                'cliente_id' => $retencionAt->cliente_id,
                'secuencial' => $secuencial,
                'fecha_emision' => now()->toDateString(),
                'periodo_fiscal' => $retencionAt->periodo_fiscal,
                'parte_rel' => $retencionAt->parte_rel,
                'estado' => 'CREADA',
                'user_id' => auth()->id(),
            ]);

            $this->guardarDocSustentos($nueva, $docSustentosData);

            return $nueva;
        });


        return redirect()->route('emisor.comprobantes.retenciones-ats.show', $nueva)
            ->with('success', 'Retención ATS duplicada correctamente.');
    }

    public function email(Request $request, RetencionAts $retencionAt): RedirectResponse
    {
        $this->autorizarAcceso($retencionAt);
        $retencionAt->load(['docSustentos.desgloses', 'docSustentos.impuestos', 'emisor', 'establecimiento', 'ptoEmision', 'camposAdicionales']);
        return $this->emailService->enviar($request, $retencionAt, 'retencion-ats', 'pdf.retencion-ats', 'ret');
    }

    private function guardarDocSustentos(RetencionAts $retencionAts, array $docSustentosData): void
    {
        foreach ($docSustentosData as $dsData) {
            $docSustento = DocSustentoRetencion::create([
                'retencion_ats_id' => $retencionAts->id,
                'cod_sustento' => $dsData['cod_sustento'],
                'cod_doc_sustento' => $dsData['cod_doc_sustento'],
                'num_doc_sustento' => $dsData['num_doc_sustento'],
                'fecha_emision_doc_sustento' => $dsData['fecha_emision_doc_sustento'],
                'fecha_registro_contable' => $dsData['fecha_registro_contable'] ?? null,
                'num_aut_doc_sustento' => $dsData['num_aut_doc_sustento'] ?? null,
                'pago_loc_ext' => $dsData['pago_loc_ext'],
                'total_sin_impuestos' => $dsData['total_sin_impuestos'],
                'total_iva' => 0,
                'importe_total' => $dsData['importe_total'],
                'forma_pago' => $dsData['forma_pago'],
            ]);

            // Impuestos del documento sustento (IVA, ICE, etc.)
            if (!empty($dsData['impuestos_doc'])) {
                $totalIva = 0;
                foreach ($dsData['impuestos_doc'] as $impDoc) {
                    ImpuestoDocSustento::create([
                        'doc_sustento_retencion_id' => $docSustento->id,
                        'codigo_impuesto' => $impDoc['codigo_impuesto'],
                        'codigo_porcentaje' => $impDoc['codigo_porcentaje'],
                        'base_imponible' => $impDoc['base_imponible'],
                        'tarifa' => $impDoc['tarifa'],
                        'valor_impuesto' => $impDoc['valor_impuesto'],
                    ]);
                    if ($impDoc['codigo_impuesto'] === '2') {
                        $totalIva += (float) $impDoc['valor_impuesto'];
                    }
                }
                $docSustento->update(['total_iva' => $totalIva]);
            }

            // Retenciones (desgloses)
            foreach ($dsData['retenciones'] as $ret) {
                DesgloceRetencion::create([
                    'doc_sustento_retencion_id' => $docSustento->id,
                    'codigo_impuesto' => $ret['codigo'],
                    'codigo_retencion' => $ret['codigo_retencion'],
                    'base_imponible' => $ret['base_imponible'],
                    'porcentaje_retener' => $ret['porcentaje_retener'],
                    'valor_retenido' => $ret['valor_retenido'],
                ]);
            }
        }
    }

    private function autorizarAcceso(RetencionAts $retencionAts): void
    {
        $user = auth()->user();
        if (!$user->esAdmin() && $retencionAts->emisor_id !== $user->emisor_id) {
            abort(403);
        }
    }
}
