<?php

namespace Tests\Unit\Application\Analytics;

use App\Application\Analytics\Queries\GetLifetimeValue;
use App\Application\Analytics\Queries\GetLifetimeValueHandler;
use App\Models\SubscriptionAnalyticsView;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetLifetimeValueHandlerTest extends TestCase
{
    use RefreshDatabase;

    private GetLifetimeValueHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->handler = new GetLifetimeValueHandler();
    }

    public function test_calculate_average_ltv(): void
    {
        // Create subscriptions with different LTVs
        SubscriptionAnalyticsView::create([
            'subscription_id' => 1,
            'user_id' => 1,
            'status' => 'active',
            'monthly_price' => 100.00,
            'months_active' => 12,
            'total_revenue' => 1200.00,
        ]);

        SubscriptionAnalyticsView::create([
            'subscription_id' => 2,
            'user_id' => 2,
            'status' => 'active',
            'monthly_price' => 50.00,
            'months_active' => 6,
            'total_revenue' => 300.00,
        ]);

        $query = new GetLifetimeValue(includeByPlan: false, includeDistribution: false);
        $result = $this->handler->handle($query);

        $this->assertEquals(2, $result['total_subscriptions']);
        $this->assertArrayHasKey('average_ltv', $result);
    }

    public function test_include_ltv_by_plan(): void
    {
        SubscriptionAnalyticsView::create([
            'subscription_id' => 1,
            'user_id' => 1,
            'status' => 'active',
            'plan_name' => 'Premium',
            'monthly_price' => 100.00,
            'total_revenue' => 1200.00,
        ]);

        SubscriptionAnalyticsView::create([
            'subscription_id' => 2,
            'user_id' => 2,
            'status' => 'active',
            'plan_name' => 'Basic',
            'monthly_price' => 50.00,
            'total_revenue' => 300.00,
        ]);

        $query = new GetLifetimeValue(includeByPlan: true, includeDistribution: false);
        $result = $this->handler->handle($query);

        $this->assertArrayHasKey('by_plan', $result);
        $this->assertCount(2, $result['by_plan']);
        $this->assertArrayHasKey('plan', $result['by_plan'][0]);
        $this->assertArrayHasKey('average_ltv', $result['by_plan'][0]);
    }

    public function test_include_ltv_distribution(): void
    {
        // Create subscriptions in different LTV buckets
        SubscriptionAnalyticsView::create([
            'subscription_id' => 1,
            'user_id' => 1,
            'status' => 'active',
            'monthly_price' => 10.00,
            'months_active' => 5,
            'total_revenue' => 50.00,
        ]);

        SubscriptionAnalyticsView::create([
            'subscription_id' => 2,
            'user_id' => 2,
            'status' => 'active',
            'monthly_price' => 50.00,
            'months_active' => 10,
            'total_revenue' => 500.00,
        ]);

        SubscriptionAnalyticsView::create([
            'subscription_id' => 3,
            'user_id' => 3,
            'status' => 'active',
            'monthly_price' => 100.00,
            'months_active' => 12,
            'total_revenue' => 1200.00,
        ]);

        $query = new GetLifetimeValue(includeDistribution: true, includeByPlan: false);
        $result = $this->handler->handle($query);

        $this->assertArrayHasKey('distribution', $result);
        $this->assertArrayHasKey('under_100', $result['distribution']);
        $this->assertArrayHasKey('100_to_500', $result['distribution']);
        $this->assertArrayHasKey('500_to_1000', $result['distribution']);
        $this->assertArrayHasKey('1000_to_5000', $result['distribution']);
        $this->assertArrayHasKey('over_5000', $result['distribution']);
    }

    public function test_zero_ltv_with_no_subscriptions(): void
    {
        $query = new GetLifetimeValue(includeByPlan: false, includeDistribution: false);
        $result = $this->handler->handle($query);

        $this->assertEquals(0, $result['average_ltv']);
        $this->assertEquals(0, $result['total_subscriptions']);
    }
}

