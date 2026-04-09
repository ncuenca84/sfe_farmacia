<?php

namespace App\Http\Controllers\Emisor;

use App\Http\Controllers\Controller;
use App\Models\CategoriaProducto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoriaProductoController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();
        $query = CategoriaProducto::where('emisor_id', $user->emisor_id);

        if ($request->filled('buscar')) {
            $query->where('nombre', 'like', "%{$request->buscar}%");
        }

        $categorias = $query->withCount('productos')->orderBy('nombre')->paginate(50);

        return view('emisor.farmacia.categorias.index', compact('categorias'));
    }

    public function create(): View
    {
        return view('emisor.farmacia.categorias.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'nombre' => 'required|string|max:150',
            'activo' => 'boolean',
        ]);

        $validated['emisor_id'] = $user->emisor_id;
        $validated['activo'] = $request->boolean('activo', true);

        CategoriaProducto::create($validated);

        return redirect()->route('emisor.farmacia.categorias.index')
            ->with('success', 'Categoria creada correctamente.');
    }

    public function edit(CategoriaProducto $categoria): View
    {
        $this->autorizarAcceso($categoria);

        return view('emisor.farmacia.categorias.edit', compact('categoria'));
    }

    public function update(Request $request, CategoriaProducto $categoria): RedirectResponse
    {
        $this->autorizarAcceso($categoria);

        $validated = $request->validate([
            'nombre' => 'required|string|max:150',
            'activo' => 'boolean',
        ]);

        $validated['activo'] = $request->boolean('activo', true);

        $categoria->update($validated);

        return redirect()->route('emisor.farmacia.categorias.index')
            ->with('success', 'Categoria actualizada correctamente.');
    }

    public function destroy(CategoriaProducto $categoria): RedirectResponse
    {
        $this->autorizarAcceso($categoria);

        if ($categoria->productos()->exists()) {
            return redirect()->route('emisor.farmacia.categorias.index')
                ->with('error', 'No se puede eliminar una categoria con productos asociados.');
        }

        $categoria->delete();

        return redirect()->route('emisor.farmacia.categorias.index')
            ->with('success', 'Categoria eliminada.');
    }

    private function autorizarAcceso(CategoriaProducto $categoria): void
    {
        $user = auth()->user();
        if (!$user->esAdmin() && $categoria->emisor_id !== $user->emisor_id) {
            abort(403);
        }
    }
}
