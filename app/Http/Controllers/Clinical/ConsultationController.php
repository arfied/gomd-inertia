<?php

namespace App\Http\Controllers\Clinical;

use App\Application\Clinical\Commands\ScheduleConsultation;
use App\Application\Commands\CommandBus;
use App\Http\Controllers\Controller;
use App\Models\ConsultationReadModel;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ConsultationController extends Controller
{
    public function index(Request $request): Response
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

        return Inertia::render('clinical/Consultations', [
            'consultations' => $consultations,
        ]);
    }

    public function store(Request $request, CommandBus $commandBus)
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
            metadata: ['source' => 'web', 'actor_user_id' => $request->user()?->id],
        );

        $commandBus->dispatch($command);

        return redirect()->route('clinical.consultations.index')
            ->with('success', 'Consultation scheduled successfully');
    }

    public function show(string $uuid): Response
    {
        $consultation = ConsultationReadModel::where('consultation_uuid', $uuid)->first();

        if (! $consultation) {
            abort(404, 'Consultation not found');
        }

        return Inertia::render('clinical/Consultations', [
            'consultations' => [$consultation],
            'selectedConsultation' => $consultation,
        ]);
    }
}

