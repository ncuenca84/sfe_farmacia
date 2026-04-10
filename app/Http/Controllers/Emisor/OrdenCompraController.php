<?php

namespace App\Http\Controllers\Emisor;

use App\Http\Controllers\Controller;
use App\Models\OrdenCompra;
use App\Models\OrdenCompraItem;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Services\LoteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrdenCompraController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();
        $query = OrdenCompra::where('emisor_id', $user->emisor_id)
            ->with(['proveedor', 'establecimiento']);

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }
        if ($request->filled('buscar')) {
            $query->where(function ($q) use ($request) {
                $q->where('numero', 'like', "%{$request->buscar}%")
                    ->orWhereHas('proveedor', fn ($p) => $p->where('nombre', 'like', "%{$request->buscar}%"));
            });
        }

        $ordenes = $query->orderByDesc('fecha')->paginate(50);

        return view('emisor.farmacia.compras.index', compact('ordenes'));
    }

    public function create(): View
    {
        $user = auth()->user();
        $proveedores = Proveedor::where('emisor_id', $user->emisor_id)->where('activo', true)->orderBy('nombre')->get();
        $establecimientos = $user->establecimientosActivos()->where('maneja_inventario', true);
        $productos = Producto::where('emisor_id', $user->emisor_id)->where('activo', true)->orderBy('nombre')->get();

        return view('emisor.farmacia.compras.create', compact('proveedores', 'establecimientos', 'productos'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'proveedor_id' => 'required|exists:proveedores,id',
            'establecimiento_id' => 'required|exists:establecimientos,id',
            'numero' => 'nullable|string|max:30',
            'fecha' => 'required|date',
            'observaciones' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.producto_id' => 'required|exists:productos,id',
            'items.*.cantidad_pedida' => 'required|numeric|min:0.0001',
            'items.*.costo_unitario' => 'nullable|numeric|min:0',
            'items.*.numero_lote' => 'nullable|string|max:100',
            'items.*.fecha_vencimiento' => 'nullable|date',
        ]);

        $orden = DB::transaction(function () use ($validated, $user) {
            $total = collect($validated['items'])->sum(fn ($i) => $i['cantidad_pedida'] * ($i['costo_unitario'] ?? 0));

            $orden = OrdenCompra::create([
                'emisor_id' => $user->emisor_id,
                'proveedor_id' => $validated['proveedor_id'],
                'establecimiento_id' => $validated['establecimiento_id'],
                'numero' => $validated['numero'],
                'fecha' => $validated['fecha'],
                'total' => $total,
                'observaciones' => $validated['observaciones'] ?? null,
                'user_id' => auth()->id(),
            ]);

            foreach ($validated['items'] as $item) {
                OrdenCompraItem::create([
                    'orden_compra_id' => $orden->id,
                    'producto_id' => $item['producto_id'],
                    'cantidad_pedida' => $item['cantidad_pedida'],
                    'costo_unitario' => $item['costo_unitario'] ?? 0,
                    'numero_lote' => $item['numero_lote'] ?? null,
                    'fecha_vencimiento' => $item['fecha_vencimiento'] ?? null,
                ]);
            }

            return $orden;
        });

        return redirect()->route('emisor.farmacia.compras.show', $orden)
            ->with('success', 'Orden de compra creada.');
    }

    public function show(OrdenCompra $compra): View
    {
        $this->autorizarAcceso($compra);
        $compra->load(['proveedor', 'establecimiento', 'items.producto', 'user']);

        return view('emisor.farmacia.compras.show', compact('compra'));
    }

    public function recibir(Request $request, OrdenCompra $compra): RedirectResponse
    {
        $this->autorizarAcceso($compra);
        abort_unless(in_array($compra->estado, ['PENDIENTE', 'PARCIAL']), 403);

        $validated = $request->validate([
            'cantidades' => 'required|array',
            'cantidades.*' => 'nullable|numeric|min:0',
            'lotes' => 'nullable|array',
            'lotes.*' => 'nullable|string|max:100',
            'vencimientos' => 'nullable|array',
            'vencimientos.*' => 'nullable|date',
        ]);

        $loteService = app(LoteService::class);

        DB::transaction(function () use ($compra, $validated, $loteService) {
            $compra->load('items.producto');

            foreach ($compra->items as $item) {
                $cantidadRecibir = (float) ($validated['cantidades'][$item->id] ?? 0);
                if ($cantidadRecibir <= 0) continue;

                $pendiente = $item->pendiente();
                $cantidadRecibir = min($cantidadRecibir, $pendiente);

                $item->cantidad_recibida = (float) $item->cantidad_recibida + $cantidadRecibir;
                $item->save();

                // Crear lote automáticamente
                $numLote = $validated['lotes'][$item->id] ?? $item->numero_lote ?? 'SN-' . now()->format('Ymd');
                $fechaVenc = $validated['vencimientos'][$item->id] ?? $item->fecha_vencimiento?->format('Y-m-d');

                $loteService->ingresarLote(
                    emisorId: $compra->emisor_id,
                    productoId: $item->producto_id,
                    establecimientoId: $compra->establecimiento_id,
                    numeroLote: $numLote,
                    cantidad: $cantidadRecibir,
                    costoUnitario: (float) $item->costo_unitario,
                    fechaVencimiento: $fechaVenc,
                    nota: "OC #{$compra->numero} - {$compra->proveedor->nombre}",
                );
            }

            // Actualizar estado
            $compra->refresh();
            $compra->load('items');
            if ($compra->estaCompleta()) {
                $compra->update(['estado' => 'RECIBIDA']);
            } else {
                $compra->update(['estado' => 'PARCIAL']);
            }
        });

        return redirect()->route('emisor.farmacia.compras.show', $compra)
            ->with('success', 'Recepcion registrada. Lotes creados automaticamente.');
    }

    public function reposicion(): View
    {
        $user = auth()->user();
        $establecimientoIds = $user->establecimientosActivos()
            ->where('maneja_inventario', true)->pluck('id');

        $productos = DB::table('inventarios')
            ->join('productos', 'inventarios.producto_id', '=', 'productos.id')
            ->join('establecimientos', 'inventarios.establecimiento_id', '=', 'establecimientos.id')
            ->where('inventarios.emisor_id', $user->emisor_id)
            ->whereIn('inventarios.establecimiento_id', $establecimientoIds)
            ->where('inventarios.stock_minimo', '>', 0)
            ->whereColumn('inventarios.stock_actual', '<=', 'inventarios.stock_minimo')
            ->select(
                'productos.id as producto_id',
                'productos.codigo_principal',
                'productos.nombre',
                'productos.principio_activo',
                'establecimientos.nombre as establecimiento',
                'inventarios.stock_actual',
                'inventarios.stock_minimo',
                DB::raw('(inventarios.stock_minimo - inventarios.stock_actual) as cantidad_sugerida')
            )
            ->orderByDesc('cantidad_sugerida')
            ->get();

        return view('emisor.farmacia.compras.reposicion', compact('productos'));
    }

    private function autorizarAcceso(OrdenCompra $compra): void
    {
        $user = auth()->user();
        if (!$user->esAdmin() && $compra->emisor_id !== $user->emisor_id) {
            abort(403);
        }
    }
}
