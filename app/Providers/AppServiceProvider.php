<?php

namespace App\Providers;

use App\Application\Commands\CommandBus;
use App\Application\Queries\QueryBus;
use App\Services\EventStore;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(EventStore::class, fn () => new EventStore());
        $this->app->singleton(CommandBus::class, fn () => new CommandBus());
        $this->app->singleton(QueryBus::class, fn () => new QueryBus());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
