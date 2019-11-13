<?php

namespace Shibuyakosuke\LaravelCrudGenerator\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Shibuyakosuke\LaravelCrudGenerator\Scopes\OwnScope;

/**
 * Class Table
 * @package Shibuyakosuke\LaravelCrudGenerator\Models
 */
class Table extends Model
{
    /**
     * @var string
     */
    protected $table = 'INFORMATION_SCHEMA.TABLES';

    protected $appends = [
        'model'
    ];

    public static function getModelName($table_name)
    {
        return Str::studly(Str::singular($table_name));
    }

    public function getPrimaryKeyAttribute()
    {
        $column = $this->columns->filter(function (Column $column) {
            return $column->COLUMN_KEY === 'PRI';
        })->first();

        if ($column) {
            return $column->COLUMN_NAME;
        }
        return null;
    }

    /**
     * @return string
     */
    public function getModelAttribute()
    {
        return Str::studly(Str::singular($this->TABLE_NAME));
    }

    /**
     * @return bool
     */
    public function hasCreatedBy(): bool
    {
        return $this->columns->pluck('COLUMN_NAME')->contains('created_by');
    }

    /**
     * @return bool
     */
    public function hasUpdatedBy(): bool
    {
        return $this->columns->pluck('COLUMN_NAME')->contains('updated_by');
    }

    /**
     * @return bool
     */
    public function hasDeletedBy(): bool
    {
        return $this->columns->pluck('COLUMN_NAME')->contains('deleted_by');
    }

    /**
     * boot
     */
    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new OwnScope());
    }

    /**
     * @return HasMany
     */
    public function columns(): HasMany
    {
        return $this->hasMany(Column::class, 'TABLE_NAME', 'TABLE_NAME');
    }

    /**
     * @return HasMany
     */
    public function constraints(): HasMany
    {
        return $this->hasMany(KeyColumnUsage::class, 'TABLE_NAME', 'TABLE_NAME')
            ->whereNotNull('REFERENCED_TABLE_NAME')
            ->whereNotNull('REFERENCED_COLUMN_NAME');
    }

    /**
     * @return HasMany
     */
    public function references()
    {
        return $this->hasMany(KeyColumnUsage::class, 'REFERENCED_TABLE_NAME', 'TABLE_NAME');
    }
}
