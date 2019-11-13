<?php

namespace Shibuyakosuke\LaravelCrudGenerator\Console\Services;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Shibuyakosuke\LaravelCrudGenerator\Helpers\Builder;

class Blades
{
    use Write;

    protected $tables;
    protected $output;
    protected $command;

    public function __construct(Collection $tables, Command $command)
    {
        $this->command = $command;
        $this->tables = $tables;
        $this->output = $command->getOutput();
    }

    public function output()
    {
        $html = [];
        $this->tables->each(function ($table) use (&$html, &$translates) {
            if (empty($table->TABLE_COMMENT)) {
                return;
            }

            if (empty($table->primarykey)) {
                return;
            }

            $html[$table->TABLE_NAME] = (new Builder($table))->get();
        });

        foreach ($html as $table_name => $buffers) {
            $path = resource_path(sprintf('views/%s', $table_name));
            foreach ($buffers as $basename => $buffer) {
                if (!file_exists($path)) {
                    mkdir($path, 0777, true);
                }
                $file = sprintf('%s/%s', $path, $basename);
                $this->write($file, $buffer);
            }
        }
    }
}
