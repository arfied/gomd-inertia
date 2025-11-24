<?php

use App\Models\Medication;
use App\Models\SignupReadModel;
use Illuminate\Support\Str;

it('selects medication by name in signup flow', function () {
    $medication = Medication::factory()->create([
        'status' => 'approved',
        'name' => 'Aspirin 500mg',
    ]);

    // Start signup
    $response = $this->postJson('/signup/start', [
        'signup_path' => 'medication_first',
    ]);

    $response->assertStatus(200);
    $signupId = $response->json('signup_id');
    expect($signupId)->not->toBeNull();

    // Select medication by name
    $response = $this->postJson('/signup/select-medication', [
        'signup_id' => $signupId,
        'medication_name' => $medication->name,
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Medication selected',
        ]);

    // Verify medication_name is stored in read model as array
    $signup = SignupReadModel::where('signup_uuid', $signupId)->first();
    expect($signup)->not->toBeNull();
    expect($signup->medication_name)->toBeArray();
    expect($signup->medication_name)->toContain($medication->name);
});

it('allows selecting multiple medications', function () {
    $medication1 = Medication::factory()->create([
        'status' => 'approved',
        'name' => 'Aspirin 500mg',
    ]);

    $medication2 = Medication::factory()->create([
        'status' => 'approved',
        'name' => 'Ibuprofen 200mg',
    ]);

    // Start signup
    $response = $this->postJson('/signup/start', [
        'signup_path' => 'medication_first',
    ]);

    $signupId = $response->json('signup_id');

    // Select first medication
    $this->postJson('/signup/select-medication', [
        'signup_id' => $signupId,
        'medication_name' => $medication1->name,
    ])->assertStatus(200);

    // Select second medication
    $this->postJson('/signup/select-medication', [
        'signup_id' => $signupId,
        'medication_name' => $medication2->name,
    ])->assertStatus(200);

    // Verify both medications are stored
    $signup = SignupReadModel::where('signup_uuid', $signupId)->first();
    expect($signup->medication_name)->toBeArray();
    expect($signup->medication_name)->toHaveCount(2);
    expect($signup->medication_name)->toContain($medication1->name);
    expect($signup->medication_name)->toContain($medication2->name);
});

it('validates medication_name is required', function () {
    $signupId = (string) Str::uuid();

    $response = $this->postJson('/signup/select-medication', [
        'signup_id' => $signupId,
        // missing medication_name
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['medication_name']);
});

it('validates medication_name is a string', function () {
    $signupId = (string) Str::uuid();

    $response = $this->postJson('/signup/select-medication', [
        'signup_id' => $signupId,
        'medication_name' => 123, // should be string
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['medication_name']);
});

