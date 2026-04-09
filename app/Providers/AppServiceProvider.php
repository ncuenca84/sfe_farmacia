<?php

namespace App\Providers;

use App\Models\ConfiguracionSitio;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        try {
            if (Schema::hasTable('configuraciones_sitio')) {
                View::share('nombreSitio', ConfiguracionSitio::nombreSitio());
            }
        } catch (\Exception $e) {
            // DB not available (migrations, artisan commands)
        }
    }
}
