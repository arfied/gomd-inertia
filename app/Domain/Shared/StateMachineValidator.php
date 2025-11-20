<?php

namespace App\Domain\Shared;

use InvalidArgumentException;

/**
 * Validates state transitions in state machines.
 *
 * Ensures that only valid transitions are allowed based on the defined state machine rules.
 */
class StateMachineValidator
{
    /** @var array<string, array<string>> */
    private array $validTransitions;

    /**
     * Create a new state machine validator.
     *
     * @param  array<string, array<string>>  $validTransitions Map of state => allowed next states
     */
    public function __construct(array $validTransitions)
    {
        $this->validTransitions = $validTransitions;
    }

    /**
     * Validate a state transition.
     *
     * @param  string  $fromState Current state
     * @param  string  $toState Desired next state
     *
     * @throws InvalidArgumentException If transition is invalid
     */
    public function validate(string $fromState, string $toState): void
    {
        if ($fromState === $toState) {
            return; // Allow staying in same state
        }

        if (!isset($this->validTransitions[$fromState])) {
            throw new InvalidArgumentException(
                "Unknown state: {$fromState}"
            );
        }

        if (!in_array($toState, $this->validTransitions[$fromState], true)) {
            $allowed = implode(', ', $this->validTransitions[$fromState]);
            throw new InvalidArgumentException(
                "Invalid transition from '{$fromState}' to '{$toState}'. Allowed: {$allowed}"
            );
        }
    }

    /**
     * Check if a transition is valid without throwing.
     *
     * @param  string  $fromState Current state
     * @param  string  $toState Desired next state
     */
    public function isValid(string $fromState, string $toState): bool
    {
        try {
            $this->validate($fromState, $toState);
            return true;
        } catch (InvalidArgumentException) {
            return false;
        }
    }
}

