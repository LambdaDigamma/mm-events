<?php

namespace LambdaDigamma\MMEvents;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use LambdaDigamma\MMEvents\Commands\MMEventsCommand;
use LambdaDigamma\MMEvents\Models\Event;

class MMEventsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/mm-events.php' => config_path('mm-events.php'),
            ], 'config');

            $this->publishes([
                __DIR__.'/../resources/views' => base_path('resources/views/vendor/mm-events'),
            ], 'views');

            $migrationFileName = 'create_mm_events_table.php';
            if (! $this->migrationFileExists($migrationFileName)) {
                $this->publishes([
                    __DIR__."/../database/migrations/{$migrationFileName}.stub" => database_path('migrations/'.date('Y_m_d_His', time()).'_'.$migrationFileName),
                ], 'migrations');
            }

            $this->commands([
                MMEventsCommand::class,
            ]);
        }

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'mm-events');
        $this->registerRoutes();
    }

    public function register()
    {
        /*
        * Register the service provider for the dependency.
        */
        $this->app->register('LaravelArchivable\LaravelArchivableServiceProvider');

        $this->mergeConfigFrom(__DIR__.'/../config/mm-events.php', 'mm-events');
    }

    public static function migrationFileExists(string $migrationFileName): bool
    {
        $len = strlen($migrationFileName);
        foreach (glob(database_path('migrations/*.php')) as $filename) {
            if ((substr($filename, -$len) === $migrationFileName)) {
                return true;
            }
        }

        return false;
    }

    protected function registerRoutes(): void
    {
        Route::bind('anyevent', function ($id) {
            return Event::query()
                ->withTrashed()
                ->withNotPublished()
                ->withArchived()
                ->findOrFail($id);
        });

        // Route::group($this->routeConfiguration(), function () {
        //     $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        // });

        Route::group([
            'prefix' => config('mm-events.admin_prefix', 'admin'),
            'as' => config('mm-events.admin_as', 'admin.'),
            'middleware' => config('mm-events.admin_middleware', ['web', 'auth']),
        ], function () {
            $this->loadRoutesFrom(__DIR__.'/../routes/admin.php');
        });
    }

    /**
     * @return (\Illuminate\Config\Repository|mixed)[]
     *
     * @psalm-return array{prefix: \Illuminate\Config\Repository|mixed, middleware: \Illuminate\Config\Repository|mixed}
     */
    protected function routeConfiguration(): array
    {
        return [
            'prefix' => config('mm-events.api_prefix'),
            'middleware' => config('mm-events.api_middleware'),
        ];
    }
}
