<?php

namespace Shibuyakosuke\LaravelCrudGenerator\Console;

use Illuminate\Console\Command;
use Shibuyakosuke\LaravelCrudGenerator\Models\Table;

class CrudCheckCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crud:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check crud generator';

    /**
     * @return void
     */
    public function handle(): void
    {
        $tables = Table::query()
            ->select('TABLE_NAME')
            ->where('TABLE_COMMENT', '!=', '')
            ->get();
//        dd($tables);
        $this->table(['テーブル名', 'コメント', 'モデル名'], $tables);
    }
}
