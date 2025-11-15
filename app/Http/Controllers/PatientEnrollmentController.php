<?php

namespace App\Http\Controllers;

use App\Application\Patient\PatientEnrollmentService;
use App\Application\Patient\Queries\GetPatientEnrollmentByUserId;
use App\Application\Queries\QueryBus;
use App\Models\PatientEnrollment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PatientEnrollmentController extends Controller
{
    public function show(Request $request, QueryBus $queryBus): JsonResponse
    {
        $user = $request->user();

        $enrollment = $queryBus->ask(
            new GetPatientEnrollmentByUserId($user->id)
        );

        return $this->formatEnrollmentResponse($enrollment);
    }

    public function store(
        Request $request,
        PatientEnrollmentService $patientEnrollmentService,
        QueryBus $queryBus,
    ): JsonResponse {
        $user = $request->user();

        $existingEnrollment = $queryBus->ask(
            new GetPatientEnrollmentByUserId($user->id)
        );

        if ($existingEnrollment instanceof PatientEnrollment) {
            return $this->formatEnrollmentResponse($existingEnrollment);
        }

        $patientEnrollmentService->enroll($user, [
            'source' => 'manual',
        ]);

        $enrollment = $queryBus->ask(
            new GetPatientEnrollmentByUserId($user->id)
        );

        return $this->formatEnrollmentResponse($enrollment, 201);
    }

    private function formatEnrollmentResponse(?PatientEnrollment $enrollment, int $status = 200): JsonResponse
    {
        return response()->json([
            'enrollment' => $enrollment ? [
                'patient_uuid' => $enrollment->patient_uuid,
                'user_id' => $enrollment->user_id,
                'source' => $enrollment->source,
                'metadata' => $enrollment->metadata,
                'enrolled_at' => optional($enrollment->enrolled_at)?->toISOString(),
            ] : null,
        ], $status);
    }
}

