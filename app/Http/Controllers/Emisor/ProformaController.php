<?php

namespace App\Http\Controllers\Emisor;

use App\Enums\Ambiente;
use App\Http\Controllers\Controller;
use App\Models\ImpuestoIva;
use App\Models\Proforma;
use App\Services\ComprobantesService;
use App\Services\ComprobanteEmailService;
use App\Services\PdfService;
use App\Services\FacturaService;
use App\Services\SuscripcionService;
use App\Services\ValidacionSriService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProformaController extends Controller
{
    public function __construct(
        private PdfService $pdfService,
        private ComprobanteEmailService $emailService,
    ) {}

    public function index(Request $request): View
    {
        $emisor = auth()->user()->emisor;

        $query = Proforma::where('emisor_id', $emisor->id)
            ->with(['cliente', 'establecimiento']);

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

        $proformas = $query->orderByDesc('fecha_emision')->paginate(50);
        return view('emisor.comprobantes.proformas.index', compact('proformas'));
    }

    public function create(): View
    {
        $emisor = auth()->user()->emisor;

        $ivas = ImpuestoIva::activos()->get();

        return view('emisor.comprobantes.proformas.create', compact('ivas'));
    }

    public function store(Request $request): RedirectResponse
    {
        $emisor = auth()->user()->emisor;

        $validated = $request->validate([
            'cliente_id' => "required|exists:clientes,id,emisor_id," . (auth()->user()->emisor_id),
            'fecha_emision' => 'required|date',
            'fecha_vencimiento' => 'nullable|date|after_or_equal:fecha_emision',
            'observaciones' => 'nullable|string|max:500',
            'detalles' => 'required|array|min:1',
            'detalles.*.descripcion' => 'required|string|max:300',
            'detalles.*.cantidad' => 'required|numeric|min:0.0001',
            'detalles.*.precio_unitario' => 'required|numeric|min:0',
            'detalles.*.descuento' => 'nullable|numeric|min:0',
            'detalles.*.codigo_principal' => 'nullable|string|max:50',
            'detalles.*.impuesto_iva_id' => 'required|exists:impuesto_ivas,id',
        ]);

        $establecimiento = auth()->user()->establecimientosActivos()->first();
        abort_unless($establecimiento, 403, 'No tiene un establecimiento asignado.');
        $ptoEmision = $establecimiento->ptoEmisiones->first();
        abort_unless($ptoEmision, 403, 'No tiene un punto de emisión asignado.');
        $validated['establecimiento_id'] = $establecimiento->id;
        $validated['pto_emision_id'] = $ptoEmision->id;

        $proforma = app(ComprobantesService::class)->crearProforma($emisor, $validated);

        return redirect()->route('emisor.comprobantes.proformas.show', $proforma)
            ->with('success', 'Proforma creada correctamente.');
    }

    public function show(Proforma $proforma): View
    {
        $this->autorizarAcceso($proforma);
        $proforma->load(['detalles.impuestos', 'cliente', 'emisor', 'establecimiento']);

        return view('emisor.comprobantes.proformas.show', compact('proforma'));
    }

    public function edit(Proforma $proforma): View
    {
        $this->autorizarAcceso($proforma);
        abort_unless(in_array($proforma->estado, ['VIGENTE', 'CREADA']), 403, 'Solo se pueden editar proformas vigentes.');

        $emisor = auth()->user()->emisor;
        $ivas = ImpuestoIva::activos()->get();
        $proforma->load(['detalles.impuestos', 'cliente']);

        return view('emisor.comprobantes.proformas.edit', compact('proforma', 'ivas'));
    }

    public function update(Request $request, Proforma $proforma): RedirectResponse
    {
        $this->autorizarAcceso($proforma);
        abort_unless(in_array($proforma->estado, ['VIGENTE', 'CREADA']), 403, 'Solo se pueden editar proformas vigentes.');

        $validated = $request->validate([
            'cliente_id' => "required|exists:clientes,id,emisor_id," . (auth()->user()->emisor_id),
            'fecha_emision' => 'required|date',
            'fecha_vencimiento' => 'nullable|date|after_or_equal:fecha_emision',
            'observaciones' => 'nullable|string|max:500',
            'detalles' => 'required|array|min:1',
            'detalles.*.descripcion' => 'required|string|max:300',
            'detalles.*.cantidad' => 'required|numeric|min:0.0001',
            'detalles.*.precio_unitario' => 'required|numeric|min:0',
            'detalles.*.descuento' => 'nullable|numeric|min:0',
            'detalles.*.codigo_principal' => 'nullable|string|max:50',
            'detalles.*.impuesto_iva_id' => 'required|exists:impuesto_ivas,id',
        ]);

        $establecimiento = auth()->user()->establecimientosActivos()->first();
        abort_unless($establecimiento, 403, 'No tiene un establecimiento asignado.');
        $ptoEmision = $establecimiento->ptoEmisiones->first();
        abort_unless($ptoEmision, 403, 'No tiene un punto de emisión asignado.');
        $validated['establecimiento_id'] = $establecimiento->id;
        $validated['pto_emision_id'] = $ptoEmision->id;

        app(ComprobantesService::class)->actualizarProforma($proforma, $validated);

        return redirect()->route('emisor.comprobantes.proformas.show', $proforma)
            ->with('success', 'Proforma actualizada correctamente.');
    }

    public function destroy(Proforma $proforma): RedirectResponse
    {
        $this->autorizarAcceso($proforma);
        if ($proforma->emisor->ambiente !== Ambiente::PRUEBAS) {
            return back()->with('error', 'Solo se pueden eliminar comprobantes emitidos en ambiente de pruebas.');
        }
        $proforma->delete();
        return redirect()->route('emisor.comprobantes.proformas.index')
            ->with('success', 'Proforma eliminada.');
    }

    public function pdf(Proforma $proforma)
    {
        $this->autorizarAcceso($proforma);
        return $this->pdfService->proforma($proforma);
    }

    public function pdfPos(Proforma $proforma)
    {
        $this->autorizarAcceso($proforma);
        return $this->pdfService->proformaPos($proforma);
    }

    public function clonar(Proforma $proforma): RedirectResponse
    {
        $this->autorizarAcceso($proforma);
        $proforma->load(['detalles.impuestos']);

        $emisor = auth()->user()->emisor;

        $data = [
            'establecimiento_id' => $proforma->establecimiento_id,
            'pto_emision_id' => $proforma->pto_emision_id,
            'cliente_id' => $proforma->cliente_id,
            'fecha_emision' => now()->toDateString(),
            'fecha_vencimiento' => $proforma->fecha_vencimiento,
            'observaciones' => $proforma->observaciones,
            'detalles' => $proforma->detalles->map(fn ($d) => [
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

        $nueva = app(ComprobantesService::class)->crearProforma($emisor, $data);

        return redirect()->route('emisor.comprobantes.proformas.edit', $nueva)
            ->with('success', 'Proforma duplicada correctamente.');
    }

    public function email(Request $request, Proforma $proforma): RedirectResponse
    {
        $this->autorizarAcceso($proforma);
        $proforma->load(['cliente', 'emisor', 'detalles.impuestos', 'establecimiento', 'ptoEmision']);

        return $this->emailService->enviar($request, $proforma, 'proforma', 'pdf.proforma', 'proforma');
    }

    public function convertirAFactura(Proforma $proforma): RedirectResponse
    {
        $this->autorizarAcceso($proforma);
        abort_unless(in_array($proforma->estado, ['VIGENTE', 'CREADA']), 403, 'Solo se pueden facturar proformas vigentes.');

        $proforma->load(['detalles.impuestos', 'cliente']);

        $emisor = auth()->user()->emisor;

        // Verificar suscripción
        app(SuscripcionService::class)->verificarEIncrementar($emisor);

        $establecimiento = auth()->user()->establecimientosActivos()->first();
        abort_unless($establecimiento, 403, 'No tiene un establecimiento asignado.');
        $ptoEmision = $establecimiento->ptoEmisiones->first();
        abort_unless($ptoEmision, 403, 'No tiene un punto de emisión asignado.');

        $data = [
            'cliente_id' => $proforma->cliente_id,
            'fecha_emision' => now()->toDateString(),
            'forma_pago' => $proforma->forma_pago ?? '01',
            'establecimiento_id' => $establecimiento->id,
            'pto_emision_id' => $ptoEmision->id,
            'observaciones' => $proforma->observaciones,
            'detalles' => $proforma->detalles->map(fn ($d) => [
                'codigo_principal' => $d->codigo_principal,
                'descripcion' => $d->descripcion,
                'cantidad' => $d->cantidad,
                'precio_unitario' => $d->precio_unitario,
                'descuento' => $d->descuento ?? 0,
                'impuesto_iva_id' => $d->impuestos->where('codigo', '2')->first()?->codigo_porcentaje
                    ? ImpuestoIva::where('codigo_porcentaje', $d->impuestos->where('codigo', '2')->first()->codigo_porcentaje)->first()?->id
                    : ImpuestoIva::first()?->id,
            ])->toArray(),
        ];

        try {
            // Validar con SRI
            app(ValidacionSriService::class)->validarFactura($emisor, $proforma->cliente, $data);

            // Crear factura
            $factura = app(FacturaService::class)->crear($emisor, $data);

            // Marcar proforma como facturada
            $proforma->update(['estado' => 'FACTURADA']);

            return redirect()->route('emisor.comprobantes.facturas.show', $factura)
                ->with('success', 'Proforma #' . $proforma->id . ' convertida a factura exitosamente.');
        } catch (\Exception $e) {
            return redirect()->route('emisor.comprobantes.proformas.show', $proforma)
                ->with('error', 'Error al convertir a factura: ' . $e->getMessage());
        }
    }

    private function autorizarAcceso(Proforma $proforma): void
    {
        $user = auth()->user();
        if (!$user->esAdmin() && $proforma->emisor_id !== $user->emisor_id) {
            abort(403);
        }
    }
}
