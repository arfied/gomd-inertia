<?php

namespace App\Http\Controllers;

use App\Application\Analytics\Queries\GetMonthlyRecurringRevenue;
use App\Application\Analytics\Queries\GetChurnMetrics;
use App\Application\Analytics\Queries\GetLifetimeValue;
use App\Application\Queries\QueryBus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller for subscription analytics dashboard.
 *
 * Provides endpoints for MRR, churn, and LTV metrics.
 */
class SubscriptionAnalyticsDashboardController extends Controller
{
    public function __construct(private QueryBus $queryBus)
    {
    }

    /**
     * Get Monthly Recurring Revenue (MRR) metrics.
     */
    public function mrr(Request $request): JsonResponse
    {
        $this->authorize('viewAnalytics', 'subscription');

        $month = $request->query('month');
        $includeTrend = $request->boolean('include_trend', true);
        $trendMonths = $request->integer('trend_months', 12);

        $metrics = $this->queryBus->ask(
            new GetMonthlyRecurringRevenue(
                month: $month,
                includeTrend: $includeTrend,
                trendMonths: $trendMonths,
            )
        );

        return response()->json($metrics);
    }

    /**
     * Get churn metrics.
     */
    public function churn(Request $request): JsonResponse
    {
        $this->authorize('viewAnalytics', 'subscription');

        $month = $request->query('month');
        $includeReasons = $request->boolean('include_reasons', true);
        $includeTrend = $request->boolean('include_trend', true);
        $trendMonths = $request->integer('trend_months', 12);

        $metrics = $this->queryBus->ask(
            new GetChurnMetrics(
                month: $month,
                includeReasons: $includeReasons,
                includeTrend: $includeTrend,
                trendMonths: $trendMonths,
            )
        );

        return response()->json($metrics);
    }

    /**
     * Get Lifetime Value (LTV) metrics.
     */
    public function ltv(Request $request): JsonResponse
    {
        $this->authorize('viewAnalytics', 'subscription');

        $month = $request->query('month');
        $includeByPlan = $request->boolean('include_by_plan', true);
        $includeDistribution = $request->boolean('include_distribution', true);

        $metrics = $this->queryBus->ask(
            new GetLifetimeValue(
                month: $month,
                includeByPlan: $includeByPlan,
                includeDistribution: $includeDistribution,
            )
        );

        return response()->json($metrics);
    }

    /**
     * Get all analytics metrics in one request.
     */
    public function dashboard(Request $request): JsonResponse
    {
        $this->authorize('viewAnalytics', 'subscription');

        $month = $request->query('month');

        $mrr = $this->queryBus->ask(new GetMonthlyRecurringRevenue(month: $month));
        $churn = $this->queryBus->ask(new GetChurnMetrics(month: $month));
        $ltv = $this->queryBus->ask(new GetLifetimeValue(month: $month));

        return response()->json([
            'mrr' => $mrr,
            'churn' => $churn,
            'ltv' => $ltv,
        ]);
    }
}

