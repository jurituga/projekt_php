<?php

namespace App\Providers;

use App\Support\ConfigureVacantoFilesystem;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->booting(function () {
            ConfigureVacantoFilesystem::apply();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ConfigureVacantoFilesystem::apply();
    }
}
