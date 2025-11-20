# Subscription Analytics Implementation

## Overview

The subscription analytics system provides real-time insights into subscription metrics including Monthly Recurring Revenue (MRR), churn rate, and Customer Lifetime Value (LTV). The system uses event-driven architecture to populate a materialized view from domain events, enabling fast analytics queries.

## Architecture

### Components

1. **Domain Events** - Subscription and payment events that trigger analytics updates
2. **Event Handlers** - Listen to domain events and update the analytics view
3. **Query Handlers** - Calculate analytics metrics from the view
4. **API Routes** - Expose analytics data via HTTP endpoints
5. **Vue Dashboard** - Interactive frontend for visualizing metrics

### Data Flow

```
Domain Events (SubscriptionCreated, PaymentAttempted, etc.)
    ↓
Event Handlers (SubscriptionCreatedHandler, PaymentAttemptedHandler, etc.)
    ↓
subscription_analytics_view (Materialized View)
    ↓
Query Handlers (GetMonthlyRecurringRevenueHandler, GetChurnMetricsHandler, etc.)
    ↓
API Routes (/analytics/subscription/*)
    ↓
Vue Dashboard (/agent/analytics)
```

## Database Schema

### subscription_analytics_view

Materialized view containing subscription analytics data:

```sql
- subscription_id (UUID, Primary Key)
- user_id (UUID)
- plan_id (UUID)
- plan_name (String)
- monthly_price (Decimal)
- status (String: active, cancelled, expired, pending_payment)
- started_at (Timestamp)
- ended_at (Timestamp)
- cancelled_at (Timestamp, nullable)
- total_revenue (Decimal)
- months_active (Integer)
- churn_reason (String, nullable)
- is_trial (Boolean)
- last_payment_date (Timestamp, nullable)
- next_payment_date (Timestamp, nullable)
```

**Indexes:**
- status
- started_at
- cancelled_at
- plan_id

## Event Handlers

### SubscriptionCreatedHandler

**Triggered by:** `SubscriptionCreated` event

**Action:** Creates initial analytics view entry with subscription details

**Data populated:**
- subscription_id, user_id, plan_id, plan_name
- monthly_price, status, started_at, ended_at
- is_trial

### SubscriptionRenewedHandler

**Triggered by:** `SubscriptionRenewed` event

**Action:** Updates analytics view with renewal data

**Calculations:**
- months_active = diffInMonths(started_at, now)
- total_revenue = monthly_price × months_active
- Updates last_payment_date and next_payment_date

### SubscriptionCancelledHandler

**Triggered by:** `SubscriptionCancelled` event

**Action:** Updates analytics view with cancellation data

**Calculations:**
- months_active = diffInMonths(started_at, cancelled_at)
- total_revenue = monthly_price × months_active
- Extracts churn_reason from event payload
- Sets status to 'cancelled'

### PaymentAttemptedHandler

**Triggered by:** `PaymentAttempted` event

**Action:** Updates payment dates in analytics view

**Data updated:**
- last_payment_date = now()
- next_payment_date = subscription.ends_at

### PaymentFailedHandler

**Triggered by:** `PaymentFailed` event

**Action:** Updates status and churn reason

**Data updated:**
- status = 'pending_payment'
- churn_reason = 'payment_failed'

## Query Handlers

### GetMonthlyRecurringRevenueHandler

**Query:** `GetMonthlyRecurringRevenue`

**Parameters:**
- month (optional): Specific month to query (default: current month)
- include_trend (optional): Include 12-month trend data

**Returns:**
```php
{
    'current_mrr' => float,
    'previous_mrr' => float,
    'change_percent' => float,
    'trend' => [ // if include_trend=true
        ['month' => 'Jan 2025', 'mrr' => 5000],
        ...
    ]
}
```

### GetChurnMetricsHandler

**Query:** `GetChurnMetrics`

**Parameters:**
- month (optional): Specific month to query
- include_trend (optional): Include trend data
- include_reasons (optional): Include churn reasons breakdown

**Returns:**
```php
{
    'churn_rate' => float,
    'churned_count' => int,
    'active_at_start' => int,
    'churn_reasons' => [ // if include_reasons=true
        ['reason' => 'payment_failed', 'count' => 5],
        ...
    ],
    'trend' => [...] // if include_trend=true
}
```

