<?php

namespace Shibuyakosuke\LaravelCrudGenerator\Helpers;

use Illuminate\Support\Str;
use Shibuyakosuke\LaravelCrudGenerator\Models\Column;

class Index
{
    protected $column = null;
    protected $foreign_key = null;
    protected $action = null;

    private $skip_columns = [
        'created_by',
        'updated_by',
        'deleted_by',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * Form constructor.
     * @param Column $column
     * @param string $action
     */
    public function __construct(Column $column, $action = 'edit')
    {
        $this->action = $action;
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
        $model_name = Str::singular($table);

        if ($name === 'id') {
            return sprintf('<td>{{ Html::linkRoute(\'%s.show\', __(\'buttons.show\'), [\'%s\' => $%s->id], [\'class\' => \'btn btn-xs btn-primary\']) }}</td>',
                $table,
                Str::singular($table),
                Str::singular($table)
            );
        } elseif ($this->foreign_key) {
            if (in_array($name, $this->skip_columns)) {
                return sprintf('<td>{{ $%s->%s->name }}</td>',
                    $model_name,
                    Str::camel($name)
                );
            }
            return sprintf('<td>{{ $%s->%s->name }}</td>',
                $model_name,
                Str::singular($this->foreign_key->REFERENCED_TABLE_NAME)
            );
        }
        return sprintf('<td>{{ $%s->%s }}</td>',
            $model_name,
            $name
        );
    }

}
