<?php

namespace Shibuyakosuke\LaravelCrudGenerator\AdminlteMenu;

use Illuminate\Events\Dispatcher;
use JeroenNoten\LaravelAdminLte\Events\BuildingMenu;
use Shibuyakosuke\LaravelCrudGenerator\Models\Table;

class AdminlteMenu
{
    private $events;
    private $config = [];
    private static $tables;

    public function __construct(Dispatcher $events)
    {
        $this->events = $events;
        $this->config = config('adminlte_menu', []);
        static::$tables = Table::query()
            ->where('TABLE_COMMENT', '!=', '')
            ->get();
    }

    private function item(Table $table)
    {
        $table_name = $table->TABLE_NAME;
        return [
            'text' => \Lang::get(sprintf('tables.%s', $table_name)),
            'route' => sprintf('%s.index', $table_name),
            'active' => [
                $table_name,
                sprintf('%s/*', $table_name),
                sprintf('regex:@^%s(page=[0-9]+)?$@', $table_name)
            ]
        ];
    }

    public function build()
    {
        static::$tables->each(function (Table $table) {
            if (empty($table->primary_key)) {
                return;
            }
            $this->config[] = $this->item($table);
        });

        $this->events->listen(BuildingMenu::class, function (BuildingMenu $event) {
            foreach ($this->config as $config) {
                $event->menu->add($config);
            }
        });
    }
}
