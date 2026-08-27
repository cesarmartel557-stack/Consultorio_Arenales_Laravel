<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $customPublic = realpath(__DIR__.'/../../../public_html/turnos')
            ?: realpath(__DIR__.'/../../../public_html')
            ?: (file_exists(base_path('../public_html/turnos')) ? base_path('../public_html/turnos') : null)
            ?: (file_exists(base_path('../public_html')) ? base_path('../public_html') : null);

        if ($customPublic) {
            $this->app->usePublicPath($customPublic);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
