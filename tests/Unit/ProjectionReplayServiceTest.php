<?php

namespace Tests\Unit;

use App\Domain\Patient\Events\PatientEnrolled;
use App\Models\StoredEvent;
use App\Services\ProjectionReplayOptions;
use App\Services\ProjectionReplayService;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectionReplayServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('pdo_sqlite extension is required for this test.');
        }

        parent::setUp();
    }

    public function test_replays_events_and_dispatches_in_order(): void
    {
        $first = StoredEvent::create([
            'aggregate_uuid' => 'patient-1',
            'aggregate_type' => 'patient',
            'event_type' => 'patient.enrolled',
            'event_data' => ['user_id' => 1],
            'metadata' => ['source' => 'test-1'],
            'occurred_at' => now()->subMinute(),
        ]);

        $second = StoredEvent::create([
            'aggregate_uuid' => 'patient-2',
            'aggregate_type' => 'patient',
            'event_type' => 'patient.enrolled',
            'event_data' => ['user_id' => 2],
            'metadata' => ['source' => 'test-2'],
            'occurred_at' => now(),
        ]);

        $fakeDispatcher = new class implements Dispatcher {
            /** @var array<int, object> */
            public array $dispatched = [];

            public function listen($events, $listener = null): void {}

            public function hasListeners($eventName): bool
            {
                return false;
            }

            public function subscribe($subscriber): void {}

            public function until($event, $payload = []): mixed
            {
                return null;
            }

            public function dispatch($event, $payload = [], $halt = false): mixed
            {
                $this->dispatched[] = $event;

                return null;
            }

            public function push($event, $payload = []): void {}

            public function flush($event): void {}

            public function forget($event): void {}

            public function forgetPushed(): void {}
        };

        $service = new ProjectionReplayService($fakeDispatcher);

        $result = $service->replay(new ProjectionReplayOptions(aggregateType: 'patient'));

        $this->assertSame(2, $result->eventsProcessed);
        $this->assertSame(2, $result->eventsDispatched);

        $this->assertCount(2, $fakeDispatcher->dispatched);
        $this->assertInstanceOf(PatientEnrolled::class, $fakeDispatcher->dispatched[0]);
        $this->assertInstanceOf(PatientEnrolled::class, $fakeDispatcher->dispatched[1]);

        $this->assertSame('patient-1', $fakeDispatcher->dispatched[0]->aggregateUuid);
        $this->assertSame('patient-2', $fakeDispatcher->dispatched[1]->aggregateUuid);
    }

    public function test_honours_id_range_and_dry_run(): void
    {
        $first = StoredEvent::create([
            'aggregate_uuid' => 'patient-1',
            'aggregate_type' => 'patient',
            'event_type' => 'patient.enrolled',
            'event_data' => ['user_id' => 1],
            'metadata' => ['source' => 'test-1'],
            'occurred_at' => now()->subMinutes(2),
        ]);

        $second = StoredEvent::create([
            'aggregate_uuid' => 'patient-2',
            'aggregate_type' => 'patient',
            'event_type' => 'patient.enrolled',
            'event_data' => ['user_id' => 2],
            'metadata' => ['source' => 'test-2'],
            'occurred_at' => now()->subMinute(),
        ]);

        $fakeDispatcher = new class implements Dispatcher {
            /** @var array<int, object> */
            public array $dispatched = [];

            public function listen($events, $listener = null): void {}

            public function hasListeners($eventName): bool
            {
                return false;
            }

            public function subscribe($subscriber): void {}

            public function until($event, $payload = []): mixed
            {
                return null;
            }

            public function dispatch($event, $payload = [], $halt = false): mixed
            {
                $this->dispatched[] = $event;

                return null;
            }

            public function push($event, $payload = []): void {}

            public function flush($event): void {}

            public function forget($event): void {}

            public function forgetPushed(): void {}
        };

        $service = new ProjectionReplayService($fakeDispatcher);

        $result = $service->replay(new ProjectionReplayOptions(
            aggregateType: 'patient',
            fromId: $second->id,
            dryRun: true,
        ));

        $this->assertSame(1, $result->eventsProcessed);
        $this->assertSame(0, $result->eventsDispatched);
        $this->assertCount(0, $fakeDispatcher->dispatched);
    }
}

