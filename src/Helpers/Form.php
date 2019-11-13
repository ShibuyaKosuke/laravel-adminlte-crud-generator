<?php

namespace Shibuyakosuke\LaravelCrudGenerator\Helpers;

use Illuminate\Support\Str;
use Shibuyakosuke\LaravelCrudGenerator\Models\Column;

class Form
{
    protected $column = null;
    protected $foreign_key = null;
    protected $action = null;

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

    public function get(): string
    {
        if ($this->foreign_key) {
            return $this->select();
        }
        return $this->input();
    }

    private function getLabel()
    {
        $required = config('adminlte_crud.required_label');
        $required_label_position = config('adminlte_crud.required_label_position');

        $table = $this->column->TABLE_NAME;
        $name = $this->column->COLUMN_NAME;
        $referenced_table_name = $this->foreign_key->REFERENCED_TABLE_NAME ?? null;

        $lang_label = ($referenced_table_name) ? "__('tables.$referenced_table_name')" : "__('columns.$table.$name')";

        if (!$this->column->is_required) {
            return $lang_label;
        }

        if ($required_label_position === 'before') {
            $html = "['html' => '<span class=\"required\">{$required}</span>&nbsp;' . $lang_label]";
        } else {
            $html = "['html' => $lang_label . '&nbsp;<span class=\"required\">{$required}</span>']";
        }

        return $html;
    }

    /**
     *
     */
    public function input(): string
    {
        $length = config('adminlte_crud.textarea');

        $table = $this->column->TABLE_NAME;
        $name = $this->column->COLUMN_NAME;
        $model_name = Str::camel(Str::singular($table));
        $old = ($this->action === 'edit') ? sprintf(', $%s->%s', $model_name, $name) : "";
        $label = $this->getLabel();

        if ($this->column->CHARACTER_MAXIMUM_LENGTH > $length) {
            return "{{ BootForm::textarea('$name', $label, old('$name'" . $old . ")) }}";
        }

        return "{{ BootForm::text('$name', $label, old('$name'" . $old . ")) }}";
    }

    /**
     *
     */
    public function select(): string
    {
        $table = $this->column->TABLE_NAME;
        $name = $this->column->COLUMN_NAME;
        $model_name = Str::camel($this->foreign_key->REFERENCED_TABLE_NAME);
        $old = ($this->action === 'edit') ? sprintf(', $%s->%s', Str::singular($table), $name) : "";
        $label = $this->getLabel();
        return sprintf('{{ BootForm::select(\'%s\', %s, $%s->pluck(\'name\', \'id\'), old(\'%s\'%s), [\'placeholder\' => \'----\']) }}',
            $name,
            $label,
            $model_name,
            $name,
            $old
        );
    }
}
