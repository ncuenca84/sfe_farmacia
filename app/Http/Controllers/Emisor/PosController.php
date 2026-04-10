<?php

namespace App\Http\Controllers\Emisor;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\ImpuestoIva;
use App\Models\Producto;
use App\Services\FacturaService;
use App\Services\SriService;
use App\Services\SuscripcionService;
use App\Services\ValidacionSriService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PosController extends Controller
{
    public function __construct(
        private SuscripcionService $suscripcionService,
        private FacturaService $facturaService,
    ) {}

    public function index(): View
    {
        $user = auth()->user();
        $emisor = $user->emisor;
        $ivas = ImpuestoIva::activos()->get();

        // Consumidor final por defecto
        $consumidorFinal = Cliente::where('emisor_id', $emisor->id)
            ->where('identificacion', '9999999999999')
            ->first();

        return view('emisor.farmacia.pos', compact('ivas', 'consumidorFinal'));
    }

    public function buscarProducto(Request $request): JsonResponse
    {
        $user = auth()->user();
        $query = Producto::where('emisor_id', $user->emisor_id)
            ->where('activo', true);

        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('nombre', 'like', "%{$request->q}%")
                    ->orWhere('codigo_principal', 'like', "%{$request->q}%")
                    ->orWhere('principio_activo', 'like', "%{$request->q}%");
            });
        }

        $productos = $query->with(['presentacion', 'impuestoIva'])
            ->orderBy('nombre')
            ->limit(20)
            ->get(['id', 'codigo_principal', 'nombre', 'principio_activo', 'concentracion',
                    'presentacion_id', 'precio_unitario', 'impuesto_iva_id', 'tipo_venta']);

        return response()->json($productos);
    }

    public function facturar(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $emisor = $user->emisor;

        $this->suscripcionService->verificarEIncrementar($emisor);

        $validated = $request->validate([
            'cliente_id' => "required|exists:clientes,id,emisor_id,{$emisor->id}",
            'forma_pago' => 'required|string|max:2',
            'detalles' => 'required|array|min:1',
            'detalles.*.codigo_principal' => 'nullable|string|max:50',
            'detalles.*.descripcion' => 'required|string|max:300',
            'detalles.*.cantidad' => 'required|numeric|min:0.0001',
            'detalles.*.precio_unitario' => 'required|numeric|min:0',
            'detalles.*.descuento' => 'nullable|numeric|min:0',
            'detalles.*.impuesto_iva_id' => 'required|exists:impuesto_ivas,id',
        ]);

        $establecimiento = $user->establecimientosActivos()->first();
        abort_unless($establecimiento, 403, 'No tiene un establecimiento asignado.');
        $ptoEmision = $establecimiento->ptoEmisiones->first();
        abort_unless($ptoEmision, 403, 'No tiene un punto de emisión asignado.');

        $validated['establecimiento_id'] = $establecimiento->id;
        $validated['pto_emision_id'] = $ptoEmision->id;
        $validated['fecha_emision'] = now()->toDateString();

        $cliente = Cliente::findOrFail($validated['cliente_id']);
        app(ValidacionSriService::class)->validarFactura($emisor, $cliente, $validated);

        $factura = $this->facturaService->crear($emisor, $validated);

        // Auto-procesar con SRI
        try {
            app(SriService::class)->procesarFactura($factura);
            $factura->refresh();
        } catch (\Throwable $e) {
            // Si falla el SRI, la factura queda en CREADA para reintento manual
        }

        if ($request->boolean('imprimir_ticket')) {
            return redirect()->route('emisor.comprobantes.facturas.pdf-pos', $factura);
        }

        return redirect()->route('emisor.farmacia.pos')
            ->with('success', "Factura {$factura->numero_completo} creada. Estado: {$factura->estado}");
    }
}
