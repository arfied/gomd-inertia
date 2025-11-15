<?php

namespace Tests;

use App\Application\Patient\Queries\GetPatientEnrollmentByUserId;
use App\Application\Queries\QueryBus;
use App\Models\PatientEnrollment;
use App\Models\StoredEvent;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PatientEnrollmentFlowTest extends TestCase
{
    use RefreshDatabase;
    protected function setUp(): void
    {
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('pdo_sqlite extension is required for this test.');
        }

        parent::setUp();
    }



    public function test_registered_user_is_enrolled_as_patient_end_to_end(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        event(new Registered($user));

        // Event store has a patient.enrolled event for this user
        $hasEvent = StoredEvent::query()
            ->where('aggregate_type', 'patient')
            ->where('event_type', 'patient.enrolled')
            ->where('event_data->user_id', $user->id)
            ->exists();

        $this->assertTrue($hasEvent);

        // Projection created for this user
        $enrollment = PatientEnrollment::query()
            ->where('user_id', $user->id)
            ->first();

        $this->assertNotNull($enrollment);
        $this->assertSame($user->id, $enrollment->user_id);
        $this->assertSame('registration', $enrollment->source);

        // QueryBus can read the enrollment back
        /** @var QueryBus $queryBus */
        $queryBus = app(QueryBus::class);

        $result = $queryBus->ask(new GetPatientEnrollmentByUserId($user->id));

        $this->assertNotNull($result);
        $this->assertSame($enrollment->id, $result->id);
    }
}

