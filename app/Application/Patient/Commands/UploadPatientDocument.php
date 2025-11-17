<?php

namespace App\Application\Patient\Commands;

use App\Domain\Shared\Commands\Command;

/**
 * Command DTO for uploading a new document to a patient's record.
 */
class UploadPatientDocument implements Command
{
    public function __construct(
        public string $patientUuid,
        public int $userId,
        public string $recordType,
        public string $filePath,
        public string $description,
        public ?string $recordDate = null,
        public ?int $doctorId = null,
        public array $metadata = [],
    ) {
    }
}

