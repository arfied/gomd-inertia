<?php

namespace App\Application\Patient\Handlers;

use App\Application\Commands\CommandHandler;
use App\Application\Patient\Commands\UploadPatientDocument;
use App\Domain\Patient\Events\PatientDocumentUploaded;
use App\Domain\Shared\Commands\Command;
use App\Services\EventStoreContract;
use Illuminate\Contracts\Events\Dispatcher;
use InvalidArgumentException;

/**
 * Command handler that persists a PatientDocumentUploaded event.
 */
class UploadPatientDocumentHandler implements CommandHandler
{
    public function __construct(
        private EventStoreContract $eventStore,
        private Dispatcher $events,
    ) {
    }

    public function handle(Command $command): void
    {
        if (! $command instanceof UploadPatientDocument) {
            throw new InvalidArgumentException('UploadPatientDocumentHandler can only handle UploadPatientDocument commands.');
        }

        $payload = $this->buildPayload($command);

        $event = new PatientDocumentUploaded(
            $command->patientUuid,
            $payload,
            $command->metadata,
        );

        $this->eventStore->store($event);

        $this->events->dispatch($event);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPayload(UploadPatientDocument $command): array
    {
        $recordDate = $command->recordDate ?? now()->toDateString();

        $payload = [
            'patient_id' => $command->userId,
            'record_type' => $command->recordType,
            'description' => $command->description,
            'record_date' => $recordDate,
            'file_path' => $command->filePath,
        ];

        if ($command->doctorId !== null) {
            $payload['doctor_id'] = $command->doctorId;
        }

        return $payload;
    }
}

