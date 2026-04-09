<?php

namespace App\Http\Controllers\Emisor;

use App\Http\Controllers\Controller;
use App\Models\Inventario;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Services\InventarioService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InventarioController extends Controller
{
    public function __construct(
        private InventarioService $inventarioService
    ) {}

    public function index(Request $request): View
    {
        $user = auth()->user();
        $emisor = $user->emisor;

        $query = Inventario::where('emisor_id', $emisor->id)
            ->with(['producto', 'establecimiento'])
            ->whereHas('establecimiento', fn ($q) => $q->where('maneja_inventario', true));

        if ($user->unidad_negocio_id) {
            $query->whereHas('establecimiento', fn ($q) => $q->where('unidad_negocio_id', $user->unidad_negocio_id));
        }

        if ($request->filled('establecimiento_id')) {
            $query->where('establecimiento_id', $request->establecimiento_id);
        }

        if ($request->filled('buscar')) {
            $query->whereHas('producto', function ($q) use ($request) {
                $q->where('nombre', 'like', "%{$request->buscar}%")
                    ->orWhere('codigo_principal', 'like', "%{$request->buscar}%");
            });
        }

        if ($request->filled('stock_bajo')) {
            $query->whereColumn('stock_actual', '<=', 'stock_minimo')
                ->where('stock_minimo', '>', 0);
        }

        $inventarios = $query->orderBy('updated_at', 'desc')->paginate(50);

        $establecimientos = $user->establecimientosActivos()->filter(fn ($e) => $e->maneja_inventario);

        return view('emisor.inventario.index', compact('inventarios', 'establecimientos'));
    }

    public function kardex(Request $request, Inventario $inventario): View
    {
        $this->autorizarAcceso($inventario);

        $query = MovimientoInventario::where('inventario_id', $inventario->id)
            ->with(['user']);

        if ($request->filled('fecha_desde')) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }

        $movimientos = $query->orderBy('created_at', 'desc')->paginate(50);

        $inventario->load(['producto', 'establecimiento']);

        return view('emisor.inventario.kardex', compact('inventario', 'movimientos'));
    }

    public function ajuste(): View
    {
        $user = auth()->user();
        $emisor = $user->emisor;

        $establecimientos = $user->establecimientosActivos();

        return view('emisor.inventario.ajuste', compact('establecimientos'));
    }

    public function guardarAjuste(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $emisor = $user->emisor;

        $validated = $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'establecimiento_id' => 'required|exists:establecimientos,id',
            'cantidad' => 'required|numeric',
            'costo_unitario' => 'nullable|numeric|min:0',
            'descripcion' => 'required|string|max:500',
        ]);

        $producto = Producto::findOrFail($validated['producto_id']);
        if ($producto->emisor_id !== $emisor->id) {
            abort(403);
        }

        // Verify the establecimiento belongs to this user's allowed ones
        $establecimientosIds = $user->establecimientosActivos()->pluck('id');
        if (!$establecimientosIds->contains((int) $validated['establecimiento_id'])) {
            abort(403, 'No tiene acceso a este establecimiento.');
        }

        $this->inventarioService->registrarAjuste(
            emisor: $emisor,
            productoId: (int) $validated['producto_id'],
            establecimientoId: (int) $validated['establecimiento_id'],
            cantidad: (float) $validated['cantidad'],
            costoUnitario: (float) ($validated['costo_unitario'] ?? 0),
            descripcion: $validated['descripcion'],
        );

        return redirect()->route('emisor.inventario.index')
            ->with('success', 'Ajuste de inventario registrado correctamente.');
    }

    public function valorizado(Request $request): View
    {
        $user = auth()->user();
        $emisor = $user->emisor;

        $query = Inventario::where('emisor_id', $emisor->id)
            ->where('stock_actual', '>', 0)
            ->with(['producto', 'establecimiento'])
            ->whereHas('establecimiento', fn ($q) => $q->where('maneja_inventario', true));

        if ($user->unidad_negocio_id) {
            $query->whereHas('establecimiento', fn ($q) => $q->where('unidad_negocio_id', $user->unidad_negocio_id));
        }

        if ($request->filled('establecimiento_id')) {
            $query->where('establecimiento_id', $request->establecimiento_id);
        }

        $inventarios = $query->orderBy('producto_id')->get();

        $totalValorizado = $inventarios->sum(function ($inv) {
            $costo = (float) $inv->costo_promedio > 0
                ? (float) $inv->costo_promedio
                : (float) ($inv->producto->precio_unitario ?? 0);
            return (float) $inv->stock_actual * $costo;
        });

        $establecimientos = $user->establecimientosActivos()->filter(fn ($e) => $e->maneja_inventario);

        return view('emisor.inventario.valorizado', compact('inventarios', 'totalValorizado', 'establecimientos'));
    }

    /**
     * Obtener datos de inventario filtrados para exportación.
     */
    private function getInventarioParaExport(Request $request, string $tipo = 'stock'): array
    {
        $user = auth()->user();
        $emisor = $user->emisor;

        $query = Inventario::where('emisor_id', $emisor->id)
            ->with(['producto', 'establecimiento'])
            ->whereHas('establecimiento', fn ($q) => $q->where('maneja_inventario', true));

        if ($tipo === 'valorizado') {
            $query->where('stock_actual', '>', 0);
        }

        if ($user->unidad_negocio_id) {
            $query->whereHas('establecimiento', fn ($q) => $q->where('unidad_negocio_id', $user->unidad_negocio_id));
        }

        if ($request->filled('establecimiento_id')) {
            $query->where('establecimiento_id', $request->establecimiento_id);
        }

        if ($tipo === 'stock') {
            if ($request->filled('buscar')) {
                $query->whereHas('producto', function ($q) use ($request) {
                    $q->where('nombre', 'like', "%{$request->buscar}%")
                        ->orWhere('codigo_principal', 'like', "%{$request->buscar}%");
                });
            }
            if ($request->filled('stock_bajo')) {
                $query->whereColumn('stock_actual', '<=', 'stock_minimo')->where('stock_minimo', '>', 0);
            }
        }

        $inventarios = $query->orderBy('producto_id')->get();

        $totalValorizado = $inventarios->sum(function ($inv) {
            $costo = (float) $inv->costo_promedio > 0
                ? (float) $inv->costo_promedio
                : (float) ($inv->producto->precio_unitario ?? 0);
            return (float) $inv->stock_actual * $costo;
        });

        return [$inventarios, $totalValorizado, $emisor];
    }

    public function exportPdf(Request $request): Response
    {
        $tipo = $request->get('tipo', 'stock');
        [$inventarios, $totalValorizado, $emisor] = $this->getInventarioParaExport($request, $tipo);

        $pdf = Pdf::loadView('emisor.inventario.export-pdf', compact('inventarios', 'totalValorizado', 'emisor', 'tipo'));
        $pdf->setPaper('A4', 'landscape');

        $filename = $tipo === 'valorizado' ? 'inventario_valorizado' : 'reporte_stock';
        return $pdf->download("{$filename}_" . now()->format('Y-m-d') . '.pdf');
    }

    public function exportExcel(Request $request): StreamedResponse
    {
        $tipo = $request->get('tipo', 'stock');
        [$inventarios, $totalValorizado, $emisor] = $this->getInventarioParaExport($request, $tipo);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $titulo = $tipo === 'valorizado' ? 'Inventario Valorizado' : 'Reporte de Stock';
        $sheet->setTitle($tipo === 'valorizado' ? 'Valorizado' : 'Stock');

        // Título
        $sheet->setCellValue('A1', $emisor->razon_social);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->setCellValue('A2', $titulo . ' - ' . now()->format('d/m/Y'));
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11);

        // Headers
        $row = 4;
        if ($tipo === 'valorizado') {
            $headers = ['Codigo', 'Producto', 'Establecimiento', 'Stock', 'Costo Promedio', 'Valor Total'];
            $lastCol = 'F';
        } else {
            $headers = ['Codigo', 'Producto', 'Establecimiento', 'Stock', 'Stock Min.', 'Costo Prom.', 'Estado'];
            $lastCol = 'G';
        }

        $sheet->fromArray($headers, null, "A{$row}");
        $headerRange = "A{$row}:{$lastCol}{$row}";
        $sheet->getStyle($headerRange)->getFont()->setBold(true);
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D9E2F3');
        $sheet->getStyle($headerRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Data
        $row = 5;
        foreach ($inventarios as $inv) {
            $costoUsado = (float) $inv->costo_promedio > 0
                ? (float) $inv->costo_promedio
                : (float) ($inv->producto->precio_unitario ?? 0);

            if ($tipo === 'valorizado') {
                $sheet->fromArray([
                    $inv->producto->codigo_principal ?? '-',
                    $inv->producto->nombre,
                    ($inv->establecimiento->codigo ?? '') . ' - ' . ($inv->establecimiento->nombre ?? ''),
                    (float) $inv->stock_actual,
                    $costoUsado,
                    (float) $inv->stock_actual * $costoUsado,
                ], null, "A{$row}");
                $sheet->getStyle("E{$row}:F{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
            } else {
                $estado = $inv->stockBajo() ? 'Stock Bajo' : 'Normal';
                $sheet->fromArray([
                    $inv->producto->codigo_principal ?? '-',
                    $inv->producto->nombre,
                    ($inv->establecimiento->codigo ?? '') . ' - ' . ($inv->establecimiento->nombre ?? ''),
                    (float) $inv->stock_actual,
                    (float) $inv->stock_minimo,
                    $costoUsado,
                    $estado,
                ], null, "A{$row}");
                $sheet->getStyle("F{$row}")->getNumberFormat()->setFormatCode('#,##0.0000');
            }
            $row++;
        }

        // Total para valorizado
        if ($tipo === 'valorizado' && $inventarios->count() > 0) {
            $sheet->setCellValue("E{$row}", 'TOTAL:');
            $sheet->setCellValue("F{$row}", $totalValorizado);
            $sheet->getStyle("E{$row}:F{$row}")->getFont()->setBold(true);
            $sheet->getStyle("F{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
        }

        foreach (range('A', $lastCol) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = ($tipo === 'valorizado' ? 'inventario_valorizado' : 'reporte_stock') . '_' . now()->format('Y-m-d') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function autorizarAcceso(Inventario $inventario): void
    {
        $user = auth()->user();
        if (!$user->esAdmin() && $inventario->emisor_id !== $user->emisor_id) {
            abort(403);
        }
    }
}
