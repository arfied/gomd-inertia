<?php

use App\Models\MedicalRecord;
use App\Models\PatientEnrollment;
use App\Models\StoredEvent;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('returns unauthorized for guests', function () {
    $response = $this->getJson(route('patient.documents.index'));

    $response->assertStatus(401);

    $response = $this->postJson(route('patient.documents.store'));

    $response->assertStatus(401);
});

it('returns documents for the authenticated user based on existing medical_records', function () {
    /** @var User $user */
    $user = User::factory()->create();

    MedicalRecord::create([
        'patient_id' => $user->id,
        'doctor_id' => null,
        'record_type' => 'lab-result',
        'description' => 'Lab report 1',
        'record_date' => '2024-01-01',
        'file_path' => '/docs/report1.pdf',
    ]);

    MedicalRecord::create([
        'patient_id' => $user->id,
        'doctor_id' => null,
        'record_type' => 'xray',
        'description' => 'X-ray report',
        'record_date' => '2024-02-01',
        'file_path' => '/docs/report2.pdf',
    ]);

    $this->actingAs($user);

    $response = $this->getJson(route('patient.documents.index'));

    $response
        ->assertOk()
        ->assertJsonCount(2, 'documents')
        ->assertJsonPath('documents.0.record_type', 'xray')
        ->assertJsonPath('documents.0.description', 'X-ray report');
});

it('rejects document upload when the patient is not enrolled', function () {
    Storage::fake('public');

    /** @var User $user */
    $user = User::factory()->create();

    $this->actingAs($user);

    $file = UploadedFile::fake()->create('report.pdf', 100, 'application/pdf');

    $response = $this->post(route('patient.documents.store'), [
        'record_type' => 'lab-result',
        'description' => 'Test document',
        'record_date' => '2024-01-01',
        'file' => $file,
    ]);

    $response
        ->assertStatus(422)
        ->assertJson([
            'message' => 'Patient is not enrolled.',
        ]);

    Storage::disk('public')->assertMissing('patient-documents/'.$user->id.'/report.pdf');
});

it('uploads document via event-sourced command and projection', function () {
    Storage::fake('public');

    /** @var User $user */
    $user = User::factory()->create();

    $this->actingAs($user);

    // Enroll the patient so we have a patient_uuid
    $this->postJson(route('patient.enrollment.store'))
        ->assertCreated();

    $file = UploadedFile::fake()->create('report.pdf', 100, 'application/pdf');

    $response = $this->post(route('patient.documents.store'), [
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

    // Assert file stored
    $storedPath = $response->json('document.file_path');
    expect($storedPath)->not->toBeNull();
    Storage::disk('public')->assertExists($storedPath);

    // Assert medical_records row created
    $hasRecord = MedicalRecord::query()
        ->where('patient_id', $user->id)
        ->where('record_type', 'lab-result')
        ->where('description', 'Lab report')
        ->exists();

    expect($hasRecord)->toBeTrue();

    // Assert event stored in event_store
    $hasEvent = StoredEvent::query()
        ->where('aggregate_type', 'patient')
        ->where('event_type', 'patient.document_uploaded')
        ->where('event_data->patient_id', $user->id)
        ->where('event_data->record_type', 'lab-result')
        ->exists();

    expect($hasEvent)->toBeTrue();
});

