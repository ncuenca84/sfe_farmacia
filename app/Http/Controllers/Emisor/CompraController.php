<?php

namespace App\Http\Controllers\Emisor;

use App\Http\Controllers\Controller;
use App\Models\Compra;
use App\Services\CompraService;
use App\Services\XmlParserSriService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompraController extends Controller
{
    public function __construct(
        private XmlParserSriService $xmlParser,
        private CompraService $compraService,
    ) {}

    public function index(Request $request): View
    {
        $emisor = auth()->user()->emisor;

        $query = Compra::where('emisor_id', $emisor->id);

        if ($request->filled('desde')) {
            $query->where('fecha_emision', '>=', $request->desde);
        }
        if ($request->filled('hasta')) {
            $query->where('fecha_emision', '<=', $request->hasta);
        }
        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->where('razon_social_proveedor', 'like', "%{$buscar}%")
                  ->orWhere('ruc_proveedor', 'like', "%{$buscar}%")
                  ->orWhere('numero_comprobante', 'like', "%{$buscar}%");
            });
        }

        $compras = $query->orderByDesc('fecha_emision')->orderByDesc('id')->paginate(50);

        return view('emisor.compras.index', compact('compras'));
    }

    public function create(): View
    {
        return view('emisor.compras.create');
    }

    public function preview(Request $request): View|RedirectResponse
    {
        $request->validate([
            'xml_file' => 'required|file|max:2048',
        ]);

        $ext = strtolower($request->file('xml_file')->getClientOriginalExtension());
        if ($ext !== 'xml') {
            return back()->withErrors(['xml_file' => 'El archivo debe ser un XML (.xml).']);
        }

        $emisor = auth()->user()->emisor;

        try {
            $xmlContent = file_get_contents($request->file('xml_file')->getRealPath());
            $datosXml = $this->xmlParser->parsearFactura($xmlContent);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['xml_file' => $e->getMessage()]);
        }

        // Check duplicate
        $claveAcceso = $datosXml['info_tributaria']['clave_acceso'];
        if ($this->compraService->existeCompra($emisor, $claveAcceso)) {
            return back()->withErrors(['xml_file' => 'Ya existe una compra registrada con esta clave de acceso.']);
        }

        // Get productos for linking
        $productos = $emisor->productos()->orderBy('descripcion')->get(['id', 'codigo_principal', 'descripcion']);

        // Encode XML as base64 to pass through hidden field
        $xmlBase64 = base64_encode($xmlContent);

        return view('emisor.compras.preview', compact('datosXml', 'productos', 'xmlBase64'));
    }

    public function store(Request $request): RedirectResponse
    {
        $emisor = auth()->user()->emisor;

        // Recover XML from hidden form field
        $xmlBase64 = $request->input('xml_content');
        if (!$xmlBase64) {
            return redirect()->route('emisor.compras.create')
                ->withErrors(['xml_file' => 'No se recibió el contenido XML. Por favor suba el archivo nuevamente.']);
        }

        $xmlContent = base64_decode($xmlBase64);
        if (!$xmlContent) {
            return redirect()->route('emisor.compras.create')
                ->withErrors(['xml_file' => 'Error al decodificar el XML. Por favor suba el archivo nuevamente.']);
        }

        // Re-parse the XML to get structured data
        try {
            $datosXml = $this->xmlParser->parsearFactura($xmlContent);
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('emisor.compras.create')
                ->withErrors(['xml_file' => $e->getMessage()]);
        }

        // Auto-assign establecimiento from user
        $establecimiento = auth()->user()->establecimientosActivos()->first();
        abort_unless($establecimiento, 403, 'No tiene un establecimiento asignado.');

        // Check duplicate
        $claveAcceso = $datosXml['info_tributaria']['clave_acceso'];
        if ($this->compraService->existeCompra($emisor, $claveAcceso)) {
            return redirect()->route('emisor.compras.index')
                ->with('error', 'Ya existe una compra registrada con esta clave de acceso.');
        }

        // Build detalles extra array
        $detallesExtra = [];
        foreach ($datosXml['detalles'] as $index => $detalle) {
            $detallesExtra[$index] = [
                'producto_id' => $request->input("detalles.{$index}.producto_id") ?: null,
                'agregar_inventario' => (bool) $request->input("detalles.{$index}.agregar_inventario", false),
            ];
        }

        // Find or create a client record for the emisor (buyer)
        $clienteId = $emisor->clientes()
            ->where('identificacion', $emisor->ruc)
            ->value('id');

        if (!$clienteId) {
            $cliente = $emisor->clientes()->firstOrCreate(
                ['identificacion' => $emisor->ruc],
                [
                    'tipo_identificacion' => '04',
                    'razon_social' => $emisor->razon_social,
                    'email' => $emisor->email ?? 'compras@sistema.local',
                    'direccion' => $emisor->direccion_matriz ?? 'N/A',
                ],
            );
            $clienteId = $cliente->id;
        }

        $compra = $this->compraService->crearDesdeXml(
            emisor: $emisor,
            datosXml: $datosXml,
            clienteId: $clienteId,
            establecimientoId: $establecimiento->id,
            detallesExtra: $detallesExtra,
            userId: auth()->id(),
        );

        // Store the raw XML content
        $compra->update(['xml_contenido' => $xmlContent]);

        return redirect()->route('emisor.compras.show', $compra)
            ->with('success', 'Compra registrada exitosamente.');
    }

    public function show(Compra $compra): View
    {
        $emisor = auth()->user()->emisor;

        if ($compra->emisor_id !== $emisor->id) {
            abort(403);
        }

        $compra->load('detalles.producto');

        return view('emisor.compras.show', compact('compra'));
    }

    public function destroy(Compra $compra): RedirectResponse
    {
        $emisor = auth()->user()->emisor;

        if ($compra->emisor_id !== $emisor->id) {
            abort(403);
        }

        $compra->detalles()->delete();
        $compra->delete();

        return redirect()->route('emisor.compras.index')
            ->with('success', 'Compra eliminada.');
    }
}
