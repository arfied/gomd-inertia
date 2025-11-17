<?php

use App\Models\MedicalRecord;
use App\Models\PatientEnrollment;
use App\Models\StoredEvent;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

it('requires authentication and staff role for patient documents endpoints', function () {
    $patientUuid = (string) Str::uuid();

    $this->getJson(route('patients.documents.index', ['patientUuid' => $patientUuid]))
        ->assertStatus(401);

    $this->postJson(route('patients.documents.store', ['patientUuid' => $patientUuid]))
        ->assertStatus(401);

    /** @var User $user */
    $user = User::factory()->create();
    $user->role = 'patient';

    $this->actingAs($user);

    $this->getJson(route('patients.documents.index', ['patientUuid' => $patientUuid]))
        ->assertStatus(403);

    $this->postJson(route('patients.documents.store', ['patientUuid' => $patientUuid]))
        ->assertStatus(403);
});

it('returns documents for a patient by patient uuid for staff', function () {
    /** @var User $staff */
    $staff = User::factory()->create();
    $staff->role = 'staff';

    /** @var User $patient */
    $patient = User::factory()->create();
    $patient->role = 'patient';

    $patientUuid = (string) Str::uuid();

    PatientEnrollment::create([
        'patient_uuid' => $patientUuid,
        'user_id' => $patient->id,
        'source' => 'manual',
        'metadata' => null,
        'enrolled_at' => now(),
    ]);

    MedicalRecord::create([
        'patient_id' => $patient->id,
        'doctor_id' => null,
        'record_type' => 'lab-result',
        'description' => 'Lab report 1',
        'record_date' => '2024-01-01',
        'file_path' => '/docs/report1.pdf',
    ]);

    MedicalRecord::create([
        'patient_id' => $patient->id,
        'doctor_id' => null,
        'record_type' => 'xray',
        'description' => 'X-ray report',
        'record_date' => '2024-02-01',
        'file_path' => '/docs/report2.pdf',
    ]);

    $this->actingAs($staff);

    $response = $this->getJson(route('patients.documents.index', ['patientUuid' => $patientUuid]));

    $response
        ->assertOk()
        ->assertJsonCount(2, 'documents')
        ->assertJsonPath('documents.0.record_type', 'xray')
        ->assertJsonPath('documents.0.description', 'X-ray report');
});

it('uploads a document for a patient via staff endpoint', function () {
    Storage::fake('public');

    /** @var User $staff */
    $staff = User::factory()->create();
    $staff->role = 'staff';

    /** @var User $patient */
    $patient = User::factory()->create();
    $patient->role = 'patient';

    $patientUuid = (string) Str::uuid();

    PatientEnrollment::create([
        'patient_uuid' => $patientUuid,
        'user_id' => $patient->id,
        'source' => 'manual',
        'metadata' => null,
        'enrolled_at' => now(),
    ]);

    $this->actingAs($staff);

    $file = UploadedFile::fake()->create('report.pdf', 100, 'application/pdf');

    $response = $this->post(route('patients.documents.store', ['patientUuid' => $patientUuid]), [
        'record_type' => 'lab-result',
        'description' => 'Lab report',
        'record_date' => '2024-01-02',
        'file' => $file,
    ]);

    $response
        ->assertStatus(201)
        ->assertJsonPath('document.record_type', 'lab-result')
        ->assertJsonPath('document.description', 'Lab report')
        ->assertJsonPath('document.record_date', '2024-01-02');

    $storedPath = $response->json('document.file_path');
    expect($storedPath)->not->toBeNull();
    Storage::disk('public')->assertExists($storedPath);

    $this->assertDatabaseHas('medical_records', [
        'patient_id' => $patient->id,
        'record_type' => 'lab-result',
        'description' => 'Lab report',
        'doctor_id' => $staff->id,
    ]);

    $hasEvent = StoredEvent::query()
        ->where('aggregate_type', 'patient')
        ->where('event_type', 'patient.document_uploaded')
        ->where('event_data->patient_id', $patient->id)
        ->where('event_data->record_type', 'lab-result')
        ->exists();

    expect($hasEvent)->toBeTrue();
});

