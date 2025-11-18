# Order Fulfillment Saga - Implementation Checklist

## Phase 1: Foundation Setup

- [ ] Create migration: `2025_11_18_000000_create_order_fulfillment_sagas_table.php`
- [ ] Create model: `app/Models/OrderFulfillmentSaga.php`
- [ ] Create saga aggregate: `app/Domain/Order/OrderFulfillmentSaga.php`
- [ ] Run migration: `php artisan migrate`

## Phase 2: Domain Events

- [ ] Create `OrderFulfillmentSagaStarted` event
- [ ] Create `OrderFulfillmentSagaStateChanged` event
- [ ] Create `CompensationRecorded` event
- [ ] Create `OrderFulfillmentSagaFailed` event
- [ ] Create `OrderFulfillmentSagaCompleted` event
- [ ] Create `PrescriptionCreated` event
- [ ] Create `PrescriptionFailed` event
- [ ] Create `InventoryReserved` event
- [ ] Create `InventoryReservationFailed` event
- [ ] Create `ShipmentInitiated` event
- [ ] Create `ShipmentInitiationFailed` event
- [ ] Create `PrescriptionCancelled` event
- [ ] Create `InventoryReleased` event

## Phase 3: Queue Jobs

- [ ] Create `CreatePrescriptionJob`
- [ ] Create `ReserveInventoryJob`
- [ ] Create `InitiateShipmentJob`
- [ ] Create `CancelOrderJob` (compensation)
- [ ] Create `CancelPrescriptionJob` (compensation)
- [ ] Create `ReleaseInventoryJob` (compensation)

## Phase 4: Saga Orchestration

- [ ] Create event listener handlers (7 handlers)
  - [ ] `OrderFulfillmentSagaHandler` (OrderCreated)
  - [ ] `PrescriptionCreatedHandler` (PrescriptionCreated)
  - [ ] `PrescriptionFailedHandler` (PrescriptionFailed)
  - [ ] `InventoryReservedHandler` (InventoryReserved)
  - [ ] `InventoryReservationFailedHandler` (InventoryReservationFailed)
  - [ ] `ShipmentInitiatedHandler` (ShipmentInitiated)
  - [ ] `ShipmentInitiationFailedHandler` (ShipmentInitiationFailed)
- [ ] Verify automatic discovery: `php artisan event:list`
- [ ] Test event dispatching

## Phase 5: Configuration

- [ ] Update `config/projection_replay.php` with all saga events
- [ ] Configure queue connection in `config/queue.php`
- [ ] Set up `order-fulfillment` queue
- [ ] Configure retry policies and backoff

## Phase 6: Commands & Handlers

- [ ] Create `CreateOrder` command
- [ ] Create `CreateOrderHandler`
- [ ] Register command handler in service provider
- [ ] Test command dispatch

## Phase 7: Testing

- [ ] Write unit tests for saga state transitions
- [ ] Write feature tests for happy path
- [ ] Write feature tests for failure paths
- [ ] Write integration tests for event persistence
- [ ] Test compensation chain execution
- [ ] Test idempotency

## Phase 8: Monitoring & Observability

- [ ] Add logging to saga handler
- [ ] Add logging to each job
- [ ] Create metrics for saga success rate
- [ ] Create metrics for average saga duration
- [ ] Create metrics for compensation frequency
- [ ] Set up alerts for failed sagas

## Phase 9: Documentation

- [ ] Document saga flow
- [ ] Document state transitions
- [ ] Document compensation actions
- [ ] Document API endpoints
- [ ] Document queue configuration
- [ ] Document monitoring setup

## Phase 10: Deployment

- [ ] Run all tests
- [ ] Run code quality checks
- [ ] Deploy migrations
- [ ] Deploy code
- [ ] Start queue workers
- [ ] Monitor for errors

---

## Best Practices

### 1. Idempotency

**Problem**: Jobs may be retried, causing duplicate operations.

**Solution**:
```php
// Use idempotency keys
$idempotencyKey = hash('sha256', $orderId . $step);
Cache::remember($idempotencyKey, 3600, function () {
    // Perform operation only once
});
```

### 2. Compensation Ordering

**Problem**: Compensation must happen in reverse order (LIFO).

**Solution**:
```php
// Store compensation stack in saga
$saga->recordCompensation('action_1', $data);
$saga->recordCompensation('action_2', $data);

// Execute in reverse order
foreach (array_reverse($saga->compensationStack) as $compensation) {
    // Execute compensation
}
```

### 3. Timeout Handling

**Problem**: Jobs may hang indefinitely.

**Solution**:
```php
// Set timeout in job
public int $timeout = 300; // 5 minutes

// Use timeout in queue worker
php artisan queue:work --timeout=300
```

### 4. Dead Letter Queue

**Problem**: Failed jobs need manual intervention.

**Solution**:
```php
// Create failed_jobs table
php artisan queue:failed-table
php artisan migrate

// Retry failed jobs
php artisan queue:retry all

// Monitor failed jobs
php artisan queue:failed
```

### 5. Monitoring Saga Progress

**Problem**: Need visibility into saga execution.

**Solution**:
```php
// Query saga state
$saga = OrderFulfillmentSaga::where('order_uuid', $orderId)->first();

// Check duration
$duration = $saga->getDurationSeconds();

// Get pending sagas
$pending = OrderFulfillmentSaga::pending()->count();

// Get failed sagas
$failed = OrderFulfillmentSaga::failed()->get();
```

### 6. Event Versioning

**Problem**: Event schema may change over time.

**Solution**:
```php
// Version events
class PrescriptionCreated extends DomainEvent
{
    public static function eventType(): string
    {
        return 'order.prescription_created.v1';
    }
}

// Handle multiple versions
if ($event->event_type === 'order.prescription_created.v1') {
    // Handle v1
} elseif ($event->event_type === 'order.prescription_created.v2') {
    // Handle v2
}
```

### 7. Saga Timeout

**Problem**: Saga may hang if a step never completes.

**Solution**:
```php
// Add timeout check
if ($saga->startedAt->addHours(24) < now()) {
    $saga->fail('Saga timeout', 'timeout');
}

// Schedule timeout check
$schedule->call(function () {
    OrderFulfillmentSaga::pending()
        ->where('started_at', '<', now()->subHours(24))
        ->each(fn($saga) => $saga->fail('Timeout', 'timeout'));
})->hourly();
```

### 8. Partial Failure Handling

**Problem**: Some compensation actions may fail.

**Solution**:
```php
// Retry compensation with exponential backoff
public int $tries = 5;
public int $backoff = 60;

// Log failures for manual intervention
public function failed(Throwable $exception): void
{
    Log::error('Compensation failed', [
        'saga_id' => $this->sagaId,
        'exception' => $exception->getMessage(),
    ]);

    // Alert operations team
    Notification::send(ops_team(), new CompensationFailedNotification($this));
}
```

### 9. Testing Compensation

**Problem**: Hard to test compensation paths.

**Solution**:
```php
// Mock external services to fail
Service::shouldReceive('create')->andThrow(new Exception('Service down'));

// Dispatch event
event(new OrderCreated(...));

// Assert compensation was triggered
Queue::assertPushed(CancelOrderJob::class);
```

### 10. Observability

**Problem**: Need to track saga execution.

**Solution**:
```php
// Log state transitions
Log::info('Saga state changed', [
    'saga_id' => $saga->uuid,
    'from_state' => $oldState,
    'to_state' => $newState,
    'duration_ms' => $duration,
]);

// Emit metrics
Metrics::gauge('saga.duration_seconds', $duration);
Metrics::increment('saga.completed');
Metrics::increment('saga.failed');
```
