<?php

namespace Kerroline\PhpGoExcel\Providers;

use Illuminate\Support\ServiceProvider;


class PhpGoExcelServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/config.php' => config_path('php-go-excel.php'),
            //   __DIR__ . '/translations/' => resource_path(),
        ]);


        // if ($this->app->runningInConsole()) {
        //   $this->commands([
        //     LogMakeCommand::class,
        //     LoggerMakeCommand::class,
        //     LoggingSetupCommand::class,
        //   ]);
        // }
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config/config.php', 'php-go-excel');
    }
}
