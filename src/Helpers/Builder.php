<?php

namespace Shibuyakosuke\LaravelCrudGenerator\Helpers;

use Illuminate\Support\Str;
use Shibuyakosuke\LaravelCrudGenerator\Models\Table;

class Builder
{
    private $table = null;

    private $indent = '    ';

    private $skip_columns = [
        'created_by',
        'updated_by',
        'deleted_by',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function __construct(Table $table)
    {
        $this->table = $table;
    }

    private function indent(int $i)
    {
        return str_repeat($this->indent, $i);
    }

    public function get()
    {
        $forms = [];
        $forms['index.blade.php'] = $this->index();
        $forms['show.blade.php'] = $this->show();
        $forms['create.blade.php'] = $this->create();
        $forms['edit.blade.php'] = $this->edit();
        return $forms;
    }

    private function index()
    {
        $forms = [];
        $this->table->columns->each(function ($column) use (&$forms) {
            $forms[] = (new Index($column))->get();
        });

        $html = [];
        $html[] = $this->indent(0) . '@extends(\'adminlte::page\')';
        $html[] = $this->indent(0) . '@section(\'content_header\')';
        $html[] = $this->indent(1) . sprintf('<h1>{{ __(\'tables.%s\') }}</h1>', $this->table->TABLE_NAME);
        $html[] = $this->indent(0) . '@stop';
        $html[] = $this->indent(0) . '@section(\'content\')';
        $html[] = $this->indent(1) . '<div class="box">';
        $html[] = $this->indent(2) . '<div class="box-header">';
        $html[] = $this->indent(3) .
            sprintf('{{ Html::linkRoute(\'%s.create\', __(\'buttons.create\'), null, [\'class\' => \'btn btn-sm btn-primary\']) }}',
                $this->table->TABLE_NAME);
        $html[] = $this->indent(3) . '<form action="" class="box-tools">';
        $html[] = $this->indent(4) . '<div class="input-group input-group-sm hidden-xs">';
        $html[] = $this->indent(5) .
            '{{ Form::text(\'q\', \\request(\'q\'), [\'placeholder\' => \'Search\', \'class\' => \'form-control pull-right\']) }}';
        $html[] = $this->indent(5) . '<div class="input-group-btn">';
        $html[] = $this->indent(6) . '<button class="btn btn-default">';
        $html[] = $this->indent(7) . '<i class="fa fa-search"></i>';
        $html[] = $this->indent(6) . '</button>';
        $html[] = $this->indent(5) . '</div>';
        $html[] = $this->indent(4) . '</div>';
        $html[] = $this->indent(3) . '</form>';
        $html[] = $this->indent(2) . '</div>'; // box-header
        $html[] = $this->indent(2) . '<div class="box-body">';
        $html[] = $this->indent(3) . '<div class="table-responsive">';
        $html[] = $this->indent(4) . '<table class="table">';
        $html[] = $this->indent(4) . '<thead>';
        $html[] = $this->indent(5) . '<tr>';

        $this->table->columns->each(function ($column) use (&$html) {
            $foreign_key = $column
                ->table
                ->constraints
                ->filter(function ($constraint) use ($column) {
                    return $constraint->COLUMN_NAME === $column->COLUMN_NAME
                        && $constraint->REFERENCED_TABLE_NAME
                        && $constraint->REFERENCED_COLUMN_NAME;
                })->reject(function ($constraint) {
                    return in_array($constraint->COLUMN_NAME, ['created_by', 'updated_by', 'deleted_by'], true);
                })->first();
            if ($foreign_key) {
                $name = $foreign_key->REFERENCED_TABLE_NAME;
                $html[] = $this->indent(6) . sprintf('<th>{{ __(\'tables.%s\') }}</th>', $name);
            } else {
                $html[] = $this->indent(6) . sprintf('<th>{{ __(\'columns.%s.%s\') }}</th>',
                        $this->table->TABLE_NAME,
                        $column->COLUMN_NAME);
            }
        });
        $html[] = $this->indent(5) . '</tr>';
        $html[] = $this->indent(5) . '</thead>';
        $html[] = $this->indent(5) . '<tbody>';
        $html[] = $this->indent(5) .
            sprintf('@foreach($%s as $%s)', $this->table->TABLE_NAME, Str::singular($this->table->TABLE_NAME));
        $html[] = $this->indent(6) . '<tr>';
        $html[] = $this->indent(7) . implode(PHP_EOL . $this->indent(7), $forms);
        $html[] = $this->indent(6) . '</tr>';
        $html[] = $this->indent(5) . '@endforeach';
        $html[] = $this->indent(5) . '</tbody>';
        $html[] = $this->indent(4) . '</table>';
        $html[] = $this->indent(3) . '</div>'; // table-responsive

        $html[] = $this->indent(2) . '</div>'; // box-body
        $html[] = $this->indent(2) . '<div class="box-footer">';
        $html[] = $this->indent(3) . '<div class="row">';
        $html[] = $this->indent(4) . '<div class="col-md-5 pad">';
        $html[] = $this->indent(5) .
            sprintf('{{ $%s->firstItem() }}&nbsp;-&nbsp;{{ $%s->lastItem() }}&nbsp;/&nbsp;{{ $%s->total() }}&nbsp;件',
                $this->table->TABLE_NAME,
                $this->table->TABLE_NAME,
                $this->table->TABLE_NAME,
            );
        $html[] = $this->indent(4) . '</div>'; // col-md-5
        $html[] = $this->indent(4) . '<div class="col-md-7 text-right">';
        $html[] = $this->indent(5) . sprintf('{{ $%s->links() }}', $this->table->TABLE_NAME);
        $html[] = $this->indent(4) . '</div>'; // col-md-7
        $html[] = $this->indent(3) . '</div>'; // row
        $html[] = $this->indent(2) . '</div>'; // box-footer
        $html[] = $this->indent(1) . '</div>';
        $html[] = '@stop';

        return implode(PHP_EOL, $html);
    }

    private function show()
    {
        $forms = [];
        $this->table->columns->each(function ($column) use (&$forms) {
            $forms = array_merge($forms, (new Show($column))->get());
        });

        $html = [];
        $html[] = $this->indent(0) . '@extends(\'adminlte::page\')';
        $html[] = $this->indent(0) . '@section(\'content_header\')';
        $html[] = $this->indent(1) . sprintf('<h1>{{ __(\'tables.%s\') }}</h1>', $this->table->TABLE_NAME);
        $html[] = $this->indent(0) . '@stop';
        $html[] = $this->indent(0) . '@section(\'content\')';
        $html[] = $this->indent(1) . '<div class=\'box\'>';
        $html[] = $this->indent(2) . '<div class=\'box-header\'>';
        $html[] = $this->indent(2) . sprintf('<a class="btn btn-sm btn-primary" href="{{ Route(\'%s.edit\', [\'%s\' => $%s->id]) }}">編集</a>',
                $this->table->TABLE_NAME,
                Str::singular($this->table->TABLE_NAME),
                Str::singular($this->table->TABLE_NAME),
            );
        $html[] = $this->indent(2) . '</div>';
        $html[] = $this->indent(2) . '<div class=\'box-body\'>';
        $html[] = $this->indent(3) . '<dl class="dl-horizontal">';
        $html[] = $this->indent(4) . implode(PHP_EOL . $this->indent(4), $forms);
        $html[] = $this->indent(3) . '</dl>';
        $html[] = $this->indent(2) . '</div>';
        $html[] = $this->indent(1) . '</div>';
        $html[] = $this->indent(0) . '@stop';
        return implode(PHP_EOL, $html);
    }

    private function create()
    {
        $forms = [];
        $this->table->columns->each(function ($column) use (&$forms) {
            if (in_array($column->COLUMN_NAME, $this->skip_columns, true)) {
                return;
            }
            if ($column->COLUMN_KEY === 'PRI') {
                return;
            }
            $forms[] = (new Form($column, 'create'))->get();
        });
        $html = [];
        $html[] = '@extends(\'adminlte::page\')';
        $html[] = $this->indent(0) . '@section(\'content_header\')';
        $html[] = $this->indent(1) . sprintf('<h1>{{ __(\'tables.%s\') }}</h1>', $this->table->TABLE_NAME);
        $html[] = $this->indent(0) . '@stop';
        $html[] = '@section(\'content\')';
        $html[] = $this->indent(1) . '<div class=\'box\'>';
        $html[] = $this->indent(2) . '<div class=\'box-header\'>';
        $html[] = $this->indent(2) . '</div>';
        $html[] = $this->indent(2) . '<div class=\'box-body\'>';
        $html[] = $this->indent(3)
            . sprintf('{{ BootForm::horizontal([\'route\' => \'%s.store\']) }}',
                $this->table->TABLE_NAME
            );
        $html[] = $this->indent(3) . implode(PHP_EOL . $this->indent(3), $forms);
        $html[] = $this->indent(3) . '{{ BootForm::submit()}}';
        $html[] = $this->indent(3) . '{{ BootForm::close()}}';
        $html[] = $this->indent(2) . '</div>';
        $html[] = $this->indent(1) . '</div>';
        $html[] = '@stop';
        return implode(PHP_EOL, $html);
    }

    private function edit()
    {
        $forms = [];
        $this->table->columns->each(function ($column) use (&$forms) {
            if (in_array($column->COLUMN_NAME, $this->skip_columns, true)) {
                return;
            }
            if ($column->COLUMN_KEY === 'PRI') {
                return;
            }
            $forms[] = (new Form($column, 'edit'))->get();
        });
        $html = [];
        $html[] = '@extends(\'adminlte::page\')';
        $html[] = $this->indent(0) . '@section(\'content_header\')';
        $html[] = $this->indent(1) . sprintf('<h1>{{ __(\'tables.%s\') }}</h1>', $this->table->TABLE_NAME);
        $html[] = $this->indent(0) . '@stop';
        $html[] = '@section(\'content\')';
        $html[] = $this->indent(1) . '<div class=\'box\'>';
        $html[] = $this->indent(2) . '<div class=\'box-header\'>';
        $html[] = $this->indent(2) . '</div>';
        $html[] = $this->indent(2) . '<div class=\'box-body\'>';
        $html[] = str_repeat($this->indent,
                3) . sprintf('{{ BootForm::horizontal([\'model\' => $%s, \'update\' => \'%s.update\']) }}',
                Str::singular($this->table->TABLE_NAME),
                $this->table->TABLE_NAME
            );
        $html[] = $this->indent(3) . implode(PHP_EOL . $this->indent(3), $forms);
        $html[] = $this->indent(3) . '{{ BootForm::submit()}}';
        $html[] = $this->indent(3) . '{{ BootForm::close()}}';
        $html[] = $this->indent(2) . '</div>';
        $html[] = $this->indent(1) . '</div>';
        $html[] = '@stop';
        return implode(PHP_EOL, $html);
    }
}
