<?php

namespace Shibuyakosuke\LaravelCrudGenerator\Helpers;

use Illuminate\Support\Str;
use Shibuyakosuke\LaravelCrudGenerator\Models\Column;

class Show
{
    protected $column = null;
    protected $foreign_key = null;

    private $skip_columns = [
        'created_by',
        'updated_by',
        'deleted_by',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * Show constructor.
     * @param Column $column
     */
    public function __construct(Column $column)
    {
        $this->column = $column;
        $this->foreign_key = $column
            ->table
            ->constraints
            ->filter(function ($constraint) use ($column) {
                return $constraint->COLUMN_NAME === $column->COLUMN_NAME
                    && $constraint->REFERENCED_TABLE_NAME
                    && $constraint->REFERENCED_COLUMN_NAME;
            })->first();
    }

    public function get()
    {
        $table = $this->column->TABLE_NAME;
        $name = $this->column->COLUMN_NAME;
        $model_name = Str::camel(Str::singular($table));
        $form = [];

        if ($this->foreign_key) {

            if (in_array($name, $this->skip_columns)) {
                $form[] = sprintf(
                    "<dt>{{ __('columns.%s.%s') }}</dt>",
                    $table,
                    $name
                );
                $form[] = sprintf(
                    "<dd>{{ $%s->%s->name }}</dd>",
                    $model_name,
                    Str::camel($name)
                );
                return $form;
            }

            $form[] = sprintf(
                "<dt>{{ __('tables.%s') }}</dt>",
                $this->foreign_key->REFERENCED_TABLE_NAME
            );
            $form[] = sprintf(
                "<dd>{{ $%s->%s->name }}</dd>",
                $model_name,
                Str::singular($this->foreign_key->REFERENCED_TABLE_NAME)
            );
            return $form;
        }

        $form[] = sprintf(
            "<dt>{{ __('columns.%s.%s') }}</dt>",
            $table,
            $name
        );
        $form[] = sprintf(
            "<dd>{{ $%s->%s }}</dd>",
            $model_name,
            $name
        );
        return $form;
    }
}
