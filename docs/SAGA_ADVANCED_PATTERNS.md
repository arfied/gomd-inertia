# Order Fulfillment Saga - Advanced Patterns & Troubleshooting

## Advanced Patterns

### 1. Saga Orchestrator Pattern

Instead of event-driven choreography, use a dedicated orchestrator:

```php
namespace App\Services;

use App\Domain\Order\OrderFulfillmentSaga;
use App\Jobs\Order\CreatePrescriptionJob;

class OrderFulfillmentOrchestrator
{
    public function orchestrate(string $orderId, array $orderData): void
    {
        $saga = OrderFulfillmentSaga::start(
            'saga-' . uniqid(),
            $orderId,
            ['source' => 'orchestrator']
        );

        try {
            // Step 1: Create prescription
            $this->createPrescription($saga, $orderData);

            // Step 2: Reserve inventory
            $this->reserveInventory($saga, $orderData);

            // Step 3: Initiate shipment
            $this->initiateShipment($saga, $orderData);

            // Mark complete
            $saga->complete();

        } catch (Exception $e) {
            $this->compensate($saga, $e);
        }
    }

    private function compensate(OrderFulfillmentSaga $saga, Exception $e): void
    {
        foreach (array_reverse($saga->compensationStack) as $action) {
            // Execute compensation
        }
        $saga->fail($e->getMessage(), 'orchestration');
    }
}
```

### 2. Saga with Timeout

```php
namespace App\Jobs\Order;

use App\Domain\Order\OrderFulfillmentSaga;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class SagaTimeoutJob implements ShouldQueue
{
    use Queueable;

    public function __construct(private string $sagaId) {}

    public function handle(): void
    {
        $saga = OrderFulfillmentSaga::find($this->sagaId);

        if ($saga->isTerminal()) {
            return; // Already completed
        }

        if ($saga->startedAt->addHours(24) < now()) {
            $saga->fail('Saga timeout', 'timeout');
            Log::error('Saga timeout', ['saga_id' => $this->sagaId]);
        }
    }
}
```

### 3. Saga with Retry Policy

```php
class CreatePrescriptionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;
    public int $maxExceptions = 3;

    public function backoff(): array
    {
        return [60, 120, 300]; // 1min, 2min, 5min
    }

    public function handle(EventStore $eventStore): void
    {
        try {
            // Attempt prescription creation
            $prescriptionId = $this->createPrescription();

            // Publish success
            $event = new PrescriptionCreated($this->orderId, [...]);
            $eventStore->store($event);
            event($event);

        } catch (RetryableException $e) {
            // Retry with backoff
            throw $e;

        } catch (NonRetryableException $e) {
            // Fail immediately
            $event = new PrescriptionFailed($this->orderId, [...]);
            $eventStore->store($event);
            event($event);
        }
    }
}
```

### 4. Saga with Circuit Breaker

```php
namespace App\Services;

use Illuminate\Support\Facades\Cache;

class CircuitBreaker
{
    private string $service;
    private int $threshold = 5;
    private int $timeout = 60;

    public function __construct(string $service)
    {
        $this->service = $service;
    }

    public function call(callable $callback)
    {
        $state = Cache::get("circuit_breaker.{$this->service}", 'closed');

        if ($state === 'open') {
            throw new CircuitBreakerOpenException(
                "Circuit breaker open for {$this->service}"
            );
        }

        try {
            $result = $callback();
            $this->recordSuccess();
            return $result;

        } catch (Exception $e) {
            $this->recordFailure();
            throw $e;
        }
    }

    private function recordFailure(): void
    {
        $failures = Cache::increment(
            "circuit_breaker.{$this->service}.failures"
        );

        if ($failures >= $this->threshold) {
            Cache::put(
                "circuit_breaker.{$this->service}",
                'open',
                $this->timeout
            );
        }
    }

    private function recordSuccess(): void
    {
        Cache::forget("circuit_breaker.{$this->service}.failures");
    }
}
```

### 5. Saga with Distributed Tracing

```php
use Illuminate\Support\Str;

class CreatePrescriptionJob implements ShouldQueue
{
    public function handle(EventStore $eventStore): void
    {
        $traceId = $this->job->payload()['trace_id'] ?? Str::uuid();

        Log::withContext(['trace_id' => $traceId])->info(
            'Starting prescription creation',
            ['order_id' => $this->orderId]
        );

        try {
            $prescriptionId = $this->createPrescription();

            Log::withContext(['trace_id' => $traceId])->info(
                'Prescription created',
                ['prescription_id' => $prescriptionId]
            );

            $event = new PrescriptionCreated($this->orderId, [...]);
            $eventStore->store($event);
            event($event);

        } catch (Exception $e) {
            Log::withContext(['trace_id' => $traceId])->error(
                'Prescription creation failed',
                ['exception' => $e->getMessage()]
            );
            throw $e;
        }
    }
}
```

