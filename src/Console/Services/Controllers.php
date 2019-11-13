<?php

namespace Shibuyakosuke\LaravelCrudGenerator\Console\Services;

use Shibuyakosuke\LaravelCrudGenerator\Models\Table;

class Controllers extends CrudAbstract
{
    public function outputFileName(Table $table): string
    {
        return app_path(sprintf('Http/Controllers/%sController.php', $table->model));
    }

    public function stab(): string
    {
        $stub = [];
        $stub[] = "<?php";
        $stub[] = "";
        $stub[] = "namespace App\\Http\\Controllers;";
        $stub[] = "";
        $stub[] = "use App\\Http\\Requests\\%sFormRequest;";
        $stub[] = "use App\\Models\\%s;";
        $stub[] = "";
        $stub[] = "class %s extends Controller";
        $stub[] = "{";
        $stub[] = "    public function index()";
        $stub[] = "    {";
        $stub[] = "        \$%s = %s::query()";
        $stub[] = "            ->with([%s])";
        $stub[] = "            ->when(\\request('q'), function (\$query) {";
        $stub[] = "                \$query->where('name', 'like', '%%' . \\request('q') . '%%');";
        $stub[] = "            })";
        $stub[] = "            ->paginate();";
        $stub[] = "        return \\view('%s.index', compact('%s'));";
        $stub[] = "    }";
        $stub[] = "";
        $stub[] = "    public function create()";
        $stub[] = "    {";
        $stub[] = "        return \\view('%s.create');";
        $stub[] = "    }";
        $stub[] = "";
        $stub[] = "    public function store(%sFormRequest \$request)";
        $stub[] = "    {";
        $stub[] = "        \$%s = new %s();";
        $stub[] = "        \$%s->fill(";
        $stub[] = "            \$request->all()";
        $stub[] = "        )->save();";
        $stub[] = "";
        $stub[] = "        return \\redirect()->route('%s.show', compact('%s'));";
        $stub[] = "    }";
        $stub[] = "";
        $stub[] = "    public function show(%s \$%s)";
        $stub[] = "    {";
        $stub[] = "        return \\view('%s.show', compact('%s'));";
        $stub[] = "    }";
        $stub[] = "";
        $stub[] = "    public function edit(%s \$%s)";
        $stub[] = "    {";
        $stub[] = "        return \\view('%s.edit', compact('%s'));";
        $stub[] = "    }";
        $stub[] = "";
        $stub[] = "    public function update(%sFormRequest \$request, %s \$%s)";
        $stub[] = "    {";
        $stub[] = "        \$%s->fill(";
        $stub[] = "            \$request->all()";
        $stub[] = "        )->save();";
        $stub[] = "";
        $stub[] = "        return \\redirect()->route('%s.show', compact('%s'));";
        $stub[] = "    }";
        $stub[] = "";
        $stub[] = "    public function destroy(%s \$%s)";
        $stub[] = "    {";
        $stub[] = "        \$%s->delete();";
        $stub[] = "        return \\redirect()->route('%s.index');";
        $stub[] = "    }";
        $stub[] = "}";
        $stub[] = "";
        return implode(PHP_EOL, $stub);
    }

    public function callback(Table $table): array
    {
        if (empty($table->TABLE_COMMENT)) {
            return [];
        }

        if (empty($table->primarykey)) {
            return [];
        }

        $foreignkeys = $table->constraints
            ->whereNotIn('COLUMN_NAME', ['created_by', 'updated_by', 'deleted_by'])
            ->map(function ($constraint) {
                return sprintf('\'%s\'', \Str::singular($constraint->REFERENCED_TABLE_NAME));
            });

        if ($table->hasCreatedBy()) {
            $foreignkeys->push('\'createdBy\'');
        }
        if ($table->hasUpdatedBy()) {
            $foreignkeys->push('\'updatedBy\'');
        }
        if ($table->hasDeletedBy()) {
            $foreignkeys->push('\'deletedBy\'');
        }

        $with = implode(', ', $foreignkeys->toArray());

        $table_name = $table->TABLE_NAME;
        $model_name = $table->model;
        $model_var_name = \Str::camel(\Str::singular($table_name));
        $models_var_name = \Str::camel($table_name);
        $controller_name = sprintf('%sController', $model_name);

        return [
            $model_name,
            $model_name,

            $controller_name,
            //index
            $models_var_name,
            $model_name,
            $with,
            $table_name,
            $models_var_name,

            // create
            $table_name,

            //store
            $model_name,
            $model_var_name,
            $model_name,
            $model_var_name,
            $table_name,
            $model_var_name,

            //show
            $model_name,
            $model_var_name,
            $table_name,
            $model_var_name,

            //edit
            $model_name,
            $model_var_name,
            $table_name,
            $model_var_name,

            //update
            $model_name,
            $model_name,
            $model_var_name,
            $model_var_name,
            $table_name,
            $model_var_name,

            //delete
            $model_name,
            $model_var_name,
            $model_var_name,
            $table_name
        ];
    }
}
