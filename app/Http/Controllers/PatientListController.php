<?php

namespace App\Http\Controllers;

use App\Application\Patient\Queries\GetPatientDemographicsByUserId;
use App\Application\Patient\Queries\GetPatientEnrollmentByPatientUuid;
use App\Application\Patient\Queries\GetPatientList;
use App\Application\Patient\Queries\GetPatientListCount;
use App\Application\Patient\Queries\GetPatientMedicalHistoryByUserId;
use App\Application\Patient\Queries\GetPatientSubscriptionByUserId;
use App\Application\Queries\QueryBus;
use App\Models\PatientEnrollment;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PatientListController extends Controller
{
    public function index(Request $request, QueryBus $queryBus): JsonResponse
    {
        $user = $request->user();

        abort_unless(
            $user && ($user->hasAnyRole(['admin', 'staff']) || in_array($user->role, ['admin', 'staff'], true)),
            403
        );

        $search = $request->string('search')->toString();
        $perPage = (int) $request->input('per_page', 15);

        if ($perPage <= 0 || $perPage > 100) {
            $perPage = 15;
        }

        $filters = [
            'has_documents' => $request->boolean('has_documents'),
            'has_medical_history' => $request->boolean('has_medical_history'),
            'has_active_subscription' => $request->boolean('has_active_subscription'),
        ];

        /** @var Paginator $paginator */
        $paginator = $queryBus->ask(
            new GetPatientList(
                search: $search !== '' ? $search : null,
                perPage: $perPage,
                filters: $filters,
            )
        );

        $patients = collect($paginator->items())->map(function ($row) {
            return [
                'patient_uuid' => $row->patient_uuid,
                'user_id' => $row->user_id,
                'fname' => $row->fname,
                'lname' => $row->lname,
                'email' => $row->email,
                'status' => $row->status,
                'enrolled_at' => optional($row->enrolled_at)?->toISOString(),
                'has_documents' => (int) ($row->documents_count ?? 0) > 0,
                'has_medical_history' => (int) ($row->medical_history_count ?? 0) > 0,
                'has_active_subscription' => $row->subscription_status === Subscription::STATUS_ACTIVE,
                'subscription' => $row->subscription_status !== null ? [
                    'status' => $row->subscription_status,
                    'plan_name' => $row->subscription_plan_name,
                    'is_trial' => (bool) $row->subscription_is_trial,
                ] : null,
            ];
        })->all();

        return response()->json([
            'patients' => $patients,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'next_page_url' => $paginator->nextPageUrl(),
                'prev_page_url' => $paginator->previousPageUrl(),
            ],
        ]);
    }

    public function show(string $patientUuid, Request $request, QueryBus $queryBus): JsonResponse
    {
        $user = $request->user();

        abort_unless(
            $user && ($user->hasAnyRole(['admin', 'staff']) || in_array($user->role, ['admin', 'staff'], true)),
            403
        );

        /** @var PatientEnrollment|null $enrollment */
        $enrollment = $queryBus->ask(
            new GetPatientEnrollmentByPatientUuid($patientUuid)
        );

        if (! $enrollment instanceof PatientEnrollment) {
            return response()->json([
                'message' => 'Patient not found.',
            ], 404);
        }

        /** @var User|null $patientUser */
        $patientUser = $queryBus->ask(
            new GetPatientDemographicsByUserId($enrollment->user_id)
        );

        /** @var Subscription|null $subscription */
        $subscription = $queryBus->ask(
            new GetPatientSubscriptionByUserId($enrollment->user_id)
        );

        $medicalHistory = $queryBus->ask(
            new GetPatientMedicalHistoryByUserId($enrollment->user_id)
        );

        return response()->json([
            'patient' => [
                'patient_uuid' => $enrollment->patient_uuid,
                'user_id' => $enrollment->user_id,
                'fname' => $patientUser?->fname,
                'lname' => $patientUser?->lname,
                'email' => $patientUser?->email,
                'status' => $patientUser?->status,
                'demographics' => $patientUser ? [
                    'gender' => $patientUser->gender,
                    'dob' => optional($patientUser->dob)?->toDateString(),
                    'address1' => $patientUser->address1,
                    'address2' => $patientUser->address2,
                    'city' => $patientUser->city,
                    'state' => $patientUser->state,
                    'zip' => $patientUser->zip,
                    'phone' => $patientUser->phone,
                    'mobile_phone' => $patientUser->mobile_phone,
                ] : null,
                'enrollment' => [
                    'source' => $enrollment->source,
                    'metadata' => $enrollment->metadata,
                    'enrolled_at' => optional($enrollment->enrolled_at)?->toISOString(),
                ],
                'subscription' => $subscription ? [
                    'id' => $subscription->id,
                    'status' => $subscription->status,
                    'plan_name' => optional($subscription->plan)->name,
                    'is_trial' => $subscription->is_trial,
                    'starts_at' => optional($subscription->starts_at)?->toISOString(),
                    'ends_at' => optional($subscription->ends_at)?->toISOString(),
                ] : null,
                'medical_history' => $medicalHistory,
            ],
        ]);
    }

    public function count(Request $request, QueryBus $queryBus): JsonResponse
    {
        $user = $request->user();

        abort_unless(
            $user && ($user->hasAnyRole(['admin', 'staff']) || in_array($user->role, ['admin', 'staff'], true)),
            403
        );

        $search = $request->string('search')->toString();

        $filters = [
            'has_documents' => $request->boolean('has_documents'),
            'has_medical_history' => $request->boolean('has_medical_history'),
            'has_active_subscription' => $request->boolean('has_active_subscription'),
        ];

        $count = $queryBus->ask(
            new GetPatientListCount(
                search: $search !== '' ? $search : null,
                filters: $filters,
            )
        );

        return response()->json([
            'count' => $count,
        ]);
    }
}

