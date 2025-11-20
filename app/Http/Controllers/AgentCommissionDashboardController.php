<?php

namespace App\Http\Controllers;

use App\Application\Commission\Queries\GetAgentEarningsOverview;
use App\Application\Commission\Queries\GetRecentCommissions;
use App\Application\Commission\Queries\GetAgentReferralHierarchy;
use App\Application\Queries\QueryBus;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AgentCommissionDashboardController extends Controller
{
    public function show(Request $request, QueryBus $queryBus): Response
    {
        $user = $request->user();

        // Verify user is an agent
        abort_unless($user && $user->agent, 403);

        $agentId = $user->agent->id;

        // Get earnings overview
        $earningsOverview = $queryBus->ask(
            new GetAgentEarningsOverview($agentId, $request->query('period', 'month'))
        );

        // Get recent commissions
        $recentCommissions = $queryBus->ask(
            new GetRecentCommissions(
                $agentId,
                $request->query('limit', 10),
                $request->query('page', 1)
            )
        );

        // Get referral hierarchy
        $referralHierarchy = $queryBus->ask(
            new GetAgentReferralHierarchy($agentId, 3)
        );

        return Inertia::render('agent/CommissionDashboard', [
            'earnings_overview' => $earningsOverview,
            'recent_commissions' => $recentCommissions,
            'referral_hierarchy' => $referralHierarchy,
        ]);
    }
}

