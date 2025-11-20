<?php

namespace App\Application\Analytics\Queries;

use App\Application\Queries\QueryHandler;
use App\Domain\Shared\Queries\Query;
use App\Models\SubscriptionAnalyticsView;
use Carbon\Carbon;
use InvalidArgumentException;

class GetLifetimeValueHandler implements QueryHandler
{
    public function handle(Query $query): array
    {
        if (!$query instanceof GetLifetimeValue) {
            throw new InvalidArgumentException(
                'GetLifetimeValueHandler can only handle GetLifetimeValue queries.'
            );
        }

        $month = $query->month ? Carbon::createFromFormat('Y-m', $query->month) : Carbon::now();

        // Calculate average LTV for all subscriptions
        $allSubscriptions = SubscriptionAnalyticsView::all();
        $averageLtv = $this->calculateAverageLtv($allSubscriptions);

        $result = [
            'average_ltv' => (float) $averageLtv,
            'total_subscriptions' => $allSubscriptions->count(),
            'month' => $month->format('Y-m'),
        ];

        // Add LTV by plan if requested
        if ($query->includeByPlan) {
            $result['by_plan'] = $this->getLtvByPlan();
        }

        // Add LTV distribution if requested
        if ($query->includeDistribution) {
            $result['distribution'] = $this->getLtvDistribution($allSubscriptions);
        }

        return $result;
    }

    private function calculateAverageLtv($subscriptions): float
    {
        if ($subscriptions->isEmpty()) {
            return 0;
        }

        $totalLtv = $subscriptions->sum(function ($sub) {
            return $this->calculateSubscriptionLtv($sub);
        });

        return $totalLtv / $subscriptions->count();
    }

    private function calculateSubscriptionLtv($subscription): float
    {
        // LTV = Monthly Price × Months Active
        $monthsActive = $subscription->months_active ?? 1;
        $monthlyPrice = $subscription->monthly_price ?? 0;

        return $monthlyPrice * $monthsActive;
    }

    private function getLtvByPlan(): array
    {
        $byPlan = SubscriptionAnalyticsView::groupBy('plan_name')
            ->selectRaw('plan_name, AVG(total_revenue) as avg_ltv, COUNT(*) as count')
            ->get()
            ->map(fn ($row) => [
                'plan' => $row->plan_name,
                'average_ltv' => (float) $row->avg_ltv,
                'subscription_count' => $row->count,
            ])
            ->toArray();

        return $byPlan;
    }

    private function getLtvDistribution($subscriptions): array
    {
        // Define LTV buckets
        $buckets = [
            'under_100' => 0,
            '100_to_500' => 0,
            '500_to_1000' => 0,
            '1000_to_5000' => 0,
            'over_5000' => 0,
        ];

        foreach ($subscriptions as $sub) {
            $ltv = $this->calculateSubscriptionLtv($sub);

            if ($ltv < 100) {
                $buckets['under_100']++;
            } elseif ($ltv < 500) {
                $buckets['100_to_500']++;
            } elseif ($ltv < 1000) {
                $buckets['500_to_1000']++;
            } elseif ($ltv < 5000) {
                $buckets['1000_to_5000']++;
            } else {
                $buckets['over_5000']++;
            }
        }

        return $buckets;
    }
}

