# Analytics API Reference

## Authentication

All endpoints require authentication via Laravel Fortify/Sanctum.

Include authentication header:
```
Authorization: Bearer YOUR_API_TOKEN
```

Or use session authentication if accessing from the web.

## Endpoints

### GET /analytics/subscription/mrr

Fetch Monthly Recurring Revenue metrics.

**Query Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| month | string | No | Month in YYYY-MM format (default: current month) |
| include_trend | boolean | No | Include 12-month trend data (default: false) |

**Response (200 OK):**

```json
{
  "current_mrr": 15000.00,
  "previous_mrr": 14500.00,
  "change_percent": 3.45,
  "trend": [
    {
      "month": "Dec 2024",
      "mrr": 14000.00
    },
    {
      "month": "Jan 2025",
      "mrr": 14500.00
    },
    {
      "month": "Feb 2025",
      "mrr": 15000.00
    }
  ]
}
```

**Example Request:**

```bash
GET /analytics/subscription/mrr?month=2025-02&include_trend=true
```

---

### GET /analytics/subscription/churn

Fetch churn rate and related metrics.

**Query Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| month | string | No | Month in YYYY-MM format (default: current month) |
| include_trend | boolean | No | Include trend data (default: false) |
| include_reasons | boolean | No | Include churn reasons breakdown (default: false) |

**Response (200 OK):**

```json
{
  "churn_rate": 8.5,
  "churned_count": 17,
  "active_at_start": 200,
  "churn_reasons": [
    {
      "reason": "payment_failed",
      "count": 8
    },
    {
      "reason": "customer_request",
      "count": 6
    },
    {
      "reason": "too_expensive",
      "count": 3
    }
  ],
  "trend": [
    {
      "month": "Dec 2024",
      "churn_rate": 7.2
    },
    {
      "month": "Jan 2025",
      "churn_rate": 8.1
    },
    {
      "month": "Feb 2025",
      "churn_rate": 8.5
    }
  ]
}
```

**Example Request:**

```bash
GET /analytics/subscription/churn?month=2025-02&include_reasons=true&include_trend=true
```

---

### GET /analytics/subscription/ltv

Fetch Customer Lifetime Value metrics.

**Query Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| month | string | No | Month in YYYY-MM format (default: current month) |
| include_by_plan | boolean | No | Include LTV breakdown by plan (default: false) |
| include_distribution | boolean | No | Include LTV distribution buckets (default: false) |

**Response (200 OK):**

```json
{
  "average_ltv": 1250.50,
  "total_subscriptions": 200,
  "by_plan": [
    {
      "plan_name": "Pro",
      "average_ltv": 1800.00
    },
    {
      "plan_name": "Basic",
      "average_ltv": 900.00
    }
  ],
  "distribution": {
    "under_100": 15,
    "100_to_500": 45,
    "500_to_1000": 65,
    "1000_to_5000": 60,
    "over_5000": 15
  }
}
```

**Example Request:**

```bash
GET /analytics/subscription/ltv?month=2025-02&include_by_plan=true&include_distribution=true
```

---

### GET /analytics/subscription/dashboard

Fetch combined analytics dashboard data.

**Query Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| month | string | No | Month in YYYY-MM format (default: current month) |
| include_trend | boolean | No | Include trend data (default: false) |
| include_reasons | boolean | No | Include churn reasons (default: false) |
| include_by_plan | boolean | No | Include LTV by plan (default: false) |
| include_distribution | boolean | No | Include LTV distribution (default: false) |

**Response (200 OK):**

```json
{
  "mrr": {
    "current_mrr": 15000.00,
    "previous_mrr": 14500.00,
    "change_percent": 3.45,
    "trend": [...]
  },
  "churn": {
    "churn_rate": 8.5,
    "churned_count": 17,
    "active_at_start": 200,
    "churn_reasons": [...],
    "trend": [...]
  },
  "ltv": {
    "average_ltv": 1250.50,
    "total_subscriptions": 200,
    "by_plan": [...],
    "distribution": {...}
  }
}
```

**Example Request:**

```bash
GET /analytics/subscription/dashboard?month=2025-02&include_trend=true&include_reasons=true&include_by_plan=true&include_distribution=true
```

---

## Error Responses

### 401 Unauthorized

```json
{
  "message": "Unauthenticated."
}
```

**Cause:** Missing or invalid authentication token

**Solution:** Include valid Authorization header

### 403 Forbidden

```json
{
  "message": "This action is unauthorized."
}
```

**Cause:** User lacks permission to access analytics

**Solution:** Verify user has appropriate role/permissions

### 422 Unprocessable Entity

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "month": ["The month field must be a valid date in YYYY-MM format."]
  }
}
```

**Cause:** Invalid query parameters

**Solution:** Verify parameter format and values

### 500 Internal Server Error

```json
{
  "message": "Server error"
}
```

**Cause:** Unexpected server error

**Solution:** Check server logs and contact support

## Rate Limiting

Analytics endpoints are subject to standard Laravel rate limiting. Default: 60 requests per minute per user.

## Caching

Consider implementing caching for frequently accessed data:

```php
// Cache dashboard data for 1 hour
$data = Cache::remember(
    "analytics:dashboard:{$month}",
    3600,
    fn() => Bus::dispatch(new GetMonthlyRecurringRevenue($month))
);
```

## Pagination

For large datasets, use pagination:

```bash
GET /analytics/subscription/dashboard?page=1&per_page=50
```

## Filtering

Filter by date range:

```bash
GET /analytics/subscription/mrr?start_date=2025-01-01&end_date=2025-02-28
```

## Sorting

Sort results:

```bash
GET /analytics/subscription/ltv?sort_by=average_ltv&sort_order=desc
```

## Webhooks

Subscribe to analytics events:

```php
// Webhook triggered when MRR changes significantly
POST /webhooks/analytics/mrr-changed
```

## SDK Examples

### JavaScript/TypeScript

```typescript
const response = await fetch('/analytics/subscription/dashboard?include_trend=true', {
  headers: {
    'Authorization': `Bearer ${token}`,
    'Accept': 'application/json'
  }
});
const data = await response.json();
```

### PHP

```php
$response = Http::withToken($token)
  ->get('/analytics/subscription/dashboard', [
    'include_trend' => true,
    'include_reasons' => true
  ]);
$data = $response->json();
```

### Python

```python
import requests

headers = {'Authorization': f'Bearer {token}'}
response = requests.get(
  'https://yourapp.com/analytics/subscription/dashboard',
  params={'include_trend': True},
  headers=headers
)
data = response.json()
```

## Changelog

### v1.0.0 (Current)

- Initial release
- MRR, Churn, LTV endpoints
- Dashboard endpoint
- Trend data support
- Breakdown by plan/reason

