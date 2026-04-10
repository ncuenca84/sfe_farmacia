<?php

namespace App\Http\Controllers\Emisor;

use App\Http\Controllers\Controller;
use App\Models\CategoriaProducto;
use App\Models\Inventario;
use App\Models\Laboratorio;
use App\Models\Presentacion;
use App\Models\Producto;
use App\Models\Proveedor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductoController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();

        $query = $this->queryProductos($user);

        if ($request->filled('buscar')) {
            $query->where(function ($q) use ($request) {
                $q->where('nombre', 'like', "%{$request->buscar}%")
                    ->orWhere('codigo_principal', 'like', "%{$request->buscar}%")
                    ->orWhere('principio_activo', 'like', "%{$request->buscar}%");
            });
        }

        $productos = $query->with(['categoriaProducto', 'proveedor', 'presentacion', 'laboratorio'])->orderBy('nombre')->paginate(50);

        return view('emisor.productos.index', compact('productos'));
    }

    public function create(): View
    {
        $user = auth()->user();
        $manejaInventario = $user->establecimientosActivos()->contains('maneja_inventario', true);
        $categorias = CategoriaProducto::where('emisor_id', $user->emisor_id)->where('activo', true)->orderBy('nombre')->get();
        $proveedores = Proveedor::where('emisor_id', $user->emisor_id)->where('activo', true)->orderBy('nombre')->get();
        $presentaciones = Presentacion::where('emisor_id', $user->emisor_id)->where('activo', true)->orderBy('nombre')->get();
        $laboratorios = Laboratorio::where('emisor_id', $user->emisor_id)->where('activo', true)->orderBy('nombre')->get();

        return view('emisor.productos.create', compact('manejaInventario', 'categorias', 'proveedores', 'presentaciones', 'laboratorios'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'codigo_principal' => 'nullable|string|max:50',
            'codigo_auxiliar' => 'nullable|string|max:50',
            'nombre' => 'required|string|max:300',
            'descripcion' => 'nullable|string',
            'principio_activo' => 'nullable|string|max:300',
            'concentracion' => 'nullable|string|max:100',
            'presentacion_id' => 'nullable|exists:presentaciones,id',
            'laboratorio_id' => 'nullable|exists:laboratorios,id',
            'tipo_venta' => 'nullable|in:venta_libre,requiere_receta,controlado',
            'registro_sanitario' => 'nullable|string|max:50',
            'categoria_producto_id' => 'nullable|exists:categorias_producto,id',
            'proveedor_id' => 'nullable|exists:proveedores,id',
            'numero_lote' => 'nullable|string|max:100',
            'fecha_vencimiento' => 'nullable|date',
            'imagen' => 'nullable|image|max:2048',
            'precio_unitario' => 'required|numeric|min:0',
            'impuesto_iva_id' => 'required|exists:impuesto_ivas,id',
            'stock_inicial' => 'nullable|numeric|min:0',
            'stock_minimo' => 'nullable|numeric|min:0',
        ]);

        $stockInicial = (float) ($validated['stock_inicial'] ?? 0);
        $stockMinimo = (float) ($validated['stock_minimo'] ?? 0);
        unset($validated['stock_inicial'], $validated['stock_minimo']);

        if ($request->hasFile('imagen')) {
            $validated['imagen'] = $request->file('imagen')->store('productos', 'public');
        }
        unset($validated['imagen_file']);

        $validated['emisor_id'] = $user->emisor_id;

        if ($user->unidad_negocio_id) {
            $validated['unidad_negocio_id'] = $user->unidad_negocio_id;
        }

        $producto = Producto::create($validated);

        // Auto-crear inventario en establecimientos que manejan inventario (misma linea de negocio)
        $establecimientos = $user->establecimientosActivos()->where('maneja_inventario', true)
            ->filter(fn ($est) => $est->unidad_negocio_id === $producto->unidad_negocio_id);
        foreach ($establecimientos as $est) {
            Inventario::firstOrCreate([
                'producto_id' => $producto->id,
                'establecimiento_id' => $est->id,
            ], [
                'emisor_id' => $user->emisor_id,
                'stock_actual' => $stockInicial,
                'stock_minimo' => $stockMinimo,
                'costo_promedio' => 0,
            ]);
        }

        return redirect()->route('emisor.configuracion.productos.index')
            ->with('success', 'Producto creado correctamente.');
    }

    public function show(Producto $producto): View
    {
        $this->autorizarAcceso($producto);

        return view('emisor.productos.show', compact('producto'));
    }

    public function edit(Producto $producto): View
    {
        $this->autorizarAcceso($producto);

        $user = auth()->user();
        $categorias = CategoriaProducto::where('emisor_id', $user->emisor_id)->where('activo', true)->orderBy('nombre')->get();
        $proveedores = Proveedor::where('emisor_id', $user->emisor_id)->where('activo', true)->orderBy('nombre')->get();
        $presentaciones = Presentacion::where('emisor_id', $user->emisor_id)->where('activo', true)->orderBy('nombre')->get();
        $laboratorios = Laboratorio::where('emisor_id', $user->emisor_id)->where('activo', true)->orderBy('nombre')->get();
        $establecimientosInventario = $user->establecimientosActivos()->where('maneja_inventario', true)
            ->filter(fn ($est) => $est->unidad_negocio_id === $producto->unidad_negocio_id);
        $manejaInventario = $establecimientosInventario->isNotEmpty();

        // Auto-crear registros de inventario faltantes para productos existentes (misma linea de negocio)
        if ($manejaInventario) {
            foreach ($establecimientosInventario as $est) {
                Inventario::firstOrCreate([
                    'producto_id' => $producto->id,
                    'establecimiento_id' => $est->id,
                ], [
                    'emisor_id' => $user->emisor_id,
                    'stock_actual' => 0,
                    'stock_minimo' => 0,
                    'costo_promedio' => 0,
                ]);
            }
        }

        $establecimientoIds = $establecimientosInventario->pluck('id');
        $inventarios = $manejaInventario
            ? $producto->inventarios()->with('establecimiento')
                ->whereIn('establecimiento_id', $establecimientoIds)->get()
            : collect();

        return view('emisor.productos.edit', compact('producto', 'manejaInventario', 'inventarios', 'categorias', 'proveedores', 'presentaciones', 'laboratorios'));
    }

    public function update(Request $request, Producto $producto): RedirectResponse
    {
        $this->autorizarAcceso($producto);

        $validated = $request->validate([
            'codigo_principal' => 'nullable|string|max:50',
            'codigo_auxiliar' => 'nullable|string|max:50',
            'nombre' => 'required|string|max:300',
            'descripcion' => 'nullable|string',
            'principio_activo' => 'nullable|string|max:300',
            'concentracion' => 'nullable|string|max:100',
            'presentacion_id' => 'nullable|exists:presentaciones,id',
            'laboratorio_id' => 'nullable|exists:laboratorios,id',
            'tipo_venta' => 'nullable|in:venta_libre,requiere_receta,controlado',
            'registro_sanitario' => 'nullable|string|max:50',
            'categoria_producto_id' => 'nullable|exists:categorias_producto,id',
            'proveedor_id' => 'nullable|exists:proveedores,id',
            'numero_lote' => 'nullable|string|max:100',
            'fecha_vencimiento' => 'nullable|date',
            'imagen' => 'nullable|image|max:2048',
            'precio_unitario' => 'required|numeric|min:0',
            'impuesto_iva_id' => 'required|exists:impuesto_ivas,id',
            'stock_actual' => 'nullable|array',
            'stock_actual.*' => 'nullable|numeric|min:0',
            'stock_minimo' => 'nullable|array',
            'stock_minimo.*' => 'nullable|numeric|min:0',
        ]);

        if ($request->hasFile('imagen')) {
            if ($producto->imagen) {
                Storage::disk('public')->delete($producto->imagen);
            }
            $validated['imagen'] = $request->file('imagen')->store('productos', 'public');
        }

        $producto->update(collect($validated)->except(['stock_actual', 'stock_minimo', 'imagen_file'])->toArray());

        if ($request->has('stock_actual') || $request->has('stock_minimo')) {
            $stockActual = $request->stock_actual ?? [];
            $stockMinimo = $request->stock_minimo ?? [];
            $ids = array_unique(array_merge(array_keys($stockActual), array_keys($stockMinimo)));

            foreach ($ids as $inventarioId) {
                $data = [];
                if (isset($stockActual[$inventarioId])) {
                    $data['stock_actual'] = (float) $stockActual[$inventarioId];
                }
                if (isset($stockMinimo[$inventarioId])) {
                    $data['stock_minimo'] = (float) $stockMinimo[$inventarioId];
                }
                if ($data) {
                    Inventario::where('id', $inventarioId)
                        ->where('producto_id', $producto->id)
                        ->update($data);
                }
            }
        }

        return redirect()->route('emisor.configuracion.productos.index')
            ->with('success', 'Producto actualizado correctamente.');
    }

    public function destroy(Producto $producto): RedirectResponse
    {
        $this->autorizarAcceso($producto);
        $producto->delete();

        return redirect()->route('emisor.configuracion.productos.index')
            ->with('success', 'Producto eliminado.');
    }

    public function buscar(Request $request): JsonResponse
    {
        $user = auth()->user();
        $perPage = (int) ($request->per_page ?? 10);

        $query = $this->queryProductos($user)->orderBy('nombre');

        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('nombre', 'like', "%{$request->q}%")
                    ->orWhere('codigo_principal', 'like', "%{$request->q}%")
                    ->orWhere('principio_activo', 'like', "%{$request->q}%");
            });
        }

        $productos = $query->paginate($perPage, ['id', 'codigo_principal', 'nombre', 'precio_unitario', 'impuesto_iva_id']);

        return response()->json($productos);
    }

    public function apiStore(Request $request): JsonResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'codigo_principal' => 'nullable|string|max:50',
            'nombre' => 'required|string|max:300',
            'precio_unitario' => 'required|numeric|min:0',
            'impuesto_iva_id' => 'required|exists:impuesto_ivas,id',
        ]);

        $validated['emisor_id'] = $user->emisor_id;

        if ($user->unidad_negocio_id) {
            $validated['unidad_negocio_id'] = $user->unidad_negocio_id;
        }

        $producto = Producto::create($validated);

        // Auto-crear inventario con stock=0 en establecimientos que manejan inventario
        $establecimientos = $user->establecimientosActivos()->where('maneja_inventario', true);
        foreach ($establecimientos as $est) {
            Inventario::firstOrCreate([
                'producto_id' => $producto->id,
                'establecimiento_id' => $est->id,
            ], [
                'emisor_id' => $user->emisor_id,
                'stock_actual' => 0,
                'stock_minimo' => 0,
                'costo_promedio' => 0,
            ]);
        }

        return response()->json($producto);
    }

    private function queryProductos($user)
    {
        if ($user->unidad_negocio_id) {
            return Producto::where('unidad_negocio_id', $user->unidad_negocio_id);
        }

        return Producto::where('emisor_id', $user->emisor_id);
    }

    private function autorizarAcceso(Producto $producto): void
    {
        $user = auth()->user();
        if (!$user->esAdmin() && $producto->emisor_id !== $user->emisor_id) {
            abort(403);
        }
    }
}
