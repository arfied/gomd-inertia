<?php

namespace App\Domain\Commission;

use App\Domain\Commission\Events\ReferralHierarchyCreated;
use App\Domain\Events\DomainEvent;
use App\Domain\Shared\AggregateRoot;

/**
 * Event-sourced aggregate for ReferralHierarchy bounded context.
 *
 * Represents the hierarchical relationship between agents and their commission rates.
 * Tracks the referral chain from LOA up through SFMO.
 */
class ReferralHierarchyAggregate extends AggregateRoot
{
    public string $uuid;
    public string $agentId;
    public ?string $parentAgentId = null;
    public string $tier; // 'sfmo' | 'fmo' | 'svg' | 'mga' | 'agent' | 'loa'
    public array $commissionRates = []; // Rates by frequency
    public array $downlineAgents = [];
    public string $status = 'active';

    /**
     * Create a new referral hierarchy and record a ReferralHierarchyCreated event.
     */
    public static function create(
        string $uuid,
        array $payload = [],
        array $metadata = []
    ): self {
        $aggregate = new self();
        $aggregate->uuid = $uuid;

        $aggregate->recordThat(new ReferralHierarchyCreated($uuid, $payload, $metadata));

        return $aggregate;
    }

    /**
     * Apply domain events to update aggregate state.
     */
    protected function apply(DomainEvent $event): void
    {
        match ($event::eventType()) {
            'referral_hierarchy.created' => $this->applyReferralHierarchyCreated($event),
            default => null,
        };
    }

    /**
     * Apply ReferralHierarchyCreated event.
     */
    private function applyReferralHierarchyCreated(DomainEvent $event): void
    {
        $this->agentId = $event->payload['agent_id'] ?? '';
        $this->parentAgentId = $event->payload['parent_agent_id'] ?? null;
        $this->tier = $event->payload['tier'] ?? '';
        $this->commissionRates = $event->payload['commission_rates'] ?? [];
        $this->downlineAgents = $event->payload['downline_agents'] ?? [];
        $this->status = $event->payload['status'] ?? 'active';
    }

    /**
     * Get commission rate for a specific frequency.
     */
    public function getCommissionRate(string $frequency = 'monthly'): float
    {
        return (float) ($this->commissionRates[$frequency] ?? 0);
    }

    /**
     * Check if this agent has a parent in the hierarchy.
     */
    public function hasParent(): bool
    {
        return $this->parentAgentId !== null;
    }

    /**
     * Get the full referral chain from this agent to the top.
     */
    public function getReferralChain(): array
    {
        return [$this->agentId, ...($this->downlineAgents ?? [])];
    }
}

