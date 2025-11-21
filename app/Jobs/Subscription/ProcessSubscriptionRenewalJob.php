<?php

namespace App\Jobs\Subscription;

use App\Models\PaymentMethod;
use App\Models\Subscription;
use App\Models\User;
use App\Services\AuthorizeNet\AuthorizeNetService;
use App\Services\AuthorizeNet\AchPaymentService;
use App\Domain\Subscription\SubscriptionRenewalSaga;
use App\Domain\Subscription\Events\RenewalFailureAlert;
use App\Services\EventStoreContract;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProcessSubscriptionRenewalJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $sagaUuid;
    public int $subscriptionId;
    public int $userId;
    public float $amount;
    public string $correlationId;
    public int $attemptNumber = 0;
    public int $maxAttempts;
    /** @var array<int> */
    public array $retrySchedule; // days

    public function __construct(string $sagaUuid, int $subscriptionId, int $userId, float $amount, ?string $correlationId = null)
    {
        $this->sagaUuid = $sagaUuid;
        $this->subscriptionId = $subscriptionId;
        $this->userId = $userId;
        $this->amount = $amount;
        $this->correlationId = $correlationId ?? Str::uuid()->toString();

        // Load configuration
        $this->maxAttempts = (int) config('subscription.renewal.max_attempts', 5);
        $this->retrySchedule = config('subscription.renewal.retry_schedule', [1, 3, 7, 14, 30]);

        $this->onQueue('subscription-renewal');
    }

    public function handle(
        EventStoreContract $eventStore,
        Dispatcher $dispatcher,
        AuthorizeNetService $authorizeNetService,
        AchPaymentService $achPaymentService
    ): void {
        try {
            // Check idempotency - prevent duplicate processing
            if ($this->isAlreadyProcessed($eventStore)) {
                Log::info('Subscription renewal already processed (idempotency check)', [
                    'saga_uuid' => $this->sagaUuid,
                    'correlation_id' => $this->correlationId,
                ]);
                return;
            }

            $subscription = Subscription::find($this->subscriptionId);
            $user = User::find($this->userId);

            if (!$subscription || !$user) {
                Log::error('Subscription or user not found for renewal', [
                    'saga_uuid' => $this->sagaUuid,
                    'subscription_id' => $this->subscriptionId,
                    'user_id' => $this->userId,
                    'correlation_id' => $this->correlationId,
                ]);
                $this->recordPaymentFailure($eventStore, $dispatcher, 'Subscription or user not found');
                return;
            }

            $paymentMethod = PaymentMethod::where('user_id', $this->userId)
                ->where('is_default', true)
                ->first();

            if (!$paymentMethod) {
                Log::warning('No default payment method found for subscription renewal', [
                    'saga_uuid' => $this->sagaUuid,
                    'user_id' => $this->userId,
                    'correlation_id' => $this->correlationId,
                ]);
                $this->recordPaymentFailure($eventStore, $dispatcher, 'No default payment method found');
                return;
            }

            // Validate payment method
            if (!$paymentMethod->isValid()) {
                $validationError = $paymentMethod->getValidationError() ?? 'Payment method is invalid';
                Log::warning('Payment method validation failed', [
                    'saga_uuid' => $this->sagaUuid,
                    'user_id' => $this->userId,
                    'payment_method_id' => $paymentMethod->id,
                    'error' => $validationError,
                    'correlation_id' => $this->correlationId,
                ]);
                $this->recordPaymentFailure($eventStore, $dispatcher, $validationError);
                return;
            }

            $paymentResult = $this->attemptPayment($paymentMethod, $authorizeNetService, $achPaymentService);

            if ($paymentResult['success']) {
                $this->recordPaymentSuccess($eventStore, $dispatcher, $paymentResult);
            } else {
                $this->handlePaymentFailure($eventStore, $dispatcher, $paymentResult['message'] ?? 'Payment failed');
            }
        } catch (\Exception $e) {
            Log::error('Error processing subscription renewal', [
                'saga_uuid' => $this->sagaUuid,
                'error' => $e->getMessage(),
                'correlation_id' => $this->correlationId,
                'trace' => $e->getTraceAsString(),
            ]);
            $this->handlePaymentFailure($eventStore, $dispatcher, $e->getMessage());
        }
    }

    /**
     * Check if this renewal has already been processed (idempotency)
     */
    private function isAlreadyProcessed(EventStoreContract $eventStore): bool
    {
        // Check if a completion or failure event already exists for this saga
        // This prevents duplicate processing if the job is retried
        $cacheKey = "renewal_processed:{$this->sagaUuid}";

        if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
            return true;
        }

        return false;
    }

    /**
     * Mark renewal as processed for idempotency
     */
    private function markAsProcessed(): void
    {
        $cacheKey = "renewal_processed:{$this->sagaUuid}";
        $ttlDays = (int) config('subscription.renewal_idempotency_ttl_days', 30);
        \Illuminate\Support\Facades\Cache::put($cacheKey, true, now()->addDays($ttlDays));
    }

    /**
     * Handle payment failure with retry logic
     */
    private function handlePaymentFailure(
        EventStoreContract $eventStore,
        Dispatcher $dispatcher,
        string $reason
    ): void {
        $this->attemptNumber++;

        if ($this->attemptNumber < $this->maxAttempts) {
            // Schedule retry with exponential backoff
            $retryDelayDays = $this->retrySchedule[$this->attemptNumber - 1] ?? 30;

            Log::info('Scheduling subscription renewal retry', [
                'saga_uuid' => $this->sagaUuid,
                'attempt_number' => $this->attemptNumber,
                'max_attempts' => $this->maxAttempts,
                'retry_delay_days' => $retryDelayDays,
                'reason' => $reason,
                'correlation_id' => $this->correlationId,
            ]);

            // Dispatch retry job with delay
            ProcessSubscriptionRenewalJob::dispatch(
                $this->sagaUuid,
                $this->subscriptionId,
                $this->userId,
                $this->amount,
                $this->correlationId
            )->delay(now()->addDays($retryDelayDays));
        } else {
            // All retries exhausted - record final failure
            Log::warning('Subscription renewal failed after all retry attempts', [
                'saga_uuid' => $this->sagaUuid,
                'attempt_number' => $this->attemptNumber,
                'max_attempts' => $this->maxAttempts,
                'reason' => $reason,
                'correlation_id' => $this->correlationId,
            ]);

            $this->recordPaymentFailure($eventStore, $dispatcher, $reason);
            $this->markAsProcessed();
        }
    }

    private function attemptPayment(
        PaymentMethod $paymentMethod,
        AuthorizeNetService $authorizeNetService,
        AchPaymentService $achPaymentService
    ): array {
        if ($paymentMethod->isCreditCard()) {
            return $authorizeNetService->processTransaction(
                $this->amount,
                $paymentMethod->cc_token,
                $paymentMethod->cc_token,
                "Subscription renewal - Subscription #{$this->subscriptionId}",
                $this->userId,
                $this->subscriptionId
            );
        } elseif ($paymentMethod->isAch()) {
            return $achPaymentService->processAchTransaction(
                $this->amount,
                $paymentMethod->ach_token,
                $paymentMethod->ach_token,
                "Subscription renewal - Subscription #{$this->subscriptionId}",
                $this->userId,
                $this->subscriptionId
            );
        }

        return [
            'success' => false,
            'message' => 'Unsupported payment method type',
        ];
    }

    private function recordPaymentSuccess(
        EventStoreContract $eventStore,
        Dispatcher $dispatcher,
        array $paymentResult
    ): void {
        // Create a saga instance with the UUID
        $saga = new SubscriptionRenewalSaga();
        $saga->uuid = $this->sagaUuid;

        $saga->complete([
            'transaction_id' => $paymentResult['transaction_id'] ?? null,
            'auth_code' => $paymentResult['auth_code'] ?? null,
            'renewed_at' => now()->toDateTimeString(),
            'correlation_id' => $this->correlationId,
        ]);

        foreach ($saga->getRecordedEvents() as $event) {
            $eventStore->store($event);
            $dispatcher->dispatch($event);
        }

        Log::info('Subscription renewal payment successful', [
            'saga_uuid' => $this->sagaUuid,
            'subscription_id' => $this->subscriptionId,
            'user_id' => $this->userId,
            'amount' => $this->amount,
            'transaction_id' => $paymentResult['transaction_id'] ?? null,
            'correlation_id' => $this->correlationId,
        ]);

        $this->markAsProcessed();
    }

    private function recordPaymentFailure(
        EventStoreContract $eventStore,
        Dispatcher $dispatcher,
        string $reason
    ): void {
        // Create a saga instance with the UUID
        $saga = new SubscriptionRenewalSaga();
        $saga->uuid = $this->sagaUuid;

        $saga->fail([
            'reason' => $reason,
            'failed_at' => now()->toDateTimeString(),
            'correlation_id' => $this->correlationId,
        ]);

        foreach ($saga->getRecordedEvents() as $event) {
            $eventStore->store($event);
            $dispatcher->dispatch($event);
        }

        Log::warning('Subscription renewal payment failed', [
            'saga_uuid' => $this->sagaUuid,
            'subscription_id' => $this->subscriptionId,
            'user_id' => $this->userId,
            'reason' => $reason,
            'correlation_id' => $this->correlationId,
        ]);

        // Dispatch failure alert event
        $dispatcher->dispatch(new RenewalFailureAlert(
            $this->sagaUuid,
            $this->subscriptionId,
            $this->userId,
            $this->amount,
            $reason,
            $this->attemptNumber,
            $this->maxAttempts,
            $this->correlationId,
        ));

        $this->markAsProcessed();
    }
}
