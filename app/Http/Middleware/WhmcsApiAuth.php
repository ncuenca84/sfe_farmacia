<?php

namespace App\Http\Middleware;

use App\Models\WhmcsConfig;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class WhmcsApiAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->header('X-WHMCS-Key');

        if (!$key || !WhmcsConfig::where('api_key', $key)->where('activo', true)->exists()) {
            return response()->json(['error' => 'No autorizado'], 401);
        }

        return $next($request);
    }
}
