<?php

namespace App\Application\Questionnaire\Commands;

use App\Domain\Shared\Commands\Command;

/**
 * Command DTO for submitting questionnaire responses.
 */
class SubmitQuestionnaireResponse implements Command
{
    public function __construct(
        public string $questionnaireId,
        public string $patientId,
        public array $responses,
        public array $metadata = [],
    ) {
    }
}

