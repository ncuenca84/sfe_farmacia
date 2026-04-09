<?php

use App\Http\Controllers\Api\WhmcsController;
use Illuminate\Support\Facades\Route;

// API WHMCS — protegida con API Key + rate limiting
Route::prefix('whmcs')->middleware(['whmcs.auth', 'throttle:60,1'])->group(function () {
    Route::post('/emisores', [WhmcsController::class, 'crear']);
    Route::post('/emisores/{id}/suspender', [WhmcsController::class, 'suspender']);
    Route::post('/emisores/{id}/reactivar', [WhmcsController::class, 'reactivar']);
    Route::post('/emisores/{id}/renovar', [WhmcsController::class, 'renovar']);
    Route::post('/emisores/{id}/cambiar-plan', [WhmcsController::class, 'cambiarPlan']);
    Route::post('/emisores/{id}/cancelar', [WhmcsController::class, 'cancelar']);
    Route::get('/emisores/{id}/estado', [WhmcsController::class, 'estado']);
    Route::get('/planes', [WhmcsController::class, 'planes']);
});
