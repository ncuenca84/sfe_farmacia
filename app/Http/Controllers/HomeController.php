<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

class HomeController extends Controller
{
    public function __invoke(): RedirectResponse
    {
        if (auth()->check()) {
            $user = auth()->user();
            if ($user->esAdmin()) {
                return redirect()->route('admin.dashboard');
            }
            return redirect()->route('emisor.dashboard');
        }
        return redirect()->route('login');
    }
}
