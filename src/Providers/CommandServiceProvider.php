<?php

namespace Shibuyakosuke\LaravelCrudGenerator\Providers;

use Illuminate\Support\ServiceProvider;
use Shibuyakosuke\LaravelCrudGenerator\Console\CrudCheckCommand;
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
        $this->app->singleton('command.crud.generate', function () {
            return new MakeCrudCommand();
        });

        $this->commands([
            'command.crud.generate',
        ]);

        $this->app->singleton('command.crud.check', function () {
            return new CrudCheckCommand();
        });

        $this->commands([
            'command.crud.check',
        ]);
    }

    public function provides()
    {
        return [
            'command.crud.generate',
            'command.crud.check'
        ];
    }
}