### GetLifetimeValueHandler

**Query:** `GetLifetimeValue`

**Parameters:**
- month (optional): Specific month to query
- include_by_plan (optional): Include LTV breakdown by plan
- include_distribution (optional): Include LTV distribution buckets

**Returns:**
```php
{
    'average_ltv' => float,
    'total_subscriptions' => int,
    'by_plan' => [ // if include_by_plan=true
        ['plan_name' => 'Pro', 'average_ltv' => 1500],
        ...
    ],
    'distribution' => [ // if include_distribution=true
        'under_100' => 10,
        '100_to_500' => 25,
        '500_to_1000' => 15,
        '1000_to_5000' => 8,
        'over_5000' => 2
    ]
}
```

## API Routes

All routes require authentication via `auth` middleware (Fortify).

### GET /analytics/subscription/mrr

Fetch Monthly Recurring Revenue data

**Query Parameters:**
- `month` (optional): YYYY-MM format
- `include_trend` (optional): boolean

**Response:** MRR metrics with optional trend data

### GET /analytics/subscription/churn

Fetch churn metrics

**Query Parameters:**
- `month` (optional): YYYY-MM format
- `include_trend` (optional): boolean
- `include_reasons` (optional): boolean

**Response:** Churn rate, count, and optional breakdown

### GET /analytics/subscription/ltv

Fetch lifetime value metrics

**Query Parameters:**
- `month` (optional): YYYY-MM format
- `include_by_plan` (optional): boolean
- `include_distribution` (optional): boolean

**Response:** Average LTV and optional distributions

### GET /analytics/subscription/dashboard

Fetch combined analytics data

**Query Parameters:**
- `month` (optional): YYYY-MM format
- `include_trend` (optional): boolean
- `include_reasons` (optional): boolean
- `include_by_plan` (optional): boolean
- `include_distribution` (optional): boolean

**Response:** Combined MRR, churn, and LTV data

## Frontend Dashboard

### Location

`resources/js/pages/agent/AnalyticsDashboard.vue`

### Route

`GET /agent/analytics` (requires authentication)

### Features

- **Month Selector** - Filter analytics by specific month
- **MRR Card** - Current MRR with percentage change
- **Churn Rate Card** - Churn percentage and count
- **LTV Card** - Average customer lifetime value
- **MRR Trend Chart** - 12-month bar chart
- **Churn Reasons Chart** - Breakdown of cancellation reasons
- **LTV Distribution Chart** - Customer distribution across LTV buckets
- **Loading States** - Spinner while fetching data
- **Error Handling** - User-friendly error messages

### Usage

Navigate to `/agent/analytics` to view the dashboard. Select a month and click "Load Data" to refresh metrics.

## Testing

All analytics components include comprehensive test coverage:

```bash
# Run analytics tests
php artisan test tests/Unit/Application/Analytics/ --no-coverage

# Run all unit tests
php artisan test tests/Unit/ --no-coverage
```

**Test Coverage:**
- 12 analytics query handler tests
- 5 event handler tests (via saga tests)
- 62 total domain tests

## Configuration

Event handlers are registered in `app/Providers/AppServiceProvider.php`:

```php
$dispatcher->listen(SubscriptionCreated::class, SubscriptionCreatedHandler::class);
$dispatcher->listen(SubscriptionRenewed::class, SubscriptionRenewedHandler::class);
$dispatcher->listen(SubscriptionCancelled::class, SubscriptionCancelledHandler::class);
$dispatcher->listen(PaymentAttempted::class, PaymentAttemptedHandler::class);
$dispatcher->listen(PaymentFailed::class, PaymentFailedHandler::class);
```

Query handlers are registered with the QueryBus in the same provider.

## Performance Considerations

1. **Materialized View** - Pre-calculated data enables fast queries
2. **Indexes** - Strategic indexes on status, dates, and plan_id
3. **Pagination** - Use simplePaginate() for large datasets
4. **Caching** - Consider caching dashboard data for frequently accessed periods

## Future Enhancements

- Real-time dashboard updates via WebSockets
- Custom date range filtering
- Export analytics to CSV/PDF
- Predictive analytics (churn prediction)
- Cohort analysis
- Revenue forecasting

