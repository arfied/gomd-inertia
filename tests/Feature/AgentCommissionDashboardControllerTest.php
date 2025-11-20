<?php

use App\Models\Agent;
use App\Models\AgentCommission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Agent Commission Dashboard Controller', function () {
    it('returns 403 if user is not an agent', function () {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->getJson('/agent/commission/dashboard');
        
        expect($response->status())->toBe(403);
    });

    it('returns commission dashboard data for agent', function () {
        $user = User::factory()->create();
        $agent = Agent::factory()->create(['user_id' => $user->id]);

        // Create some commissions
        AgentCommission::factory(5)->create([
            'agent_id' => $agent->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->getJson('/agent/commission/dashboard');

        expect($response->status())->toBe(200);
        expect($response->json())->toHaveKeys([
            'earnings_overview',
            'recent_commissions',
            'referral_hierarchy',
        ]);
    });

    it('returns earnings overview with period parameter', function () {
        $user = User::factory()->create();
        $agent = Agent::factory()->create(['user_id' => $user->id]);

        AgentCommission::factory()->create([
            'agent_id' => $agent->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->getJson('/agent/commission/dashboard?period=month');

        expect($response->status())->toBe(200);
        expect($response->json('earnings_overview.period'))->toBe('month');
    });

    it('returns paginated recent commissions', function () {
        $user = User::factory()->create();
        $agent = Agent::factory()->create(['user_id' => $user->id]);

        AgentCommission::factory(15)->create([
            'agent_id' => $agent->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->getJson('/agent/commission/dashboard?limit=10&page=1');

        expect($response->status())->toBe(200);
        expect($response->json('recent_commissions.total'))->toBe(15);
        expect($response->json('recent_commissions.per_page'))->toBe(10);
    });

    it('returns referral hierarchy', function () {
        $user = User::factory()->create();
        $agent = Agent::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->getJson('/agent/commission/dashboard');

        expect($response->status())->toBe(200);
        expect($response->json())->toHaveKey('referral_hierarchy');
    });
});

