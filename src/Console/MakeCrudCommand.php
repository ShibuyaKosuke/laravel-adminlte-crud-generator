<?php

namespace Shibuyakosuke\LaravelCrudGenerator\Console;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Artisan;
use Shibuyakosuke\LaravelCrudGenerator\Console\Services\Blades;
use Shibuyakosuke\LaravelCrudGenerator\Console\Services\Controllers;
use Shibuyakosuke\LaravelCrudGenerator\Console\Services\Languages;
use Shibuyakosuke\LaravelCrudGenerator\Console\Services\Models;
use Shibuyakosuke\LaravelCrudGenerator\Console\Services\Request;
use Shibuyakosuke\LaravelCrudGenerator\Console\Services\Routes;
use Shibuyakosuke\LaravelCrudGenerator\Console\Services\Test;
use Shibuyakosuke\LaravelCrudGenerator\Console\Services\ViewComposer;
use Shibuyakosuke\LaravelCrudGenerator\Models\Table;

class MakeCrudCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crud:generate
                            {--force : Allow overwrite}
                            {--migrate : migrate before output}
                            {--table= : specify table names(comma separated)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Output CRUD files.';

    /**
     * @param array $tables
     * @return Collection
     */
    private function getTables(array $tables = []): Collection
    {
        $arg = $this->option('table');
        if ($arg) {
            $tables = explode(',', $arg);
        }

        return Table::query()
            ->with(['columns', 'constraints', 'references'])
            ->when(count($tables), function ($query) use ($tables) {
                $query->whereIn('TABLE_NAME', $tables);
            })
            ->get();
    }

    /**
     * initialize laravel cache
     */
    private function init()
    {
        Artisan::call('route:clear');
        Artisan::call('view:clear');
        Artisan::call('cache:clear');
        if ($this->option('migrate')) {
            Artisan::call('migrate');
        }
    }

    /**
     * @return void
     */
    public function handle(): void
    {
        try {
            $this->init();

            $tables = $this->getTables();

            $this->output->title('Lang files');
            (new Languages($this))->output();

            $this->output->title('Blade files');
            (new Blades($tables, $this))->output();

            $this->output->title('Models');
            (new Models($tables, $this))->output();

            $this->output->title('Controllers');
            (new Controllers($tables, $this))->output();

            $this->output->title('FormRequests');
            (new Request($tables, $this))->output();

            $this->output->title('ViewComposers');
            (new ViewComposer($tables, $this))->output();

            $this->output->title('Routing');
            (new Routes($tables, $this->output))->add();

            $this->output->title('Tests');
            (new Test($tables, $this))->output();

            $this->output->success('Finished!');

        } catch (\Throwable $e) {
            $this->output->title($e->getMessage());
            $this->output->writeln($e->getTraceAsString());
        } finally {
            $this->init();
        }
    }
}
