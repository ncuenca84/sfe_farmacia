<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaginaLegal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaginaLegalController extends Controller
{
    public function index(): View
    {
        $paginas = PaginaLegal::orderBy('titulo')->get();
        return view('admin.paginas-legales.index', compact('paginas'));
    }

    public function create(): View
    {
        return view('admin.paginas-legales.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'slug' => 'required|string|max:100|unique:paginas_legales,slug|regex:/^[a-z0-9\-]+$/',
            'contenido' => 'required|string',
            'activo' => 'boolean',
        ]);

        $validated['activo'] = $request->has('activo');

        PaginaLegal::create($validated);

        return redirect()->route('admin.paginas-legales.index')
            ->with('success', 'Página legal creada correctamente.');
    }

    public function edit(PaginaLegal $paginaLegale): View
    {
        return view('admin.paginas-legales.edit', ['pagina' => $paginaLegale]);
    }

    public function update(Request $request, PaginaLegal $paginaLegale): RedirectResponse
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'slug' => 'required|string|max:100|regex:/^[a-z0-9\-]+$/|unique:paginas_legales,slug,' . $paginaLegale->id,
            'contenido' => 'required|string',
            'activo' => 'boolean',
        ]);

        $validated['activo'] = $request->has('activo');

        $paginaLegale->update($validated);

        return redirect()->route('admin.paginas-legales.index')
            ->with('success', 'Página legal actualizada correctamente.');
    }

    public function destroy(PaginaLegal $paginaLegale): RedirectResponse
    {
        $paginaLegale->delete();

        return redirect()->route('admin.paginas-legales.index')
            ->with('success', 'Página legal eliminada correctamente.');
    }
}
