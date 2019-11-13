<?php

namespace Shibuyakosuke\LaravelCrudGenerator\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Class ResourceServiceProvider
 * @package Shibuyakosuke\LaravelCrudGenerator\Providers
 */
class ResourceServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/adminlte_crud.php', 'adminlte_crud'
        );

        $this->mergeConfigFrom(
            __DIR__ . '/../config/adminlte_menu.php', 'adminlte_menu'
        );

        $this->publishes([
            __DIR__ . '/../config/adminlte_crud.php' => config_path('adminlte_crud.php'),
            __DIR__ . '/../Observers' => app_path('Observers'),
            __DIR__ . '/../Traits' => app_path('Traits'),
        ]);
    }
}
