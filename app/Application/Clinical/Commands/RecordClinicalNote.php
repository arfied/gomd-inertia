<?php

namespace App\Application\Clinical\Commands;

use App\Domain\Shared\Commands\Command;

/**
 * Command DTO for recording a clinical note.
 */
class RecordClinicalNote implements Command
{
    public function __construct(
        public string $clinicalNoteUuid,
        public string $patientId,
        public int $doctorId,
        public string $noteType,
        public string $content,
        public ?array $attachments = null,
        public ?string $recordedAt = null,
        public array $metadata = [],
    ) {
    }
}

