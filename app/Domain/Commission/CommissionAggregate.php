<?php

namespace App\Domain\Commission;

use App\Domain\Commission\Events\CommissionCancelled;
use App\Domain\Commission\Events\CommissionEarned;
use App\Domain\Events\DomainEvent;
use App\Domain\Shared\AggregateRoot;

/**
 * Event-sourced aggregate for Commission bounded context.
 *
 * Represents a single commission earned by an agent/LOA through the referral hierarchy.
 * Tracks commission state, amounts, rates, and payout status.
 */
class CommissionAggregate extends AggregateRoot
{
    public string $uuid;
    public string $orderId;
    public string $patientId;
    public string $recipientId;
    public string $recipientType; // 'sfmo' | 'fmo' | 'svg' | 'mga' | 'agent' | 'loa'
    public float $amount;
    public float $rate;
    public string $status; // 'pending' | 'paid' | 'cancelled'
    public ?string $paidAt = null;
    public ?string $payoutId = null;
    public array $referralChain = []; // Full hierarchy chain
    public float $orderTotal;
    public string $productType;
    public string $commissionFrequency; // 'monthly' | 'biannual' | 'annual'

    /**
     * Create a new commission and record a CommissionEarned event.
     */
    public static function create(
        string $uuid,
        array $payload = [],
        array $metadata = []
    ): self {
        $aggregate = new self();
        $aggregate->uuid = $uuid;

        $aggregate->recordThat(new CommissionEarned($uuid, $payload, $metadata));

        return $aggregate;
    }

    /**
     * Cancel this commission.
     */
    public function cancel(array $payload = [], array $metadata = []): void
    {
        if ($this->status === 'cancelled') {
            return; // Already cancelled
        }

        $this->recordThat(new CommissionCancelled($this->uuid, $payload, $metadata));
    }

    /**
     * Apply domain events to update aggregate state.
     */
    protected function apply(DomainEvent $event): void
    {
        match ($event::eventType()) {
            'commission.earned' => $this->applyCommissionEarned($event),
            'commission.cancelled' => $this->applyCommissionCancelled($event),
            default => null,
        };
    }

    /**
     * Apply CommissionEarned event.
     */
    private function applyCommissionEarned(DomainEvent $event): void
    {
        $this->orderId = $event->payload['order_id'] ?? '';
        $this->patientId = $event->payload['patient_id'] ?? '';
        $this->recipientId = $event->payload['recipient_id'] ?? '';
        $this->recipientType = $event->payload['recipient_type'] ?? '';
        $this->amount = (float) ($event->payload['amount'] ?? 0);
        $this->rate = (float) ($event->payload['rate'] ?? 0);
        $this->status = 'pending';
        $this->orderTotal = (float) ($event->payload['order_total'] ?? 0);
        $this->productType = $event->payload['product_type'] ?? '';
        $this->commissionFrequency = $event->payload['commission_frequency'] ?? 'monthly';
        $this->referralChain = $event->payload['referral_chain'] ?? [];
    }

    /**
     * Apply CommissionCancelled event.
     */
    private function applyCommissionCancelled(DomainEvent $event): void
    {
        $this->status = 'cancelled';
    }
}

