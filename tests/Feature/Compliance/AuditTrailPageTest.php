<?php

use App\Models\User;
use App\Models\AuditTrailReadModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('redirects guests to login', function () {
    $response = $this->get('/compliance/audit-trail');

    $response->assertRedirect('/login');
});

it('renders audit trail page for authenticated users', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/compliance/audit-trail');

    $response->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('compliance/AuditTrail')
            ->has('auditTrail')
        );
});

it('passes audit trail data to the page', function () {
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

    $response = $this->actingAs($user)->get('/compliance/audit-trail');

    $response->assertInertia(fn ($page) => $page
        ->component('compliance/AuditTrail')
        ->has('auditTrail.data', 1)
        ->where('auditTrail.data.0.access_type', 'view')
    );
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
        'ip_address' => '192.168.1.1',
        'user_agent' => 'Mozilla/5.0',
    ]);

    AuditTrailReadModel::create([
        'audit_uuid' => 'audit-uuid-456',
        'patient_id' => 'patient-456',
        'accessed_by' => $user->id,
        'access_type' => 'view',
        'resource' => 'medical_records',
        'accessed_at' => now(),
        'ip_address' => '192.168.1.1',
        'user_agent' => 'Mozilla/5.0',
    ]);

    $response = $this->actingAs($user)->get('/compliance/audit-trail?patient_id=patient-123');

    $response->assertInertia(fn ($page) => $page
        ->has('auditTrail.data', 1)
        ->where('auditTrail.data.0.patient_id', 'patient-123')
    );
});

it('filters audit trail by access_type', function () {
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

    AuditTrailReadModel::create([
        'audit_uuid' => 'audit-uuid-456',
        'patient_id' => 'patient-123',
        'accessed_by' => $user->id,
        'access_type' => 'edit',
        'resource' => 'medical_records',
        'accessed_at' => now(),
        'ip_address' => '192.168.1.1',
        'user_agent' => 'Mozilla/5.0',
    ]);

    $response = $this->actingAs($user)->get('/compliance/audit-trail?access_type=view');

    $response->assertInertia(fn ($page) => $page
        ->has('auditTrail.data', 1)
        ->where('auditTrail.data.0.access_type', 'view')
    );
});

it('paginates audit trail records', function () {
    $user = User::factory()->create();

    for ($i = 0; $i < 50; $i++) {
        AuditTrailReadModel::create([
            'audit_uuid' => "audit-uuid-{$i}",
            'patient_id' => 'patient-123',
            'accessed_by' => $user->id,
            'access_type' => 'view',
            'resource' => 'medical_records',
            'accessed_at' => now(),
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Mozilla/5.0',
        ]);
    }

    $response = $this->actingAs($user)->get('/compliance/audit-trail');

    $response->assertInertia(fn ($page) => $page
        ->has('auditTrail.data', 25)
        ->where('auditTrail.current_page', 1)
        ->where('auditTrail.per_page', 25)
        ->where('auditTrail.total', 50)
    );
});

