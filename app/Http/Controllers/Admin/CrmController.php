<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\CrmNotificacionMail;
use App\Models\CrmHistorialEmail;
use App\Models\CrmNota;
use App\Models\CrmNotificacion;
use App\Models\Emisor;
use App\Models\EmisorSuscripcion;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class CrmController extends Controller
{
    /**
     * Dashboard CRM - Resumen general.
     */
    public function index(): View
    {
        $totalClientes = Emisor::count();
        $clientesActivos = Emisor::where('activo', true)->count();
        $clientesInactivos = Emisor::where('activo', false)->count();

        // Suscripciones por vencer en 15 días
        $porVencer = EmisorSuscripcion::with('emisor', 'plan')
            ->where('estado', 'ACTIVA')
            ->whereBetween('fecha_fin', [now(), now()->addDays(15)])
            ->orderBy('fecha_fin')
            ->get();

        // Firmas por vencer en 30 días
        $firmasPorVencer = Emisor::where('activo', true)
            ->whereNotNull('firma_vigencia')
            ->whereBetween('firma_vigencia', [now(), now()->addDays(30)])
            ->orderBy('firma_vigencia')
            ->get();

        // Firmas vencidas
        $firmasVencidas = Emisor::where('activo', true)
            ->whereNotNull('firma_vigencia')
            ->where('firma_vigencia', '<', now())
            ->orderBy('firma_vigencia')
            ->get();

        // Emisores sin firma
        $sinFirma = Emisor::where('activo', true)
            ->whereNull('firma_path')
            ->get();

        // Últimas notificaciones
        $ultimasNotificaciones = CrmNotificacion::with('creadoPor')
            ->latest()
            ->limit(5)
            ->get();

        return view('admin.crm.index', compact(
            'totalClientes', 'clientesActivos', 'clientesInactivos',
            'porVencer', 'firmasPorVencer', 'firmasVencidas', 'sinFirma',
            'ultimasNotificaciones'
        ));
    }

    /**
     * Listado de clientes (emisores) con datos de contacto y suscripcion.
     */
    public function clientes(Request $request): View
    {
        $query = Emisor::with(['suscripcionActiva.plan', 'users']);

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->where('ruc', 'like', "%{$buscar}%")
                    ->orWhere('razon_social', 'like', "%{$buscar}%")
                    ->orWhere('celular', 'like', "%{$buscar}%");
            });
        }

        $emisores = $query->orderBy('razon_social')->paginate(50)->appends($request->only('buscar'));

        return view('admin.crm.clientes', compact('emisores'));
    }

    /**
     * Gestión de firmas electrónicas.
     */
    public function firmas(): View
    {
        $emisores = Emisor::where('activo', true)
            ->orderByRaw('CASE
                WHEN firma_vigencia IS NULL THEN 3
                WHEN firma_vigencia < NOW() THEN 1
                WHEN firma_vigencia < DATE_ADD(NOW(), INTERVAL 30 DAY) THEN 2
                ELSE 4
            END')
            ->orderBy('firma_vigencia')
            ->paginate(50);

        return view('admin.crm.firmas', compact('emisores'));
    }

    /**
     * Gestión de suscripciones y suspensiones.
     */
    public function suscripciones(Request $request): View
    {
        $filtro = $request->get('estado', 'todas');

        $query = EmisorSuscripcion::with('emisor', 'plan')->latest();

        if ($filtro === 'activas') {
            $query->where('estado', 'ACTIVA');
        } elseif ($filtro === 'vencidas') {
            $query->where('estado', 'VENCIDA');
        } elseif ($filtro === 'suspendidas') {
            $query->where('estado', 'SUSPENDIDA');
        }

        $suscripciones = $query->paginate(50)->appends(['estado' => $filtro]);

        return view('admin.crm.suscripciones', compact('suscripciones', 'filtro'));
    }

    /**
     * Suspender una suscripción.
     */
    public function suspender(Request $request, EmisorSuscripcion $suscripcion): RedirectResponse
    {
        $request->validate(['motivo' => 'required|string|max:500']);

        $suscripcion->update(['estado' => 'SUSPENDIDA']);

        CrmNota::create([
            'emisor_id' => $suscripcion->emisor_id,
            'contenido' => 'Suscripción suspendida. Motivo: ' . $request->motivo,
            'creado_por' => Auth::id(),
        ]);

        return back()->with('success', 'Suscripción suspendida correctamente.');
    }

    /**
     * Reactivar una suscripción suspendida.
     */
    public function reactivar(EmisorSuscripcion $suscripcion): RedirectResponse
    {
        $suscripcion->update(['estado' => 'ACTIVA']);

        CrmNota::create([
            'emisor_id' => $suscripcion->emisor_id,
            'contenido' => 'Suscripción reactivada.',
            'creado_por' => Auth::id(),
        ]);

        return back()->with('success', 'Suscripción reactivada correctamente.');
    }

    /**
     * Listado de notificaciones.
     */
    public function notificaciones(): View
    {
        $notificaciones = CrmNotificacion::with('creadoPor')->latest()->paginate(30);
        return view('admin.crm.notificaciones.index', compact('notificaciones'));
    }

    /**
     * Formulario para crear notificación.
     */
    public function crearNotificacion(): View
    {
        $emisores = Emisor::where('activo', true)->orderBy('razon_social')->get();
        return view('admin.crm.notificaciones.crear', compact('emisores'));
    }

    /**
     * Enviar notificación masiva por email.
     */
    public function enviarNotificacion(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'asunto' => 'required|string|max:255',
            'mensaje' => 'required|string',
            'tipo' => 'required|in:MANUAL,PROMOCION,ALERTA_PLAN,ALERTA_FIRMA,GENERAL',
            'destinatarios' => 'required|in:TODOS,ACTIVOS,INACTIVOS,VENCIDOS,SELECCIONADOS',
            'emisor_ids' => 'nullable|array',
            'emisor_ids.*' => 'exists:emisores,id',
        ]);

        $notificacion = CrmNotificacion::create([
            'asunto' => $validated['asunto'],
            'mensaje' => $validated['mensaje'],
            'tipo' => $validated['tipo'],
            'destinatarios' => $validated['destinatarios'],
            'emisor_ids' => $validated['destinatarios'] === 'SELECCIONADOS' ? ($validated['emisor_ids'] ?? []) : null,
            'creado_por' => Auth::id(),
        ]);

        // Obtener emisores destino
        $emisores = $this->resolverDestinatarios($validated['destinatarios'], $validated['emisor_ids'] ?? []);

        $enviados = 0;
        $fallidos = 0;

        foreach ($emisores as $emisor) {
            $email = $emisor->mail_from_address ?? $emisor->users()->first()?->email;
            if (!$email) {
                $fallidos++;
                CrmHistorialEmail::create([
                    'emisor_id' => $emisor->id,
                    'notificacion_id' => $notificacion->id,
                    'email_destino' => 'N/A',
                    'asunto' => $validated['asunto'],
                    'estado' => 'FALLIDO',
                    'error' => 'Sin email configurado',
                ]);
                continue;
            }

            try {
                Mail::to($email)->send(new CrmNotificacionMail(
                    $validated['asunto'],
                    $validated['mensaje']
                ));

                CrmHistorialEmail::create([
                    'emisor_id' => $emisor->id,
                    'notificacion_id' => $notificacion->id,
                    'email_destino' => $email,
                    'asunto' => $validated['asunto'],
                    'estado' => 'ENVIADO',
                ]);
                $enviados++;
            } catch (\Exception $e) {
                CrmHistorialEmail::create([
                    'emisor_id' => $emisor->id,
                    'notificacion_id' => $notificacion->id,
                    'email_destino' => $email,
                    'asunto' => $validated['asunto'],
                    'estado' => 'FALLIDO',
                    'error' => $e->getMessage(),
                ]);
                $fallidos++;
            }
        }

        $notificacion->update([
            'estado' => 'ENVIADA',
            'enviados' => $enviados,
            'fallidos' => $fallidos,
            'enviada_at' => now(),
        ]);

        return redirect()->route('admin.crm.notificaciones')
            ->with('success', "Notificación enviada. Exitosos: {$enviados}, Fallidos: {$fallidos}.");
    }

    /**
     * Ver detalle de una notificación.
     */
    public function verNotificacion(CrmNotificacion $notificacion): View
    {
        $notificacion->load(['creadoPor', 'historialEmails.emisor']);
        return view('admin.crm.notificaciones.ver', compact('notificacion'));
    }

    /**
     * Historial de comunicaciones por emisor.
     */
    public function historialEmisor(Emisor $emisor): View
    {
        $emisor->load(['suscripcionActiva.plan', 'suscripciones.plan']);
        $historial = CrmHistorialEmail::where('emisor_id', $emisor->id)->latest()->paginate(20);
        $notas = CrmNota::where('emisor_id', $emisor->id)->with('creadoPor')->latest()->get();

        return view('admin.crm.emisor-historial', compact('emisor', 'historial', 'notas'));
    }

    /**
     * Agregar nota a un emisor.
     */
    public function agregarNota(Request $request, Emisor $emisor): RedirectResponse
    {
        $request->validate(['contenido' => 'required|string|max:2000']);

        CrmNota::create([
            'emisor_id' => $emisor->id,
            'contenido' => $request->contenido,
            'creado_por' => Auth::id(),
        ]);

        return back()->with('success', 'Nota agregada.');
    }

    /**
     * Resolver qué emisores reciben la notificación.
     */
    private function resolverDestinatarios(string $tipo, array $ids = []): \Illuminate\Support\Collection
    {
        return match ($tipo) {
            'TODOS' => Emisor::all(),
            'ACTIVOS' => Emisor::where('activo', true)->get(),
            'INACTIVOS' => Emisor::where('activo', false)->get(),
            'VENCIDOS' => Emisor::whereHas('suscripciones', fn ($q) => $q->where('estado', 'VENCIDA')->latest()->limit(1))
                ->whereDoesntHave('suscripciones', fn ($q) => $q->where('estado', 'ACTIVA'))
                ->get(),
            'SELECCIONADOS' => Emisor::whereIn('id', $ids)->get(),
            default => collect(),
        };
    }
}
