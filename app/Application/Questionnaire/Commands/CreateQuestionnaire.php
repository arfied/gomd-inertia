<?php

namespace App\Application\Questionnaire\Commands;

use App\Domain\Shared\Commands\Command;

/**
 * Command DTO for creating a questionnaire.
 */
class CreateQuestionnaire implements Command
{
    public function __construct(
        public string $questionnaireId,
        public string $title,
        public string $description,
        public array $questions,
        public ?string $conditionId = null,
        public array $metadata = [],
    ) {
    }
}

