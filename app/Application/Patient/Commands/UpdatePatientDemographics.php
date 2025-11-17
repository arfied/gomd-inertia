<?php

namespace App\Application\Patient\Commands;

use App\Domain\Shared\Commands\Command;

/**
 * Command to update a patient's demographics.
 *
 * This is a thin DTO; validation happens at the HTTP layer.
 */
class UpdatePatientDemographics implements Command
{
    /**
     * @param  string  $patientUuid  Event-sourced patient aggregate identifier
     * @param  int  $userId          Underlying User model id
     * @param  array<string, mixed>  $demographics  Whitelisted demographics fields
     * @param  array<string, mixed>  $metadata      Event metadata (e.g. source, actor)
     */
    public function __construct(
        public string $patientUuid,
        public int $userId,
        public array $demographics,
        public array $metadata = [],
    ) {
    }
}

