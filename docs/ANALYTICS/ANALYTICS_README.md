# Subscription Analytics Documentation

Complete documentation for the TeleMed Pro subscription analytics system.

## Documentation Files

### 1. [ANALYTICS_IMPLEMENTATION.md](./ANALYTICS_IMPLEMENTATION.md)
**Comprehensive technical documentation**

- System architecture and data flow
- Database schema for subscription_analytics_view
- All 5 event handlers with detailed explanations
- All 3 query handlers with parameters and responses
- API routes and authentication
- Frontend dashboard features
- Testing and configuration
- Performance considerations
- Future enhancements

**Best for:** Understanding the complete system architecture and technical details

---

### 2. [ANALYTICS_QUICK_START.md](./ANALYTICS_QUICK_START.md)
**Getting started guide for end users**

- How to access the dashboard
- Understanding the three main metrics (MRR, Churn, LTV)
- Analyzing charts and trends
- Common tasks and workflows
- Troubleshooting guide
- Integration examples for developers
- Next steps and advanced features

**Best for:** New users learning how to use the analytics dashboard

---

### 3. [ANALYTICS_API_REFERENCE.md](./ANALYTICS_API_REFERENCE.md)
**Complete API endpoint documentation**

- Authentication requirements
- 4 main endpoints with full specifications:
  - GET /analytics/subscription/mrr
  - GET /analytics/subscription/churn
  - GET /analytics/subscription/ltv
  - GET /analytics/subscription/dashboard
- Query parameters for each endpoint
- Response formats with examples
- Error responses and troubleshooting
- Rate limiting and caching
- SDK examples (JavaScript, PHP, Python)
- Changelog

**Best for:** Developers integrating with the analytics API

---

### 4. [ANALYTICS_EVENT_HANDLERS.md](./ANALYTICS_EVENT_HANDLERS.md)
**Event-driven architecture documentation**

- Event handler registration
- Detailed documentation for all 5 handlers:
  - SubscriptionCreatedHandler
  - SubscriptionRenewedHandler
  - SubscriptionCancelledHandler
  - PaymentAttemptedHandler
  - PaymentFailedHandler
- Event payload examples
- Database impact for each handler
- Complete data flow example
- Testing event handlers
- Debugging and troubleshooting
- Performance optimization

**Best for:** Developers maintaining or extending the event system

---

## Quick Navigation

### I want to...

**Use the analytics dashboard**
→ Read [ANALYTICS_QUICK_START.md](./ANALYTICS_QUICK_START.md)

**Integrate analytics into my app**
→ Read [ANALYTICS_API_REFERENCE.md](./ANALYTICS_API_REFERENCE.md)

**Understand how it works**
→ Read [ANALYTICS_IMPLEMENTATION.md](./ANALYTICS_IMPLEMENTATION.md)

**Debug event handlers**
→ Read [ANALYTICS_EVENT_HANDLERS.md](./ANALYTICS_EVENT_HANDLERS.md)

**Extend the system**
→ Read all documentation files

---

## Key Concepts

### Monthly Recurring Revenue (MRR)
Total predictable revenue from active subscriptions in a given month.
- **Formula:** Sum of monthly_price for active subscriptions
- **Use:** Track revenue growth and trends

### Churn Rate
Percentage of subscriptions cancelled in a given period.
- **Formula:** (Churned / Active at Start) × 100
- **Use:** Monitor customer retention and identify issues

### Lifetime Value (LTV)
Average total revenue expected from a customer over their lifetime.
- **Formula:** Average of total_revenue across subscriptions
- **Use:** Understand customer value and segment analysis

---

## System Architecture

```
Domain Events
    ↓
Event Handlers (5 handlers)
    ↓
subscription_analytics_view (Materialized View)
    ↓
Query Handlers (3 handlers)
    ↓
API Routes (4 endpoints)
    ↓
Vue Dashboard (/agent/analytics)
```

---

## File Locations

### Backend

**Event Handlers:**
- `app/Application/Analytics/EventHandlers/SubscriptionCreatedHandler.php`
- `app/Application/Analytics/EventHandlers/SubscriptionRenewedHandler.php`
- `app/Application/Analytics/EventHandlers/SubscriptionCancelledHandler.php`
- `app/Application/Analytics/EventHandlers/PaymentAttemptedHandler.php`
- `app/Application/Analytics/EventHandlers/PaymentFailedHandler.php`

**Query Handlers:**
- `app/Application/Analytics/Queries/GetMonthlyRecurringRevenueHandler.php`
- `app/Application/Analytics/Queries/GetChurnMetricsHandler.php`
- `app/Application/Analytics/Queries/GetLifetimeValueHandler.php`

**Models:**
- `app/Models/SubscriptionAnalyticsView.php`

**Routes:**
- `routes/web.php` (Dashboard page and API routes)

### Frontend

**Dashboard:**
- `resources/js/pages/agent/AnalyticsDashboard.vue`

**Routes:**
- `resources/js/routes/agent/analytics.ts`
- `resources/js/routes/analytics/subscription/index.ts`

### Database

**Migration:**
- `database/migrations/2025_11_20_000000_create_subscription_analytics_view.php`

### Tests

**Analytics Tests:**
- `tests/Unit/Application/Analytics/GetMonthlyRecurringRevenueHandlerTest.php`
- `tests/Unit/Application/Analytics/GetChurnMetricsHandlerTest.php`
- `tests/Unit/Application/Analytics/GetLifetimeValueHandlerTest.php`

---

## Running Tests

```bash
# Run all analytics tests
php artisan test tests/Unit/Application/Analytics/ --no-coverage

# Run specific test
php artisan test tests/Unit/Application/Analytics/GetMonthlyRecurringRevenueHandlerTest.php

# Run all unit tests
php artisan test tests/Unit/ --no-coverage
```

---

## Common Tasks

### Access the Dashboard
Navigate to `/agent/analytics` (requires authentication)

### Get MRR Data via API
```bash
curl -H "Authorization: Bearer TOKEN" \
  "https://yourapp.com/analytics/subscription/mrr?include_trend=true"
```

### Get Combined Dashboard Data
```bash
curl -H "Authorization: Bearer TOKEN" \
  "https://yourapp.com/analytics/subscription/dashboard?include_trend=true&include_reasons=true&include_distribution=true"
```

### Debug Event Handlers
Check `app/Providers/AppServiceProvider.php` for event listener registration

### Monitor Analytics View
```sql
SELECT * FROM subscription_analytics_view LIMIT 10;
```

---

## Support

For issues or questions:

1. Check the relevant documentation file
2. Review the troubleshooting section
3. Run tests to verify system health
4. Check application logs for errors
5. Contact the development team

---

## Version

**Current Version:** 1.0.0

**Last Updated:** November 20, 2025

**Status:** Production Ready ✅

