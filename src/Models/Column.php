<?php

namespace Shibuyakosuke\LaravelCrudGenerator\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Shibuyakosuke\LaravelCrudGenerator\Scopes\OwnScope;

/**
 * Class Column
 * @package Shibuyakosuke\LaravelCrudGenerator\Models
 */
class Column extends Model
{
    /**
     * @var string
     */
    protected $table = 'INFORMATION_SCHEMA.COLUMNS';

    protected $appends = [
        'is_required'
    ];

    /**
     * boot
     */
    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new OwnScope());
    }

    /**
     * @return bool
     */
    public function getIsPrimaryKeyAttribute()
    {
        return $this->COLUMN_KEY === 'PRI';
    }

    /**
     * @return bool
     */
    public function getIsUniqueAttribute()
    {
        return in_array($this->COLUMN_KEY, ['PRI', 'UNI']);
    }

    /**
     * @return bool
     */
    public function getIsIndexAttribute()
    {
        return in_array($this->COLUMN_KEY, ['PRI', 'UNI', 'MUL']);
    }

    public function getIsRequiredAttribute()
    {
        return $this->IS_NULLABLE === 'NO';
    }

    /**
     * @return BelongsTo
     */
    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class, 'TABLE_NAME', 'TABLE_NAME');
    }
}
