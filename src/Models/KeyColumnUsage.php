<?php

namespace Shibuyakosuke\LaravelCrudGenerator\Models;

use Illuminate\Database\Eloquent\Model;
use Shibuyakosuke\LaravelCrudGenerator\Scopes\OwnScope;

/**
 * Class KeyColumnUsage
 * @package Shibuyakosuke\LaravelCrudGenerator\Models
 */
class KeyColumnUsage extends Model
{
    protected $connection;

//    protected $primaryKey = ['COLUMN_NAME', 'TABLE_NAME'];

    /**
     * @var string
     */
    protected $table = 'INFORMATION_SCHEMA.KEY_COLUMN_USAGE';

    /**
     * boot
     */
    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new OwnScope());
    }

    public function table()
    {
        return $this->belongsTo(Table::class, 'TABLE_NAME', 'TABLE_NAME');
    }
}
