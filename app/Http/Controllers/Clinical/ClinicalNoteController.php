<?php

namespace App\Http\Controllers\Clinical;

use App\Application\Clinical\Commands\RecordClinicalNote;
use App\Application\Commands\CommandBus;
use App\Http\Controllers\Controller;
use App\Models\ClinicalNoteReadModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ClinicalNoteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $patientId = $request->query('patient_id');
        $doctorId = $request->query('doctor_id');
        $noteType = $request->query('note_type');
        $page = $request->query('page', 1);
        $perPage = $request->query('per_page', 15);

        $query = ClinicalNoteReadModel::query();

        if ($patientId) {
            $query->where('patient_id', $patientId);
        }

        if ($doctorId) {
            $query->where('doctor_id', $doctorId);
        }

        if ($noteType) {
            $query->where('note_type', $noteType);
        }

        $notes = $query->orderBy('recorded_at', 'desc')->paginate($perPage, ['*'], 'page', $page);

        return response()->json($notes);
    }

    public function store(Request $request, CommandBus $commandBus): JsonResponse
    {
        $data = $request->validate([
            'patient_id' => 'required|string',
            'note_type' => 'required|string|in:progress,assessment,plan,consultation',
            'content' => 'required|string',
            'attachments' => 'nullable|array',
        ]);

        $noteUuid = (string) Str::uuid();

        $command = new RecordClinicalNote(
            clinicalNoteUuid: $noteUuid,
            patientId: $data['patient_id'],
            doctorId: $request->user()?->id,
            noteType: $data['note_type'],
            content: $data['content'],
            attachments: $data['attachments'] ?? [],
            recordedAt: now(),
            metadata: ['source' => 'api', 'actor_user_id' => $request->user()?->id],
        );

        $commandBus->dispatch($command);

        return response()->json([
            'clinical_note_uuid' => $noteUuid,
            'message' => 'Clinical note recorded successfully',
        ], 201);
    }

    public function show(string $uuid): JsonResponse
    {
        $note = ClinicalNoteReadModel::where('clinical_note_uuid', $uuid)->first();

        if (! $note) {
            return response()->json(['message' => 'Clinical note not found'], 404);
        }

        return response()->json($note);
    }
}

