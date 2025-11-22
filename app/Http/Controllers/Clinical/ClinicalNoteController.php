<?php

namespace App\Http\Controllers\Clinical;

use App\Application\Clinical\Commands\RecordClinicalNote;
use App\Application\Commands\CommandBus;
use App\Http\Controllers\Controller;
use App\Models\ClinicalNoteReadModel;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ClinicalNoteController extends Controller
{
    public function index(Request $request): Response
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

        return Inertia::render('clinical/ClinicalNotes', [
            'notes' => $notes,
        ]);
    }

    public function store(Request $request, CommandBus $commandBus)
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
            metadata: ['source' => 'web', 'actor_user_id' => $request->user()?->id],
        );

        $commandBus->dispatch($command);

        return redirect()->route('clinical.notes.index')
            ->with('success', 'Clinical note recorded successfully');
    }

    public function show(string $uuid): Response
    {
        $note = ClinicalNoteReadModel::where('clinical_note_uuid', $uuid)->first();

        if (! $note) {
            abort(404, 'Clinical note not found');
        }

        return Inertia::render('clinical/ClinicalNotes', [
            'notes' => [$note],
            'selectedNote' => $note,
        ]);
    }
}

