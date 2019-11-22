<?php

namespace Shibuyakosuke\LaravelCrudGenerator\Console\Services;

use Shibuyakosuke\LaravelCrudGenerator\Models\Column;
use Shibuyakosuke\LaravelCrudGenerator\Models\KeyColumnUsage;
use Shibuyakosuke\LaravelCrudGenerator\Models\Table;

class Request extends CrudAbstract
{
    public function outputFileName(Table $table): string
    {
        return app_path(sprintf('Http/Requests/%sFormRequest.php', $table->model));
    }

    public function stab(): string
    {
        $stab = [];
        $stab[] = '<?php';
        $stab[] = '';
        $stab[] = 'namespace App\Http\Requests;';
        $stab[] = '';
        $stab[] = 'use Illuminate\Foundation\Http\FormRequest;';
        $stab[] = '';
        $stab[] = 'class %sFormRequest extends FormRequest';
        $stab[] = '{';
        $stab[] = '    public function authorize()';
        $stab[] = '    {';
        $stab[] = '        return true;';
        $stab[] = '    }';
        $stab[] = '';
        $stab[] = '    public function rules()';
        $stab[] = '    {';
        $stab[] = '        return [';
        $stab[] = '%s';
        $stab[] = '        ];';
        $stab[] = '    }';
        $stab[] = '';
        $stab[] = '    public function attributes()';
        $stab[] = '    {';
        $stab[] = '        return array_merge(\\Lang::get(\'columns.%s\'), [';
        $stab[] = '%s';
        $stab[] = '        ]);';
        $stab[] = '    }';
        $stab[] = '}';
        $stab[] = '';
        return implode(PHP_EOL, $stab);
    }

    public function callback(Table $table): array
    {
        if (empty($table->TABLE_COMMENT)) {
            return [];
        }

        if (empty($table->primarykey)) {
            return [];
        }

        $table_name = $table->TABLE_NAME;
        $model_name = $table->model;

        $rules = [];
        $table->columns->each(function (Column $column) use (&$rules) {
            $column_name = $column->COLUMN_NAME;
            if (in_array($column_name,
                ['id', 'created_by', 'updated_by', 'deleted_by', 'created_at', 'updated_at', 'deleted_at'])) {
                return;
            }

            // IS_NULLABLE
            $rules[$column_name][] = $column->is_required ? 'required' : 'nullable';

            // CHARACTER_MAXIMUM_LENGTH
            if ($column->CHARACTER_MAXIMUM_LENGTH) {
                $rules[$column_name][] = sprintf('max:%d', $column->CHARACTER_MAXIMUM_LENGTH);
            }

            // data type
            $rules[$column_name][] = $column->validate_type;

        });

        $table->constraints->each(function (KeyColumnUsage $constraint) use (&$rules) {
            $column_name = $constraint->COLUMN_NAME;
            if (in_array($column_name,
                ['id', 'created_by', 'updated_by', 'deleted_by', 'created_at', 'updated_at', 'deleted_at'])) {
                return;
            }
            $rules[$column_name][] = sprintf('exists:%s,%s',
                $constraint->REFERENCED_TABLE_NAME,
                $constraint->REFERENCED_COLUMN_NAME
            );
        });

        $rules_string = [];
        foreach ($rules as $column_name => $values) {
            $values = array_map(function ($val) {
                return sprintf("'%s'", $val);
            }, $values);
            $rules_string[] = sprintf(str_repeat(' ', 12) . '\'%s\' => [%s],',
                $column_name,
                implode(', ', $values)
            );
        }

        // attributes
        $attributes = [];
        $foreign_key = $table
            ->constraints
            ->filter(function ($constraint) {
                return $constraint->REFERENCED_TABLE_NAME
                    && $constraint->REFERENCED_COLUMN_NAME;
            })->reject(function ($constraint) {
                return in_array($constraint->COLUMN_NAME,
                    ['created_by', 'updated_by', 'deleted_by', 'created_at', 'updated_at', 'deleted_at']);
            });
        foreach ($foreign_key as $column) {
            $attributes[] = sprintf('            \'%s\' => trans(\'tables.%s\'),',
                $column->COLUMN_NAME,
                $column->REFERENCED_TABLE_NAME
            );
        }

        return [
            $model_name,
            implode(PHP_EOL, $rules_string),
            $table_name,
            implode(PHP_EOL, $attributes),
        ];
    }
}
