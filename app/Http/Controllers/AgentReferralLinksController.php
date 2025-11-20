<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\ReferralLink;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * AgentReferralLinksController
 *
 * Handles displaying agent referral links and their performance metrics.
 */
class AgentReferralLinksController extends Controller
{
    /**
     * Display the agent's referral links page.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $agent = $user->agent;

        if (!$agent) {
            abort(403, 'User is not an agent');
        }

        // Get all referral links for this agent with pagination
        $referralLinks = ReferralLink::where('agent_id', $agent->id)
            ->orderBy('created_at', 'desc')
            ->simplePaginate(10);

        // Calculate overall performance metrics
        $allLinks = ReferralLink::where('agent_id', $agent->id)->get();
        $totalClicks = $allLinks->sum('clicks_count');
        $totalConversions = $allLinks->sum('conversions_count');
        $overallConversionRate = $totalClicks > 0 ? ($totalConversions / $totalClicks) * 100 : 0;

        // Group by referral type for summary
        $performanceByType = [];
        foreach ($allLinks->groupBy('referral_type') as $type => $links) {
            $clicks = $links->sum('clicks_count');
            $conversions = $links->sum('conversions_count');
            $rate = $clicks > 0 ? ($conversions / $clicks) * 100 : 0;

            $performanceByType[$type] = [
                'clicks' => $clicks,
                'conversions' => $conversions,
                'conversion_rate' => round($rate, 2),
                'count' => $links->count(),
            ];
        }

        return Inertia::render('agent/ReferralLinks', [
            'referral_links' => $referralLinks,
            'performance_summary' => [
                'total_links' => $allLinks->count(),
                'total_clicks' => $totalClicks,
                'total_conversions' => $totalConversions,
                'overall_conversion_rate' => round($overallConversionRate, 2),
            ],
            'performance_by_type' => $performanceByType,
        ]);
    }
}

