<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;

class AuthorObserver
{

    /**
     * Batch
     */
    const BATCH_USER = 1;

    /**
     * @var int|null
     */
    protected $id = null;

    /**
     * AuthorObserver constructor.
     */
    public function __construct()
    {
        $this->id = !is_null(\Auth::user()) ? \Auth::id() : self::BATCH_USER;
    }

    public function hasColumn(Model $model, string $column_name)
    {
        return \Schema::hasColumn($model->getTable(), $column_name);
    }

    /**
     * @param Model $model
     */
    public function creating(Model $model): void
    {
        if ($this->hasColumn($model, 'created_by')) {
            $model->created_by = $this->id;
        }
    }

    /**
     * @param Model $model
     */
    public function saving(Model $model): void
    {
        if ($this->hasColumn($model, 'updated_by')) {
            $model->updated_by = $this->id;
        }
    }

    /**
     * @param Model $model
     */
    public function deleting(Model $model): void
    {
        if ($this->hasColumn($model, 'deleted_by')) {
            $model->deleted_by = $this->id;
        }
    }

    /**
     * @param Model $model
     */
    public function restoring(Model $model): void
    {
        if ($this->hasColumn($model, 'deleted_by')) {
            $model->deleted_by = null;
        }
    }
}
