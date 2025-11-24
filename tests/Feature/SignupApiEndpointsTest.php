<?php

use App\Models\Medication;
use App\Models\Condition;
use App\Models\SubscriptionPlan;
use App\Models\QuestionnaireReadModel;

it('returns medications list from API', function () {
    $response = $this->getJson('/api/medications');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'generic_name', 'description']
            ]
        ]);

    // Verify data is an array
    expect($response->json('data'))->toBeArray();
});

it('filters medications by search query', function () {
    Medication::factory()->create([
        'status' => 'approved',
        'generic_name' => 'Acetylsalicylic acid',
        'description' => 'Pain reliever',
    ]);

    $response = $this->getJson('/api/medications?search=Acetylsalicylic');

    $response->assertStatus(200);

    // Check if any results contain the generic name
    $data = $response->json('data');
    $found = collect($data)->contains(fn($item) => str_contains($item['generic_name'], 'Acetylsalicylic'));
    expect($found)->toBeTrue();
});

it('returns conditions list from API', function () {
    $response = $this->getJson('/api/conditions');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'description', 'therapeutic_use']
            ],
            'pagination' => ['per_page', 'current_page', 'next_page_url']
        ]);
});

it('filters conditions by search query', function () {
    // Create a condition with a unique name
    $uniqueName = 'Unique Test Condition ' . uniqid();
    Condition::create([
        'name' => $uniqueName,
        'slug' => \Illuminate\Support\Str::slug($uniqueName),
        'therapeutic_use' => 'Test therapeutic use',
    ]);

    $response = $this->getJson('/api/conditions?search=Unique');

    $response->assertStatus(200);

    // Verify we got results
    $data = $response->json('data');
    expect(count($data))->toBeGreaterThan(0);
});

it('returns subscription plans list from API', function () {
    SubscriptionPlan::factory()->count(3)->create([
        'is_active' => true,
        'group_id' => 3,
    ]);

    $response = $this->getJson('/api/plans');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'price', 'duration_months', 'features', 'benefits', 'is_featured']
            ],
            'pagination' => ['per_page', 'current_page', 'next_page_url']
        ])
        ->assertJsonCount(3, 'data');
});

it('filters plans by search query', function () {
    SubscriptionPlan::factory()->create([
        'is_active' => true,
        'group_id' => 3,
        'name' => 'Premium Plan',
    ]);
    SubscriptionPlan::factory()->create([
        'is_active' => true,
        'group_id' => 3,
        'name' => 'Basic Plan',
    ]);

    $response = $this->getJson('/api/plans?search=Premium');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Premium Plan');
});

it('returns single medication by ID', function () {
    $medication = Medication::factory()->create(['status' => 'approved']);

    $response = $this->getJson("/api/medications/{$medication->id}");

    $response->assertStatus(200)
        ->assertJsonPath('data.id', $medication->id)
        ->assertJsonPath('data.name', $medication->name);
});

it('returns single condition by ID', function () {
    $conditionName = 'Test Condition ' . uniqid();
    $condition = Condition::create([
        'name' => $conditionName,
        'slug' => \Illuminate\Support\Str::slug($conditionName),
        'therapeutic_use' => 'Test therapeutic use',
    ]);

    $response = $this->getJson("/api/conditions/{$condition->id}");

    $response->assertStatus(200)
        ->assertJsonPath('data.id', $condition->id)
        ->assertJsonPath('data.name', $condition->name);
});

it('returns single plan by ID', function () {
    $plan = SubscriptionPlan::factory()->create(['is_active' => true]);

    $response = $this->getJson("/api/plans/{$plan->id}");

    $response->assertStatus(200)
        ->assertJsonPath('data.id', $plan->id)
        ->assertJsonPath('data.name', $plan->name);
});

it('returns questionnaire for signup flow', function () {
    $questions = [
        ['id' => 'q1', 'text' => 'How are you feeling?', 'type' => 'text'],
        ['id' => 'q2', 'text' => 'Any allergies?', 'type' => 'checkbox'],
    ];

    QuestionnaireReadModel::create([
        'questionnaire_uuid' => 'questionnaire-uuid-123',
        'title' => 'Signup Questionnaire',
        'description' => 'Initial questionnaire',
        'questions' => $questions,
        'status' => 'active',
    ]);

    $response = $this->getJson('/api/questionnaires');

    $response->assertStatus(200)
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.id', 'q1')
        ->assertJsonPath('data.0.text', 'How are you feeling?');
});

it('returns empty questionnaire when none exist', function () {
    $response = $this->getJson('/api/questionnaires');

    $response->assertStatus(200)
        ->assertJsonPath('data', []);
});

