<?php

use App\Models\Agent;
use App\Models\ReferralLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Agent Referral Links', function () {
    it('displays agent referral links page', function () {
        $user = User::factory()->create();
        $agent = Agent::factory()->create(['user_id' => $user->id]);
        ReferralLink::factory()->count(3)->create(['agent_id' => $agent->id]);

        $response = $this->actingAs($user)->get(route('agent.referral-links.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('agent/ReferralLinks')
            ->has('referral_links.data', 3)
            ->has('performance_summary')
            ->has('performance_by_type')
        );
    });

    it('returns 403 if user is not an agent', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('agent.referral-links.index'));

        $response->assertStatus(403);
    });

    it('calculates performance metrics correctly', function () {
        $user = User::factory()->create();
        $agent = Agent::factory()->create(['user_id' => $user->id]);
        ReferralLink::factory()->create([
            'agent_id' => $agent->id,
            'clicks_count' => 100,
            'conversions_count' => 10,
            'conversion_rate' => 10.0,
        ]);

        $response = $this->actingAs($user)->get(route('agent.referral-links.index'));

        $response->assertInertia(fn ($page) => $page
            ->where('performance_summary.total_links', 1)
            ->where('performance_summary.total_clicks', 100)
            ->where('performance_summary.total_conversions', 10)
            ->where('performance_summary.overall_conversion_rate', 10)
        );
    });
});

