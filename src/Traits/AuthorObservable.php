<?php

namespace App\Traits;

use App\Models\User;
use App\Observers\AuthorObserver;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait AuthorObservable
{
    public static function bootAuthorObservable()
    {
        self::observe(AuthorObserver::class);
    }

    /**
     * created_by
     * @return BelongsTo
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')
            ->withDefault(function ($model) {
                $model->name = '----';
            });
    }

    /**
     * updated_by
     * @return BelongsTo
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by')
            ->withDefault(function ($model) {
                $model->name = '----';
            });
    }

    /**
     * deleted_by
     * @return BelongsTo
     */
    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by')
            ->withDefault(function ($model) {
                $model->name = '----';
            });
    }
}
