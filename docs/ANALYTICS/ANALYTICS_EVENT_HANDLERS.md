# Analytics Event Handlers

## Overview

Event handlers automatically populate the `subscription_analytics_view` materialized view from domain events. This event-driven approach ensures analytics data stays synchronized with subscription state changes.

## Event Handler Registration

All event handlers are registered in `app/Providers/AppServiceProvider.php`:

```php
$dispatcher = $this->app->make(Dispatcher::class);
$dispatcher->listen(SubscriptionCreated::class, SubscriptionCreatedHandler::class);
$dispatcher->listen(SubscriptionRenewed::class, SubscriptionRenewedHandler::class);
$dispatcher->listen(SubscriptionCancelled::class, SubscriptionCancelledHandler::class);
$dispatcher->listen(PaymentAttempted::class, PaymentAttemptedHandler::class);
$dispatcher->listen(PaymentFailed::class, PaymentFailedHandler::class);
```

## Event Handlers

### 1. SubscriptionCreatedHandler

**File:** `app/Application/Analytics/EventHandlers/SubscriptionCreatedHandler.php`

**Triggered by:** `SubscriptionCreated` event

**Purpose:** Create initial analytics view entry when subscription is created

**Process:**
1. Retrieve subscription and plan from database
2. Create new analytics view entry with:
   - subscription_id, user_id, plan_id, plan_name
   - monthly_price from plan
   - status = 'active'
   - started_at from subscription
   - ended_at from subscription
   - is_trial from subscription

**Example Event Payload:**
```php
[
    'subscription_id' => 'uuid-123',
    'user_id' => 'uuid-456',
    'plan_id' => 'uuid-789',
]
```

**Database Impact:**
```sql
INSERT INTO subscription_analytics_view (
    subscription_id, user_id, plan_id, plan_name, monthly_price,
    status, started_at, ended_at, is_trial
) VALUES (...)
```

---

### 2. SubscriptionRenewedHandler

**File:** `app/Application/Analytics/EventHandlers/SubscriptionRenewedHandler.php`

**Triggered by:** `SubscriptionRenewed` event

**Purpose:** Update analytics view with renewal data and calculate metrics

**Process:**
1. Find analytics view entry by subscription_id
2. Calculate months_active = diffInMonths(started_at, now)
3. Calculate total_revenue = monthly_price × months_active
4. Update last_payment_date = now()
5. Update next_payment_date = subscription.ends_at

**Example Event Payload:**
```php
[
    'subscription_id' => 'uuid-123',
    'previous_ends_at' => '2025-02-28',
    'new_ends_at' => '2025-03-28',
    'renewal_reason' => 'auto_renewal',
    'transaction_id' => 'txn-123',
]
```

**Calculations:**
```php
$monthsActive = $startedAt->diffInMonths(now());
$totalRevenue = $monthlyPrice * $monthsActive;
```

**Database Impact:**
```sql
UPDATE subscription_analytics_view SET
    months_active = ?,
    total_revenue = ?,
    last_payment_date = NOW(),
    next_payment_date = ?
WHERE subscription_id = ?
```

---

### 3. SubscriptionCancelledHandler

**File:** `app/Application/Analytics/EventHandlers/SubscriptionCancelledHandler.php`

**Triggered by:** `SubscriptionCancelled` event

**Purpose:** Update analytics view with cancellation data and churn reason

**Process:**
1. Find analytics view entry by subscription_id
2. Calculate months_active = diffInMonths(started_at, cancelled_at)
3. Calculate total_revenue = monthly_price × months_active
4. Extract churn_reason from event payload
5. Set status = 'cancelled'
6. Set cancelled_at timestamp

**Example Event Payload:**
```php
[
    'subscription_id' => 'uuid-123',
    'cancelled_at' => '2025-02-15 10:30:00',
    'cancellation_reason' => 'payment_failed',
    'effective_date' => '2025-02-15',
]
```

**Churn Reasons:**
- `payment_failed` - Payment processing failed
- `customer_request` - Customer requested cancellation
- `too_expensive` - Customer found it too expensive
- `no_longer_needed` - Customer no longer needs service
- `switching_provider` - Customer switched to competitor
- `other` - Other reason

**Database Impact:**
```sql
UPDATE subscription_analytics_view SET
    status = 'cancelled',
    cancelled_at = ?,
    months_active = ?,
    total_revenue = ?,
    churn_reason = ?
WHERE subscription_id = ?
```

---

### 4. PaymentAttemptedHandler

**File:** `app/Application/Analytics/EventHandlers/PaymentAttemptedHandler.php`

**Triggered by:** `PaymentAttempted` event

**Purpose:** Update payment dates in analytics view