## Troubleshooting

### Issue 1: Saga Stuck in Pending State

**Symptoms**: Saga remains in `PENDING_PRESCRIPTION` for hours.

**Diagnosis**:
```php
// Check if job is queued
$jobs = DB::table('jobs')->where('queue', 'order-fulfillment')->get();

// Check if queue worker is running
ps aux | grep 'queue:work'

// Check job failures
php artisan queue:failed
```

**Solution**:
```bash
# Restart queue worker
php artisan queue:work --queue=order-fulfillment

# Retry failed jobs
php artisan queue:retry all

# Check logs
tail -f storage/logs/laravel.log
```

### Issue 2: Compensation Not Triggered

**Symptoms**: Order not cancelled when prescription fails.

**Diagnosis**:
```php
// Check if event listener is registered
php artisan event:list

// Check if handler is called
Log::info('Handler called'); // Add to handler

// Check event dispatch
Event::fake();
event(new PrescriptionFailed(...));
Event::assertDispatched(PrescriptionFailed::class);
```

**Solution**:
```php
// Ensure listener is registered in EventServiceProvider
protected $listen = [
    PrescriptionFailed::class => [
        OrderFulfillmentSagaHandler::class,
    ],
];

// Ensure handler is public
public function handlePrescriptionFailed(PrescriptionFailed $event): void
```

### Issue 3: Duplicate Events

**Symptoms**: Same event stored multiple times.

**Diagnosis**:
```php
// Check event store for duplicates
DB::table('event_store')
    ->where('aggregate_uuid', $orderId)
    ->where('event_type', 'order.prescription_created')
    ->count();
```

**Solution**:
```php
// Use idempotency key
$idempotencyKey = hash('sha256', $orderId . 'prescription_created');

if (Cache::has($idempotencyKey)) {
    return; // Already processed
}

// Process and mark as done
Cache::put($idempotencyKey, true, 3600);
```

### Issue 4: Queue Worker Crashes

**Symptoms**: Queue worker stops processing jobs.

**Diagnosis**:
```bash
# Check worker logs
tail -f storage/logs/laravel.log

# Check system resources
free -h
df -h

# Check database connections
mysql -e "SHOW PROCESSLIST;"
```

**Solution**:
```bash
# Increase memory limit
php artisan queue:work --memory=512

# Use supervisor for auto-restart
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work --queue=order-fulfillment
autostart=true
autorestart=true
numprocs=4
redirect_stderr=true
stdout_logfile=/path/to/worker.log
```

### Issue 5: Event Store Growing Too Large

**Symptoms**: Event store table has millions of rows, queries slow.

**Diagnosis**:
```php
// Check event store size
DB::table('event_store')->count();

// Check by aggregate type
DB::table('event_store')
    ->groupBy('aggregate_type')
    ->selectRaw('aggregate_type, count(*) as count')
    ->get();
```

**Solution**:
```php
// Archive old events
DB::table('event_store')
    ->where('occurred_at', '<', now()->subMonths(6))
    ->delete();

// Add indexes
Schema::table('event_store', function (Blueprint $table) {
    $table->index(['aggregate_type', 'occurred_at']);
});

// Use event snapshots
class EventSnapshot extends Model
{
    // Store aggregate state at intervals
}
```

### Issue 6: Compensation Chain Breaks

**Symptoms**: Compensation starts but doesn't complete.

**Diagnosis**:
```php
// Check compensation stack
$saga = OrderFulfillmentSaga::find($sagaId);
dd($saga->compensationStack);

// Check job queue
DB::table('jobs')->where('queue', 'order-fulfillment')->get();
```

**Solution**:
```php
// Ensure each compensation job dispatches the next
public function handle(): void
{
    try {
        $this->compensate();
        
        // Dispatch next compensation
        dispatch(new NextCompensationJob(...))->onQueue('order-fulfillment');
        
    } catch (Exception $e) {
        Log::error('Compensation failed', ['exception' => $e]);
        throw $e;
    }
}
```

## Performance Optimization

### 1. Batch Event Processing

```php
// Process events in batches
StoredEvent::query()
    ->where('aggregate_type', 'order')
    ->chunkById(100, function ($events) {
        foreach ($events as $event) {
            // Process
        }
    });
```

### 2. Event Snapshots

```php
// Store aggregate snapshots
class AggregateSnapshot extends Model
{
    protected $fillable = [
        'aggregate_uuid',
        'aggregate_type',
        'state',
        'version',
    ];
}
```

### 3. Read Model Caching

```php
// Cache saga state
Cache::remember(
    "saga.{$sagaId}",
    3600,
    fn() => OrderFulfillmentSaga::find($sagaId)
);
```
