<?php

use App\Models\Agent;
use App\Models\ReferralLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Referral Network', function () {
    it('retrieves referral network hierarchy', function () {
        $user = User::factory()->create();
        $agent = Agent::factory()->create(['user_id' => $user->id]);
        $referralLink = ReferralLink::factory()->create(['agent_id' => $agent->id]);

        $response = $this->actingAs($user)->get(route('agent.referral-network.hierarchy', $agent->id));

        $response->assertStatus(200);
        $response->assertJsonPath('id', $agent->id);
        $response->assertJsonPath('name', $agent->user->name);
        $response->assertJsonPath('tier', $agent->tier);
    });

    it('retrieves referral performance metrics', function () {
        $user = User::factory()->create();
        $agent = Agent::factory()->create(['user_id' => $user->id]);
        ReferralLink::factory()->create([
            'agent_id' => $agent->id,
            'clicks_count' => 10,
            'conversions_count' => 2,
        ]);

        $response = $this->actingAs($user)->get(route('agent.referral-network.performance', $agent->id));

        $response->assertStatus(200);
        $response->assertJsonPath('agent_id', $agent->id);
        $response->assertJsonPath('total_clicks', 10);
        $response->assertJsonPath('total_conversions', 2);
    });

    it('builds hierarchy with downline agents', function () {
        $user1 = User::factory()->create();
        $agent1 = Agent::factory()->create(['user_id' => $user1->id]);

        $user2 = User::factory()->create();
        $agent2 = Agent::factory()->create([
            'user_id' => $user2->id,
            'referring_agent_id' => $agent1->id,
        ]);

        $response = $this->actingAs($user1)->get(route('agent.referral-network.hierarchy', $agent1->id));

        $response->assertStatus(200);
        $response->assertJsonPath('downline_count', 1);
        $response->assertJsonPath('children.0.id', $agent2->id);
    });

    it('calculates average conversion rate', function () {
        $user = User::factory()->create();
        $agent = Agent::factory()->create(['user_id' => $user->id]);
        ReferralLink::factory()->create([
            'agent_id' => $agent->id,
            'conversion_rate' => 20.0,
        ]);
        ReferralLink::factory()->create([
            'agent_id' => $agent->id,
            'conversion_rate' => 40.0,
        ]);

        $response = $this->actingAs($user)->get(route('agent.referral-network.hierarchy', $agent->id));

        $response->assertStatus(200);
        expect($response->json('average_conversion_rate'))->toBe(30);
    });
});

