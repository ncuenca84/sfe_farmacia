<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Emisor;
use App\Models\Plan;
use App\Services\EmisorService;
use App\Services\SriRucService;
use App\Services\SuscripcionService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\CampoAdicional;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class EmisorController extends Controller
{
    public function __construct(
        private EmisorService $emisorService,
        private SuscripcionService $suscripcionService,
        private SriRucService $sriRucService
    ) {}

    public function index(Request $request): View
    {
        $query = Emisor::with('suscripcionActiva.plan');

        if ($request->filled('buscar')) {
            $query->where(function ($q) use ($request) {
                $q->where('ruc', 'like', "%{$request->buscar}%")
                    ->orWhere('razon_social', 'like', "%{$request->buscar}%")
                    ->orWhere('nombre_comercial', 'like', "%{$request->buscar}%");
            });
        }

        $emisores = $query->orderBy('razon_social')->paginate(50);
        return view('admin.emisores.index', compact('emisores'));
    }

    public function create(): View
    {
        $planes = Plan::where('activo', true)->get();
        return view('admin.emisores.create', compact('planes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ruc' => 'required|string|size:13|unique:emisores,ruc',
            'razon_social' => 'required|string|max:300',
            'nombre_comercial' => 'nullable|string|max:300',
            'direccion_matriz' => 'nullable|string',
            'celular' => 'nullable|string|max:20',
            'ambiente' => 'required|in:1,2',
            'tipo_emision' => 'required|in:1',
            'obligado_contabilidad' => 'nullable|boolean',
            'contribuyente_especial' => 'nullable|string|max:20',
            'agente_retencion' => 'nullable|string|max:10',
            'regimen' => 'required|in:GENERAL,RIMPE,NEGOCIO_POPULAR,EPS',
            'codigo_numerico' => 'required|string|size:8',
            // Firma
            'firma' => 'nullable|file|extensions:p12|max:5120',
            'firma_password' => 'nullable|string',
            // Correo
            'mail_host' => 'nullable|string|max:100',
            'mail_port' => 'nullable|integer|between:1,65535',
            'mail_username' => 'nullable|string|max:150',
            'mail_password' => 'nullable|string',
            'mail_encryption' => 'nullable|in:ssl,tls',
            'mail_from_address' => 'nullable|string|max:150',
            'mail_from_name' => 'nullable|string|max:150',
            // Logo
            'logo' => 'nullable|image|max:2048',
            // Establecimiento + Punto de Emision
            'estab_codigo' => 'nullable|string|max:3',
            'estab_nombre' => 'nullable|string|max:200',
            'estab_direccion' => 'nullable|string',
            'pto_codigo' => 'nullable|string|max:3',
            'pto_nombre' => 'nullable|string|max:200',
            // Plan
            'plan_id' => 'required|exists:planes,id',
            'fecha_inicio' => 'required|date',
            // Usuario admin del emisor
            'admin_username' => 'required|string|max:100|unique:users,username',
            'admin_nombre' => 'required|string|max:150',
            'admin_apellido' => 'required|string|max:150',
            'admin_email' => 'required|email|max:200',
            'admin_password' => 'required|string|min:8',
        ]);

        $emisor = $this->emisorService->crear($validated, $request);

        $plan = Plan::findOrFail($validated['plan_id']);
        $this->suscripcionService->asignarPlan($emisor, $plan, Carbon::parse($validated['fecha_inicio']));

        return redirect()->route('admin.emisores.index')
            ->with('success', 'Emisor creado correctamente.');
    }

    public function show(Emisor $emisor): View
    {
        $emisor->load(['suscripcionActiva.plan', 'establecimientos.ptoEmisiones', 'users']);
        return view('admin.emisores.show', compact('emisor'));
    }

    public function edit(Emisor $emisor): View
    {
        $planes = Plan::where('activo', true)->get();
        return view('admin.emisores.edit', compact('emisor', 'planes'));
    }

    public function update(Request $request, Emisor $emisor): RedirectResponse
    {
        $validated = $request->validate([
            'ruc' => 'required|string|size:13|unique:emisores,ruc,' . $emisor->id,
            'razon_social' => 'required|string|max:300',
            'nombre_comercial' => 'nullable|string|max:300',
            'direccion_matriz' => 'nullable|string',
            'celular' => 'nullable|string|max:20',
            'ambiente' => 'required|in:1,2',
            'obligado_contabilidad' => 'nullable|boolean',
            'contribuyente_especial' => 'nullable|string|max:20',
            'agente_retencion' => 'nullable|string|max:10',
            'regimen' => 'required|in:GENERAL,RIMPE,NEGOCIO_POPULAR,EPS',
            'codigo_numerico' => 'required|string|size:8',
            'firma' => 'nullable|file|extensions:p12|max:5120',
            'firma_password' => 'nullable|string',
            'mail_host' => 'nullable|string|max:100',
            'mail_port' => 'nullable|integer|between:1,65535',
            'mail_username' => 'nullable|string|max:150',
            'mail_password' => 'nullable|string',
            'mail_encryption' => 'nullable|in:ssl,tls',
            'mail_from_address' => 'nullable|string|max:150',
            'mail_from_name' => 'nullable|string|max:150',
            'logo' => 'nullable|image|max:2048',
            'activo' => 'boolean',
        ]);

        $this->emisorService->actualizar($emisor, $validated, $request);

        return redirect()->route('admin.emisores.index')
            ->with('success', 'Emisor actualizado correctamente.');
    }

    public function destroy(Emisor $emisor): RedirectResponse
    {
        // Solo desactivar, nunca eliminar
        $emisor->update(['activo' => false]);
        return redirect()->route('admin.emisores.index')
            ->with('success', 'Emisor desactivado correctamente.');
    }

    public function activar(Emisor $emisor): RedirectResponse
    {
        $emisor->update(['activo' => true]);
        return redirect()->route('admin.emisores.index')
            ->with('success', 'Emisor activado correctamente.');
    }

    public function eliminarPermanente(Request $request, Emisor $emisor): RedirectResponse
    {
        $request->validate([
            'ruc_confirmacion' => 'required|string',
        ]);

        if ($request->ruc_confirmacion !== $emisor->ruc) {
            return back()->with('error', 'El RUC ingresado no coincide. No se elimino el emisor.');
        }

        DB::transaction(function () use ($emisor) {
            $emisorId = $emisor->id;

            // 1. Eliminar campos adicionales y mensajes polimorficos
            $comprobanteTypes = [
                'App\\Models\\Factura' => 'facturas',
                'App\\Models\\NotaCredito' => 'nota_creditos',
                'App\\Models\\NotaDebito' => 'nota_debitos',
                'App\\Models\\Retencion' => 'retenciones',
                'App\\Models\\RetencionAts' => 'retencion_ats',
                'App\\Models\\LiquidacionCompra' => 'liquidacion_compras',
                'App\\Models\\Guia' => 'guias',
            ];
            foreach ($comprobanteTypes as $type => $tabla) {
                if (Schema::hasTable($tabla)) {
                    $ids = DB::table($tabla)->where('emisor_id', $emisorId)->pluck('id');
                    if ($ids->isNotEmpty()) {
                        if (Schema::hasTable('campos_adicionales')) {
                            DB::table('campos_adicionales')->where('comprobante_type', $type)->whereIn('comprobante_id', $ids)->delete();
                        }
                        if (Schema::hasTable('mensajes')) {
                            DB::table('mensajes')->where('mensajeable_type', $type)->whereIn('mensajeable_id', $ids)->delete();
                        }
                    }
                }
            }

            // 2. Eliminar detalles e impuestos de cada comprobante
            $this->eliminarComprobante($emisorId, 'facturas', 'factura_id', ['factura_detalles', 'factura_reembolsos', 'info_guia_remisiones']);
            $this->eliminarComprobante($emisorId, 'nota_creditos', 'nota_credito_id', ['nota_credito_detalles']);
            $this->eliminarComprobante($emisorId, 'nota_debitos', 'nota_debito_id', ['nota_debito_motivos']);
            $this->eliminarComprobante($emisorId, 'retenciones', 'retencion_id', ['retencion_impuestos']);
            $this->eliminarComprobante($emisorId, 'liquidacion_compras', 'liquidacion_compra_id', ['liquidacion_detalles', 'liquidacion_reembolsos']);
            $this->eliminarComprobante($emisorId, 'guias', 'guia_id', ['guia_detalles']);
            $this->eliminarComprobante($emisorId, 'proformas', 'proforma_id', ['proforma_detalles']);
            $this->eliminarComprobante($emisorId, 'compras', 'compra_id', ['compra_detalles']);

            // 3. Eliminar retencion_ats con sus hijos anidados (doc_sustento → desgloce)
            if (Schema::hasTable('retencion_ats')) {
                $ratIds = DB::table('retencion_ats')->where('emisor_id', $emisorId)->pluck('id');
                if ($ratIds->isNotEmpty() && Schema::hasTable('doc_sustento_retenciones')) {
                    $docSusIds = DB::table('doc_sustento_retenciones')->whereIn('retencion_ats_id', $ratIds)->pluck('id');
                    if ($docSusIds->isNotEmpty() && Schema::hasTable('desgloce_retenciones')) {
                        DB::table('desgloce_retenciones')->whereIn('doc_sustento_retencion_id', $docSusIds)->delete();
                    }
                    DB::table('doc_sustento_retenciones')->whereIn('retencion_ats_id', $ratIds)->delete();
                }
                DB::table('retencion_ats')->where('emisor_id', $emisorId)->delete();
            }

            // 4. Eliminar inventario y movimientos
            if (Schema::hasTable('movimiento_inventarios')) {
                DB::table('movimiento_inventarios')->where('emisor_id', $emisorId)->delete();
            }
            if (Schema::hasTable('inventarios')) {
                DB::table('inventarios')->where('emisor_id', $emisorId)->delete();
            }

            // 5. Eliminar carga_archivos con sus errores
            if (Schema::hasTable('carga_archivos')) {
                $cargaIds = DB::table('carga_archivos')->where('emisor_id', $emisorId)->pluck('id');
                if ($cargaIds->isNotEmpty() && Schema::hasTable('carga_errors')) {
                    DB::table('carga_errors')->whereIn('carga_archivo_id', $cargaIds)->delete();
                }
                DB::table('carga_archivos')->where('emisor_id', $emisorId)->delete();
            }

            // 6. Eliminar tablas simples con emisor_id
            $tablasSimples = [
                'transportistas', 'clientes', 'productos', 'emisor_suscripciones',
                'crm_notas', 'crm_historial_emails',
            ];
            foreach ($tablasSimples as $tabla) {
                if (Schema::hasTable($tabla)) {
                    DB::table($tabla)->where('emisor_id', $emisorId)->delete();
                }
            }

            // 7. Eliminar puntos de emision, establecimientos y unidades de negocio
            if (Schema::hasTable('establecimientos')) {
                $estIds = DB::table('establecimientos')->where('emisor_id', $emisorId)->pluck('id');
                if ($estIds->isNotEmpty() && Schema::hasTable('pto_emisiones')) {
                    DB::table('pto_emisiones')->whereIn('establecimiento_id', $estIds)->delete();
                }
                DB::table('establecimientos')->where('emisor_id', $emisorId)->delete();
            }
            if (Schema::hasTable('unidades_negocio')) {
                DB::table('unidades_negocio')->where('emisor_id', $emisorId)->delete();
            }

            // 8. Eliminar usuarios del emisor
            DB::table('users')->where('emisor_id', $emisorId)->delete();

            // 9. Eliminar el emisor
            $emisor->delete();
        });

        return redirect()->route('admin.emisores.index')
            ->with('success', 'El emisor ' . $emisor->ruc . ' y todos sus datos han sido eliminados permanentemente.');
    }

    /**
     * Consulta datos del SRI por RUC (AJAX).
     */
    public function consultarRuc(string $ruc): JsonResponse
    {
        if (strlen($ruc) !== 13) {
            return response()->json(['error' => 'El RUC debe tener 13 digitos.'], 422);
        }

        // Verificar si ya existe en el sistema
        $existe = Emisor::where('ruc', $ruc)->exists();
        if ($existe) {
            return response()->json(['error' => 'Ya existe un emisor con este RUC en el sistema.'], 409);
        }

        try {
            $datos = $this->sriRucService->consultar($ruc);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al conectar con el servicio de consulta. Verifique la conexion a internet del servidor.',
            ], 503);
        }

        if (!$datos) {
            return response()->json([
                'error' => 'No se encontraron datos para este RUC. Puede ingresar los datos manualmente.',
            ], 404);
        }

        return response()->json($datos);
    }

    protected function eliminarComprobante(int $emisorId, string $tablaPadre, string $fk, array $tablasHijas): void
    {
        if (!Schema::hasTable($tablaPadre)) return;

        $ids = DB::table($tablaPadre)->where('emisor_id', $emisorId)->pluck('id');
        if ($ids->isNotEmpty()) {
            foreach ($tablasHijas as $tablaHija) {
                if (Schema::hasTable($tablaHija)) {
                    DB::table($tablaHija)->whereIn($fk, $ids)->delete();
                }
            }
        }
        DB::table($tablaPadre)->where('emisor_id', $emisorId)->delete();
    }

    public function impersonar(Emisor $emisor): RedirectResponse
    {
        session(['impersonar_emisor_id' => $emisor->id]);

        Log::info('Impersonacion iniciada', [
            'admin_id' => auth()->id(),
            'admin_username' => auth()->user()->username ?? auth()->user()->email,
            'emisor_id' => $emisor->id,
            'emisor_ruc' => $emisor->ruc,
            'ip' => request()->ip(),
        ]);

        return redirect()->route('emisor.dashboard')
            ->with('info', "Accediendo como soporte al emisor: {$emisor->razon_social}");
    }

    public function dejarImpersonar(): RedirectResponse
    {
        $emisorId = session('impersonar_emisor_id');
        session()->forget('impersonar_emisor_id');

        Log::info('Impersonacion finalizada', [
            'admin_id' => auth()->id(),
            'admin_username' => auth()->user()->username ?? auth()->user()->email,
            'emisor_id' => $emisorId,
            'ip' => request()->ip(),
        ]);

        return redirect()->route('admin.emisores.index')
            ->with('success', 'Se ha dejado de impersonar al emisor.');
    }
}
