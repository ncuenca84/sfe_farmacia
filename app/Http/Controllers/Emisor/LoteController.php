<?php

namespace App\Http\Controllers\Emisor;

use App\Http\Controllers\Controller;
use App\Models\Lote;
use App\Models\Producto;
use App\Services\LoteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LoteController extends Controller
{
    public function __construct(
        private LoteService $loteService,
    ) {}

    public function index(Request $request): View
    {
        $user = auth()->user();
        $emisorId = $user->emisor_id;
        $establecimientoIds = $user->establecimientosActivos()
            ->where('maneja_inventario', true)->pluck('id');

        $query = Lote::where('emisor_id', $emisorId)
            ->whereIn('establecimiento_id', $establecimientoIds)
            ->with(['producto', 'establecimiento']);

        if ($request->filled('buscar')) {
            $query->where(function ($q) use ($request) {
                $q->where('numero_lote', 'like', "%{$request->buscar}%")
                    ->orWhereHas('producto', function ($pq) use ($request) {
                        $pq->where('nombre', 'like', "%{$request->buscar}%")
                            ->orWhere('principio_activo', 'like', "%{$request->buscar}%");
                    });
            });
        }

        $filtro = $request->get('filtro', 'con_stock');
        if ($filtro === 'con_stock') {
            $query->where('cantidad_actual', '>', 0)->where('activo', true);
        } elseif ($filtro === 'vencidos') {
            $query->where('activo', true)
                ->whereNotNull('fecha_vencimiento')
                ->where('fecha_vencimiento', '<', now());
        } elseif ($filtro === 'agotados') {
            $query->where('cantidad_actual', '<=', 0);
        }

        $lotes = $query->orderByRaw('fecha_vencimiento IS NULL ASC')
            ->orderBy('fecha_vencimiento')
            ->paginate(50);

        return view('emisor.farmacia.lotes.index', compact('lotes', 'filtro'));
    }

    public function ingreso(): View
    {
        $user = auth()->user();
        $establecimientos = $user->establecimientosActivos()
            ->where('maneja_inventario', true);
        $productos = Producto::where('emisor_id', $user->emisor_id)
            ->where('activo', true)->orderBy('nombre')->get();

        return view('emisor.farmacia.lotes.ingreso', compact('establecimientos', 'productos'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'establecimiento_id' => 'required|exists:establecimientos,id',
            'numero_lote' => 'required|string|max:100',
            'fecha_vencimiento' => 'nullable|date',
            'cantidad' => 'required|numeric|min:0.0001',
            'costo_unitario' => 'nullable|numeric|min:0',
            'fecha_ingreso' => 'nullable|date',
            'nota' => 'nullable|string|max:300',
        ]);

        $this->loteService->ingresarLote(
            emisorId: $user->emisor_id,
            productoId: $validated['producto_id'],
            establecimientoId: $validated['establecimiento_id'],
            numeroLote: $validated['numero_lote'],
            cantidad: (float) $validated['cantidad'],
            costoUnitario: (float) ($validated['costo_unitario'] ?? 0),
            fechaVencimiento: $validated['fecha_vencimiento'] ?? null,
            fechaIngreso: $validated['fecha_ingreso'] ?? null,
            nota: $validated['nota'] ?? null,
        );

        return redirect()->route('emisor.farmacia.lotes.index')
            ->with('success', 'Lote ingresado correctamente.');
    }

    public function kardex(Lote $lote): View
    {
        $user = auth()->user();
        if (!$user->esAdmin() && $lote->emisor_id !== $user->emisor_id) {
            abort(403);
        }

        $lote->load(['producto', 'establecimiento']);
        $movimientos = $lote->movimientos()
            ->with('user')
            ->orderByDesc('created_at')
            ->paginate(50);

        return view('emisor.farmacia.lotes.kardex', compact('lote', 'movimientos'));
    }

    public function ajuste(Lote $lote): View
    {
        $user = auth()->user();
        if (!$user->esAdmin() && $lote->emisor_id !== $user->emisor_id) {
            abort(403);
        }

        $lote->load(['producto', 'establecimiento']);

        return view('emisor.farmacia.lotes.ajuste', compact('lote'));
    }

    public function guardarAjuste(Request $request, Lote $lote): RedirectResponse
    {
        $user = auth()->user();
        if (!$user->esAdmin() && $lote->emisor_id !== $user->emisor_id) {
            abort(403);
        }

        $validated = $request->validate([
            'cantidad_actual' => 'required|numeric|min:0',
            'descripcion' => 'nullable|string|max:300',
        ]);

        $this->loteService->ajustarLote(
            lote: $lote,
            nuevaCantidad: (float) $validated['cantidad_actual'],
            descripcion: $validated['descripcion'],
        );

        return redirect()->route('emisor.farmacia.lotes.index')
            ->with('success', 'Lote ajustado correctamente.');
    }
}
