<?php

namespace App\Domain\Commission;

use App\Domain\Commission\Events\PayoutProcessed;
use App\Domain\Commission\Events\PayoutRequested;
use App\Domain\Events\DomainEvent;
use App\Domain\Shared\AggregateRoot;

/**
 * Event-sourced aggregate for Payout bounded context.
 *
 * Represents a payout transaction for an agent's pending commissions.
 * Tracks payout status, amounts, and included commissions.
 */
class PayoutAggregate extends AggregateRoot
{
    public string $uuid;
    public string $agentId;
    public float $totalAmount = 0;
    public array $commissionIds = [];
    public string $status = 'pending'; // 'pending' | 'processing' | 'processed' | 'failed'
    public ?string $processedAt = null;
    public ?string $failureReason = null;

    /**
     * Create a new payout request and record a PayoutRequested event.
     */
    public static function create(
        string $uuid,
        array $payload = [],
        array $metadata = []
    ): self {
        $aggregate = new self();
        $aggregate->uuid = $uuid;

        $aggregate->recordThat(new PayoutRequested($uuid, $payload, $metadata));

        return $aggregate;
    }

    /**
     * Mark payout as processed.
     */
    public function process(array $payload = [], array $metadata = []): void
    {
        if ($this->status === 'processed') {
            return; // Already processed
        }

        $this->recordThat(new PayoutProcessed($this->uuid, $payload, $metadata));
    }

    /**
     * Apply domain events to update aggregate state.
     */
    protected function apply(DomainEvent $event): void
    {
        match ($event::eventType()) {
            'payout.requested' => $this->applyPayoutRequested($event),
            'payout.processed' => $this->applyPayoutProcessed($event),
            default => null,
        };
    }

    /**
     * Apply PayoutRequested event.
     */
    private function applyPayoutRequested(DomainEvent $event): void
    {
        $this->agentId = $event->payload['agent_id'] ?? '';
        $this->totalAmount = (float) ($event->payload['total_amount'] ?? 0);
        $this->commissionIds = $event->payload['commission_ids'] ?? [];
        $this->status = 'pending';
    }

    /**
     * Apply PayoutProcessed event.
     */
    private function applyPayoutProcessed(DomainEvent $event): void
    {
        $this->status = 'processed';
        $this->processedAt = $event->payload['processed_at'] ?? now()->toIso8601String();
    }

    /**
     * Get the number of commissions included in this payout.
     */
    public function getCommissionCount(): int
    {
        return count($this->commissionIds);
    }

    /**
     * Check if payout is ready to process.
     */
    public function isReadyToProcess(): bool
    {
        return $this->status === 'pending' && $this->totalAmount > 0;
    }
}

