<?php

namespace App\Application\Clinical\Commands;

use App\Domain\Shared\Commands\Command;

/**
 * Command DTO for creating a questionnaire.
 */
class CreateQuestionnaire implements Command
{
    public function __construct(
        public string $questionnaireUuid,
        public string $title,
        public ?string $description = null,
        public ?array $questions = null,
        public ?int $createdBy = null,
        public ?string $patientId = null,
        public array $metadata = [],
    ) {
    }
}

