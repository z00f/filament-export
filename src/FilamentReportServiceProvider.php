<?php

namespace z00f\FilamentReport;

use Filament\Facades\Filament;
use Illuminate\Support\ServiceProvider;

class FilamentReportServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/filament-report.php', 'filament-report');
    }

    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'filament-report');

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'filament-report');

        $this->publishes([
            __DIR__ . '/../config/filament-report.php' => config_path('filament-report.php'),
        ], 'config');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/filament-report'),
        ], 'views');

        Filament::serving(function () {
            Filament::registerScripts([__DIR__ . '/../resources/js/filament-report.js']);
            Filament::registerStyles([__DIR__ . '/../resources/css/filament-report.css']);
        });
    }
}
