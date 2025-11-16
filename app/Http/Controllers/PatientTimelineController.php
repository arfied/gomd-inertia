<?php

namespace App\Http\Controllers;

use App\Application\Patient\Queries\GetPatientEventTimelineByUserId;
use App\Application\Queries\QueryBus;
use App\Models\StoredEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PatientTimelineController extends Controller
{
    public function index(Request $request, QueryBus $queryBus): JsonResponse
    {
        $user = $request->user();

        /** @var Collection<int, StoredEvent> $events */
        $events = $queryBus->ask(
            new GetPatientEventTimelineByUserId($user->id)
        );

        return response()->json([
            'events' => $events->map(
                static function (StoredEvent $event): array {
                    $source = is_array($event->metadata)
                        ? ($event->metadata['source'] ?? null)
                        : null;

                    return [
                        'id' => $event->id,
                        'aggregate_uuid' => $event->aggregate_uuid,
                        'event_type' => $event->event_type,
                        'source' => $source,
                        'description' => match ($event->event_type) {
                            'patient.enrolled' => match ($source) {
                                'registration' => 'Patient enrolled automatically after registration.',
                                'manual' => 'Patient enrollment started manually from the dashboard.',
                                default => 'Patient enrolled in TeleMed Pro.',
                            },
                            default => $event->event_type,
                        },
                        'payload' => $event->event_data,
                        'metadata' => $event->metadata,
                        'occurred_at' => optional($event->occurred_at)?->toISOString(),
                    ];
                }
            )->all(),
        ]);
    }
}

