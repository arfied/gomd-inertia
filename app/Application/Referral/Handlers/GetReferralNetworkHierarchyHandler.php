<?php

namespace App\Application\Referral\Handlers;

use App\Application\Referral\Queries\GetReferralNetworkHierarchy;
use App\Models\Agent;

/**
 * GetReferralNetworkHierarchyHandler
 *
 * Builds the referral network hierarchy for visualization.
 */
class GetReferralNetworkHierarchyHandler
{
    public function handle(GetReferralNetworkHierarchy $query): array
    {
        $agent = Agent::find($query->agentId);

        if (!$agent) {
            return [];
        }

        return $this->buildHierarchy($agent, $query->depth ?? 10);
    }

    private function buildHierarchy(Agent $agent, int $depth): array
    {
        if ($depth <= 0) {
            return [];
        }

        $referralLinks = $agent->referralLinks()->active()->get();
        $referrals = $agent->referrals()->get();

        return [
            'id' => $agent->id,
            'uuid' => $agent->user->id,
            'name' => $agent->user->name,
            'tier' => $agent->tier,
            'status' => $agent->status,
            'email' => $agent->user->email,
            'referral_code' => $agent->referral_code,
            'referral_links_count' => $referralLinks->count(),
            'total_clicks' => $referralLinks->sum('clicks_count'),
            'total_conversions' => $referralLinks->sum('conversions_count'),
            'average_conversion_rate' => $referralLinks->avg('conversion_rate') ?? 0,
            'downline_count' => $referrals->count(),
            'children' => $referrals->map(fn($referral) => $this->buildHierarchy($referral, $depth - 1))->toArray(),
        ];
    }
}

