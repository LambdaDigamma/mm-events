<?php

namespace LambdaDigamma\MMEvents;

use Illuminate\Support\ServiceProvider;
use LambdaDigamma\MMEvents\Commands\MMEventsCommand;

class MMEventsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/mm-events.php' => config_path('mm-events.php'),
            ], 'config');

            $this->publishes([
                __DIR__ . '/../resources/views' => base_path('resources/views/vendor/mm-events'),
            ], 'views');

            $migrationFileName = 'create_mm_events_table.php';
            if (! $this->migrationFileExists($migrationFileName)) {
                $this->publishes([
                    __DIR__ . "/../database/migrations/{$migrationFileName}.stub" => database_path('migrations/' . date('Y_m_d_His', time()) . '_' . $migrationFileName),
                ], 'migrations');
            }

            $this->commands([
                MMEventsCommand::class,
            ]);
        }

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'mm-events');
    }

    public function register()
    {
        /*
        * Register the service provider for the dependency.
        */
        $this->app->register('LaravelArchivable\LaravelArchivableServiceProvider');

        $this->mergeConfigFrom(__DIR__ . '/../config/mm-events.php', 'mm-events');
    }

    public static function migrationFileExists(string $migrationFileName): bool
    {
        $len = strlen($migrationFileName);
        foreach (glob(database_path("migrations/*.php")) as $filename) {
            if ((substr($filename, -$len) === $migrationFileName)) {
                return true;
            }
        }

        return false;
    }
}
