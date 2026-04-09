<?php

namespace App\Http\Controllers\Emisor;

use App\Enums\Ambiente;
use App\Http\Controllers\Controller;
use App\Models\Factura;
use App\Models\Guia;
use App\Models\Impuesto;
use App\Models\LiquidacionCompra;
use App\Models\NotaCredito;
use App\Models\NotaDebito;
use App\Models\Retencion;
use App\Services\EmisorService;
use App\Services\FirmaElectronicaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConfiguracionController extends Controller
{
    public function __construct(
        private EmisorService $emisorService,
        private FirmaElectronicaService $firmaElectronicaService,
    ) {}

    public function editEmisor(): View
    {
        $emisor = auth()->user()->emisor;

        return view('emisor.configuracion.emisor', compact('emisor'));
    }

    public function updateEmisor(Request $request): RedirectResponse
    {
        $emisor = auth()->user()->emisor;

        $validated = $request->validate([
            'razon_social' => 'required|string|max:300',
            'nombre_comercial' => 'nullable|string|max:300',
            'direccion_matriz' => 'required|string|max:300',
            'obligado_contabilidad' => 'required|in:0,1',
            'contribuyente_especial' => 'nullable|string|max:20',
            'agente_retencion' => 'nullable|string|max:10',
            'regimen' => 'required|in:GENERAL,RIMPE,NEGOCIO_POPULAR,EPS',
            'logo' => 'nullable|image|max:2048',
            'certificado_p12' => 'nullable|file|extensions:p12|max:5120',
            'clave_certificado' => 'nullable|string|max:255',
            'ambiente' => 'required|in:1,2',
            'mail_host' => 'nullable|string|max:100',
            'mail_port' => 'nullable|integer|between:1,65535',
            'mail_username' => 'nullable|string|max:150',
            'mail_password' => 'nullable|string',
            'mail_encryption' => 'nullable|in:ssl,tls',
            'mail_from_address' => 'nullable|email|max:150',
            'mail_from_name' => 'nullable|string|max:150',
        ]);

        $dirEmisor = $emisor->dir_doc_autorizados;

        if ($request->hasFile('logo') && $dirEmisor) {
            $dirLogos = $dirEmisor . '/logos';
            $this->emisorService->crearDirectorioEmisor($dirLogos);
            $archivo = $request->file('logo');
            $nombre = 'logo_' . time() . '.' . ($archivo->getClientOriginalExtension() ?: 'png');
            $archivo->move($dirLogos, $nombre);
            $validated['logo_path'] = $dirLogos . '/' . $nombre;
        }

        if ($request->hasFile('certificado_p12') && $dirEmisor) {
            $dirFirmas = $dirEmisor . '/firmas';
            $this->emisorService->crearDirectorioEmisor($dirFirmas);
            $archivo = $request->file('certificado_p12');
            $nombre = 'firma_' . time() . '.p12';
            $archivo->move($dirFirmas, $nombre);
            $validated['firma_path'] = $dirFirmas . '/' . $nombre;
        }

        if (!empty($validated['clave_certificado'])) {
            $validated['firma_password'] = $validated['clave_certificado'];
        }

        // Guardar referencia antes de limpiar para auto-registro CRM
        $firmaSubida = isset($validated['firma_path']);
        $firmaPath = $validated['firma_path'] ?? null;
        $firmaPassword = $validated['firma_password'] ?? null;

        if (empty($validated['mail_password'])) {
            unset($validated['mail_password']);
        }

        unset($validated['certificado_p12'], $validated['clave_certificado']);

        $emisor->update($validated);

        // Auto-registrar en gestión de firmas CRM
        if ($firmaSubida && $firmaPath && $firmaPassword) {
            $this->firmaElectronicaService->registrarDesdeP12($firmaPath, $firmaPassword, $emisor);
        }

        return back()->with('success', 'Datos del emisor actualizados correctamente.');
    }

    public function eliminarComprobantesPrueba(): RedirectResponse
    {
        $emisor = auth()->user()->emisor;

        abort_unless($emisor->ambiente === Ambiente::PRUEBAS, 403, 'Solo disponible en ambiente de pruebas.');

        $modelos = [Factura::class, NotaCredito::class, NotaDebito::class, Retencion::class, Guia::class, LiquidacionCompra::class];
        $total = 0;

        foreach ($modelos as $modelo) {
            $total += $modelo::where('emisor_id', $emisor->id)
                ->where('estado', '!=', 'ANULADA')
                ->delete();
        }

        return back()->with('success', "Se eliminaron {$total} comprobantes de prueba.");
    }

    public function impuestos(): View
    {
        $emisor = auth()->user()->emisor;

        $impuestos = Impuesto::orderBy('codigo')->get();

        return view('emisor.configuracion.impuestos', compact('emisor', 'impuestos'));
    }
}
