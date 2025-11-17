<?php

namespace App\Providers;

use App\Application\Commands\CommandBus;
use App\Application\Order\Commands\CancelOrder;
use App\Application\Order\Commands\CreateOrder;
use App\Application\Order\Commands\FulfillOrder;
use App\Application\Order\EloquentOrderProjector;
use App\Application\Order\EloquentPatientOrderFinder;
use App\Application\Order\Handlers\CancelOrderHandler;
use App\Application\Order\Handlers\CreateOrderHandler;
use App\Application\Order\Handlers\FulfillOrderHandler;
use App\Application\Order\OrderProjector;
use App\Application\Order\PatientOrderFinder;
use App\Application\Order\Queries\GetPatientOrdersByPatientUuid;
use App\Application\Order\Queries\GetPatientOrdersByPatientUuidHandler;
use App\Application\Order\Queries\GetPatientOrdersByUserId;
use App\Application\Order\Queries\GetPatientOrdersByUserIdHandler;
use App\Application\Patient\Commands\EnrollPatient;
use App\Application\Patient\Commands\RecordPatientAllergy;
use App\Application\Patient\Commands\RecordPatientCondition;
use App\Application\Patient\Commands\RecordPatientMedication;
use App\Application\Patient\Commands\RecordPatientVisitSummary;
use App\Application\Patient\Commands\UpdatePatientDemographics;
use App\Application\Patient\Commands\UploadPatientDocument;
use App\Application\Patient\EloquentPatientActivityFinder;
use App\Application\Patient\EloquentPatientActivityProjector;
use App\Application\Patient\EloquentPatientDemographicsFinder;
use App\Application\Patient\EloquentPatientDemographicsProjector;
use App\Application\Patient\EloquentPatientDocumentFinder;
use App\Application\Patient\EloquentPatientDocumentProjector;
use App\Application\Patient\EloquentPatientEnrollmentFinder;
use App\Application\Patient\EloquentPatientEnrollmentProjector;
use App\Application\Patient\EloquentPatientListFinder;
use App\Application\Patient\EloquentPatientMedicalHistoryFinder;
use App\Application\Patient\EloquentPatientMedicalHistoryProjector;
use App\Application\Patient\EloquentPatientSubscriptionFinder;
use App\Application\Patient\EloquentPatientTimelineFinder;
use App\Application\Patient\Handlers\EnrollPatientHandler;
use App\Application\Patient\Handlers\RecordPatientAllergyHandler;
use App\Application\Patient\Handlers\RecordPatientConditionHandler;
use App\Application\Patient\Handlers\RecordPatientMedicationHandler;
use App\Application\Patient\Handlers\RecordPatientVisitSummaryHandler;
use App\Application\Patient\Handlers\UpdatePatientDemographicsHandler;
use App\Application\Patient\Handlers\UploadPatientDocumentHandler;
use App\Application\Patient\PatientActivityFinder;
use App\Application\Patient\PatientActivityProjector;
use App\Application\Patient\PatientDemographicsFinder;
use App\Application\Patient\PatientDemographicsProjector;
use App\Application\Patient\PatientDocumentFinder;
use App\Application\Patient\PatientDocumentProjector;
use App\Application\Patient\PatientEnrollmentFinder;
use App\Application\Patient\PatientEnrollmentProjector;
use App\Application\Patient\PatientListFinder;
use App\Application\Patient\PatientMedicalHistoryFinder;
use App\Application\Patient\PatientMedicalHistoryProjector;
use App\Application\Patient\PatientSubscriptionFinder;
use App\Application\Patient\PatientTimelineFinder;
use App\Application\Patient\Queries\GetPatientDemographicsByPatientUuid;
use App\Application\Patient\Queries\GetPatientDemographicsByPatientUuidHandler;
use App\Application\Patient\Queries\GetPatientDemographicsByUserId;
use App\Application\Patient\Queries\GetPatientDemographicsByUserIdHandler;
use App\Application\Patient\Queries\GetPatientDocumentsByPatientUuid;
use App\Application\Patient\Queries\GetPatientDocumentsByPatientUuidHandler;
use App\Application\Patient\Queries\GetPatientDocumentsByUserId;
use App\Application\Patient\Queries\GetPatientDocumentsByUserIdHandler;
use App\Application\Patient\Queries\GetPatientEnrollmentByUserId;
use App\Application\Patient\Queries\GetPatientEnrollmentByUserIdHandler;
use App\Application\Patient\Queries\GetPatientEnrollmentByPatientUuid;
use App\Application\Patient\Queries\GetPatientEnrollmentByPatientUuidHandler;
use App\Application\Patient\Queries\GetPatientEventTimelineByUserId;
use App\Application\Patient\Queries\GetPatientEventTimelineByUserIdHandler;
use App\Application\Patient\Queries\GetPatientList;
use App\Application\Patient\Queries\GetPatientListHandler;
use App\Application\Patient\Queries\GetPatientListCount;
use App\Application\Patient\Queries\GetPatientListCountHandler;
use App\Application\Patient\Queries\GetPatientMedicalHistoryByUserId;
use App\Application\Patient\Queries\GetPatientMedicalHistoryByUserIdHandler;
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

        $this->app->bind(PatientDocumentProjector::class, EloquentPatientDocumentProjector::class);
        $this->app->bind(PatientDocumentFinder::class, EloquentPatientDocumentFinder::class);

        $this->app->bind(PatientActivityProjector::class, EloquentPatientActivityProjector::class);
        $this->app->bind(PatientActivityFinder::class, EloquentPatientActivityFinder::class);

        $this->app->bind(PatientTimelineFinder::class, EloquentPatientTimelineFinder::class);
        $this->app->bind(PatientSubscriptionFinder::class, EloquentPatientSubscriptionFinder::class);
        $this->app->bind(PatientListFinder::class, EloquentPatientListFinder::class);
        $this->app->bind(PatientMedicalHistoryFinder::class, EloquentPatientMedicalHistoryFinder::class);
        $this->app->bind(PatientMedicalHistoryProjector::class, EloquentPatientMedicalHistoryProjector::class);
        $this->app->bind(OrderProjector::class, EloquentOrderProjector::class);
        $this->app->bind(PatientOrderFinder::class, EloquentPatientOrderFinder::class);

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
                CreateOrder::class,
                $app->make(CreateOrderHandler::class)
            );

            $bus->register(
                FulfillOrder::class,
                $app->make(FulfillOrderHandler::class)
            );

            $bus->register(
                CancelOrder::class,
                $app->make(CancelOrderHandler::class)
            );

            $bus->register(
                EnrollPatient::class,
                $app->make(EnrollPatientHandler::class)
            );

            $bus->register(
                UpdatePatientDemographics::class,
                $app->make(UpdatePatientDemographicsHandler::class)
            );

            $bus->register(
                UploadPatientDocument::class,
                $app->make(UploadPatientDocumentHandler::class)
            );

            $bus->register(
                RecordPatientAllergy::class,
                $app->make(RecordPatientAllergyHandler::class)
            );

            $bus->register(
                RecordPatientCondition::class,
                $app->make(RecordPatientConditionHandler::class)
            );

            $bus->register(
                RecordPatientMedication::class,
                $app->make(RecordPatientMedicationHandler::class)
            );

            $bus->register(
                RecordPatientVisitSummary::class,
                $app->make(RecordPatientVisitSummaryHandler::class)
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
                GetPatientMedicalHistoryByUserId::class,
                $app->make(GetPatientMedicalHistoryByUserIdHandler::class)
            );

            $bus->register(
                GetPatientEventTimelineByUserId::class,
                $app->make(GetPatientEventTimelineByUserIdHandler::class)
            );

            $bus->register(
                GetPatientSubscriptionByUserId::class,
                $app->make(GetPatientSubscriptionByUserIdHandler::class)
            );

            $bus->register(
                GetPatientDocumentsByUserId::class,
                $app->make(GetPatientDocumentsByUserIdHandler::class)
            );

            $bus->register(
                GetPatientDocumentsByPatientUuid::class,
                $app->make(GetPatientDocumentsByPatientUuidHandler::class)
            );

            $bus->register(
                GetPatientList::class,
                $app->make(GetPatientListHandler::class)
            );

            $bus->register(
                GetPatientListCount::class,
                $app->make(GetPatientListCountHandler::class)
            );
            $bus->register(
                GetPatientOrdersByUserId::class,
                $app->make(GetPatientOrdersByUserIdHandler::class)
            );

            $bus->register(
                GetPatientOrdersByPatientUuid::class,
                $app->make(GetPatientOrdersByPatientUuidHandler::class)
            );

        });
    }
}
