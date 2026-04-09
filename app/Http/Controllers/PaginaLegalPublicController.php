<?php

namespace App\Http\Controllers;

use App\Models\PaginaLegal;
use Illuminate\View\View;

class PaginaLegalPublicController extends Controller
{
    public function show(string $slug): View
    {
        $pagina = PaginaLegal::where('slug', $slug)->where('activo', true)->firstOrFail();
        return view('legal.show', compact('pagina'));
    }
}
