<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EmisorActivo
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Admin impersonando un emisor
        if ($user && $user->esAdmin() && session('impersonar_emisor_id')) {
            $emisor = \App\Models\Emisor::find(session('impersonar_emisor_id'));
            if ($emisor) {
                $user->setRelation('emisor', $emisor);
                $user->emisor_id = $emisor->id;
            } else {
                session()->forget('impersonar_emisor_id');
            }
        }

        if ($user && !$user->esAdmin() && $user->emisor && !$user->emisor->activo) {
            return redirect()->route('login')
                ->withErrors(['username' => 'Su cuenta de emisor está suspendida. Contacte al administrador.']);
        }

        return $next($request);
    }
}
