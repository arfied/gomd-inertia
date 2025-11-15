<?php

namespace App\Providers;

use App\Application\Commands\CommandBus;
use App\Application\Patient\Commands\EnrollPatient;
use App\Application\Patient\Handlers\EnrollPatientHandler;
use App\Application\Queries\QueryBus;
use App\Services\EventStore;
use App\Services\EventStoreContract;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(EventStore::class, fn () => new EventStore());
        $this->app->alias(EventStore::class, EventStoreContract::class);

        $this->app->singleton(CommandBus::class, fn () => new CommandBus());
        $this->app->singleton(QueryBus::class, fn () => new QueryBus());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->app->resolving(CommandBus::class, function (CommandBus $bus, $app) {
            $bus->register(
                EnrollPatient::class,
                $app->make(EnrollPatientHandler::class)
            );
        });
    }
}
