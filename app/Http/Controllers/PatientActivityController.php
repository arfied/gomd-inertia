<?php

namespace App\Http\Controllers;

use App\Application\Patient\Queries\GetRecentPatientActivityByUserId;
use App\Application\Queries\QueryBus;
use App\Models\Activity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PatientActivityController extends Controller
{
    public function index(Request $request, QueryBus $queryBus): JsonResponse
    {
        $user = $request->user();

        /** @var Collection<int, Activity> $activities */
        $activities = $queryBus->ask(
            new GetRecentPatientActivityByUserId($user->id)
        );

        return response()->json([
            'activities' => $activities->map(
                static function (Activity $activity): array {
                    return [
                        'id' => $activity->id,
                        'type' => $activity->type,
                        'description' => $activity->description,
                        'metadata' => $activity->metadata,
                        'created_at' => optional($activity->created_at)?->toISOString(),
                    ];
                }
            )->all(),
        ]);
    }
}

