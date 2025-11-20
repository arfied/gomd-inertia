<?php

use App\Models\Agent;
use App\Models\ReferralLink;
use App\Models\ReferralClick;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Referral Tracking', function () {
    it('tracks a referral link click', function () {
        $user = User::factory()->create();
        $agent = Agent::factory()->create(['user_id' => $user->id]);
        $referralLink = ReferralLink::factory()->create([
            'agent_id' => $agent->id,
            'clicks_count' => 0,
        ]);

        $response = $this->actingAs($user)->post(route('referral.track', ['ref' => $referralLink->referral_code]));

        $response->assertStatus(200);
        expect(ReferralClick::count())->toBe(1);
        expect($referralLink->fresh()->clicks_count)->toBe(1);
    });

    it('records a referral conversion', function () {
        $user = User::factory()->create();
        $agent = Agent::factory()->create(['user_id' => $user->id]);
        $referralLink = ReferralLink::factory()->create([
            'agent_id' => $agent->id,
            'conversions_count' => 0,
        ]);
        ReferralClick::factory()->create(['referral_link_id' => $referralLink->id]);

        $response = $this->actingAs($user)->post(route('referral.convert'), [
            'referral_code' => $referralLink->referral_code,
            'converted_entity_id' => 1,
            'converted_entity_type' => 'patient',
        ]);

        $response->assertStatus(200);
        expect($referralLink->fresh()->conversions_count)->toBe(1);
    });

    it('calculates conversion rate correctly', function () {
        $user = User::factory()->create();
        $agent = Agent::factory()->create(['user_id' => $user->id]);
        $referralLink = ReferralLink::factory()->create([
            'agent_id' => $agent->id,
            'clicks_count' => 10,
            'conversions_count' => 2,
        ]);

        $referralLink->updateConversionRate();

        expect($referralLink->fresh()->conversion_rate)->toBe(20.0);
    });

    it('retrieves referral link details', function () {
        $user = User::factory()->create();
        $agent = Agent::factory()->create(['user_id' => $user->id]);
        $referralLink = ReferralLink::factory()->create(['agent_id' => $agent->id]);

        $response = $this->actingAs($user)->get(route('referral.show', $referralLink->referral_code));

        $response->assertStatus(200);
        $response->assertJsonPath('referral_code', $referralLink->referral_code);
    });
});

