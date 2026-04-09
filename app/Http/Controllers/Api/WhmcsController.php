<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Emisor;
use App\Models\Plan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WhmcsController extends Controller
{
    public function crear(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ruc' => 'required|string|max:13',
            'razon_social' => 'required|string|max:300',
            'email' => 'required|string|email|max:255',
            'plan_id' => 'required|exists:planes,id',
            'nombre_comercial' => 'nullable|string|max:300',
            'direccion_matriz' => 'nullable|string|max:300',
        ]);

        // TODO: crear emisor y usuario via servicio
        return response()->json([
            'success' => true,
            'message' => 'Emisor creado correctamente.',
            'data' => [
                'emisor_id' => null,
                'usuario_email' => $validated['email'],
            ],
        ], 201);
    }

    public function suspender(int $id): JsonResponse
    {
        $emisor = Emisor::findOrFail($id);
        $emisor->update(['estado' => 'suspendido']);

        return response()->json([
            'success' => true,
            'message' => 'Emisor suspendido correctamente.',
        ]);
    }

    public function reactivar(int $id): JsonResponse
    {
        $emisor = Emisor::findOrFail($id);
        $emisor->update(['estado' => 'activo']);

        return response()->json([
            'success' => true,
            'message' => 'Emisor reactivado correctamente.',
        ]);
    }

    public function renovar(int $id, Request $request): JsonResponse
    {
        $emisor = Emisor::findOrFail($id);

        // TODO: renovar suscripción via servicio
        return response()->json([
            'success' => true,
            'message' => 'Suscripción renovada correctamente.',
        ]);
    }

    public function cambiarPlan(int $id, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:planes,id',
        ]);

        $emisor = Emisor::findOrFail($id);

        // TODO: cambiar plan via servicio
        return response()->json([
            'success' => true,
            'message' => 'Plan cambiado correctamente.',
        ]);
    }

    public function cancelar(int $id): JsonResponse
    {
        $emisor = Emisor::findOrFail($id);
        $emisor->update(['estado' => 'cancelado']);

        return response()->json([
            'success' => true,
            'message' => 'Servicio cancelado correctamente.',
        ]);
    }

    public function estado(int $id): JsonResponse
    {
        $emisor = Emisor::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $emisor->id,
                'ruc' => $emisor->ruc,
                'razon_social' => $emisor->razon_social,
                'estado' => $emisor->estado,
                'plan' => $emisor->plan?->nombre,
                'comprobantes_emitidos' => $emisor->comprobantes_emitidos ?? 0,
                'comprobantes_limite' => $emisor->plan?->limite_comprobantes,
            ],
        ]);
    }

    public function planes(): JsonResponse
    {
        $planes = Plan::where('activo', true)->get(['id', 'nombre', 'limite_comprobantes', 'precio']);

        return response()->json([
            'success' => true,
            'data' => $planes,
        ]);
    }
}
