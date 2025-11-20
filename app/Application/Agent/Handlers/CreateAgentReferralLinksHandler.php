<?php

namespace App\Application\Agent\Handlers;

use App\Enums\ReferralType;
use App\Enums\ReferralLinkStatus;
use App\Models\Agent;
use App\Models\ReferralLink;
use Illuminate\Support\Str;

/**
 * CreateAgentReferralLinksHandler
 *
 * Creates referral links for an agent across different referral types.
 * Called during agent onboarding to set up initial referral links.
 */
class CreateAgentReferralLinksHandler
{
    /**
     * Create referral links for an agent.
     */
    public function handle(int $agentId): void
    {
        $agent = Agent::find($agentId);
        if (!$agent) {
            return;
        }

        // Ensure agent has a referral code
        if (!$agent->referral_code) {
            $agent->generateReferralCode();
        }

        // Create referral links for different types
        $referralTypes = [ReferralType::Patient, ReferralType::Agent, ReferralType::Business];

        foreach ($referralTypes as $type) {
            // Check if link already exists
            $exists = ReferralLink::where('agent_id', $agentId)
                ->where('referral_type', $type->value)
                ->exists();

            if (!$exists) {
                ReferralLink::create([
                    'agent_id' => $agentId,
                    'referral_type' => $type,
                    'referral_code' => strtoupper(Str::random(8)),
                    'referral_token' => Str::uuid(),
                    'clicks_count' => 0,
                    'conversions_count' => 0,
                    'conversion_rate' => 0,
                    'status' => ReferralLinkStatus::Active,
                ]);
            }
        }
    }
}

