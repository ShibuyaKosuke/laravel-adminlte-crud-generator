<?php

namespace Shibuyakosuke\LaravelCrudGenerator\Console;

use Illuminate\Console\Command;

class CrudSetupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crud:setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Output CRUD files.';

    /**
     * @return void
     */
    public function handle(): void
    {

    }
}
