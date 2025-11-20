# Analytics Quick Start Guide

## Getting Started

### 1. Access the Dashboard

Navigate to `/agent/analytics` in your browser (requires authentication).

### 2. Select a Month

Use the month selector to choose which month's data to view. Click "Load Data" to refresh.

### 3. View Metrics

The dashboard displays three main cards:

- **Monthly Recurring Revenue (MRR)** - Total predictable revenue from active subscriptions
- **Churn Rate** - Percentage of subscriptions cancelled in the period
- **Average Lifetime Value (LTV)** - Average total revenue per customer

### 4. Analyze Charts

- **MRR Trend** - 12-month bar chart showing revenue trends
- **Churn Reasons** - Breakdown of why customers cancelled
- **LTV Distribution** - How many customers fall into each LTV bucket

## API Usage

### Get MRR Data

```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
  "https://yourapp.com/analytics/subscription/mrr?month=2025-11&include_trend=true"
```

### Get Churn Metrics

```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
  "https://yourapp.com/analytics/subscription/churn?month=2025-11&include_reasons=true"
```

### Get LTV Data

```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
  "https://yourapp.com/analytics/subscription/ltv?month=2025-11&include_distribution=true"
```

### Get Combined Dashboard Data

```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
  "https://yourapp.com/analytics/subscription/dashboard?month=2025-11&include_trend=true&include_reasons=true&include_distribution=true"
```

## Understanding the Metrics

### Monthly Recurring Revenue (MRR)

**Definition:** Total predictable revenue from all active subscriptions in a given month.

**Formula:** Sum of monthly_price for all active subscriptions

**Interpretation:**
- Increasing MRR = Growing revenue base
- Decreasing MRR = Churn or downgrades
- Percentage change shows month-over-month growth

### Churn Rate

**Definition:** Percentage of subscriptions cancelled in a given period.

**Formula:** (Churned Subscriptions / Active at Start) × 100

**Interpretation:**
- Lower is better (industry average: 5-10% monthly)
- Churn reasons help identify problem areas
- Trends show if churn is improving or worsening

### Lifetime Value (LTV)

**Definition:** Average total revenue expected from a customer over their lifetime.

**Formula:** Average of total_revenue across all subscriptions

**Interpretation:**
- Higher LTV = More valuable customers
- Distribution shows customer value spread
- By-plan breakdown identifies most valuable plans

## Common Tasks

### Monitor Revenue Growth

1. Go to `/agent/analytics`
2. Check MRR card for current month
3. Review MRR Trend chart for 12-month pattern
4. Compare percentage change to previous month

### Identify Churn Issues

1. Go to `/agent/analytics`
2. Check Churn Rate card
3. Review Churn Reasons chart
4. Focus on top cancellation reasons

### Analyze Customer Value

1. Go to `/agent/analytics`
2. Check Average LTV card
3. Review LTV Distribution chart
4. Identify high-value customer segments

## Troubleshooting

### Dashboard Shows No Data

- Ensure you're authenticated
- Check that subscriptions exist in the database
- Verify events are being dispatched correctly
- Check browser console for API errors

### Metrics Seem Incorrect

- Verify subscription data in database
- Check that event handlers are registered
- Ensure analytics view is populated
- Run: `php artisan test tests/Unit/Application/Analytics/`

### Performance Issues

- Consider caching dashboard data
- Use pagination for large datasets
- Check database indexes on analytics view
- Monitor query performance in logs

## Integration Examples

### Vue Component Usage

```vue
<script setup>
import { ref, onMounted } from 'vue'

const dashboardData = ref(null)

const loadDashboard = async () => {
  const response = await fetch('/analytics/subscription/dashboard?include_trend=true')
  dashboardData.value = await response.json()
}

onMounted(() => loadDashboard())
</script>

<template>
  <div v-if="dashboardData">
    <h2>MRR: {{ dashboardData.mrr.current_mrr }}</h2>
    <h2>Churn: {{ dashboardData.churn.churn_rate }}%</h2>
    <h2>LTV: {{ dashboardData.ltv.average_ltv }}</h2>
  </div>
</template>
```

### Laravel Usage

```php
use App\Application\Analytics\Queries\GetMonthlyRecurringRevenue;
use Illuminate\Support\Facades\Bus;

$query = new GetMonthlyRecurringRevenue(
    month: '2025-11',
    includeTrend: true
);

$result = Bus::dispatch($query);
echo "Current MRR: " . $result['current_mrr'];
```

## Next Steps

- Set up alerts for MRR drops
- Create custom reports combining analytics with other data
- Implement predictive analytics for churn
- Build cohort analysis for customer segments
- Export analytics to BI tools

