<?php

namespace Shibuyakosuke\LaravelCrudGenerator\Console\Services;

use Illuminate\Support\Str;
use Shibuyakosuke\LaravelCrudGenerator\Models\Table;

class Test extends CrudAbstract
{
    public function outputFileName(Table $table): string
    {
        return base_path(sprintf('tests/Feature/app/%sCrudTest.php', $table->model));
    }

    public function stab(): string
    {
        $stab = [];
        $stab[] = '<?php';
        $stab[] = '';
        $stab[] = 'namespace Tests\Feature\app;';
        $stab[] = '';
        $stab[] = 'use App\Models\%s;';
        $stab[] = 'use Tests\TestCase;';
        $stab[] = '';
        $stab[] = 'class %sControllerTest extends TestCase';
        $stab[] = '{';
        $stab[] = '    private $response;';
        $stab[] = '';
        $stab[] = '    public function setUp(): void';
        $stab[] = '    {';
        $stab[] = '        parent::setUp();';
        $stab[] = '        $this->response = $this->actingAs(\App\Models\User::find(1));';
        $stab[] = '    }';
        $stab[] = '';
        $stab[] = '    public function testIndex()';
        $stab[] = '    {';
        $stab[] = '        $this->response->get(route(\'%s.index\'))->assertOk();';
        $stab[] = '    }';
        $stab[] = '';
        $stab[] = '    public function testCreate()';
        $stab[] = '    {';
        $stab[] = '        $this->response->get(route(\'%s.create\'))->assertOk();';
        $stab[] = '    }';
        $stab[] = '';
        $stab[] = '    public function testShow()';
        $stab[] = '    {';
        $stab[] = '        $%s = %s::query()->inRandomOrder()->first();';
        $stab[] = '        $this->response->get(route(\'%s.show\', compact(\'%s\')))->assertOk();';
        $stab[] = '    }';
        $stab[] = '';
        $stab[] = '    public function testEdit()';
        $stab[] = '    {';
        $stab[] = '        $%s = %s::query()->inRandomOrder()->first();';
        $stab[] = '        $this->response->get(route(\'%s.edit\', compact(\'%s\')))->assertOk();';
        $stab[] = '    }';
        $stab[] = '}';
        $stab[] = '';
        return implode(PHP_EOL, $stab);
    }

    public function callback(Table $table): array
    {
        if (empty($table->TABLE_COMMENT)) {
            return [];
        }

        if (empty($table->primarykey)) {
            return [];
        }

        return [
            $table->model,
            $table->model,
            $table->TABLE_NAME,
            $table->TABLE_NAME,
            Str::singular($table->TABLE_NAME),
            $table->model,
            $table->TABLE_NAME,
            Str::singular($table->TABLE_NAME),
            Str::singular($table->TABLE_NAME),
            $table->model,
            $table->TABLE_NAME,
            Str::singular($table->TABLE_NAME),
        ];
    }
}
