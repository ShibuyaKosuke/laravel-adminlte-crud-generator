<?php

namespace Shibuyakosuke\LaravelCrudGenerator\Console\Services;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Shibuyakosuke\LaravelCrudGenerator\Models\CrudClass;
use Shibuyakosuke\LaravelCrudGenerator\Models\CrudMap;
use Shibuyakosuke\LaravelCrudGenerator\Models\CrudTable;
use Shibuyakosuke\LaravelCrudGenerator\Models\Table;

abstract class CrudAbstract
{
    use Write;

    protected $collection;
    protected $buffer;
    protected $output;
    protected $command;

    public function indent(int $indent): string
    {
        return str_repeat(' ', 4 * $indent);
    }

    abstract public function outputFileName(Table $table): string;

    abstract public function stab(): string;

    abstract public function callback(Table $table): array;

    /**
     * CrudAbstract constructor.
     * @param Collection $tables
     * @param Command $command
     */
    public function __construct(Collection $tables, Command $command)
    {
        $this->command = $command;
        $this->output = $command->getOutput();
        $this->collection = $tables;

        $this->collection->each(function (Table $table) {
            $array = $this->callback($table);
            if (count($array) == 0) {
                return;
            }
            $this->buffer[] = [
                'file' => $this->outputFileName($table),
                'buffer' => vsprintf($this->stab(), $array)
            ];
        });
    }

    public function output()
    {
        if (count($this->buffer) == 0) {
            return;
        }
        foreach ($this->buffer as $value) {
            $this->write($value['file'], $value['buffer']);
        }
    }
}
