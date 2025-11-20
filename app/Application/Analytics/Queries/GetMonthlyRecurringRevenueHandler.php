<?php

namespace App\Application\Analytics\Queries;

use App\Application\Queries\QueryHandler;
use App\Domain\Shared\Queries\Query;
use App\Models\SubscriptionAnalyticsView;
use Carbon\Carbon;
use InvalidArgumentException;

class GetMonthlyRecurringRevenueHandler implements QueryHandler
{
    public function handle(Query $query): array
    {
        if (!$query instanceof GetMonthlyRecurringRevenue) {
            throw new InvalidArgumentException(
                'GetMonthlyRecurringRevenueHandler can only handle GetMonthlyRecurringRevenue queries.'
            );
        }

        $month = $query->month ? Carbon::createFromFormat('Y-m', $query->month) : Carbon::now();
        $monthStart = $month->clone()->startOfMonth();
        $monthEnd = $month->clone()->endOfMonth();

        // Get current month MRR
        $currentMrr = $this->calculateMrrForMonth($monthStart, $monthEnd);

        // Get previous month MRR for comparison
        $previousMonthStart = $monthStart->clone()->subMonth()->startOfMonth();
        $previousMonthEnd = $monthStart->clone()->subMonth()->endOfMonth();
        $previousMrr = $this->calculateMrrForMonth($previousMonthStart, $previousMonthEnd);

        // Calculate change
        $change = $previousMrr > 0 ? (($currentMrr - $previousMrr) / $previousMrr) * 100 : 0;

        $result = [
            'current_mrr' => (float) $currentMrr,
            'previous_mrr' => (float) $previousMrr,
            'change_amount' => (float) ($currentMrr - $previousMrr),
            'change_percent' => round($change, 2),
            'month' => $month->format('Y-m'),
        ];

        // Add trend data if requested
        if ($query->includeTrend) {
            $result['trend'] = $this->calculateTrend($month, $query->trendMonths);
        }

        return $result;
    }

    private function calculateMrrForMonth(Carbon $start, Carbon $end): float
    {
        return (float) SubscriptionAnalyticsView::where('status', 'active')
            ->where(function ($q) use ($start, $end) {
                // Subscription was active during this month
                $q->where('started_at', '<=', $end)
                    ->where(function ($q2) use ($end) {
                        $q2->whereNull('ended_at')
                            ->orWhere('ended_at', '>=', $end);
                    });
            })
            ->sum('monthly_price');
    }

    private function calculateTrend(Carbon $month, int $months): array
    {
        $trend = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $currentMonth = $month->clone()->subMonths($i);
            $monthStart = $currentMonth->clone()->startOfMonth();
            $monthEnd = $currentMonth->clone()->endOfMonth();

            $mrr = $this->calculateMrrForMonth($monthStart, $monthEnd);

            $trend[] = [
                'month' => $monthStart->format('Y-m'),
                'mrr' => (float) $mrr,
            ];
        }

        return $trend;
    }
}

