<?php

namespace App\Application\Referral\Handlers;

use App\Application\Referral\Queries\GetAgentReferralPerformance;
use App\Models\Agent;
use App\Models\ReferralClick;
use Carbon\Carbon;

/**
 * GetAgentReferralPerformanceHandler
 *
 * Calculates referral performance metrics for an agent.
 */
class GetAgentReferralPerformanceHandler
{
    public function handle(GetAgentReferralPerformance $query): array
    {
        $agent = Agent::find($query->agentId);

        if (!$agent) {
            return [];
        }

        $startDate = $this->getStartDate($query->period);
        $referralLinks = $agent->referralLinks()->where('created_at', '>=', $startDate)->get();

        $totalClicks = $referralLinks->sum('clicks_count');
        $totalConversions = $referralLinks->sum('conversions_count');
        $conversionRate = $totalClicks > 0 ? ($totalConversions / $totalClicks) * 100 : 0;

        return [
            'agent_id' => $agent->id,
            'agent_name' => $agent->user->name,
            'tier' => $agent->tier,
            'period' => $query->period,
            'total_referral_links' => $referralLinks->count(),
            'total_clicks' => $totalClicks,
            'total_conversions' => $totalConversions,
            'conversion_rate' => round($conversionRate, 2),
            'by_type' => $this->getPerformanceByType($referralLinks),
        ];
    }

    private function getStartDate(?string $period): Carbon
    {
        return match($period) {
            'week' => now()->subWeek(),
            'month' => now()->subMonth(),
            'quarter' => now()->subQuarter(),
            'year' => now()->subYear(),
            default => now()->subMonth(),
        };
    }

    private function getPerformanceByType($referralLinks): array
    {
        $performance = [];

        foreach ($referralLinks->groupBy('referral_type') as $type => $links) {
            $clicks = $links->sum('clicks_count');
            $conversions = $links->sum('conversions_count');
            $rate = $clicks > 0 ? ($conversions / $clicks) * 100 : 0;

            $performance[$type] = [
                'clicks' => $clicks,
                'conversions' => $conversions,
                'conversion_rate' => round($rate, 2),
            ];
        }

        return $performance;
    }
}

