<?php

namespace App\Http\Controllers;

use App\Application\Patient\Queries\GetPatientList;
use App\Application\Patient\Queries\GetPatientListCount;
use App\Application\Queries\QueryBus;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PatientListController extends Controller
{
    public function index(Request $request, QueryBus $queryBus): JsonResponse
    {
        $user = $request->user();

        abort_unless(in_array($user->role, ['admin', 'staff'], true), 403);

        $search = $request->string('search')->toString();
        $perPage = (int) $request->input('per_page', 15);

        if ($perPage <= 0 || $perPage > 100) {
            $perPage = 15;
        }

        /** @var Paginator $paginator */
        $paginator = $queryBus->ask(
            new GetPatientList(
                search: $search !== '' ? $search : null,
                perPage: $perPage,
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

    public function count(Request $request, QueryBus $queryBus): JsonResponse
    {
        $user = $request->user();

        abort_unless(in_array($user->role, ['admin', 'staff'], true), 403);

        $search = $request->string('search')->toString();

        $count = $queryBus->ask(
            new GetPatientListCount(
                search: $search !== '' ? $search : null,
            )
        );

        return response()->json([
            'count' => $count,
        ]);
    }
}

