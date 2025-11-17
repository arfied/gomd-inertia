<?php

use App\Models\PatientEnrollment;
use App\Models\StoredEvent;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;

use function Pest\Laravel\assertDatabaseHas;

test('projections:replay rebuilds patient enrollment projection from event store', function () {
    if (! extension_loaded('pdo_sqlite')) {
        $this->markTestSkipped('pdo_sqlite extension is required for this test.');
    }

    $user = User::factory()->create();

    $patientUuid = 'patient-uuid-replay-1';

    PatientEnrollment::query()->delete();

    StoredEvent::create([
        'aggregate_uuid' => $patientUuid,
        'aggregate_type' => 'patient',
        'event_type' => 'patient.enrolled',
        'event_data' => ['user_id' => $user->id],
        'metadata' => ['source' => 'replay-test'],
        'occurred_at' => now(),
    ]);

    Artisan::call('projections:replay', [
        '--projection' => 'patient-enrollment',
        '--aggregate-type' => 'patient',
    ]);

    assertDatabaseHas('patient_enrollments', [
        'patient_uuid' => $patientUuid,
        'user_id' => $user->id,
        'source' => 'replay-test',
    ]);
});

test('projections:replay supports dry run without mutating projections', function () {
    if (! extension_loaded('pdo_sqlite')) {
        $this->markTestSkipped('pdo_sqlite extension is required for this test.');
    }

    $user = User::factory()->create();

    $patientUuid = 'patient-uuid-replay-2';

    PatientEnrollment::query()->delete();

    StoredEvent::create([
        'aggregate_uuid' => $patientUuid,
        'aggregate_type' => 'patient',
        'event_type' => 'patient.enrolled',
        'event_data' => ['user_id' => $user->id],
        'metadata' => ['source' => 'replay-test'],
        'occurred_at' => now(),
    ]);

    Artisan::call('projections:replay', [
        '--projection' => 'patient-enrollment',
        '--aggregate-type' => 'patient',
        '--dry-run' => true,
    ]);

    expect(PatientEnrollment::query()->count())->toBe(0);
});

