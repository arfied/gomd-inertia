<?php

namespace App\Http\Controllers;

use App\Application\Referral\Queries\GetReferralNetworkHierarchy;
use App\Application\Referral\Queries\GetAgentReferralPerformance;
use App\Application\Referral\Handlers\GetReferralNetworkHierarchyHandler;
use App\Application\Referral\Handlers\GetAgentReferralPerformanceHandler;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * ReferralNetworkController
 *
 * Handles referral network visualization and performance queries.
 */
class ReferralNetworkController extends Controller
{
    /**
     * Get the referral network hierarchy for an agent.
     */
    public function hierarchy(Request $request, int $agentId): JsonResponse
    {
        $depth = $request->query('depth', 10);

        $query = new GetReferralNetworkHierarchy(
            agentId: $agentId,
            depth: (int) $depth,
        );

        $hierarchy = (new GetReferralNetworkHierarchyHandler())->handle($query);

        return response()->json($hierarchy);
    }

    /**
     * Get referral performance metrics for an agent.
     */
    public function performance(Request $request, int $agentId): JsonResponse
    {
        $period = $request->query('period', 'month');

        $query = new GetAgentReferralPerformance(
            agentId: $agentId,
            period: $period,
        );

        $performance = (new GetAgentReferralPerformanceHandler())->handle($query);

        return response()->json($performance);
    }
}

