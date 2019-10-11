<?php

namespace LaravelEnso\Tables\Tests\units\Services;

use Faker\Factory;
use Illuminate\Support\Facades\Route;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Tables\app\Services\Config;
use LaravelEnso\Tables\app\Services\Template;
use LaravelEnso\Tables\app\Services\Table\Request;
use LaravelEnso\Tables\Tests\units\Services\TestModel;
use LaravelEnso\Tables\Tests\units\Services\TestTable;
use LaravelEnso\Tables\Tests\units\Services\BuilderTestEnum;

trait SetUp
{
    private $faker;
    private $testModel;
    private $table;
    private $config;

    protected function setUp(): void
    {
        parent::setUp();

        // $this->withoutExceptionHandling();

        $this->faker = Factory::create();

        Route::any('route')->name('testTables.tableData');
        Route::getRoutes()->refreshNameLookups();

        TestModel::createTable();
        
        $this->testModel = $this->createTestModel();

        $columns = $filters = $intervals = $params = [];

        $meta = ['length' => 10, 'search' => '', 'searchMode' => 'full'];

        $request = new Request(
            $columns, $meta, $filters, $intervals, $params
        );

        $request->columns()->push(new Obj([
            'name' => 'name',
            'data' => 'name',
            'meta' => ['searchable' => true],
        ]));
        
        $this->table = new TestTable();

        $template = (new Template())->build($this->table);

        $this->config = new Config($request, $template);
    }

    protected function createTestModel()
    {
        return TestModel::create([
            'name' => $this->faker->name,
            'is_active' => $this->faker->boolean,
            'price' => $this->faker->numberBetween(1000, 10000),
            'color' => BuilderTestEnum::Red,
        ]);
    }
}
