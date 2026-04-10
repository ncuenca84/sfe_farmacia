<?php

namespace App\Http\Controllers\Emisor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReporteFarmaciaController extends Controller
{
    public function rotacion(Request $request): View
    {
        $user = auth()->user();
        $emisorId = $user->emisor_id;
        $desde = $request->get('desde', now()->subMonths(3)->format('Y-m-d'));
        $hasta = $request->get('hasta', now()->format('Y-m-d'));

        $productos = DB::table('factura_detalles')
            ->join('facturas', 'factura_detalles.factura_id', '=', 'facturas.id')
            ->join('productos', function ($join) {
                $join->on('factura_detalles.codigo_principal', '=', 'productos.codigo_principal')
                    ->on('facturas.emisor_id', '=', 'productos.emisor_id');
            })
            ->where('facturas.emisor_id', $emisorId)
            ->where('facturas.estado', 'AUTORIZADO')
            ->whereBetween('facturas.fecha_emision', [$desde, $hasta])
            ->select(
                'productos.id',
                'productos.codigo_principal',
                'productos.nombre',
                'productos.principio_activo',
                DB::raw('SUM(factura_detalles.cantidad) as total_vendido'),
                DB::raw('COUNT(DISTINCT facturas.id) as num_facturas'),
                DB::raw('SUM(factura_detalles.precio_total_sin_impuesto) as total_ventas')
            )
            ->groupBy('productos.id', 'productos.codigo_principal', 'productos.nombre', 'productos.principio_activo')
            ->orderByDesc('total_vendido')
            ->limit(50)
            ->get();

        return view('emisor.farmacia.reportes.rotacion', compact('productos', 'desde', 'hasta'));
    }

    public function rentabilidad(Request $request): View
    {
        $user = auth()->user();
        $emisorId = $user->emisor_id;
        $desde = $request->get('desde', now()->subMonths(3)->format('Y-m-d'));
        $hasta = $request->get('hasta', now()->format('Y-m-d'));

        $categorias = DB::table('factura_detalles')
            ->join('facturas', 'factura_detalles.factura_id', '=', 'facturas.id')
            ->join('productos', function ($join) {
                $join->on('factura_detalles.codigo_principal', '=', 'productos.codigo_principal')
                    ->on('facturas.emisor_id', '=', 'productos.emisor_id');
            })
            ->leftJoin('categorias_producto', 'productos.categoria_producto_id', '=', 'categorias_producto.id')
            ->where('facturas.emisor_id', $emisorId)
            ->where('facturas.estado', 'AUTORIZADO')
            ->whereBetween('facturas.fecha_emision', [$desde, $hasta])
            ->select(
                DB::raw("COALESCE(categorias_producto.nombre, 'Sin Categoria') as categoria"),
                DB::raw('COUNT(DISTINCT productos.id) as num_productos'),
                DB::raw('SUM(factura_detalles.cantidad) as total_vendido'),
                DB::raw('SUM(factura_detalles.precio_total_sin_impuesto) as total_ventas')
            )
            ->groupBy('categorias_producto.id', 'categorias_producto.nombre')
            ->orderByDesc('total_ventas')
            ->get();

        return view('emisor.farmacia.reportes.rentabilidad', compact('categorias', 'desde', 'hasta'));
    }

    public function vencidosProveedor(Request $request): View
    {
        $user = auth()->user();

        $productos = DB::table('productos')
            ->leftJoin('proveedores', 'productos.proveedor_id', '=', 'proveedores.id')
            ->where('productos.emisor_id', $user->emisor_id)
            ->where('productos.activo', true)
            ->whereNotNull('productos.fecha_vencimiento')
            ->where('productos.fecha_vencimiento', '<', now())
            ->select(
                DB::raw("COALESCE(proveedores.nombre, 'Sin Proveedor') as proveedor"),
                'productos.nombre',
                'productos.numero_lote',
                'productos.fecha_vencimiento',
                'productos.precio_unitario'
            )
            ->orderBy('proveedores.nombre')
            ->orderBy('productos.fecha_vencimiento')
            ->get();

        $porProveedor = $productos->groupBy('proveedor');

        return view('emisor.farmacia.reportes.vencidos-proveedor', compact('porProveedor'));
    }
}
