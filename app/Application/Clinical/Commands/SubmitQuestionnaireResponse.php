<?php

namespace App\Application\Clinical\Commands;

use App\Domain\Shared\Commands\Command;

/**
 * Command DTO for submitting questionnaire responses.
 */
class SubmitQuestionnaireResponse implements Command
{
    public function __construct(
        public string $questionnaireUuid,
        public string $patientId,
        public array $responses,
        public ?string $submittedAt = null,
        public array $metadata = [],
    ) {
    }
}

