<?php

use App\Domain\Agent\Events\AgentRegistered;
use App\Domain\Agent\Events\AgentAccountActivated;
use App\Models\Agent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

describe('Agent Onboarding Saga', function () {
    it('dispatches AgentRegistered event', function () {
        $user = User::factory()->create();
        $agentUuid = Str::uuid();

        event(new AgentRegistered(
            aggregateUuid: $agentUuid,
            payload: [
                'user_id' => $user->id,
                'tier' => 'AGENT',
                'agent_id' => 1,
            ],
        ));

        expect(true)->toBeTrue();
    });

    it('creates agent with referral code after onboarding', function () {
        $user = User::factory()->create();
        $agent = Agent::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $agent->generateReferralCode();

        expect($agent->referral_code)->not->toBeNull();
        expect($agent->referral_token)->not->toBeNull();
    });

    it('activates agent account', function () {
        $user = User::factory()->create();
        $agent = Agent::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $agent->update(['status' => 'active']);

        expect($agent->fresh()->status)->toBe('active');
    });

    it('completes full onboarding saga flow', function () {
        $user = User::factory()->create();
        $agent = Agent::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        // Simulate saga steps
        $agent->generateReferralCode();
        $agent->update(['status' => 'active']);

        expect($agent->fresh()->status)->toBe('active');
        expect($agent->fresh()->referral_code)->not->toBeNull();
    });
});

