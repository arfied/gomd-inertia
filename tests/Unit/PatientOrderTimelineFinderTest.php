<?php

namespace Tests\Unit;

use App\Application\Order\EloquentPatientOrderTimelineFinder;
use App\Domain\Order\Events\OrderCreated;
use App\Models\MedicationOrder;
use App\Models\PatientEnrollment;
use App\Models\StoredEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientOrderTimelineFinderTest extends TestCase
{
    use RefreshDatabase;

    private EloquentPatientOrderTimelineFinder $finder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->finder = new EloquentPatientOrderTimelineFinder();
    }

    public function test_find_timeline_by_user_id_returns_empty_collection_when_no_orders(): void
    {
        $user = User::factory()->create();

        $result = $this->finder->findTimelineByUserId($user->id);

        $this->assertEmpty($result);
    }

    public function test_find_timeline_by_user_id_returns_order_events(): void
    {
        $user = User::factory()->create();
        $order = MedicationOrder::factory()->create(['patient_id' => $user->id]);

        $event = StoredEvent::create([
            'aggregate_uuid' => $order->id,
            'aggregate_type' => 'order',
            'event_type' => 'order.created',
            'event_data' => ['patient_id' => $user->id],
            'metadata' => [],
            'occurred_at' => now(),
        ]);

        $result = $this->finder->findTimelineByUserId($user->id);

        $this->assertCount(1, $result);
        $this->assertEquals($event->id, $result->first()->id);
    }

    public function test_find_timeline_by_user_id_filters_by_event_type(): void
    {
        $user = User::factory()->create();
        $order = MedicationOrder::factory()->create(['patient_id' => $user->id]);

        StoredEvent::create([
            'aggregate_uuid' => $order->id,
            'aggregate_type' => 'order',
            'event_type' => 'order.created',
            'event_data' => ['patient_id' => $user->id],
            'metadata' => [],
            'occurred_at' => now(),
        ]);

        StoredEvent::create([
            'aggregate_uuid' => $order->id,
            'aggregate_type' => 'order',
            'event_type' => 'order.fulfilled',
            'event_data' => ['patient_id' => $user->id],
            'metadata' => [],
            'occurred_at' => now()->addMinute(),
        ]);

        $result = $this->finder->findTimelineByUserId($user->id, filter: 'fulfilled');

        $this->assertCount(1, $result);
        $this->assertEquals('order.fulfilled', $result->first()->event_type);
    }

    public function test_find_timeline_by_user_id_respects_limit(): void
    {
        $user = User::factory()->create();
        $order = MedicationOrder::factory()->create(['patient_id' => $user->id]);

        for ($i = 0; $i < 5; $i++) {
            StoredEvent::create([
                'aggregate_uuid' => $order->id,
                'aggregate_type' => 'order',
                'event_type' => 'order.created',
                'event_data' => ['patient_id' => $user->id],
                'metadata' => [],
                'occurred_at' => now()->addSeconds($i),
            ]);
        }

        $result = $this->finder->findTimelineByUserId($user->id, limit: 2);

        $this->assertCount(2, $result);
    }
}

