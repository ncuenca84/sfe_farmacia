<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Emisor;
use App\Models\FirmaElectronica;
use App\Services\FirmaElectronicaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FirmaElectronicaController extends Controller
{
    public function __construct(
        private FirmaElectronicaService $firmaService
    ) {}

    /**
     * Listado de firmas electrónicas.
     */
    public function index(Request $request): View
    {
        $buscar = $request->get('buscar');
        $estado = $request->get('estado');

        $query = FirmaElectronica::with('emisor')->latest();

        if ($buscar) {
            $query->where(function ($q) use ($buscar) {
                $q->where('identificacion', 'like', "%{$buscar}%")
                  ->orWhere('nombres', 'like', "%{$buscar}%")
                  ->orWhere('apellidos', 'like', "%{$buscar}%")
                  ->orWhere('correo', 'like', "%{$buscar}%");
            });
        }

        if ($estado === 'vencidas') {
            $query->whereNotNull('fecha_fin')->where('fecha_fin', '<', now());
        } elseif ($estado === 'por_vencer') {
            $query->whereNotNull('fecha_fin')
                  ->where('fecha_fin', '>=', now())
                  ->where('fecha_fin', '<=', now()->addDays(30));
        } elseif ($estado === 'vigentes') {
            $query->whereNotNull('fecha_fin')->where('fecha_fin', '>', now()->addDays(30));
        }

        $firmas = $query->paginate(25)->appends($request->only('buscar', 'estado'));

        $stats = [
            'total' => FirmaElectronica::count(),
            'vigentes' => FirmaElectronica::whereNotNull('fecha_fin')->where('fecha_fin', '>', now()->addDays(30))->count(),
            'por_vencer' => FirmaElectronica::whereNotNull('fecha_fin')->where('fecha_fin', '>=', now())->where('fecha_fin', '<=', now()->addDays(30))->count(),
            'vencidas' => FirmaElectronica::whereNotNull('fecha_fin')->where('fecha_fin', '<', now())->count(),
        ];

        return view('admin.crm.firmas-electronicas.index', compact('firmas', 'stats', 'buscar', 'estado'));
    }

    /**
     * Formulario para crear firma manualmente.
     */
    public function create(): View
    {
        $emisores = Emisor::where('activo', true)->orderBy('razon_social')->get();
        return view('admin.crm.firmas-electronicas.crear', compact('emisores'));
    }

    /**
     * Guardar firma (manual o desde .p12).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'identificacion' => 'required|string|max:20',
            'nombres' => 'required|string|max:150',
            'apellidos' => 'required|string|max:150',
            'celular' => 'nullable|string|max:20',
            'correo' => 'nullable|email|max:200',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date',
            'archivo_p12' => 'nullable|file|extensions:p12|max:5120',
            'password_p12' => 'nullable|string',
            'observaciones' => 'nullable|string|max:2000',
            'emisor_id' => 'nullable|exists:emisores,id',
        ]);

        // Guardar archivo .p12
        if ($request->hasFile('archivo_p12')) {
            $validated['archivo_p12'] = $request->file('archivo_p12')
                ->store('firmas_electronicas', 'local');
        }

        FirmaElectronica::create($validated);

        return redirect()->route('admin.crm.firmas-electronicas.index')
            ->with('success', 'Firma electrónica registrada correctamente.');
    }

    /**
     * Editar firma.
     */
    public function edit(FirmaElectronica $firma): View
    {
        $emisores = Emisor::where('activo', true)->orderBy('razon_social')->get();
        return view('admin.crm.firmas-electronicas.editar', compact('firma', 'emisores'));
    }

    /**
     * Actualizar firma.
     */
    public function update(Request $request, FirmaElectronica $firma): RedirectResponse
    {
        $validated = $request->validate([
            'identificacion' => 'required|string|max:20',
            'nombres' => 'required|string|max:150',
            'apellidos' => 'required|string|max:150',
            'celular' => 'nullable|string|max:20',
            'correo' => 'nullable|email|max:200',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date',
            'archivo_p12' => 'nullable|file|extensions:p12|max:5120',
            'password_p12' => 'nullable|string',
            'observaciones' => 'nullable|string|max:2000',
            'emisor_id' => 'nullable|exists:emisores,id',
        ]);

        if ($request->hasFile('archivo_p12')) {
            if ($firma->archivo_p12) {
                Storage::disk('local')->delete($firma->archivo_p12);
            }
            $validated['archivo_p12'] = $request->file('archivo_p12')
                ->store('firmas_electronicas', 'local');
        }

        // Solo actualizar password si se proporcionó
        if (empty($validated['password_p12'])) {
            unset($validated['password_p12']);
        }

        $firma->update($validated);

        return redirect()->route('admin.crm.firmas-electronicas.index')
            ->with('success', 'Firma electrónica actualizada.');
    }

    /**
     * Eliminar firma.
     */
    public function destroy(FirmaElectronica $firma): RedirectResponse
    {
        if ($firma->archivo_p12) {
            Storage::disk('local')->delete($firma->archivo_p12);
        }
        $firma->delete();

        return redirect()->route('admin.crm.firmas-electronicas.index')
            ->with('success', 'Firma electrónica eliminada.');
    }

    /**
     * AJAX: Leer datos del certificado .p12 automáticamente.
     */
    public function leerP12(Request $request): JsonResponse
    {
        $request->validate([
            'archivo' => 'required|file|extensions:p12|max:5120',
            'password' => 'required|string',
        ]);

        try {
            $datos = $this->firmaService->leerCertificado(
                $request->file('archivo')->getPathname(),
                $request->password
            );

            return response()->json([
                'success' => true,
                'datos' => [
                    'identificacion' => $datos['identificacion'],
                    'nombres' => $datos['nombres'],
                    'apellidos' => $datos['apellidos'],
                    'correo' => $datos['correo'],
                    'fecha_inicio' => $datos['fecha_inicio']?->format('Y-m-d'),
                    'fecha_fin' => $datos['fecha_fin']?->format('Y-m-d'),
                    'emisor_cn' => $datos['emisor_cn'],
                    'serial_number' => $datos['serial_number'],
                    'organizacion' => $datos['organizacion'],
                ],
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Importar firmas desde Excel.
     */
    public function importarExcel(Request $request): RedirectResponse
    {
        $request->validate([
            'archivo_excel' => 'required|file|mimes:xlsx,xls|max:10240',
        ]);

        try {
            $spreadsheet = IOFactory::load($request->file('archivo_excel')->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray(null, true, true, true);

            $importados = 0;
            $errores = [];

            foreach ($rows as $index => $row) {
                if ($index === 1) continue; // Saltar encabezado

                $identificacion = trim($row['A'] ?? '');
                if (empty($identificacion)) continue;

                try {
                    // Parsear fechas del Excel
                    $fechaInicio = $this->parsearFechaExcel($row['F'] ?? null);
                    $fechaFin = $this->parsearFechaExcel($row['G'] ?? null);

                    FirmaElectronica::updateOrCreate(
                        ['identificacion' => $identificacion],
                        [
                            'nombres' => trim($row['B'] ?? ''),
                            'apellidos' => trim($row['C'] ?? ''),
                            'celular' => trim($row['D'] ?? ''),
                            'correo' => trim($row['E'] ?? ''),
                            'fecha_inicio' => $fechaInicio,
                            'fecha_fin' => $fechaFin,
                        ]
                    );
                    $importados++;
                } catch (\Exception $e) {
                    $errores[] = "Fila {$index}: {$e->getMessage()}";
                }
            }

            $mensaje = "Importación completada. {$importados} registros procesados.";
            if (!empty($errores)) {
                $mensaje .= ' Errores: ' . implode('; ', array_slice($errores, 0, 5));
            }

            return back()->with('success', $mensaje);
        } catch (\Exception $e) {
            return back()->with('error', 'Error al procesar el archivo: ' . $e->getMessage());
        }
    }

    /**
     * Descargar plantilla Excel.
     */
    public function descargarPlantilla(): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Firmas Electrónicas');

        // Encabezados
        $headers = ['A1' => 'Identificacion', 'B1' => 'Nombres', 'C1' => 'Apellidos', 'D1' => 'Celular', 'E1' => 'Correo', 'F1' => 'Fecha Inicio', 'G1' => 'Fecha Fin'];
        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        // Estilo encabezados
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2563EB']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];
        $sheet->getStyle('A1:G1')->applyFromArray($headerStyle);

        // Fila de ejemplo
        $sheet->setCellValue('A2', '0102030405');
        $sheet->setCellValue('B2', 'Juan Carlos');
        $sheet->setCellValue('C2', 'Pérez López');
        $sheet->setCellValue('D2', '0991234567');
        $sheet->setCellValue('E2', 'juan@ejemplo.com');
        $sheet->setCellValue('F2', '2024/01/15');
        $sheet->setCellValue('G2', '2026/01/15');

        $sheet->getStyle('A2:G2')->getFont()->setItalic(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF808080'));

        // Anchos de columna
        $widths = ['A' => 18, 'B' => 25, 'C' => 25, 'D' => 15, 'E' => 30, 'F' => 15, 'G' => 15];
        foreach ($widths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        // Formato de fecha para columnas F y G
        $sheet->getStyle('F2:G100')->getNumberFormat()->setFormatCode('YYYY/MM/DD');

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, 'plantilla_firmas_electronicas.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Exportar firmas a Excel.
     */
    public function exportar(): StreamedResponse
    {
        $firmas = FirmaElectronica::orderBy('apellidos')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Firmas Electrónicas');

        $headers = ['Identificación', 'Nombres', 'Apellidos', 'Celular', 'Correo', 'Fecha Inicio', 'Fecha Fin', 'Días Restantes', 'Estado', 'Organización'];
        foreach ($headers as $i => $h) {
            $col = chr(65 + $i);
            $sheet->setCellValue($col . '1', $h);
        }

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2563EB']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ];
        $sheet->getStyle('A1:J1')->applyFromArray($headerStyle);

        $row = 2;
        foreach ($firmas as $firma) {
            $sheet->setCellValue("A{$row}", $firma->identificacion);
            $sheet->setCellValue("B{$row}", $firma->nombres);
            $sheet->setCellValue("C{$row}", $firma->apellidos);
            $sheet->setCellValue("D{$row}", $firma->celular);
            $sheet->setCellValue("E{$row}", $firma->correo);
            $sheet->setCellValue("F{$row}", $firma->fecha_inicio?->format('Y/m/d'));
            $sheet->setCellValue("G{$row}", $firma->fecha_fin?->format('Y/m/d'));
            $sheet->setCellValue("H{$row}", $firma->diasRestantes());
            $sheet->setCellValue("I{$row}", $firma->estadoTexto());
            $sheet->setCellValue("J{$row}", $firma->organizacion);
            $row++;
        }

        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, 'firmas_electronicas_' . date('Y-m-d') . '.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Parsear fecha que puede venir como string o como serial de Excel.
     */
    private function parsearFechaExcel(mixed $value): ?\Carbon\Carbon
    {
        if (empty($value)) return null;

        // Si es numérico, es un serial de Excel
        if (is_numeric($value)) {
            $unixDate = ($value - 25569) * 86400;
            return \Carbon\Carbon::createFromTimestamp($unixDate);
        }

        // Intentar parsear como string de fecha
        try {
            return \Carbon\Carbon::parse($value);
        } catch (\Exception) {
            return null;
        }
    }
}
