<?php

namespace App\Http\Controllers\Emisor;

use App\Http\Controllers\Controller;
use App\Models\Establecimiento;
use App\Models\UnidadNegocio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EstablecimientoController extends Controller
{
    public function index(Request $request): View
    {
        $emisor = auth()->user()->emisor;

        $query = Establecimiento::with('unidadNegocio')->where('emisor_id', $emisor->id);

        if (auth()->user()->unidad_negocio_id) {
            $query->where('unidad_negocio_id', auth()->user()->unidad_negocio_id);
        }

        $establecimientos = $query->orderBy('codigo')->paginate(50);
        $tieneMultiplesUnidades = UnidadNegocio::where('emisor_id', $emisor->id)->count() > 1;

        return view('emisor.establecimientos.index', compact('establecimientos', 'tieneMultiplesUnidades'));
    }

    public function create(): View
    {
        $emisor = auth()->user()->emisor;
        $unidadesNegocio = UnidadNegocio::where('emisor_id', $emisor->id)->where('activo', true)->orderBy('nombre')->get();

        return view('emisor.establecimientos.create', compact('unidadesNegocio'));
    }

    public function store(Request $request): RedirectResponse
    {
        $emisor = auth()->user()->emisor;

        $validated = $request->validate([
            'codigo' => 'required|string|max:3',
            'direccion' => 'required|string|max:300',
            'nombre' => 'nullable|string|max:200',
            'activo' => 'boolean',
            'maneja_inventario' => 'boolean',
            'logo' => 'nullable|image|max:2048',
            'unidad_negocio_id' => 'nullable|exists:unidades_negocio,id',
        ]);

        $validated['emisor_id'] = $emisor->id;
        $validated['maneja_inventario'] = $request->boolean('maneja_inventario');

        // Si el usuario tiene unidad de negocio fija, usarla
        if (auth()->user()->unidad_negocio_id) {
            $validated['unidad_negocio_id'] = auth()->user()->unidad_negocio_id;
        }

        if ($request->hasFile('logo') && $emisor->dir_doc_autorizados) {
            $validated['logo_path'] = $this->guardarLogo($request, $emisor->dir_doc_autorizados);
        }
        unset($validated['logo']);

        Establecimiento::create($validated);

        return redirect()->route('emisor.configuracion.establecimientos.index')
            ->with('success', 'Establecimiento creado correctamente.');
    }

    public function show(Establecimiento $establecimiento): View
    {
        $this->autorizarAcceso($establecimiento);

        return view('emisor.establecimientos.show', compact('establecimiento'));
    }

    public function edit(Establecimiento $establecimiento): View
    {
        $this->autorizarAcceso($establecimiento);

        $emisor = auth()->user()->emisor;
        $unidadesNegocio = UnidadNegocio::where('emisor_id', $emisor->id)->where('activo', true)->orderBy('nombre')->get();

        return view('emisor.establecimientos.edit', compact('establecimiento', 'unidadesNegocio'));
    }

    public function update(Request $request, Establecimiento $establecimiento): RedirectResponse
    {
        $this->autorizarAcceso($establecimiento);

        $validated = $request->validate([
            'codigo' => 'required|string|max:3',
            'direccion' => 'required|string|max:300',
            'nombre' => 'nullable|string|max:200',
            'activo' => 'boolean',
            'maneja_inventario' => 'boolean',
            'logo' => 'nullable|image|max:2048',
            'unidad_negocio_id' => 'nullable|exists:unidades_negocio,id',
        ]);

        $validated['maneja_inventario'] = $request->boolean('maneja_inventario');

        $emisor = auth()->user()->emisor;

        // Si el usuario tiene unidad de negocio fija, no permitir cambiarla
        if (auth()->user()->unidad_negocio_id) {
            unset($validated['unidad_negocio_id']);
        }

        // Quitar logo existente
        if ($request->input('quitar_logo') === '1' && !$request->hasFile('logo')) {
            $validated['logo_path'] = null;
        }

        // Subir nuevo logo
        if ($request->hasFile('logo') && $emisor->dir_doc_autorizados) {
            $validated['logo_path'] = $this->guardarLogo($request, $emisor->dir_doc_autorizados);
        }
        unset($validated['logo']);

        $establecimiento->update($validated);

        return redirect()->route('emisor.configuracion.establecimientos.index')
            ->with('success', 'Establecimiento actualizado correctamente.');
    }

    public function destroy(Establecimiento $establecimiento): RedirectResponse
    {
        $this->autorizarAcceso($establecimiento);
        $establecimiento->delete();

        return redirect()->route('emisor.configuracion.establecimientos.index')
            ->with('success', 'Establecimiento eliminado.');
    }

    private function guardarLogo(Request $request, string $dirBase): string
    {
        $dirLogos = $dirBase . '/logos';
        if (!is_dir($dirLogos)) {
            mkdir($dirLogos, 0775, true);
        }
        $archivo = $request->file('logo');
        $nombre = 'logo_est_' . time() . '.' . ($archivo->getClientOriginalExtension() ?: 'png');
        $archivo->move($dirLogos, $nombre);

        return $dirLogos . '/' . $nombre;
    }

    private function autorizarAcceso(Establecimiento $establecimiento): void
    {
        $user = auth()->user();
        if (!$user->esAdmin() && $establecimiento->emisor_id !== $user->emisor_id) {
            abort(403);
        }
    }
}
