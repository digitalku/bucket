<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.app', function (\Illuminate\View\View $view): void {
            $view->getFactory()->startPush(
                'styles',
                '<meta name="application-name" content="Copyright Digitalku">' . PHP_EOL
                . '    <meta name="creator" content="https://www.digitalku.com">' . PHP_EOL
            );
        });
    }
}
