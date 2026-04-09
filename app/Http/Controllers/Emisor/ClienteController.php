<?php

namespace App\Http\Controllers\Emisor;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Services\SriRucService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClienteController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();

        $query = $this->queryClientes($user);

        if ($request->filled('buscar')) {
            $query->where(function ($q) use ($request) {
                $q->where('razon_social', 'like', "%{$request->buscar}%")
                    ->orWhere('identificacion', 'like', "%{$request->buscar}%")
                    ->orWhere('email', 'like', "%{$request->buscar}%");
            });
        }

        $clientes = $query->orderBy('razon_social')->paginate(50);

        return view('emisor.clientes.index', compact('clientes'));
    }

    public function create(): View
    {
        return view('emisor.clientes.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'tipo_identificacion' => 'required|string|max:2',
            'identificacion' => 'required|string|max:20',
            'razon_social' => 'required|string|max:300',
            'direccion' => 'nullable|string|max:300',
            'email' => ['nullable', 'string', 'max:500', function ($attribute, $value, $fail) {
                if ($value) {
                    foreach (preg_split('/\s*,\s*/', $value) as $email) {
                        if ($email && !filter_var(trim($email), FILTER_VALIDATE_EMAIL)) {
                            $fail("El email '$email' no es valido.");
                        }
                    }
                }
            }],
            'telefono' => 'nullable|string|max:20',
        ]);

        $validated['emisor_id'] = $user->emisor_id;

        if ($user->unidad_negocio_id) {
            $validated['unidad_negocio_id'] = $user->unidad_negocio_id;
        }

        Cliente::create($validated);

        return redirect()->route('emisor.configuracion.clientes.index')
            ->with('success', 'Cliente creado correctamente.');
    }

    public function show(Cliente $cliente): View
    {
        $this->autorizarAcceso($cliente);

        return view('emisor.clientes.show', compact('cliente'));
    }

    public function edit(Cliente $cliente): View
    {
        $this->autorizarAcceso($cliente);

        return view('emisor.clientes.edit', compact('cliente'));
    }

    public function update(Request $request, Cliente $cliente): RedirectResponse
    {
        $this->autorizarAcceso($cliente);

        $validated = $request->validate([
            'tipo_identificacion' => 'required|string|max:2',
            'identificacion' => 'required|string|max:20',
            'razon_social' => 'required|string|max:300',
            'direccion' => 'nullable|string|max:300',
            'email' => ['nullable', 'string', 'max:500', function ($attribute, $value, $fail) {
                if ($value) {
                    foreach (preg_split('/\s*,\s*/', $value) as $email) {
                        if ($email && !filter_var(trim($email), FILTER_VALIDATE_EMAIL)) {
                            $fail("El email '$email' no es valido.");
                        }
                    }
                }
            }],
            'telefono' => 'nullable|string|max:20',
        ]);

        $cliente->update($validated);

        return redirect()->route('emisor.configuracion.clientes.index')
            ->with('success', 'Cliente actualizado correctamente.');
    }

    public function destroy(Cliente $cliente): RedirectResponse
    {
        $this->autorizarAcceso($cliente);
        $cliente->delete();

        return redirect()->route('emisor.configuracion.clientes.index')
            ->with('success', 'Cliente eliminado.');
    }

    public function buscar(Request $request): JsonResponse
    {
        $user = auth()->user();
        $perPage = (int) ($request->per_page ?? 10);

        $query = $this->queryClientes($user)->orderBy('razon_social');

        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('razon_social', 'like', "%{$request->q}%")
                    ->orWhere('identificacion', 'like', "%{$request->q}%");
            });
        }

        $clientes = $query->paginate($perPage, ['id', 'identificacion', 'razon_social', 'email', 'direccion']);

        return response()->json($clientes);
    }

    public function apiStore(Request $request): JsonResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'tipo_identificacion' => 'required|string|max:2',
            'identificacion' => 'required|string|max:20',
            'razon_social' => 'required|string|max:300',
            'direccion' => 'nullable|string|max:300',
            'email' => ['nullable', 'string', 'max:500', function ($attribute, $value, $fail) {
                if ($value) {
                    foreach (preg_split('/\s*,\s*/', $value) as $email) {
                        if ($email && !filter_var(trim($email), FILTER_VALIDATE_EMAIL)) {
                            $fail("El email '$email' no es valido.");
                        }
                    }
                }
            }],
            'telefono' => 'nullable|string|max:20',
        ]);

        $validated['emisor_id'] = $user->emisor_id;

        if ($user->unidad_negocio_id) {
            $validated['unidad_negocio_id'] = $user->unidad_negocio_id;
        }

        $cliente = Cliente::create($validated);

        return response()->json($cliente);
    }

    private function queryClientes($user)
    {
        if ($user->unidad_negocio_id) {
            return Cliente::where('unidad_negocio_id', $user->unidad_negocio_id);
        }

        return Cliente::where('emisor_id', $user->emisor_id);
    }

    public function consultarIdentificacion(string $identificacion, SriRucService $sriRucService): JsonResponse
    {
        $identificacion = preg_replace('/[^0-9]/', '', $identificacion);
        $esCedula = strlen($identificacion) === 10;

        if ($esCedula) {
            $identificacion .= '001';
        }

        if (strlen($identificacion) !== 13) {
            return response()->json(['error' => 'La identificacion debe tener 10 (cedula) o 13 (RUC) digitos.'], 422);
        }

        try {
            $datos = $sriRucService->consultar($identificacion);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al conectar con el servicio del SRI.'], 503);
        }

        if (!$datos) {
            $mensaje = $esCedula
                ? 'No se encontraron datos. La persona puede no tener RUC registrado en el SRI.'
                : 'No se encontraron datos para este RUC en el SRI.';
            return response()->json(['error' => $mensaje], 404);
        }

        return response()->json([
            'razon_social' => $datos['razon_social'] ?? null,
            'direccion' => $datos['direccion'] ?? null,
        ]);
    }

    private function autorizarAcceso(Cliente $cliente): void
    {
        $user = auth()->user();
        if (!$user->esAdmin() && $cliente->emisor_id !== $user->emisor_id) {
            abort(403);
        }
    }
}
