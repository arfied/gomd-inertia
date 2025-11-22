<?php

use App\Models\User;
use App\Models\AuditTrailReadModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns unauthorized for guests', function () {
    $response = $this->getJson('/api/audit-trail');

    $response->assertStatus(401);
});

it('returns empty list when no audit records exist', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson('/api/audit-trail');

    $response->assertStatus(200)
        ->assertJsonPath('data', []);
});

it('returns audit trail records for authenticated user', function () {
    $user = User::factory()->create();

    AuditTrailReadModel::create([
        'audit_uuid' => 'audit-uuid-123',
        'patient_id' => 'patient-123',
        'accessed_by' => $user->id,
        'access_type' => 'view',
        'resource' => 'medical_records',
        'accessed_at' => now(),
        'ip_address' => '192.168.1.1',
        'user_agent' => 'Mozilla/5.0',
    ]);

    $response = $this->actingAs($user)->getJson('/api/audit-trail');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.access_type', 'view');
});

it('filters audit trail by patient_id', function () {
    $user = User::factory()->create();

    AuditTrailReadModel::create([
        'audit_uuid' => 'audit-uuid-123',
        'patient_id' => 'patient-123',
        'accessed_by' => $user->id,
        'access_type' => 'view',
        'resource' => 'medical_records',
        'accessed_at' => now(),
    ]);

    AuditTrailReadModel::create([
        'audit_uuid' => 'audit-uuid-456',
        'patient_id' => 'patient-456',
        'accessed_by' => $user->id,
        'access_type' => 'view',
        'resource' => 'medical_records',
        'accessed_at' => now(),
    ]);

    $response = $this->actingAs($user)->getJson('/api/audit-trail?patient_id=patient-123');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.patient_id', 'patient-123');
});

it('exports audit trail records', function () {
    $user = User::factory()->create();

    AuditTrailReadModel::create([
        'audit_uuid' => 'audit-uuid-123',
        'patient_id' => 'patient-123',
        'accessed_by' => $user->id,
        'access_type' => 'view',
        'resource' => 'medical_records',
        'accessed_at' => now(),
    ]);

    $response = $this->actingAs($user)->getJson('/api/audit-trail/export');

    $response->assertStatus(200)
        ->assertJsonPath('total_records', 1)
        ->assertJsonStructure(['total_records', 'records', 'exported_at']);
});

