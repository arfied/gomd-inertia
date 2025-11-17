<?php

namespace Tests\Unit;

use App\Domain\Patient\Events\PatientEnrolled;
use App\Models\StoredEvent;
use App\Services\EventStore;
use App\Services\EventStoreMonitor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class EventStoreMonitorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('pdo_sqlite extension is required for this test.');
        }

        parent::setUp();
    }

    public function test_event_store_invokes_monitor_and_updates_metrics(): void
    {
        Cache::shouldReceive('increment')->times(2);
        Log::shouldReceive('info')->once();

        $monitor = new EventStoreMonitor();
        $store = new EventStore($monitor);

        $event = new PatientEnrolled('patient-1', ['user_id' => 1], ['source' => 'test']);

        $stored = $store->store($event);

        $this->assertInstanceOf(StoredEvent::class, $stored);
        $this->assertDatabaseHas('event_store', [
            'aggregate_uuid' => 'patient-1',
            'aggregate_type' => $event::aggregateType(),
            'event_type' => $event::eventType(),
        ]);
    }
}

