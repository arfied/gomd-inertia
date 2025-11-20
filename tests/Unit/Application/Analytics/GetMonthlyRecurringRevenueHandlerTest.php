<?php

namespace Tests\Unit\Application\Analytics;

use App\Application\Analytics\Queries\GetMonthlyRecurringRevenue;
use App\Application\Analytics\Queries\GetMonthlyRecurringRevenueHandler;
use App\Models\SubscriptionAnalyticsView;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetMonthlyRecurringRevenueHandlerTest extends TestCase
{
    use RefreshDatabase;

    private GetMonthlyRecurringRevenueHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->handler = new GetMonthlyRecurringRevenueHandler();
    }

    public function test_calculate_current_month_mrr(): void
    {
        $now = Carbon::now();
        $monthStart = $now->clone()->startOfMonth();
        $monthEnd = $now->clone()->endOfMonth();

        // Create active subscriptions
        SubscriptionAnalyticsView::create([
            'subscription_id' => 1,
            'user_id' => 1,
            'plan_name' => 'Premium',
            'monthly_price' => 99.99,
            'status' => 'active',
            'started_at' => $monthStart->subMonth(),
            'ended_at' => null,
        ]);

        SubscriptionAnalyticsView::create([
            'subscription_id' => 2,
            'user_id' => 2,
            'plan_name' => 'Basic',
            'monthly_price' => 49.99,
            'status' => 'active',
            'started_at' => $monthStart->subMonth(),
            'ended_at' => null,
        ]);

        $query = new GetMonthlyRecurringRevenue(includeTrend: false);
        $result = $this->handler->handle($query);

        $this->assertEquals(149.98, $result['current_mrr']);
        $this->assertArrayHasKey('previous_mrr', $result);
        $this->assertArrayHasKey('change_amount', $result);
        $this->assertArrayHasKey('change_percent', $result);
    }

    public function test_exclude_cancelled_subscriptions(): void
    {
        $now = Carbon::now();
        $monthStart = $now->clone()->startOfMonth();

        // Active subscription
        SubscriptionAnalyticsView::create([
            'subscription_id' => 1,
            'user_id' => 1,
            'monthly_price' => 99.99,
            'status' => 'active',
            'started_at' => $monthStart->subMonth(),
            'ended_at' => null,
        ]);

        // Cancelled subscription
        SubscriptionAnalyticsView::create([
            'subscription_id' => 2,
            'user_id' => 2,
            'monthly_price' => 49.99,
            'status' => 'cancelled',
            'started_at' => $monthStart->subMonth(),
            'cancelled_at' => $monthStart,
        ]);

        $query = new GetMonthlyRecurringRevenue(includeTrend: false);
        $result = $this->handler->handle($query);

        $this->assertEquals(99.99, $result['current_mrr']);
    }

    public function test_include_trend_data(): void
    {
        $now = Carbon::now();
        $monthStart = $now->clone()->startOfMonth();

        SubscriptionAnalyticsView::create([
            'subscription_id' => 1,
            'user_id' => 1,
            'monthly_price' => 99.99,
            'status' => 'active',
            'started_at' => $monthStart->subMonths(12),
            'ended_at' => null,
        ]);

        $query = new GetMonthlyRecurringRevenue(includeTrend: true, trendMonths: 3);
        $result = $this->handler->handle($query);

        $this->assertArrayHasKey('trend', $result);
        $this->assertCount(3, $result['trend']);
        $this->assertArrayHasKey('month', $result['trend'][0]);
        $this->assertArrayHasKey('mrr', $result['trend'][0]);
    }

    public function test_calculate_specific_month(): void
    {
        $specificMonth = '2025-06';
        $query = new GetMonthlyRecurringRevenue(month: $specificMonth, includeTrend: false);
        $result = $this->handler->handle($query);

        $this->assertEquals($specificMonth, $result['month']);
    }
}

