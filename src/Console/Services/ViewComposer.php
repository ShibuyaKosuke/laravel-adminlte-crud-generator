<?php

namespace Shibuyakosuke\LaravelCrudGenerator\Console\Services;

use Shibuyakosuke\LaravelCrudGenerator\Models\KeyColumnUsage;
use Shibuyakosuke\LaravelCrudGenerator\Models\Table;

class ViewComposer extends CrudAbstract
{
    public function outputFileName(Table $table): string
    {
        return app_path(sprintf('Http/View/Composers/%sComposer.php', $table->model));
    }

    public function stab(): string
    {
        $stab = [];
        $stab[] = '<?php';
        $stab[] = '';
        $stab[] = 'namespace App\Http\View\Composers;';
        $stab[] = '';
        $stab[] = '%s';
        $stab[] = 'use Illuminate\View\View;';
        $stab[] = '';
        $stab[] = 'class %s';
        $stab[] = '{';
        $stab[] = '    public function compose(View $view)';
        $stab[] = '    {';
        $stab[] = '        %s';
        $stab[] = '    }';
        $stab[] = '}';
        $stab[] = '';
        return implode(PHP_EOL, $stab);
    }

    public function callback(Table $table): array
    {
        if ($table->constraints->count() == 0 || empty($table->TABLE_COMMENT)) {
            return [];
        }

        if (empty($table->primarykey)) {
            return [];
        }

        $lines = [];
        $uses = [];

        $composer_name = sprintf('%sComposer', $table->model);

        $constraints = $table->constraints
            ->whereNotIn('COLUMN_NAME', ['created_by', 'updated_by', 'deleted_by']);

        $model_path = config('adminlte_crud.default_model_path');
        $model_path = str_replace('/', '\\', ucfirst($model_path));

        $constraints->each(function (KeyColumnUsage $constraint) use (&$lines, &$uses, $model_path) {
            $model = Table::getModelName($constraint->REFERENCED_TABLE_NAME);
            $uses[] = sprintf('use %s%s;', $model_path, $model);
            $lines[] = sprintf('$%s = %s::all();',
                $constraint->REFERENCED_TABLE_NAME,
                $model,
            );
        });

        $foreign_keys = $constraints->map(function ($constraint) {
            return sprintf('\'%s\'', $constraint->REFERENCED_TABLE_NAME);
        });
        $lines[] = sprintf('$view->with(compact([%s]));', implode(', ', $foreign_keys->toArray()));

        if (count($uses) == 0) {
            return [];
        }

        return [
            implode(PHP_EOL, $uses),
            $composer_name,
            implode(PHP_EOL . str_repeat(' ', 8), $lines)
        ];
    }
}
