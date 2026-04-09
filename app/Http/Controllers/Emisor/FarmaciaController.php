<?php

namespace App\Http\Controllers\Emisor;

use App\Http\Controllers\Controller;
use App\Models\CategoriaProducto;
use App\Models\Producto;
use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class FarmaciaController extends Controller
{
    public function dashboard(): View
    {
        $user = auth()->user();
        $emisorId = $user->emisor_id;

        $queryProductos = $this->queryProductos($user);

        // KPIs
        $totalProductos = (clone $queryProductos)->where('activo', true)->count();
        $totalProveedores = Proveedor::where('emisor_id', $emisorId)->where('activo', true)->count();
        $totalCategorias = CategoriaProducto::where('emisor_id', $emisorId)->where('activo', true)->count();

        $productosVencidos = (clone $queryProductos)
            ->where('activo', true)
            ->whereNotNull('fecha_vencimiento')
            ->where('fecha_vencimiento', '<', now())
            ->count();

        $proximosVencer = (clone $queryProductos)
            ->where('activo', true)
            ->whereNotNull('fecha_vencimiento')
            ->where('fecha_vencimiento', '>=', now())
            ->where('fecha_vencimiento', '<=', now()->addDays(30))
            ->count();

        // Stock bajo (productos con inventario bajo mínimo)
        $stockBajo = DB::table('inventarios')
            ->where('emisor_id', $emisorId)
            ->whereColumn('stock_actual', '<=', 'stock_minimo')
            ->where('stock_minimo', '>', 0)
            ->count();

        // Productos por categoría
        $porCategoria = (clone $queryProductos)
            ->where('productos.activo', true)
            ->whereNotNull('categoria_producto_id')
            ->join('categorias_producto', 'productos.categoria_producto_id', '=', 'categorias_producto.id')
            ->select('categorias_producto.nombre', DB::raw('COUNT(*) as total'))
            ->groupBy('categorias_producto.id', 'categorias_producto.nombre')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // Últimos productos por vencer (próximos 60 días)
        $proximosVencerLista = (clone $queryProductos)
            ->where('activo', true)
            ->whereNotNull('fecha_vencimiento')
            ->where('fecha_vencimiento', '>=', now())
            ->where('fecha_vencimiento', '<=', now()->addDays(60))
            ->orderBy('fecha_vencimiento')
            ->limit(10)
            ->get();

        // Productos ya vencidos
        $vencidosLista = (clone $queryProductos)
            ->where('activo', true)
            ->whereNotNull('fecha_vencimiento')
            ->where('fecha_vencimiento', '<', now())
            ->orderByDesc('fecha_vencimiento')
            ->limit(10)
            ->get();

        return view('emisor.farmacia.dashboard', compact(
            'totalProductos', 'totalProveedores', 'totalCategorias',
            'productosVencidos', 'proximosVencer', 'stockBajo',
            'porCategoria', 'proximosVencerLista', 'vencidosLista'
        ));
    }

    public function vencidos(Request $request): View
    {
        $user = auth()->user();
        $query = $this->queryProductos($user)
            ->where('activo', true)
            ->whereNotNull('fecha_vencimiento');

        $filtro = $request->get('filtro', 'vencidos');

        if ($filtro === 'vencidos') {
            $query->where('fecha_vencimiento', '<', now());
        } elseif ($filtro === 'proximos') {
            $dias = (int) $request->get('dias', 30);
            $query->where('fecha_vencimiento', '>=', now())
                ->where('fecha_vencimiento', '<=', now()->addDays($dias));
        } else {
            // todos con fecha de vencimiento
        }

        if ($request->filled('buscar')) {
            $query->where(function ($q) use ($request) {
                $q->where('nombre', 'like', "%{$request->buscar}%")
                    ->orWhere('codigo_principal', 'like', "%{$request->buscar}%")
                    ->orWhere('numero_lote', 'like', "%{$request->buscar}%");
            });
        }

        $productos = $query->with(['categoriaProducto', 'proveedor'])
            ->orderBy('fecha_vencimiento')
            ->paginate(50);

        return view('emisor.farmacia.vencidos', compact('productos', 'filtro'));
    }

    private function queryProductos($user)
    {
        if ($user->unidad_negocio_id) {
            return Producto::where('unidad_negocio_id', $user->unidad_negocio_id);
        }

        return Producto::where('emisor_id', $user->emisor_id);
    }
}
