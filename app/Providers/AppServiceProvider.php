<?php

namespace App\Providers;

use App\Application\Commands\CommandBus;
use App\Application\Patient\Commands\EnrollPatient;
use App\Application\Patient\EloquentPatientActivityFinder;
use App\Application\Patient\EloquentPatientActivityProjector;
use App\Application\Patient\EloquentPatientEnrollmentFinder;
use App\Application\Patient\EloquentPatientEnrollmentProjector;
use App\Application\Patient\EloquentPatientTimelineFinder;
use App\Application\Patient\Handlers\EnrollPatientHandler;
use App\Application\Patient\PatientActivityFinder;
use App\Application\Patient\PatientActivityProjector;
use App\Application\Patient\PatientEnrollmentFinder;
use App\Application\Patient\PatientEnrollmentProjector;
use App\Application\Patient\PatientTimelineFinder;
use App\Application\Patient\Queries\GetPatientEnrollmentByUserId;
use App\Application\Patient\Queries\GetPatientEnrollmentByUserIdHandler;
use App\Application\Patient\Queries\GetPatientEnrollmentByPatientUuid;
use App\Application\Patient\Queries\GetPatientEnrollmentByPatientUuidHandler;
use App\Application\Patient\Queries\GetPatientEventTimelineByUserId;
use App\Application\Patient\Queries\GetPatientEventTimelineByUserIdHandler;
use App\Application\Patient\Queries\GetRecentPatientActivityByUserId;
use App\Application\Patient\Queries\GetRecentPatientActivityByUserIdHandler;
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

        $this->app->bind(PatientEnrollmentProjector::class, EloquentPatientEnrollmentProjector::class);
        $this->app->bind(PatientEnrollmentFinder::class, EloquentPatientEnrollmentFinder::class);

        $this->app->bind(PatientActivityProjector::class, EloquentPatientActivityProjector::class);
        $this->app->bind(PatientActivityFinder::class, EloquentPatientActivityFinder::class);

        $this->app->bind(PatientTimelineFinder::class, EloquentPatientTimelineFinder::class);
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

        $this->app->resolving(QueryBus::class, function (QueryBus $bus, $app) {
            $bus->register(
                GetPatientEnrollmentByUserId::class,
                $app->make(GetPatientEnrollmentByUserIdHandler::class)
            );

            $bus->register(
                GetPatientEnrollmentByPatientUuid::class,
                $app->make(GetPatientEnrollmentByPatientUuidHandler::class)
            );

            $bus->register(
                GetRecentPatientActivityByUserId::class,
                $app->make(GetRecentPatientActivityByUserIdHandler::class)
            );

            $bus->register(
                GetPatientEventTimelineByUserId::class,
                $app->make(GetPatientEventTimelineByUserIdHandler::class)
            );
        });
    }
}
