<?php

namespace Shibuyakosuke\LaravelCrudGenerator\Console\Services;

use Illuminate\Support\Str;
use Shibuyakosuke\LaravelCrudGenerator\Models\Column;
use Shibuyakosuke\LaravelCrudGenerator\Models\KeyColumnUsage;
use Shibuyakosuke\LaravelCrudGenerator\Models\Table;

class Models extends CrudAbstract
{
    public function outputFileName(Table $table): string
    {
        $model_path = config('adminlte_crud.default_model_path');
        return base_path(sprintf('%s%s.php', $model_path, $table->model));
    }

    /**
     * @return string
     *
     * @todo Delete $softDeletes unless table has no 'deleted_at' column.
     */
    public function stab(): string
    {
        $model = [];
        $model[] = '<?php';
        $model[] = '';
        $model[] = 'namespace App\Models;';
        $model[] = '';
        $model[] = 'use App\Traits\AuthorObservable;';
        $model[] = 'use Illuminate\Database\Eloquent\Model;';
        $model[] = 'use Illuminate\Database\Eloquent\Relations\BelongsTo;';
        $model[] = 'use Illuminate\Database\Eloquent\Relations\BelongsToMany;';
        $model[] = 'use Illuminate\Database\Eloquent\Relations\HasMany;';
        $model[] = 'use Illuminate\Database\Eloquent\SoftDeletes;';
        $model[] = '';
        $model[] = 'class %s extends Model';
        $model[] = '{';
        $model[] = '    use AuthorObservable;';
        $model[] = '    use SoftDeletes;';
        $model[] = '';
        $model[] = '    %s'; // primary_key
        $model[] = '    %s'; // propaties
        $model[] = '    %s'; // propaties
        $model[] = '    %s'; // $methods
        $model[] = '}';
        $model[] = '';
        return implode(PHP_EOL . $this->indent(0), $model);
    }

    private function fillable()
    {
        $fillable = [];
        $fillable[] = '';
        $fillable[] = 'protected $fillable = [';
        $fillable[] = '    %s';
        $fillable[] = '];';
        return implode(PHP_EOL . $this->indent(1), $fillable);
    }

    private function dates()
    {
        $dates = [];
        $dates[] = '';
        $dates[] = 'protected $dates = [';
        $dates[] = '    %s';
        $dates[] = '];';
        return implode(PHP_EOL . $this->indent(1), $dates);
    }

    private function belongsTo()
    {
        $belongs_to = [];
        $belongs_to[] = '';
        $belongs_to[] = '/**';
        $belongs_to[] = ' * @return BelongsTo %s';
        $belongs_to[] = ' */';
        $belongs_to[] = 'public function %s(): BelongsTo';
        $belongs_to[] = '{';
        $belongs_to[] = '    return $this->belongsTo(%s::class)%s;';
        $belongs_to[] = '}';
        return implode(PHP_EOL . $this->indent(1), $belongs_to);
    }

    private function hasMany()
    {
        $has_many = [];
        $has_many[] = '';
        $has_many[] = '/**';
        $has_many[] = ' * @return HasMany %s[]';
        $has_many[] = ' */';
        $has_many[] = 'public function %s(): HasMany';
        $has_many[] = '{';
        $has_many[] = '    return $this->hasMany(%s::class);';
        $has_many[] = '}';
        return implode(PHP_EOL . $this->indent(1), $has_many);
    }

    public function callback(Table $table): array
    {
        if ($table->TABLE_COMMENT == null || $table->TABLE_NAME === 'users') {
            return [];
        }

        if (empty($table->primarykey)) {
            return [];
        }

        // filleble
        $fillable = sprintf(
            $this->fillable(),
            implode(
                PHP_EOL . str_repeat(' ', 8),
                $table->columns->reject(function ($column) {
                    return in_array($column->COLUMN_NAME, [
                        'id',
                        'created_by',
                        'updated_by',
                        'deleted_by',
                        'created_at',
                        'updated_at',
                        'deleted_at'
                    ], true);
                })->map(function ($item, $key) {
                    return sprintf('\'%s\',', $item->COLUMN_NAME);
                })->toArray()
            )
        );

        // dates
        $dates = sprintf($this->dates(),
            implode(PHP_EOL . str_repeat(' ', 8),
                $table->columns->reject(function ($value) {
                    return in_array($value->COLUMN_NAME, ['created_at', 'updated_at', 'deleted_at'], true);
                })->filter(function (Column $column) {
                    return in_array($column->DATA_TYPE, ['timestamp', 'datetime', 'date'], true);
                })->map(function ($item) {
                    return sprintf('\'%s\',', $item->COLUMN_NAME);;
                })->toArray()
            ));

        $methods = [];

        // BelongsTo
        $table->constraints->each(function (KeyColumnUsage $constraint) use (&$methods) {
            $column_name = $constraint->COLUMN_NAME;
            if (in_array($column_name, ['created_by', 'updated_by', 'deleted_by'], true)) {
                return;
            }
            if (is_null($constraint->REFERENCED_TABLE_NAME)) {
                return;
            }
            $column = $constraint->table->columns->filter(function (Column $column) use ($column_name) {
                return $column->COLUMN_NAME === $column_name;
            })->first();

            $default = $column->is_required ? '' : '->withDefault()';

            $method_name = Table::getModelName($constraint->REFERENCED_TABLE_NAME);
            $methods[] = sprintf($this->belongsTo(),
                $method_name,
                lcfirst($method_name),
                $method_name,
                $default,
            );
        });

        // HasMany
        $table->references->each(function (KeyColumnUsage $constraint) use (&$methods) {
            if (is_null($constraint->TABLE_NAME)) {
                return;
            }
            if (in_array($constraint->COLUMN_NAME, ['created_by', 'updated_by', 'deleted_by'], true)) {
                return;
            }
            $method_name = Str::camel($constraint->TABLE_NAME);
            $methods[] = sprintf(
                $this->hasMany(),
                Table::getModelName($method_name),
                $method_name,
                Table::getModelName($method_name)
            );
        });

        $pk = ($table->primary_key) ? sprintf('protected $primaryKey = \'%s\';', $table->primary_key) : '';

        return [
            $table->model,
            $pk,
            $fillable,
            $dates,
            implode(PHP_EOL, $methods)
        ];
    }
}
