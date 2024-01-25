<?php

namespace Polarwhite\LaravelGptArchitect;

use Illuminate\Support\ServiceProvider;
use Polarwhite\LaravelGptArchitect\Console\Commands\GenerateGptResourceCommand;

class LaravelGptArchitectServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/config.php' => config_path('gpt-architect.php'),
        ]);

        $this->mergeConfigFrom(
            __DIR__.'/../config/config.php', 'gpt-architect'
        );

        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateGptResourceCommand::class,
            ]);
        }
    }

    public function register()
    {
        // Binding things into the service container, if necessary
    }
}