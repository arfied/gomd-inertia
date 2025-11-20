<?php

use App\Models\Agent;
use App\Models\AgentCommission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Agent Commission Dashboard Controller', function () {
    it('returns 403 if user is not an agent', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/agent/commission/dashboard');

        expect($response->status())->toBe(403);
    });

    it('returns commission dashboard page for agent', function () {
        $user = User::factory()->create();
        $agent = Agent::factory()->create(['user_id' => $user->id]);

        // Create some commissions
        AgentCommission::factory(5)->create([
            'agent_id' => $agent->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->get('/agent/commission/dashboard');

        expect($response->status())->toBe(200);
        $response->assertInertia(fn ($page) => $page
            ->component('agent/CommissionDashboard')
            ->has('earnings_overview')
            ->has('recent_commissions')
            ->has('referral_hierarchy')
        );
    });

    it('returns earnings overview with period parameter', function () {
        $user = User::factory()->create();
        $agent = Agent::factory()->create(['user_id' => $user->id]);

        AgentCommission::factory()->create([
            'agent_id' => $agent->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->get('/agent/commission/dashboard?period=month');

        expect($response->status())->toBe(200);
        $response->assertInertia(fn ($page) => $page
            ->where('earnings_overview.period', 'month')
        );
    });

    it('returns paginated recent commissions', function () {
        $user = User::factory()->create();
        $agent = Agent::factory()->create(['user_id' => $user->id]);

        AgentCommission::factory(15)->create([
            'agent_id' => $agent->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->get('/agent/commission/dashboard?limit=10&page=1');

        expect($response->status())->toBe(200);
        $response->assertInertia(fn ($page) => $page
            ->where('recent_commissions.total', 15)
            ->where('recent_commissions.per_page', 10)
        );
    });

    it('returns referral hierarchy', function () {
        $user = User::factory()->create();
        $agent = Agent::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get('/agent/commission/dashboard');

        expect($response->status())->toBe(200);
        $response->assertInertia(fn ($page) => $page
            ->has('referral_hierarchy')
        );
    });
});

