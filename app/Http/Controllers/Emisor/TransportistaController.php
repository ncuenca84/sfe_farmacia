<?php

namespace App\Http\Controllers\Emisor;

use App\Http\Controllers\Controller;
use App\Models\Transportista;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransportistaController extends Controller
{
    public function buscar(Request $request): JsonResponse
    {
        $user = auth()->user();
        $perPage = (int) ($request->per_page ?? 10);

        $query = $this->queryTransportistas($user)->orderBy('razon_social');

        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('razon_social', 'like', "%{$request->q}%")
                    ->orWhere('identificacion', 'like', "%{$request->q}%");
            });
        }

        $transportistas = $query->paginate($perPage, ['id', 'identificacion', 'razon_social', 'placa', 'email', 'telefono']);

        return response()->json($transportistas);
    }

    public function apiStore(Request $request): JsonResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'tipo_identificacion' => 'required|string|max:2',
            'identificacion' => 'required|string|max:20',
            'razon_social' => 'required|string|max:300',
            'placa' => 'nullable|string|max:20',
            'email' => 'nullable|string|email|max:255',
            'telefono' => 'nullable|string|max:20',
        ]);

        $validated['emisor_id'] = $user->emisor_id;

        if ($user->unidad_negocio_id) {
            $validated['unidad_negocio_id'] = $user->unidad_negocio_id;
        }

        $transportista = Transportista::create($validated);

        return response()->json($transportista);
    }

    private function queryTransportistas($user)
    {
        if ($user->unidad_negocio_id) {
            return Transportista::where('unidad_negocio_id', $user->unidad_negocio_id);
        }

        return Transportista::where('emisor_id', $user->emisor_id);
    }
}
