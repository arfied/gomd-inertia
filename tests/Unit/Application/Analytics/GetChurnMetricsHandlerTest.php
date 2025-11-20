<?php

namespace Tests\Unit\Application\Analytics;

use App\Application\Analytics\Queries\GetChurnMetrics;
use App\Application\Analytics\Queries\GetChurnMetricsHandler;
use App\Models\SubscriptionAnalyticsView;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetChurnMetricsHandlerTest extends TestCase
{
    use RefreshDatabase;

    private GetChurnMetricsHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->handler = new GetChurnMetricsHandler();
    }

    public function test_calculate_churn_rate(): void
    {
        $now = Carbon::now();
        $monthStart = $now->clone()->startOfMonth();
        $monthEnd = $now->clone()->endOfMonth();

        // 10 active subscriptions at start of month (started before month)
        for ($i = 1; $i <= 10; $i++) {
            SubscriptionAnalyticsView::create([
                'subscription_id' => $i,
                'user_id' => $i,
                'monthly_price' => 99.99,
                'status' => 'active',
                'started_at' => $monthStart->clone()->subMonths(2),
                'ended_at' => null,
            ]);
        }

        // 2 subscriptions churned during month (were active at start, cancelled during month)
        for ($i = 11; $i <= 12; $i++) {
            SubscriptionAnalyticsView::create([
                'subscription_id' => $i,
                'user_id' => $i,
                'monthly_price' => 99.99,
                'status' => 'cancelled',
                'started_at' => $monthStart->clone()->subMonths(2),
                'cancelled_at' => $monthStart->addDays(5),
                'churn_reason' => 'user_requested',
            ]);
        }

        $query = new GetChurnMetrics(includeReasons: false, includeTrend: false);
        $result = $this->handler->handle($query);

        $this->assertEquals(20.0, $result['churn_rate']); // 2/12 = 16.67% or 2/10 = 20%
        $this->assertEquals(2, $result['churned_count']);
    }

    public function test_include_churn_reasons(): void
    {
        $now = Carbon::now();
        $monthStart = $now->clone()->startOfMonth();

        // Create 10 active subscriptions (started before month)
        for ($i = 1; $i <= 10; $i++) {
            SubscriptionAnalyticsView::create([
                'subscription_id' => $i,
                'user_id' => $i,
                'status' => 'active',
                'started_at' => $monthStart->clone()->subMonths(2),
                'ended_at' => null,
            ]);
        }

        // Create churned subscriptions with different reasons
        SubscriptionAnalyticsView::create([
            'subscription_id' => 11,
            'user_id' => 11,
            'status' => 'cancelled',
            'started_at' => $monthStart->clone()->subMonths(2),
            'cancelled_at' => $monthStart->addDays(5),
            'churn_reason' => 'user_requested',
        ]);

        SubscriptionAnalyticsView::create([
            'subscription_id' => 12,
            'user_id' => 12,
            'status' => 'cancelled',
            'started_at' => $monthStart->clone()->subMonths(2),
            'cancelled_at' => $monthStart->addDays(10),
            'churn_reason' => 'payment_failed',
        ]);

        $query = new GetChurnMetrics(includeReasons: true, includeTrend: false);
        $result = $this->handler->handle($query);

        $this->assertArrayHasKey('churn_reasons', $result);
        $this->assertCount(2, $result['churn_reasons']);
    }

    public function test_include_trend_data(): void
    {
        $now = Carbon::now();
        $monthStart = $now->clone()->startOfMonth();

        // Create active subscriptions
        for ($i = 1; $i <= 10; $i++) {
            SubscriptionAnalyticsView::create([
                'subscription_id' => $i,
                'user_id' => $i,
                'status' => 'active',
                'started_at' => $monthStart->subMonths(12),
                'ended_at' => null,
            ]);
        }

        $query = new GetChurnMetrics(includeTrend: true, trendMonths: 3);
        $result = $this->handler->handle($query);

        $this->assertArrayHasKey('trend', $result);
        $this->assertCount(3, $result['trend']);
        $this->assertArrayHasKey('month', $result['trend'][0]);
        $this->assertArrayHasKey('churn_rate', $result['trend'][0]);
    }

    public function test_zero_churn_when_no_subscriptions(): void
    {
        $query = new GetChurnMetrics(includeReasons: false, includeTrend: false);
        $result = $this->handler->handle($query);

        $this->assertEquals(0, $result['churn_rate']);
        $this->assertEquals(0, $result['churned_count']);
    }
}

