<?php

namespace App\Application\Analytics\Queries;

use App\Application\Queries\QueryHandler;
use App\Domain\Shared\Queries\Query;
use App\Models\SubscriptionAnalyticsView;
use Carbon\Carbon;
use InvalidArgumentException;

class GetChurnMetricsHandler implements QueryHandler
{
    public function handle(Query $query): array
    {
        if (!$query instanceof GetChurnMetrics) {
            throw new InvalidArgumentException(
                'GetChurnMetricsHandler can only handle GetChurnMetrics queries.'
            );
        }

        $month = $query->month ? Carbon::createFromFormat('Y-m', $query->month) : Carbon::now();
        $monthStart = $month->clone()->startOfMonth();
        $monthEnd = $month->clone()->endOfMonth();

        // Get subscriptions that were active at start of month
        $activeAtStart = SubscriptionAnalyticsView::where('status', 'active')
            ->where('started_at', '<=', $monthStart)
            ->where(function ($q) use ($monthStart) {
                $q->whereNull('ended_at')
                    ->orWhere('ended_at', '>=', $monthStart);
            })
            ->count();

        // Get subscriptions that churned during this month
        $churned = SubscriptionAnalyticsView::where('status', 'cancelled')
            ->whereBetween('cancelled_at', [$monthStart, $monthEnd])
            ->count();

        // Calculate churn rate
        $churnRate = $activeAtStart > 0 ? ($churned / $activeAtStart) * 100 : 0;

        $result = [
            'churn_rate' => round($churnRate, 2),
            'churned_count' => $churned,
            'active_at_start' => $activeAtStart,
            'month' => $month->format('Y-m'),
        ];

        // Add churn reasons breakdown if requested
        if ($query->includeReasons) {
            $result['churn_reasons'] = $this->getChurnReasons($monthStart, $monthEnd);
        }

        // Add trend data if requested
        if ($query->includeTrend) {
            $result['trend'] = $this->calculateTrend($month, $query->trendMonths);
        }

        return $result;
    }

    private function getChurnReasons(Carbon $start, Carbon $end): array
    {
        $reasons = SubscriptionAnalyticsView::where('status', 'cancelled')
            ->whereBetween('cancelled_at', [$start, $end])
            ->groupBy('churn_reason')
            ->selectRaw('churn_reason, COUNT(*) as count')
            ->get()
            ->map(fn ($row) => [
                'reason' => $row->churn_reason ?? 'unknown',
                'count' => $row->count,
            ])
            ->toArray();

        return $reasons;
    }

    private function calculateTrend(Carbon $month, int $months): array
    {
        $trend = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $currentMonth = $month->clone()->subMonths($i);
            $monthStart = $currentMonth->clone()->startOfMonth();
            $monthEnd = $currentMonth->clone()->endOfMonth();

            $activeAtStart = SubscriptionAnalyticsView::where('status', 'active')
                ->where('started_at', '<=', $monthStart)
                ->where(function ($q) use ($monthStart) {
                    $q->whereNull('ended_at')
                        ->orWhere('ended_at', '>=', $monthStart);
                })
                ->count();

            $churned = SubscriptionAnalyticsView::where('status', 'cancelled')
                ->whereBetween('cancelled_at', [$monthStart, $monthEnd])
                ->count();

            $churnRate = $activeAtStart > 0 ? ($churned / $activeAtStart) * 100 : 0;

            $trend[] = [
                'month' => $monthStart->format('Y-m'),
                'churn_rate' => round($churnRate, 2),
                'churned_count' => $churned,
            ];
        }

        return $trend;
    }
}

