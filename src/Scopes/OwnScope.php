<?php

namespace Shibuyakosuke\LaravelCrudGenerator\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Shibuyakosuke\LaravelCrudGenerator\Models\Column;
use Shibuyakosuke\LaravelCrudGenerator\Models\KeyColumnUsage;
use Shibuyakosuke\LaravelCrudGenerator\Models\Table;

/**
 * Class OwnScope
 * @package Shibuyakosuke\LaravelCrudGenerator\Scopes
 */
class OwnScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param Builder $builder
     * @param Model $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        $class = get_class($model);
        if (!in_array($class, [Table::class, Column::class, KeyColumnUsage::class])) {
            return;
        }
        $builder->where('TABLE_SCHEMA', \app('db.connection')->getDatabaseName())
            ->where('TABLE_NAME', '<>', 'migrations');
    }
}
