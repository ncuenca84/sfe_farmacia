<?php

namespace App\Http\Controllers\Emisor;

use App\Enums\Ambiente;
use App\Http\Controllers\Controller;
use App\Models\Factura;
use App\Models\Guia;
use App\Services\ComprobantesService;
use App\Services\ComprobanteEmailService;
use App\Services\PdfService;
use App\Services\SuscripcionService;
use App\Services\ValidacionSriService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GuiaController extends Controller
{
    public function __construct(
        private SuscripcionService $suscripcionService,
        private PdfService $pdfService,
        private ComprobanteEmailService $emailService
    ) {}

    public function index(Request $request): View
    {
        $emisor = auth()->user()->emisor;

        $query = Guia::where('emisor_id', $emisor->id)
            ->with(['establecimiento', 'ptoEmision']);

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
                $q->where('ruc_transportista', 'like', "%{$request->buscar}%")
                    ->orWhere('razon_social_transportista', 'like', "%{$request->buscar}%");
            });
        }

        $guias = $query->orderByDesc('fecha_emision')->orderByDesc('secuencial')->paginate(50);
        return view('emisor.comprobantes.guias.index', compact('guias'));
    }

    public function create(): View
    {
        $emisor = auth()->user()->emisor;

        return view('emisor.comprobantes.guias.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $emisor = auth()->user()->emisor;

        $this->suscripcionService->verificarEIncrementar($emisor);

        $validated = $request->validate([
            'fecha_emision' => 'required|date|date_equals:today',
            'observaciones' => 'nullable|string|max:500',
            'dir_partida' => 'required|string|max:300',
            'dir_llegada' => 'nullable|string|max:300',
            'ruc_transportista' => 'required|string|max:13',
            'razon_social_transportista' => 'required|string|max:300',
            'placa' => 'nullable|string|max:20',
            'fecha_inicio_transporte' => 'required|date',
            'fecha_fin_transporte' => 'required|date',
            'destinatarios' => 'required|array|min:1',
            'destinatarios.*.identificacion' => 'required|string|max:20',
            'destinatarios.*.razon_social' => 'required|string|max:300',
            'destinatarios.*.direccion' => 'required|string|max:300',
            'destinatarios.*.motivo_traslado' => 'required|string|max:300',
            'destinatarios.*.doc_aduanero_unico' => 'nullable|string|max:20',
            'destinatarios.*.cod_establecimiento_destino' => 'nullable|string|max:3',
            'destinatarios.*.ruta' => 'nullable|string|max:300',
            'destinatarios.*.productos' => 'nullable|array',
            'destinatarios.*.productos.*.codigo_principal' => 'nullable|string|max:50',
            'destinatarios.*.productos.*.descripcion' => 'required|string|max:300',
            'destinatarios.*.productos.*.cantidad' => 'required|numeric|min:0.0001',
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
        app(ValidacionSriService::class)->validarGuia($emisor, $validated);

        $guia = app(ComprobantesService::class)->crearGuia($emisor, $validated);


        return redirect()->route('emisor.comprobantes.guias.show', $guia)
            ->with('success', 'Guía de remisión guardada correctamente.');
    }

    public function show(Guia $guia): View
    {
        $this->autorizarAcceso($guia);
        $guia->load(['detalles', 'emisor', 'establecimiento', 'ptoEmision', 'camposAdicionales', 'mensajes']);

        return view('emisor.comprobantes.guias.show', compact('guia'));
    }

    public function edit(Guia $guia): View
    {
        $this->autorizarAcceso($guia);
        abort_unless(in_array($guia->estado, ['CREADA', 'NO AUTORIZADO', 'DEVUELTA']), 403, 'Solo se pueden editar guias en estado CREADA o NO AUTORIZADO.');

        $emisor = auth()->user()->emisor;
        $guia->load(['detalles']);

        return view('emisor.comprobantes.guias.edit', compact('guia'));
    }

    public function update(Request $request, Guia $guia): RedirectResponse
    {
        $this->autorizarAcceso($guia);
        abort_unless(in_array($guia->estado, ['CREADA', 'NO AUTORIZADO', 'DEVUELTA']), 403, 'Solo se pueden editar guias en estado CREADA o NO AUTORIZADO.');

        if ($guia->estado !== 'CREADA') {
            $guia->update(['estado' => 'CREADA', 'motivo_rechazo' => null]);
        }

        $validated = $request->validate([
            'fecha_emision' => 'required|date|date_equals:today',
            'observaciones' => 'nullable|string|max:500',
            'dir_partida' => 'required|string|max:300',
            'dir_llegada' => 'nullable|string|max:300',
            'ruc_transportista' => 'required|string|max:13',
            'razon_social_transportista' => 'required|string|max:300',
            'placa' => 'nullable|string|max:20',
            'fecha_inicio_transporte' => 'required|date',
            'fecha_fin_transporte' => 'required|date',
            'destinatarios' => 'required|array|min:1',
            'destinatarios.*.identificacion' => 'required|string|max:20',
            'destinatarios.*.razon_social' => 'required|string|max:300',
            'destinatarios.*.direccion' => 'required|string|max:300',
            'destinatarios.*.motivo_traslado' => 'required|string|max:300',
            'destinatarios.*.doc_aduanero_unico' => 'nullable|string|max:20',
            'destinatarios.*.cod_establecimiento_destino' => 'nullable|string|max:3',
            'destinatarios.*.ruta' => 'nullable|string|max:300',
            'destinatarios.*.productos' => 'nullable|array',
            'destinatarios.*.productos.*.codigo_principal' => 'nullable|string|max:50',
            'destinatarios.*.productos.*.descripcion' => 'required|string|max:300',
            'destinatarios.*.productos.*.cantidad' => 'required|numeric|min:0.0001',
        ], [
            'fecha_emision.date_equals' => 'La fecha de emision debe ser la fecha de hoy (normativa SRI).',
        ]);

        $establecimiento = auth()->user()->establecimientosActivos()->first();
        abort_unless($establecimiento, 403, 'No tiene un establecimiento asignado.');
        $ptoEmision = $establecimiento->ptoEmisiones->first();
        abort_unless($ptoEmision, 403, 'No tiene un punto de emisión asignado.');
        $validated['establecimiento_id'] = $establecimiento->id;
        $validated['pto_emision_id'] = $ptoEmision->id;

        app(ComprobantesService::class)->actualizarGuia($guia, $validated);

        return redirect()->route('emisor.comprobantes.guias.show', $guia)
            ->with('success', 'Guia de remision actualizada correctamente.');
    }

    public function destroy(Guia $guia): RedirectResponse
    {
        $this->autorizarAcceso($guia);
        if ($guia->emisor->ambiente !== Ambiente::PRUEBAS) {
            return back()->with('error', 'Solo se pueden eliminar comprobantes emitidos en ambiente de pruebas.');
        }
        $guia->detalles()->delete();
        $guia->delete();

        $this->suscripcionService->decrementarContador($guia->emisor);

        return redirect()->route('emisor.comprobantes.guias.index')
            ->with('success', 'Guía de remisión eliminada.');
    }

    public function procesar(Guia $guia): RedirectResponse
    {
        $this->autorizarAcceso($guia);

        if (in_array($guia->estado, ['AUTORIZADO', 'PROCESANDOSE'])) {
            return back()->with('info', 'Este comprobante ya fue enviado al SRI.');
        }

        app(\App\Services\SriService::class)->procesarComprobante($guia, '06');
        $guia->refresh();
        if ($guia->estado === 'AUTORIZADO') {
            $guia->load(['detalles', 'emisor', 'establecimiento', 'ptoEmision', 'destinatarios']);
            app(\App\Services\ComprobanteEmailService::class)->enviarAutomatico($guia, 'guia', 'pdf.guia', 'guia');
            return back()->with('success', 'Guía de remisión autorizada por el SRI. Email enviado al cliente.');
        }
        if ($guia->estado === 'PROCESANDOSE') {
            return back()->with('info', $guia->motivo_rechazo ?? 'El comprobante está siendo procesado por el SRI. Consulte el estado en unos minutos.');
        }
        return back()->with('error', 'Guía de remisión no autorizada por el SRI. Revise el detalle abajo.');
    }

    public function xml(Guia $guia)
    {
        $this->autorizarAcceso($guia);
        if (!$guia->xml_path || !file_exists($guia->xml_path)) {
            return back()->with('error', 'XML no disponible.');
        }
        return response()->download($guia->xml_path);
    }

    public function consultarEstado(Guia $guia): RedirectResponse
    {
        $this->autorizarAcceso($guia);
        if (!$guia->clave_acceso) {
            return back()->with('error', 'La guía de remisión no tiene clave de acceso.');
        }
        $estado = app(\App\Services\SriService::class)->consultarYActualizar($guia);
        if ($estado === 'AUTORIZADO') {
            $guia->load(['detalles', 'emisor', 'establecimiento', 'ptoEmision', 'destinatarios']);
            app(\App\Services\ComprobanteEmailService::class)->enviarAutomatico($guia, 'guia', 'pdf.guia', 'guia');
        }
        return match ($estado) {
            'AUTORIZADO' => back()->with('success', 'Guía de remisión autorizada por el SRI. Email enviado al cliente.'),
            'NO AUTORIZADO' => back()->with('error', 'Guía de remisión no autorizada por el SRI. Revise el detalle abajo.'),
            default => back()->with('info', 'El comprobante sigue siendo procesado por el SRI. Intente nuevamente en unos minutos.'),
        };
    }

    public function pdf(Guia $guia)
    {
        $this->autorizarAcceso($guia);
        return $this->pdfService->guia($guia);
    }

    public function pdfPos(Guia $guia)
    {
        $this->autorizarAcceso($guia);
        return $this->pdfService->guiaPos($guia);
    }

    public function anular(Guia $guia): RedirectResponse
    {
        $this->autorizarAcceso($guia);
        $guia->update(['estado' => 'ANULADA']);
        return back()->with('success', 'Guía de remisión anulada internamente. Recuerde completar la anulación en el portal del SRI.');
    }

    public function clonar(Guia $guia): RedirectResponse
    {
        $this->autorizarAcceso($guia);
        $guia->load(['detalles']);

        $emisor = auth()->user()->emisor;
        $this->suscripcionService->verificarEIncrementar($emisor);

        $destinatarios = [];
        $seen = [];
        foreach ($guia->detalles as $det) {
            $key = $det->identificacion_destinatario . '|' . $det->razon_social_destinatario;
            if (!isset($seen[$key])) {
                $seen[$key] = count($destinatarios);
                $destinatarios[] = [
                    'identificacion' => $det->identificacion_destinatario,
                    'razon_social' => $det->razon_social_destinatario,
                    'direccion' => $det->dir_destinatario,
                    'motivo_traslado' => $det->motivo_traslado,
                    'doc_aduanero_unico' => $det->doc_aduanero_unico,
                    'cod_establecimiento_destino' => $det->cod_establecimiento_destino,
                    'ruta' => $det->ruta,
                    'productos' => [],
                ];
            }
            if ($det->descripcion) {
                $destinatarios[$seen[$key]]['productos'][] = [
                    'codigo_principal' => $det->codigo_principal,
                    'descripcion' => $det->descripcion,
                    'cantidad' => $det->cantidad,
                ];
            }
        }

        $data = [
            'establecimiento_id' => $guia->establecimiento_id,
            'pto_emision_id' => $guia->pto_emision_id,
            'fecha_emision' => now()->toDateString(),
            'dir_partida' => $guia->dir_partida,
            'dir_llegada' => $guia->dir_llegada,
            'ruc_transportista' => $guia->ruc_transportista,
            'razon_social_transportista' => $guia->razon_social_transportista,
            'placa' => $guia->placa,
            'fecha_inicio_transporte' => $guia->fecha_ini_transporte,
            'fecha_fin_transporte' => $guia->fecha_fin_transporte,
            'observaciones' => $guia->observaciones,
            'destinatarios' => $destinatarios,
        ];

        $nueva = app(ComprobantesService::class)->crearGuia($emisor, $data);

        return redirect()->route('emisor.comprobantes.guias.edit', $nueva)
            ->with('success', 'Guía de remisión duplicada correctamente.');
    }

    public function email(Request $request, \App\Models\Guia $guia): \Illuminate\Http\RedirectResponse
    {
        $this->autorizarAcceso($guia);
        $guia->load(['detalles', 'emisor', 'establecimiento', 'ptoEmision', 'destinatarios']);
        return $this->emailService->enviar($request, $guia, 'guia', 'pdf.guia', 'guia');
    }

    public function crearDesdeFactura(Factura $factura): View
    {
        $user = auth()->user();
        if (!$user->esAdmin() && $factura->emisor_id !== $user->emisor_id) {
            abort(403);
        }

        $factura->load(['cliente', 'detalles', 'infoGuia']);
        $emisor = $user->emisor;
        $prefill = [
            'establecimiento_id' => $factura->establecimiento_id,
            'pto_emision_id' => $factura->pto_emision_id,
            'factura_numero' => ($factura->establecimiento->codigo ?? '000') . '-' .
                ($factura->ptoEmision->codigo ?? '000') . '-' .
                str_pad($factura->secuencial ?? 0, 9, '0', STR_PAD_LEFT),
        ];

        // Pre-fill transportista data from infoGuia if available
        if ($factura->infoGuia) {
            $prefill['dir_partida'] = $factura->infoGuia->dir_partida;
            $prefill['dir_llegada'] = $factura->infoGuia->dir_llegada;
            $prefill['ruc_transportista'] = $factura->infoGuia->ruc_transportista;
            $prefill['razon_social_transportista'] = $factura->infoGuia->razon_social_transportista;
            $prefill['placa'] = $factura->infoGuia->placa;
            $prefill['fecha_inicio_transporte'] = $factura->infoGuia->fecha_ini_transporte;
            $prefill['fecha_fin_transporte'] = $factura->infoGuia->fecha_fin_transporte;
        }

        // Pre-fill destinatario from factura's client
        $prefill['destinatarios'] = [[
            'identificacion' => $factura->cliente->identificacion ?? '',
            'razon_social' => $factura->cliente->razon_social ?? '',
            'direccion' => $factura->cliente->direccion ?? '',
            'motivo_traslado' => 'Venta',
            'productos' => $factura->detalles->map(fn ($d) => [
                'codigo_principal' => $d->codigo_principal ?? '',
                'descripcion' => $d->descripcion,
                'cantidad' => $d->cantidad,
            ])->toArray(),
        ]];

        return view('emisor.comprobantes.guias.create', compact('prefill'));
    }

    private function autorizarAcceso(Guia $guia): void
    {
        $user = auth()->user();
        if (!$user->esAdmin() && $guia->emisor_id !== $user->emisor_id) {
            abort(403);
        }
    }
}
