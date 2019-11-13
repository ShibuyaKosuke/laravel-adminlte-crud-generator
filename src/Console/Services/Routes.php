<?php

namespace Shibuyakosuke\LaravelCrudGenerator\Console\Services;

use Illuminate\Console\OutputStyle;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Shibuyakosuke\LaravelCrudGenerator\Models\Table;

class Routes
{
    protected $tables;
    protected $output;

    public function __construct(Collection $tables, OutputStyle $output)
    {
        $this->tables = $tables;
        $this->output = $output;
    }

    public function add()
    {
        $file = base_path() . '/routes/web.php';

        $this->tables->reject(function ($value) {
            return $value->TABLE_COMMENT == null;
        })->each(function (Table $table) use ($file) {

            if (empty($table->primarykey)) {
                return;
            }

            $table_name = $table->TABLE_NAME;
            $model_name = Str::studly(Str::singular($table_name));

            $route = sprintf('Route::resource(\'/%s\', \'%sController\');',
                $table_name,
                $model_name
            );

            $content = file_get_contents($file);
            if (strstr($content, $route)) {
                return;
            }
            file_put_contents($file, $route . PHP_EOL, FILE_APPEND);
            $this->output->write($route);
        });
    }
}
