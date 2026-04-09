<?php

namespace App\Http\Controllers\Emisor;

use App\Http\Controllers\Controller;
use App\Models\Factura;
use App\Models\Guia;
use App\Models\LiquidacionCompra;
use App\Models\NotaCredito;
use App\Models\NotaDebito;
use App\Models\Retencion;
use App\Models\RetencionImpuesto;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReporteController extends Controller
{
    // ─── Modelos por tipo de comprobante ───
    private const MODELOS = [
        'facturas' => Factura::class,
        'notas-credito' => NotaCredito::class,
        'notas-debito' => NotaDebito::class,
        'retenciones' => Retencion::class,
        'guias' => Guia::class,
        'liquidaciones' => LiquidacionCompra::class,
    ];

    // ══════════════════════════════════════════════════════════════
    //  1. COMPROBANTES
    // ══════════════════════════════════════════════════════════════

    public function comprobantes(Request $request): View
    {
        $emisor = auth()->user()->emisor;
        $tipo = $request->input('tipo', 'facturas');
        $modelClass = self::MODELOS[$tipo] ?? Factura::class;
        $esGuia = $tipo === 'guias';

        $query = $modelClass::where('emisor_id', $emisor->id)
            ->with($esGuia ? ['establecimiento', 'ptoEmision'] : ['cliente', 'establecimiento', 'ptoEmision']);

        $this->aplicarFiltrosComprobantes($query, $request, $esGuia);

        // Totales globales (no solo la página actual)
        // Guias y retenciones no tienen columna importe_total
        $sinTotales = in_array($tipo, ['guias', 'retenciones']);
        $totales = !$sinTotales ? (clone $query)->selectRaw('COUNT(*) as cantidad, COALESCE(SUM(importe_total), 0) as total')->first() : null;

        $comprobantes = $query->orderByDesc('fecha_emision')->paginate(50);

        return view('emisor.reportes.comprobantes', compact('comprobantes', 'tipo', 'totales'));
    }

    public function exportComprobantes(Request $request): BinaryFileResponse
    {
        $emisor = auth()->user()->emisor;
        $tipo = $request->input('tipo', 'facturas');
        $modelClass = self::MODELOS[$tipo] ?? Factura::class;
        $esGuia = $tipo === 'guias';
        $esRetencion = $tipo === 'retenciones';

        $withs = $esGuia ? ['establecimiento', 'ptoEmision'] : ['cliente', 'establecimiento', 'ptoEmision'];
        if ($esRetencion) {
            $withs[] = 'impuestosRetencion';
        }

        $query = $modelClass::where('emisor_id', $emisor->id)->with($withs);

        $this->aplicarFiltrosComprobantes($query, $request, $esGuia);
        $query->orderByDesc('fecha_emision');

        if ($esGuia) {
            return $this->generarExcel("comprobantes_{$tipo}", ['Fecha', 'Numero', 'Transportista', 'RUC Transportista', 'Estado'], $query, function ($comp) {
                return [
                    $comp->fecha_emision->format('d/m/Y'),
                    ($comp->establecimiento->codigo ?? '000') . '-' . ($comp->ptoEmision->codigo ?? '000') . '-' . str_pad($comp->secuencial, 9, '0', STR_PAD_LEFT),
                    $comp->razon_social_transportista ?? '-',
                    $comp->ruc_transportista ?? '-',
                    $comp->estado,
                ];
            });
        }

        if ($esRetencion) {
            return $this->generarExcel("comprobantes_{$tipo}", ['Fecha', 'Numero', 'Cliente', 'RUC/CI', 'Estado', 'Total Retenido'], $query, function ($comp) {
                return [
                    $comp->fecha_emision->format('d/m/Y'),
                    ($comp->establecimiento->codigo ?? '000') . '-' . ($comp->ptoEmision->codigo ?? '000') . '-' . str_pad($comp->secuencial, 9, '0', STR_PAD_LEFT),
                    $comp->cliente->razon_social ?? '-',
                    $comp->cliente->identificacion ?? '-',
                    $comp->estado,
                    (float) $comp->impuestosRetencion->sum('valor_retenido'),
                ];
            });
        }

        return $this->generarExcel("comprobantes_{$tipo}", ['Fecha', 'Numero', 'Cliente', 'RUC/CI', 'Estado', 'Total'], $query, function ($comp) {
            return [
                $comp->fecha_emision->format('d/m/Y'),
                ($comp->establecimiento->codigo ?? '000') . '-' . ($comp->ptoEmision->codigo ?? '000') . '-' . str_pad($comp->secuencial, 9, '0', STR_PAD_LEFT),
                $comp->cliente->razon_social ?? '-',
                $comp->cliente->identificacion ?? '-',
                $comp->estado,
                (float) ($comp->importe_total ?? 0),
            ];
        });
    }

    // ══════════════════════════════════════════════════════════════
    //  2. VENTAS
    // ══════════════════════════════════════════════════════════════

    public function ventas(Request $request): View
    {
        $emisor = auth()->user()->emisor;

        $query = Factura::where('emisor_id', $emisor->id)
            ->where('estado', 'AUTORIZADO')
            ->with(['cliente', 'establecimiento', 'ptoEmision']);

        $this->aplicarFiltroUnidadNegocio($query);
        $this->aplicarFiltrosFecha($query, $request);

        if ($request->filled('buscar')) {
            $this->aplicarFiltroBuscar($query, $request->buscar);
        }

        $totales = (clone $query)->selectRaw('COUNT(*) as cantidad, COALESCE(SUM(total_sin_impuestos), 0) as subtotal, COALESCE(SUM(total_iva), 0) as iva, COALESCE(SUM(importe_total), 0) as total')->first();

        $facturas = $query->orderByDesc('fecha_emision')->paginate(50);

        return view('emisor.reportes.ventas', compact('facturas', 'totales'));
    }

    public function exportVentas(Request $request): BinaryFileResponse
    {
        $emisor = auth()->user()->emisor;

        $query = Factura::where('emisor_id', $emisor->id)
            ->where('estado', 'AUTORIZADO')
            ->with(['cliente', 'establecimiento', 'ptoEmision']);

        $this->aplicarFiltroUnidadNegocio($query);
        $this->aplicarFiltrosFecha($query, $request);
        if ($request->filled('buscar')) {
            $this->aplicarFiltroBuscar($query, $request->buscar);
        }
        $query->orderByDesc('fecha_emision');

        return $this->generarExcel('ventas', ['Fecha', 'Numero', 'Cliente', 'RUC/CI', 'Subtotal', 'IVA', 'Total'], $query, function ($f) {
            return [
                $f->fecha_emision->format('d/m/Y'),
                ($f->establecimiento->codigo ?? '000') . '-' . ($f->ptoEmision->codigo ?? '000') . '-' . str_pad($f->secuencial, 9, '0', STR_PAD_LEFT),
                $f->cliente->razon_social ?? '-',
                $f->cliente->identificacion ?? '-',
                (float) ($f->total_sin_impuestos ?? 0),
                (float) ($f->total_iva ?? 0),
                (float) ($f->importe_total ?? 0),
            ];
        });
    }

    // ══════════════════════════════════════════════════════════════
    //  3. VENTAS DETALLADA
    // ══════════════════════════════════════════════════════════════

    public function ventasDetallada(Request $request): View
    {
        $emisor = auth()->user()->emisor;

        $query = Factura::where('emisor_id', $emisor->id)
            ->where('estado', 'AUTORIZADO')
            ->with(['detalles', 'cliente', 'establecimiento', 'ptoEmision']);

        $this->aplicarFiltroUnidadNegocio($query);
        $this->aplicarFiltrosFecha($query, $request);

        if ($request->filled('buscar')) {
            $this->aplicarFiltroBuscar($query, $request->buscar);
        }

        $totales = (clone $query)->selectRaw('COUNT(*) as cantidad, COALESCE(SUM(total_sin_impuestos), 0) as subtotal, COALESCE(SUM(total_iva), 0) as iva, COALESCE(SUM(importe_total), 0) as total')->first();

        $facturas = $query->orderByDesc('fecha_emision')->paginate(25);

        return view('emisor.reportes.ventas-detallada', compact('facturas', 'totales'));
    }

    public function exportVentasDetallada(Request $request): BinaryFileResponse
    {
        $emisor = auth()->user()->emisor;

        $query = Factura::where('emisor_id', $emisor->id)
            ->where('estado', 'AUTORIZADO')
            ->with(['detalles', 'cliente', 'establecimiento', 'ptoEmision']);

        $this->aplicarFiltroUnidadNegocio($query);
        $this->aplicarFiltrosFecha($query, $request);
        if ($request->filled('buscar')) {
            $this->aplicarFiltroBuscar($query, $request->buscar);
        }
        $query->orderByDesc('fecha_emision');

        return $this->generarExcelDetallada($query);
    }

    // ══════════════════════════════════════════════════════════════
    //  4. RETENCIONES TOTALIZADAS
    // ══════════════════════════════════════════════════════════════

    public function retencionesTotalizadas(Request $request): View
    {
        $emisor = auth()->user()->emisor;

        $query = Retencion::where('emisor_id', $emisor->id)
            ->where('estado', 'AUTORIZADO')
            ->with(['impuestosRetencion', 'cliente', 'establecimiento', 'ptoEmision']);

        $this->aplicarFiltroUnidadNegocio($query);
        $this->aplicarFiltrosFecha($query, $request);

        if ($request->filled('buscar')) {
            $this->aplicarFiltroBuscar($query, $request->buscar);
        }

        // Total global de valor retenido
        $retencionIds = (clone $query)->pluck('id');
        $totalRetenidoGlobal = RetencionImpuesto::whereIn('retencion_id', $retencionIds)->sum('valor_retenido');
        $totalComprobantes = $retencionIds->count();

        $retenciones = $query->orderByDesc('fecha_emision')->paginate(50);

        return view('emisor.reportes.retenciones-totalizadas', compact('retenciones', 'totalRetenidoGlobal', 'totalComprobantes'));
    }

    public function exportRetencionesTotalizadas(Request $request): BinaryFileResponse
    {
        $emisor = auth()->user()->emisor;

        $query = Retencion::where('emisor_id', $emisor->id)
            ->where('estado', 'AUTORIZADO')
            ->with(['impuestosRetencion', 'cliente', 'establecimiento', 'ptoEmision']);

        $this->aplicarFiltroUnidadNegocio($query);
        $this->aplicarFiltrosFecha($query, $request);
        if ($request->filled('buscar')) {
            $this->aplicarFiltroBuscar($query, $request->buscar);
        }
        $query->orderByDesc('fecha_emision');

        return $this->generarExcel('retenciones_totalizadas', ['Fecha', 'Numero', 'Sujeto', 'RUC/CI', 'Doc. Sustento', 'Total Retenido'], $query, function ($ret) {
            return [
                $ret->fecha_emision->format('d/m/Y'),
                ($ret->establecimiento->codigo ?? '000') . '-' . ($ret->ptoEmision->codigo ?? '000') . '-' . str_pad($ret->secuencial, 9, '0', STR_PAD_LEFT),
                $ret->cliente->razon_social ?? '-',
                $ret->cliente->identificacion ?? '-',
                $ret->num_doc_sustento ?? '-',
                (float) $ret->impuestosRetencion->sum('valor_retenido'),
            ];
        });
    }

    // ══════════════════════════════════════════════════════════════
    //  5. RETENCIONES POR FACTURA
    // ══════════════════════════════════════════════════════════════

    public function retencionesFactura(Request $request): View
    {
        $emisor = auth()->user()->emisor;

        $query = Retencion::where('emisor_id', $emisor->id)
            ->where('estado', 'AUTORIZADO')
            ->with(['impuestosRetencion', 'cliente', 'establecimiento', 'ptoEmision']);

        $this->aplicarFiltroUnidadNegocio($query);
        $this->aplicarFiltrosFecha($query, $request);

        if ($request->filled('factura')) {
            $query->where('num_doc_sustento', 'like', "%{$request->factura}%");
        }
        if ($request->filled('buscar')) {
            $this->aplicarFiltroBuscar($query, $request->buscar);
        }

        // Total global
        $retencionIds = (clone $query)->pluck('id');
        $totalRetenidoGlobal = RetencionImpuesto::whereIn('retencion_id', $retencionIds)->sum('valor_retenido');
        $totalComprobantes = $retencionIds->count();

        $retenciones = $query->orderByDesc('fecha_emision')->paginate(50);

        return view('emisor.reportes.retenciones-factura', compact('retenciones', 'totalRetenidoGlobal', 'totalComprobantes'));
    }

    public function exportRetencionesFactura(Request $request): BinaryFileResponse
    {
        $emisor = auth()->user()->emisor;

        $query = Retencion::where('emisor_id', $emisor->id)
            ->where('estado', 'AUTORIZADO')
            ->with(['impuestosRetencion', 'cliente', 'establecimiento', 'ptoEmision']);

        $this->aplicarFiltroUnidadNegocio($query);
        $this->aplicarFiltrosFecha($query, $request);
        if ($request->filled('factura')) {
            $query->where('num_doc_sustento', 'like', "%{$request->factura}%");
        }
        if ($request->filled('buscar')) {
            $this->aplicarFiltroBuscar($query, $request->buscar);
        }
        $query->orderByDesc('fecha_emision');

        return $this->generarExcelRetencionesDetalle($query);
    }

    // ══════════════════════════════════════════════════════════════
    //  HELPERS PRIVADOS
    // ══════════════════════════════════════════════════════════════

    private function aplicarFiltroUnidadNegocio(Builder $query): void
    {
        if (auth()->user()->unidad_negocio_id) {
            $query->whereHas('establecimiento', fn ($q) => $q->where('unidad_negocio_id', auth()->user()->unidad_negocio_id));
        }
    }

    private function aplicarFiltrosComprobantes(Builder $query, Request $request, bool $esGuia = false): void
    {
        $this->aplicarFiltroUnidadNegocio($query);
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }
        $this->aplicarFiltrosFecha($query, $request);
        if ($request->filled('buscar')) {
            if ($esGuia) {
                $buscar = $request->buscar;
                $query->where(function ($q) use ($buscar) {
                    $q->where('razon_social_transportista', 'like', "%{$buscar}%")
                      ->orWhere('ruc_transportista', 'like', "%{$buscar}%");
                });
            } else {
                $this->aplicarFiltroBuscar($query, $request->buscar);
            }
        }
    }

    private function aplicarFiltrosFecha(Builder $query, Request $request): void
    {
        if ($request->filled('desde')) {
            $query->where('fecha_emision', '>=', $request->desde);
        }
        if ($request->filled('hasta')) {
            $query->where('fecha_emision', '<=', $request->hasta);
        }
    }

    private function aplicarFiltroBuscar(Builder $query, string $buscar): void
    {
        $query->where(function ($q) use ($buscar) {
            $q->whereHas('cliente', fn ($c) => $c->where('razon_social', 'like', "%{$buscar}%")
                ->orWhere('identificacion', 'like', "%{$buscar}%"));
        });
    }

    // ─── Generador Excel genérico con OpenSpout ───

    private function generarExcel(string $nombre, array $headers, Builder $query, callable $rowMapper): BinaryFileResponse
    {
        $filename = "{$nombre}_" . now()->format('Ymd_His') . '.xlsx';
        $tempPath = storage_path('app/temp/' . $filename);

        if (!is_dir(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0755, true);
        }

        $writer = new Writer();
        $writer->openToFile($tempPath);
        $writer->addRow(Row::fromValues($headers));

        $query->chunk(500, function ($registros) use ($writer, $rowMapper) {
            foreach ($registros as $registro) {
                $writer->addRow(Row::fromValues($rowMapper($registro)));
            }
        });

        $writer->close();

        return response()->download($tempPath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    private function generarExcelDetallada(Builder $query): BinaryFileResponse
    {
        $filename = 'ventas_detallada_' . now()->format('Ymd_His') . '.xlsx';
        $tempPath = storage_path('app/temp/' . $filename);

        if (!is_dir(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0755, true);
        }

        $writer = new Writer();
        $writer->openToFile($tempPath);
        $writer->addRow(Row::fromValues([
            'Fecha', 'Numero', 'Cliente', 'RUC/CI',
            'Codigo', 'Descripcion', 'Cantidad', 'P. Unitario', 'Descuento', 'Subtotal',
            'IVA Factura', 'Total Factura',
        ]));

        $query->chunk(200, function ($facturas) use ($writer) {
            foreach ($facturas as $factura) {
                $numero = ($factura->establecimiento->codigo ?? '000') . '-' . ($factura->ptoEmision->codigo ?? '000') . '-' . str_pad($factura->secuencial, 9, '0', STR_PAD_LEFT);
                foreach ($factura->detalles as $det) {
                    $writer->addRow(Row::fromValues([
                        $factura->fecha_emision->format('d/m/Y'),
                        $numero,
                        $factura->cliente->razon_social ?? '-',
                        $factura->cliente->identificacion ?? '-',
                        $det->codigo_principal ?? '',
                        $det->descripcion,
                        (float) $det->cantidad,
                        (float) $det->precio_unitario,
                        (float) ($det->descuento ?? 0),
                        (float) ($det->precio_total_sin_impuesto ?? 0),
                        (float) ($factura->total_iva ?? 0),
                        (float) ($factura->importe_total ?? 0),
                    ]));
                }
            }
        });

        $writer->close();

        return response()->download($tempPath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    private function generarExcelRetencionesDetalle(Builder $query): BinaryFileResponse
    {
        $filename = 'retenciones_por_factura_' . now()->format('Ymd_His') . '.xlsx';
        $tempPath = storage_path('app/temp/' . $filename);

        if (!is_dir(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0755, true);
        }

        $writer = new Writer();
        $writer->openToFile($tempPath);
        $writer->addRow(Row::fromValues([
            'Fecha', 'Numero Retencion', 'Sujeto', 'RUC/CI',
            'Doc. Sustento', 'Cod. Impuesto', 'Cod. Retencion',
            'Base Imponible', '% Retencion', 'Valor Retenido',
        ]));

        $query->chunk(200, function ($retenciones) use ($writer) {
            foreach ($retenciones as $ret) {
                $numero = ($ret->establecimiento->codigo ?? '000') . '-' . ($ret->ptoEmision->codigo ?? '000') . '-' . str_pad($ret->secuencial, 9, '0', STR_PAD_LEFT);
                foreach ($ret->impuestosRetencion as $imp) {
                    $writer->addRow(Row::fromValues([
                        $ret->fecha_emision->format('d/m/Y'),
                        $numero,
                        $ret->cliente->razon_social ?? '-',
                        $ret->cliente->identificacion ?? '-',
                        $imp->num_doc_sustento ?? $ret->num_doc_sustento ?? '-',
                        $imp->codigo_impuesto ?? '-',
                        $imp->codigo_retencion ?? '-',
                        (float) ($imp->base_imponible ?? 0),
                        (float) ($imp->porcentaje_retener ?? 0),
                        (float) ($imp->valor_retenido ?? 0),
                    ]));
                }
            }
        });

        $writer->close();

        return response()->download($tempPath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }
}
