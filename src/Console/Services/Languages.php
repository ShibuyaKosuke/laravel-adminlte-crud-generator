<?php

namespace Shibuyakosuke\LaravelCrudGenerator\Console\Services;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Shibuyakosuke\LaravelCrudGenerator\Models\Table;

class Languages
{
    use Write;

    protected $tables;
    protected $output;
    protected $command;

    public function __construct(Command $command)
    {
        $this->command = $command;
        $this->tables = Table::query()
            ->with(['columns', 'constraints', 'references'])
            ->get();
        $this->output = $command->getOutput();
    }

    public function output()
    {
        function array_parse(array $array, int $indent = 0)
        {
            $indent++;
            $buffer = '';
            foreach ($array as $k => $v) {
                if (is_array($v)) {
                    $buffer .= str_repeat(' ', 4 * $indent)
                        . sprintf("'%s' => %s,", $k, array_parse($v, $indent))
                        . "\n";
                } else {
                    $buffer .= str_repeat(' ', 4 * $indent)
                        . sprintf("'%s' => '%s',", $k, $v)
                        . "\n";
                }
            }
            return "[\n" . $buffer . str_repeat(' ', 4 * ($indent - 1)) . "]";
        }

        $translates = [];
        $this->tables->each(function ($table) use (&$html, &$translates) {
            if (empty($table->TABLE_COMMENT)) {
                return;
            }

            if (empty($table->primarykey)) {
                return;
            }

            $translates['tables'][$table->TABLE_NAME] = $table->TABLE_COMMENT;
            $translates['columns'][$table->TABLE_NAME] = $table->columns->pluck('COLUMN_COMMENT',
                'COLUMN_NAME')->toArray();
        });

        foreach (['tables', 'columns'] as $type) {
            $file = resource_path(sprintf('lang/%s/%s.php', App::getLocale(), $type));
            $buffer = "<?php\n\nreturn " . array_parse($translates[$type]) . ";\n";
            $this->write($file, $buffer);
        }
    }

}
