<?php

namespace DanilPetrenko\LaravelHelpers\Providers;

use DanilPetrenko\LaravelHelpers\Console\Commands\GenerateHelpersAutoload;

class LaravelHelpersServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {

            $this->publishes([
                __DIR__ . '/../../config/laravel-helpers.php' => config_path('laravel-helpers.php'),
            ]);

            $this->commands([
                GenerateHelpersAutoload::class,
            ]);
        }
    }
}