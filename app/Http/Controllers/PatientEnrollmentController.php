<?php

namespace App\Http\Controllers;

use App\Application\Patient\Queries\GetPatientEnrollmentByUserId;
use App\Application\Queries\QueryBus;
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

        return response()->json([
            'enrollment' => $enrollment ? [
                'patient_uuid' => $enrollment->patient_uuid,
                'user_id' => $enrollment->user_id,
                'source' => $enrollment->source,
                'metadata' => $enrollment->metadata,
                'enrolled_at' => optional($enrollment->enrolled_at)?->toISOString(),
            ] : null,
        ]);
    }
}

