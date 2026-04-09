<?php

namespace App\Http\Controllers\Emisor;

use App\Http\Controllers\Controller;
use App\Models\CargaArchivo;
use App\Models\Cliente;
use App\Models\ImpuestoIva;
use App\Models\Inventario;
use App\Models\Producto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CargaMasivaController extends Controller
{
    public function index(Request $request): View
    {
        $emisor = auth()->user()->emisor;

        $cargas = CargaArchivo::where('emisor_id', $emisor->id)
            ->orderByDesc('created_at')
            ->paginate(50);

        return view('emisor.carga-masiva.index', compact('cargas'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tipo' => 'required|string|in:clientes,productos,facturas',
            'archivo' => 'required|file|mimes:xlsx,csv,xls|max:10240',
        ]);

        $user = auth()->user();
        $emisor = $user->emisor;
        $tipo = $validated['tipo'];

        $file = $request->file('archivo');
        $rows = $this->leerArchivo($file);

        if ($rows === null) {
            return back()->with('error', 'No se pudo leer el archivo. Verifique el formato.');
        }

        // Skip header row
        array_shift($rows);
        $rows = array_values(array_filter($rows, fn ($r) => !empty(array_filter($r))));

        $totalRegistros = count($rows);
        $procesados = 0;
        $errores = 0;
        $mensajesError = [];

        if ($tipo === 'clientes') {
            [$procesados, $errores, $mensajesError] = $this->importarClientes($rows, $emisor->id, $user->unidad_negocio_id);
        } elseif ($tipo === 'productos') {
            [$procesados, $errores, $mensajesError] = $this->importarProductos($rows, $emisor->id, $user->unidad_negocio_id);
        } elseif ($tipo === 'facturas') {
            [$procesados, $errores, $mensajesError] = $this->importarFacturas($rows, $emisor, $user);
        }

        $carga = CargaArchivo::create([
            'emisor_id' => $emisor->id,
            'user_id' => $user->id,
            'tipo' => $tipo,
            'archivo_nombre' => $file->getClientOriginalName(),
            'total_registros' => $totalRegistros,
            'procesados' => $procesados,
            'errores' => $errores,
            'estado' => $errores > 0 && $procesados > 0 ? 'completado' : ($errores > 0 ? 'error' : 'completado'),
        ]);

        foreach ($mensajesError as $err) {
            $carga->cargaErrors()->create([
                'fila' => $err['fila'],
                'mensaje' => $err['mensaje'],
            ]);
        }

        $msg = "Carga completada: {$procesados} registros importados.";
        if ($errores > 0) {
            $msg .= " {$errores} errores encontrados.";
        }

        $redirect = redirect()->route('emisor.carga-masiva.index')
            ->with($errores > 0 && $procesados === 0 ? 'error' : 'success', $msg);

        if (!empty($mensajesError)) {
            $redirect->with('errores_detalle', $mensajesError);
        }

        return $redirect;
    }

    /**
     * Descargar plantilla XLSX para cada tipo de carga.
     */
    public function descargarPlantilla(string $tipo): StreamedResponse
    {
        if (!in_array($tipo, ['clientes', 'productos', 'facturas'])) {
            abort(404);
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        if ($tipo === 'clientes') {
            $sheet->setTitle('Clientes');
            $headers = ['Nombre', 'Tipo Identificacion', 'Identificacion', 'Direccion', 'Telefono', 'Email'];
            $sheet->fromArray($headers, null, 'A1');
            $sheet->fromArray(['Nombre Cliente Ruc', '04', '1792722705001', 'Quito', '099999999', 'cliente@mail.com'], null, 'A2');
            $sheet->fromArray(['Nombre Cliente Cedula', '05', '1792722705', 'Quito', '099999999', 'cliente@mail.com'], null, 'A3');
            $sheet->fromArray(['Consumidor Final', '07', '9999999999999', 'Quito', '099999999', 'cliente@mail.com'], null, 'A4');
            $lastCol = 'F';
        } elseif ($tipo === 'productos') {
            $sheet->setTitle('Productos');
            $headers = ['Nombre', 'Codigo Principal', 'Codigo Auxiliar', 'Precio Unitario', 'Codigo IVA', 'Codigo ICE', 'Codigo IRBPNR', 'Stock Inicial', 'Stock Minimo'];
            $sheet->fromArray($headers, null, 'A1');
            $sheet->fromArray(['Nuevo Producto 4', 'P004', '', '10.00', '2', '', '', '100', '10'], null, 'A2');
            $lastCol = 'I';
        } else {
            $sheet->setTitle('Facturas');
            $headers = ['ID Factura', 'Forma Pago', 'Cod Producto', 'Cant', 'Descuento', 'Tipo Identificacion', 'Identificacion Cliente', 'Nombre y Apellidos', 'Email'];
            $sheet->fromArray($headers, null, 'A1');
            $sheet->fromArray(['1', '01', 'P001', '5.00', '0.00', '05', '1755154679', 'Alex Mera', 'info@gmc.ec'], null, 'A2');
            $lastCol = 'I';
        }

        // Estilo del header
        $headerRange = "A1:{$lastCol}1";
        $sheet->getStyle($headerRange)->getFont()->setBold(true);
        $sheet->getStyle($headerRange)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('D9E2F3');
        $sheet->getStyle($headerRange)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle($headerRange)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Autosize columnas
        foreach (range('A', $lastCol) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Hoja de ayuda con códigos de referencia
        $helpSheet = $spreadsheet->createSheet();
        $helpSheet->setTitle('Codigos de Referencia');

        // Códigos IVA
        $helpSheet->setCellValue('A1', 'CODIGOS IVA');
        $helpSheet->getStyle('A1')->getFont()->setBold(true)->setSize(12);
        $helpSheet->fromArray(['Codigo', 'Nombre', 'Tarifa %'], null, 'A2');
        $helpSheet->getStyle('A2:C2')->getFont()->setBold(true);
        $helpSheet->getStyle('A2:C2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D9E2F3');
        $ivaData = [
            ['0', 'IVA 0%', '0.00%'],
            ['2', 'IVA 12% (historico)', '12.00%'],
            ['4', 'IVA 15%', '15.00%'],
            ['5', 'IVA 5%', '5.00%'],
            ['6', 'No objeto IVA', '0.00%'],
            ['7', 'Exento IVA', '0.00%'],
            ['10', 'IVA 13%', '13.00%'],
        ];
        $helpSheet->fromArray($ivaData, null, 'A3');

        if ($tipo === 'clientes' || $tipo === 'facturas') {
            // Tipos de identificación
            $helpSheet->setCellValue('E1', 'TIPOS DE IDENTIFICACION');
            $helpSheet->getStyle('E1')->getFont()->setBold(true)->setSize(12);
            $helpSheet->fromArray(['Codigo', 'Tipo'], null, 'E2');
            $helpSheet->getStyle('E2:F2')->getFont()->setBold(true);
            $helpSheet->getStyle('E2:F2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D9E2F3');
            $tipoIdData = [
                ['04', 'RUC'],
                ['05', 'Cedula'],
                ['06', 'Pasaporte'],
                ['07', 'Consumidor Final'],
                ['08', 'Identificacion Exterior'],
            ];
            $helpSheet->fromArray($tipoIdData, null, 'E3');
            foreach (range('E', 'F') as $col) {
                $helpSheet->getColumnDimension($col)->setAutoSize(true);
            }
        }

        if ($tipo === 'facturas') {
            // Formas de pago
            $helpSheet->setCellValue('H1', 'FORMAS DE PAGO');
            $helpSheet->getStyle('H1')->getFont()->setBold(true)->setSize(12);
            $helpSheet->fromArray(['Codigo', 'Descripcion'], null, 'H2');
            $helpSheet->getStyle('H2:I2')->getFont()->setBold(true);
            $helpSheet->getStyle('H2:I2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D9E2F3');
            $fpData = [
                ['01', 'SIN UTILIZACION DEL SISTEMA FINANCIERO'],
                ['15', 'COMPENSACION DE DEUDAS'],
                ['16', 'TARJETA DE DEBITO'],
                ['17', 'DINERO ELECTRONICO'],
                ['18', 'TARJETA PREPAGO'],
                ['19', 'TARJETA DE CREDITO'],
                ['20', 'OTROS CON UTILIZACION SISTEMA FINANCIERO'],
                ['21', 'ENDOSO DE TITULOS'],
            ];
            $helpSheet->fromArray($fpData, null, 'H3');
            foreach (range('H', 'I') as $col) {
                $helpSheet->getColumnDimension($col)->setAutoSize(true);
            }
        }

        foreach (range('A', 'C') as $col) {
            $helpSheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Volver a la primera hoja como activa
        $spreadsheet->setActiveSheetIndex(0);

        $filename = "plantilla_{$tipo}.xlsx";

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Lee un archivo CSV o XLSX y retorna un array indexado de filas.
     */
    private function leerArchivo($file): ?array
    {
        $extension = strtolower($file->getClientOriginalExtension());

        if (in_array($extension, ['xlsx', 'xls'])) {
            try {
                $spreadsheet = IOFactory::load($file->getPathname());
                return $spreadsheet->getActiveSheet()->toArray(null, true, true, false);
            } catch (\Exception $e) {
                return null;
            }
        }

        if ($extension === 'csv') {
            return $this->leerCsv($file->getPathname());
        }

        return null;
    }

    private function leerCsv(string $path): array
    {
        $rows = [];
        $handle = fopen($path, 'r');
        if (!$handle) return [];

        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        while (($line = fgetcsv($handle, 0, ',')) !== false) {
            $rows[] = $line;
        }
        fclose($handle);

        // Si solo tiene 1 columna, intentar con punto y coma
        if (count($rows) > 0 && count($rows[0]) <= 1) {
            $rows = [];
            $handle = fopen($path, 'r');
            $bom = fread($handle, 3);
            if ($bom !== "\xEF\xBB\xBF") rewind($handle);
            while (($line = fgetcsv($handle, 0, ';')) !== false) {
                $rows[] = $line;
            }
            fclose($handle);
        }

        return $rows;
    }

    /**
     * Importar clientes.
     * Columnas: A=Nombre, B=Tipo Identificacion, C=Identificacion, D=Direccion, E=Telefono, F=Email
     */
    private function importarClientes(array $rows, int $emisorId, ?int $unidadNegocioId): array
    {
        $procesados = 0;
        $errores = 0;
        $mensajes = [];

        foreach ($rows as $idx => $row) {
            $fila = $idx + 2;
            $nombre = trim($row[0] ?? '');
            $tipoId = trim($row[1] ?? '');
            $identificacion = trim($row[2] ?? '');
            $direccion = trim($row[3] ?? '');
            $telefono = trim($row[4] ?? '');
            $email = trim($row[5] ?? '');

            if (empty($identificacion) || empty($nombre)) {
                $errores++;
                $mensajes[] = ['fila' => $fila, 'mensaje' => 'Nombre e Identificacion son obligatorios'];
                continue;
            }

            if (empty($tipoId)) {
                $tipoId = '05';
            }

            if (!in_array($tipoId, ['04', '05', '06', '07', '08'])) {
                $errores++;
                $mensajes[] = ['fila' => $fila, 'mensaje' => "Tipo identificacion '{$tipoId}' no valido (use 04,05,06,07,08)"];
                continue;
            }

            if ($email) {
                foreach (preg_split('/\s*,\s*/', $email) as $e) {
                    if ($e && !filter_var(trim($e), FILTER_VALIDATE_EMAIL)) {
                        $errores++;
                        $mensajes[] = ['fila' => $fila, 'mensaje' => "Email '{$e}' no es valido"];
                        continue 2;
                    }
                }
            }

            try {
                Cliente::updateOrCreate(
                    ['emisor_id' => $emisorId, 'identificacion' => $identificacion],
                    [
                        'tipo_identificacion' => $tipoId,
                        'razon_social' => $nombre,
                        'direccion' => $direccion ?: null,
                        'email' => $email ?: null,
                        'telefono' => $telefono ?: null,
                        'unidad_negocio_id' => $unidadNegocioId,
                        'activo' => true,
                    ]
                );
                $procesados++;
            } catch (\Exception $e) {
                $errores++;
                $mensajes[] = ['fila' => $fila, 'mensaje' => 'Error: ' . $e->getMessage()];
            }
        }

        return [$procesados, $errores, $mensajes];
    }

    /**
     * Importar productos.
     * Columnas: A=Nombre, B=Codigo Principal, C=Codigo Auxiliar, D=Precio Unitario,
     *           E=Codigo IVA, F=Codigo ICE, G=Codigo IRBPNR, H=Stock Inicial, I=Stock Minimo
     */
    private function importarProductos(array $rows, int $emisorId, ?int $unidadNegocioId): array
    {
        $procesados = 0;
        $errores = 0;
        $mensajes = [];

        $ivaOptions = ImpuestoIva::where('activo', true)->get();

        // Obtener solo establecimientos de la línea de negocio del usuario que manejan inventario
        $user = auth()->user();
        $establecimientos = $user->establecimientosActivos()->filter(fn ($e) => $e->maneja_inventario);

        if ($establecimientos->isEmpty()) {
            // Verificar si hay establecimientos activos sin inventario
            $estSinInv = $user->establecimientosActivos()->filter(fn ($e) => !$e->maneja_inventario);
            if ($estSinInv->isNotEmpty()) {
                $nombres = $estSinInv->pluck('nombre')->implode(', ');
                $mensajes[] = ['fila' => 0, 'mensaje' => "Sus establecimientos ({$nombres}) no tienen 'Maneja Inventario' activado. Los productos se crearán sin stock. Active esta opción en Establecimientos > Editar."];
            } else {
                $mensajes[] = ['fila' => 0, 'mensaje' => 'No se encontraron establecimientos asignados. Los productos se crearán sin stock.'];
            }
        }

        foreach ($rows as $idx => $row) {
            $fila = $idx + 2;
            $nombre = trim($row[0] ?? '');
            $codigo = trim($row[1] ?? '');
            $codigoAux = trim($row[2] ?? '');
            $precio = trim($row[3] ?? '0');
            $codigoIva = trim($row[4] ?? '');
            // $codigoIce = trim($row[5] ?? '');
            // $codigoIrbpnr = trim($row[6] ?? '');
            $stockInicial = (float) trim($row[7] ?? '0');
            $stockMinimo = (float) trim($row[8] ?? '0');

            if (empty($nombre)) {
                $errores++;
                $mensajes[] = ['fila' => $fila, 'mensaje' => 'El nombre del producto es obligatorio'];
                continue;
            }

            $precioNum = (float) $precio;
            if ($precioNum < 0) {
                $errores++;
                $mensajes[] = ['fila' => $fila, 'mensaje' => 'El precio no puede ser negativo'];
                continue;
            }

            $ivaId = null;
            if ($codigoIva !== '') {
                $iva = $ivaOptions->first(fn ($i) => $i->id == (int) $codigoIva);
                if ($iva) {
                    $ivaId = $iva->id;
                } else {
                    $errores++;
                    $mensajes[] = ['fila' => $fila, 'mensaje' => "Codigo IVA '{$codigoIva}' no encontrado"];
                    continue;
                }
            } else {
                $ivaDefault = $ivaOptions->first(fn ($i) => (float) $i->tarifa === 15.0) ?? $ivaOptions->first();
                $ivaId = $ivaDefault?->id;
            }

            if (!$ivaId) {
                $errores++;
                $mensajes[] = ['fila' => $fila, 'mensaje' => 'No se pudo determinar el IVA'];
                continue;
            }

            try {
                $data = [
                    'nombre' => $nombre,
                    'codigo_auxiliar' => $codigoAux ?: null,
                    'precio_unitario' => $precioNum,
                    'impuesto_iva_id' => $ivaId,
                    'unidad_negocio_id' => $unidadNegocioId,
                    'activo' => true,
                ];

                if ($codigo) {
                    $producto = Producto::updateOrCreate(
                        ['emisor_id' => $emisorId, 'codigo_principal' => $codigo],
                        $data
                    );
                } else {
                    $data['emisor_id'] = $emisorId;
                    $producto = Producto::create($data);
                }

                // Crear inventario en establecimientos que manejan inventario
                if ($establecimientos->isNotEmpty()) {
                    foreach ($establecimientos as $est) {
                        Inventario::updateOrCreate(
                            [
                                'producto_id' => $producto->id,
                                'establecimiento_id' => $est->id,
                            ],
                            [
                                'emisor_id' => $emisorId,
                                'stock_actual' => $stockInicial,
                                'stock_minimo' => $stockMinimo,
                                'costo_promedio' => $precioNum,
                            ]
                        );
                    }
                }

                $procesados++;
            } catch (\Exception $e) {
                $errores++;
                $mensajes[] = ['fila' => $fila, 'mensaje' => 'Error: ' . $e->getMessage()];
            }
        }

        return [$procesados, $errores, $mensajes];
    }

    /**
     * Importar facturas.
     * Columnas: A=ID Factura, B=Forma Pago, C=Cod Producto, D=Cant, E=Descuento,
     *           F=Tipo Identificacion, G=Identificacion Cliente, H=Nombre y Apellidos, I=Email
     */
    private function importarFacturas(array $rows, $emisor, $user): array
    {
        $procesados = 0;
        $errores = 0;
        $mensajes = [];

        $grupos = [];
        foreach ($rows as $idx => $row) {
            $idFactura = trim($row[0] ?? '');
            if (empty($idFactura)) continue;
            $grupos[$idFactura][] = ['row' => $row, 'fila' => $idx + 2];
        }

        $establecimiento = $user->establecimientosActivos()->first();
        if (!$establecimiento) {
            return [0, 1, [['fila' => 0, 'mensaje' => 'No tiene un establecimiento asignado']]];
        }
        $ptoEmision = $establecimiento->ptoEmisiones->first();
        if (!$ptoEmision) {
            return [0, 1, [['fila' => 0, 'mensaje' => 'No tiene un punto de emision asignado']]];
        }

        $facturaService = app(\App\Services\FacturaService::class);
        $suscripcionService = app(\App\Services\SuscripcionService::class);

        foreach ($grupos as $idFact => $filas) {
            $primeraFila = $filas[0]['row'];
            $numFila = $filas[0]['fila'];

            $formaPago = trim($primeraFila[1] ?? '01');
            $tipoIdCliente = trim($primeraFila[5] ?? '');
            $idCliente = trim($primeraFila[6] ?? '');
            $nombreCliente = trim($primeraFila[7] ?? '');
            $emailCliente = trim($primeraFila[8] ?? '');

            if (empty($idCliente) || empty($nombreCliente)) {
                $errores++;
                $mensajes[] = ['fila' => $numFila, 'mensaje' => "Factura {$idFact}: Identificacion y Nombre del cliente son obligatorios"];
                continue;
            }

            if (empty($tipoIdCliente)) {
                $tipoIdCliente = '05';
            }

            try {
                $cliente = Cliente::updateOrCreate(
                    ['emisor_id' => $emisor->id, 'identificacion' => $idCliente],
                    [
                        'tipo_identificacion' => $tipoIdCliente,
                        'razon_social' => $nombreCliente,
                        'email' => $emailCliente ?: null,
                        'unidad_negocio_id' => $user->unidad_negocio_id,
                        'activo' => true,
                    ]
                );
            } catch (\Exception $e) {
                $errores++;
                $mensajes[] = ['fila' => $numFila, 'mensaje' => "Factura {$idFact}: Error creando cliente - " . $e->getMessage()];
                continue;
            }

            $detalles = [];
            $detallesOk = true;
            foreach ($filas as $item) {
                $r = $item['row'];
                $f = $item['fila'];
                $codProd = trim($r[2] ?? '');
                $cant = (float) ($r[3] ?? 0);
                $descuento = (float) ($r[4] ?? 0);

                if (empty($codProd)) {
                    $errores++;
                    $mensajes[] = ['fila' => $f, 'mensaje' => "Factura {$idFact}: Codigo de producto obligatorio"];
                    $detallesOk = false;
                    break;
                }

                if ($cant <= 0) {
                    $errores++;
                    $mensajes[] = ['fila' => $f, 'mensaje' => "Factura {$idFact}: La cantidad debe ser mayor a 0"];
                    $detallesOk = false;
                    break;
                }

                $producto = Producto::where('emisor_id', $emisor->id)
                    ->where('codigo_principal', $codProd)
                    ->first();

                if (!$producto) {
                    $errores++;
                    $mensajes[] = ['fila' => $f, 'mensaje' => "Factura {$idFact}: Producto '{$codProd}' no encontrado"];
                    $detallesOk = false;
                    break;
                }

                $detalles[] = [
                    'codigo_principal' => $producto->codigo_principal,
                    'descripcion' => $producto->nombre,
                    'cantidad' => $cant,
                    'precio_unitario' => (float) $producto->precio_unitario,
                    'descuento' => $descuento,
                    'impuesto_iva_id' => $producto->impuesto_iva_id,
                ];
            }

            if (!$detallesOk) continue;

            try {
                $suscripcionService->verificarEIncrementar($emisor);
            } catch (\Exception $e) {
                $errores++;
                $mensajes[] = ['fila' => $numFila, 'mensaje' => "Factura {$idFact}: " . $e->getMessage()];
                continue;
            }

            try {
                $data = [
                    'cliente_id' => $cliente->id,
                    'establecimiento_id' => $establecimiento->id,
                    'pto_emision_id' => $ptoEmision->id,
                    'fecha_emision' => now()->toDateString(),
                    'forma_pago' => $formaPago,
                    'detalles' => $detalles,
                ];

                $facturaService->crear($emisor, $data);
                $procesados++;
            } catch (\Exception $e) {
                $errores++;
                $mensajes[] = ['fila' => $numFila, 'mensaje' => "Factura {$idFact}: " . $e->getMessage()];
            }
        }

        return [$procesados, $errores, $mensajes];
    }
}