**Process:**
1. Find or create analytics view entry
2. Update last_payment_date = now()
3. Update next_payment_date = subscription.ends_at
4. Ensure status is not 'pending_payment'

**Example Event Payload:**
```php
[
    'subscription_id' => 'uuid-123',
    'payment_method_id' => 'uuid-456',
    'amount' => 99.99,
    'attempt_number' => 1,
    'transaction_id' => 'txn-123',
    'attempted_at' => '2025-02-15 10:30:00',
]
```

**Database Impact:**
```sql
UPDATE subscription_analytics_view SET
    last_payment_date = NOW(),
    next_payment_date = ?
WHERE subscription_id = ?
```

---

### 5. PaymentFailedHandler

**File:** `app/Application/Analytics/EventHandlers/PaymentFailedHandler.php`

**Triggered by:** `PaymentFailed` event

**Purpose:** Update status and churn reason when payment fails

**Process:**
1. Find or create analytics view entry
2. Set status = 'pending_payment'
3. Set churn_reason = 'payment_failed'
4. Record failed_at timestamp

**Example Event Payload:**
```php
[
    'subscription_id' => 'uuid-123',
    'payment_method_id' => 'uuid-456',
    'amount' => 99.99,
    'attempt_number' => 1,
    'error_code' => 'card_declined',
    'error_message' => 'Your card was declined',
    'failed_at' => '2025-02-15 10:30:00',
    'next_retry_date' => '2025-02-16 10:30:00',
]
```

**Database Impact:**
```sql
UPDATE subscription_analytics_view SET
    status = 'pending_payment',
    churn_reason = 'payment_failed'
WHERE subscription_id = ?
```

## Data Flow Example

### Scenario: New Subscription Created and Renewed

**Step 1: Subscription Created**
```
SubscriptionCreated event dispatched
    ↓
SubscriptionCreatedHandler triggered
    ↓
Analytics view entry created:
{
    subscription_id: 'sub-123',
    status: 'active',
    started_at: '2025-01-01',
    ended_at: '2025-02-01',
    monthly_price: 99.99,
    months_active: 0,
    total_revenue: 0
}
```

**Step 2: Subscription Renewed (Feb 1)**
```
SubscriptionRenewed event dispatched
    ↓
SubscriptionRenewedHandler triggered
    ↓
Analytics view entry updated:
{
    subscription_id: 'sub-123',
    status: 'active',
    started_at: '2025-01-01',
    ended_at: '2025-03-01',
    monthly_price: 99.99,
    months_active: 1,
    total_revenue: 99.99,
    last_payment_date: '2025-02-01 10:00:00',
    next_payment_date: '2025-03-01'
}
```

**Step 3: Subscription Cancelled (Feb 15)**
```
SubscriptionCancelled event dispatched
    ↓
SubscriptionCancelledHandler triggered
    ↓
Analytics view entry updated:
{
    subscription_id: 'sub-123',
    status: 'cancelled',
    started_at: '2025-01-01',
    cancelled_at: '2025-02-15',
    months_active: 1,
    total_revenue: 99.99,
    churn_reason: 'payment_failed'
}
```

## Testing Event Handlers

Event handlers are tested through saga tests:

```bash
php artisan test tests/Unit/Domain/Subscription/SubscriptionRenewalSagaTest.php
php artisan test tests/Unit/Domain/Payment/DunningManagementSagaTest.php
```

## Debugging

### Check Event Listener Registration

```php
// In tinker
$dispatcher = app('events');
$listeners = $dispatcher->getListeners(\App\Domain\Subscription\Events\SubscriptionCreated::class);
dd($listeners);
```

### Monitor Event Dispatching

Add logging to event handlers:

```php
public function handle(SubscriptionCreated $event)
{
    Log::info('SubscriptionCreated event handled', [
        'subscription_id' => $event->aggregateId(),
    ]);
    // ... rest of handler
}
```

### Verify Analytics View Population

```sql
SELECT * FROM subscription_analytics_view WHERE subscription_id = 'uuid-123';
```

## Performance Optimization

1. **Batch Updates** - Group multiple updates in transactions
2. **Async Processing** - Use queued jobs for heavy calculations
3. **Caching** - Cache frequently accessed analytics data
4. **Indexing** - Ensure proper indexes on analytics view

## Troubleshooting

### Analytics View Not Updating

1. Verify event handlers are registered in AppServiceProvider
2. Check that events are being dispatched
3. Review application logs for errors
4. Run: `php artisan test tests/Unit/Application/Analytics/`

### Incorrect Calculations

1. Verify event payload contains correct data
2. Check date calculations in handlers
3. Review database schema for data types
4. Test with specific subscription scenarios

### Performance Issues

1. Check analytics view indexes
2. Monitor query performance
3. Consider archiving old data
4. Implement caching strategy

