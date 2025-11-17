<?php

namespace App\Http\Controllers;

use App\Application\Commands\CommandBus;
use App\Application\Patient\Commands\UploadPatientDocument;
use App\Application\Patient\Queries\GetPatientDocumentsByPatientUuid;
use App\Application\Patient\Queries\GetPatientEnrollmentByPatientUuid;
use App\Application\Queries\QueryBus;
use App\Models\MedicalRecord;
use App\Models\PatientEnrollment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

class StaffPatientDocumentsController extends Controller
{
    public function index(string $patientUuid, Request $request, QueryBus $queryBus): JsonResponse
    {
        /** @var User|null $authUser */
        $authUser = $request->user();

        abort_unless(
            $authUser && ($authUser->hasAnyRole(['admin', 'staff']) || in_array($authUser->role, ['admin', 'staff'], true)),
            403,
        );

        /** @var Collection<int, MedicalRecord> $documents */
        $documents = $queryBus->ask(
            new GetPatientDocumentsByPatientUuid($patientUuid),
        );

        return response()->json([
            'documents' => $documents->map(
                static function (MedicalRecord $record): array {
                    return [
                        'id' => $record->id,
                        'patient_id' => $record->patient_id,
                        'doctor_id' => $record->doctor_id,
                        'record_type' => $record->record_type,
                        'description' => $record->description,
                        'record_date' => optional($record->record_date)?->toDateString(),
                        'file_path' => $record->file_path,
                        'created_at' => optional($record->created_at)?->toISOString(),
                        'updated_at' => optional($record->updated_at)?->toISOString(),
                    ];
                },
            )->all(),
        ]);
    }

    public function store(string $patientUuid, Request $request, QueryBus $queryBus, CommandBus $commandBus): JsonResponse
    {
        /** @var User|null $authUser */
        $authUser = $request->user();

        abort_unless(
            $authUser && ($authUser->hasAnyRole(['admin', 'staff']) || in_array($authUser->role, ['admin', 'staff'], true)),
            403,
        );

        $data = $request->validate([
            'record_type' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'record_date' => ['nullable', 'date'],
            'file' => ['required', 'file', 'max:10240'],
        ]);

        /** @var PatientEnrollment|null $enrollment */
        $enrollment = $queryBus->ask(
            new GetPatientEnrollmentByPatientUuid($patientUuid),
        );

        if (! $enrollment instanceof PatientEnrollment) {
            return response()->json([
                'message' => 'Patient not found.',
            ], 404);
        }

        /** @var UploadedFile $file */
        $file = $data['file'];

        $path = $file->store('patient-documents/'.$enrollment->user_id, 'public');

        $command = new UploadPatientDocument(
            patientUuid: $enrollment->patient_uuid,
            userId: $enrollment->user_id,
            recordType: $data['record_type'],
            filePath: $path,
            description: $data['description'],
            recordDate: isset($data['record_date']) ? (string) $data['record_date'] : null,
            doctorId: $authUser->id,
            metadata: [
                'source' => 'manual',
                'actor_user_id' => $authUser->id,
                'original_filename' => $file->getClientOriginalName(),
                'mime_type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
            ],
        );

        $commandBus->dispatch($command);

        /** @var Collection<int, MedicalRecord> $documents */
        $documents = $queryBus->ask(
            new GetPatientDocumentsByPatientUuid($patientUuid),
        );

        /** @var MedicalRecord|null $document */
        $document = $documents->first();

        return response()->json([
            'document' => $document ? [
                'id' => $document->id,
                'patient_id' => $document->patient_id,
                'doctor_id' => $document->doctor_id,
                'record_type' => $document->record_type,
                'description' => $document->description,
                'record_date' => optional($document->record_date)?->toDateString(),
                'file_path' => $document->file_path,
                'created_at' => optional($document->created_at)?->toISOString(),
                'updated_at' => optional($document->updated_at)?->toISOString(),
            ] : null,
        ], 201);
    }
}

