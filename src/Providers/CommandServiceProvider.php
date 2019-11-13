<?php

namespace Shibuyakosuke\LaravelCrudGenerator\Providers;

use Illuminate\Support\ServiceProvider;
use Shibuyakosuke\LaravelCrudGenerator\Console\MakeCrudCommand;

/**
 * Class CommandServiceProvider
 * @package Shibuyakosuke\LaravelCrudGenerator\Providers
 */
class CommandServiceProvider extends ServiceProvider
{
    protected $defer = true;

    public function boot()
    {
        $this->registerCommands();
    }

    public function register()
    {
        // register bindings
    }

    protected function registerCommands()
    {
        $this->app->singleton('command.make.crud', function () {
            return new MakeCrudCommand();
        });

        $this->commands([
            'command.make.crud',
        ]);
    }

    public function provides()
    {
        return [
            'command.make.crud',
        ];
    }
}
