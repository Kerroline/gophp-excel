<?php

namespace Kerroline\PhpGoExcel\Providers;

use Illuminate\Support\ServiceProvider;
use Kerroline\PhpGoExcel\Commands\SetupCommand;

class PhpGoExcelServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/config.php' => config_path('php-go-excel.php'),
            ]);
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/config.php', 'php-go-excel');
    }
}
