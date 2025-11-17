<?php

namespace App\Providers;

use App\Application\Commands\CommandBus;
use App\Application\Patient\Commands\EnrollPatient;
use App\Application\Patient\Commands\UpdatePatientDemographics;
use App\Application\Patient\EloquentPatientActivityFinder;
use App\Application\Patient\EloquentPatientActivityProjector;
use App\Application\Patient\EloquentPatientDemographicsFinder;
use App\Application\Patient\EloquentPatientDemographicsProjector;
use App\Application\Patient\EloquentPatientEnrollmentFinder;
use App\Application\Patient\EloquentPatientEnrollmentProjector;
use App\Application\Patient\EloquentPatientSubscriptionFinder;
use App\Application\Patient\EloquentPatientTimelineFinder;
use App\Application\Patient\Handlers\EnrollPatientHandler;
use App\Application\Patient\Handlers\UpdatePatientDemographicsHandler;
use App\Application\Patient\PatientActivityFinder;
use App\Application\Patient\PatientActivityProjector;
use App\Application\Patient\PatientDemographicsFinder;
use App\Application\Patient\PatientDemographicsProjector;
use App\Application\Patient\PatientEnrollmentFinder;
use App\Application\Patient\PatientEnrollmentProjector;
use App\Application\Patient\PatientSubscriptionFinder;
use App\Application\Patient\PatientTimelineFinder;
use App\Application\Patient\Queries\GetPatientDemographicsByPatientUuid;
use App\Application\Patient\Queries\GetPatientDemographicsByPatientUuidHandler;
use App\Application\Patient\Queries\GetPatientDemographicsByUserId;
use App\Application\Patient\Queries\GetPatientDemographicsByUserIdHandler;
use App\Application\Patient\Queries\GetPatientEnrollmentByUserId;
use App\Application\Patient\Queries\GetPatientEnrollmentByUserIdHandler;
use App\Application\Patient\Queries\GetPatientEnrollmentByPatientUuid;
use App\Application\Patient\Queries\GetPatientEnrollmentByPatientUuidHandler;
use App\Application\Patient\Queries\GetPatientEventTimelineByUserId;
use App\Application\Patient\Queries\GetPatientEventTimelineByUserIdHandler;
use App\Application\Patient\Queries\GetPatientSubscriptionByUserId;
use App\Application\Patient\Queries\GetPatientSubscriptionByUserIdHandler;
use App\Application\Patient\Queries\GetRecentPatientActivityByUserId;
use App\Application\Patient\Queries\GetRecentPatientActivityByUserIdHandler;
use App\Application\Queries\QueryBus;
use App\Services\EventStore;
use App\Services\EventStoreContract;
use App\Services\EventStoreMonitor;
use App\Services\ProjectionRegistry;
use App\Services\ProjectionReplayService;
use App\Services\QueueMonitor;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(EventStoreMonitor::class, fn () => new EventStoreMonitor());
        $this->app->singleton(EventStore::class, fn ($app) => new EventStore($app->make(EventStoreMonitor::class)));
        $this->app->alias(EventStore::class, EventStoreContract::class);

        $this->app->singleton(CommandBus::class, fn () => new CommandBus());
        $this->app->singleton(QueryBus::class, fn () => new QueryBus());

        $this->app->singleton(ProjectionRegistry::class, fn () => new ProjectionRegistry());
        $this->app->singleton(QueueMonitor::class, fn () => new QueueMonitor());

        $this->app->singleton(ProjectionReplayService::class, function ($app): ProjectionReplayService {
            return new ProjectionReplayService(
                dispatcher: $app->make(Dispatcher::class),
                registry: $app->make(ProjectionRegistry::class),
            );
        });

        $this->app->bind(PatientEnrollmentProjector::class, EloquentPatientEnrollmentProjector::class);
        $this->app->bind(PatientEnrollmentFinder::class, EloquentPatientEnrollmentFinder::class);

        $this->app->bind(PatientDemographicsProjector::class, EloquentPatientDemographicsProjector::class);
        $this->app->bind(PatientDemographicsFinder::class, EloquentPatientDemographicsFinder::class);

        $this->app->bind(PatientActivityProjector::class, EloquentPatientActivityProjector::class);
        $this->app->bind(PatientActivityFinder::class, EloquentPatientActivityFinder::class);

        $this->app->bind(PatientTimelineFinder::class, EloquentPatientTimelineFinder::class);
        $this->app->bind(PatientSubscriptionFinder::class, EloquentPatientSubscriptionFinder::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $queueMonitor = $this->app->make(QueueMonitor::class);

        Queue::after(function ($event) use ($queueMonitor): void {
            if ($event instanceof \Illuminate\Queue\Events\JobProcessed) {
                $queueMonitor->recordProcessed($event);
            }
        });

        Queue::failing(function (\Illuminate\Queue\Events\JobFailed $event) use ($queueMonitor): void {
            $queueMonitor->recordFailed($event);
        });

        $this->app->resolving(CommandBus::class, function (CommandBus $bus, $app) {
            $bus->register(
                EnrollPatient::class,
                $app->make(EnrollPatientHandler::class)
            );

            $bus->register(
                UpdatePatientDemographics::class,
                $app->make(UpdatePatientDemographicsHandler::class)
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
                GetPatientDemographicsByUserId::class,
                $app->make(GetPatientDemographicsByUserIdHandler::class)
            );

            $bus->register(
                GetPatientDemographicsByPatientUuid::class,
                $app->make(GetPatientDemographicsByPatientUuidHandler::class)
            );

            $bus->register(
                GetRecentPatientActivityByUserId::class,
                $app->make(GetRecentPatientActivityByUserIdHandler::class)
            );

            $bus->register(
                GetPatientEventTimelineByUserId::class,
                $app->make(GetPatientEventTimelineByUserIdHandler::class)
            );

            $bus->register(
                GetPatientSubscriptionByUserId::class,
                $app->make(GetPatientSubscriptionByUserIdHandler::class)
            );
        });
    }
}
