<?php

namespace App\Http\Controllers\Clinical;

use App\Application\Clinical\Commands\ScheduleConsultation;
use App\Application\Commands\CommandBus;
use App\Http\Controllers\Controller;
use App\Models\ConsultationReadModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ConsultationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $patientId = $request->query('patient_id');
        $doctorId = $request->query('doctor_id');
        $status = $request->query('status');
        $page = $request->query('page', 1);
        $perPage = $request->query('per_page', 15);

        $query = ConsultationReadModel::query();

        if ($patientId) {
            $query->where('patient_id', $patientId);
        }

        if ($doctorId) {
            $query->where('doctor_id', $doctorId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $consultations = $query->orderBy('scheduled_at', 'desc')->paginate($perPage, ['*'], 'page', $page);

        return response()->json($consultations);
    }

    public function store(Request $request, CommandBus $commandBus): JsonResponse
    {
        $data = $request->validate([
            'patient_id' => 'required|string',
            'doctor_id' => 'nullable|integer',
            'scheduled_at' => 'required|date_format:Y-m-d H:i:s',
            'reason' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $consultationUuid = (string) Str::uuid();

        $command = new ScheduleConsultation(
            consultationUuid: $consultationUuid,
            patientId: $data['patient_id'],
            doctorId: $data['doctor_id'] ?? $request->user()?->id,
            scheduledAt: $data['scheduled_at'],
            reason: $data['reason'] ?? null,
            notes: $data['notes'] ?? null,
            metadata: ['source' => 'api', 'actor_user_id' => $request->user()?->id],
        );

        $commandBus->dispatch($command);

        return response()->json([
            'consultation_uuid' => $consultationUuid,
            'message' => 'Consultation scheduled successfully',
        ], 201);
    }

    public function show(string $uuid): JsonResponse
    {
        $consultation = ConsultationReadModel::where('consultation_uuid', $uuid)->first();

        if (! $consultation) {
            return response()->json(['message' => 'Consultation not found'], 404);
        }

        return response()->json($consultation);
    }
}

