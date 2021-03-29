<?php

namespace LambdaDigamma\MMEvents\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use LambdaDigamma\MMEvents\MMEventsServiceProvider;
use LaravelArchivable\LaravelArchivableServiceProvider;
use LaravelPublishable\LaravelPublishableServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\LaravelRay\RayServiceProvider;
use Spatie\Translatable\TranslatableServiceProvider;

class TestCase extends Orchestra
{
    public function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'LambdaDigamma\\MMEvents\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            MMEventsServiceProvider::class,
            LaravelArchivableServiceProvider::class,
            LaravelPublishableServiceProvider::class,
            TranslatableServiceProvider::class,
            RayServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function setUpDatabase()
    {
        $this->loadLaravelMigrations();
        include_once __DIR__.'/../database/migrations/create_mm_events_table.php.stub';
        (new \CreateMMEventsTable())->up();
    }
}
