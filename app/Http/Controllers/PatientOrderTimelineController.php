<?php

namespace App\Http\Controllers;

use App\Application\Order\Queries\GetPatientOrderTimelineByUserId;
use App\Application\Queries\QueryBus;
use App\Models\StoredEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PatientOrderTimelineController extends Controller
{
    public function index(Request $request, QueryBus $queryBus): JsonResponse
    {
        $user = $request->user();

        $filter = $request->query('filter');

        if (! is_string($filter) || ! in_array($filter, ['created', 'prescribed', 'fulfilled', 'cancelled'], true)) {
            $filter = null;
        }

        /** @var Collection<int, StoredEvent> $events */
        $events = $queryBus->ask(
            new GetPatientOrderTimelineByUserId($user->id, filter: $filter)
        );

        return response()->json([
            'events' => $events->map(
                static function (StoredEvent $event): array {
                    return [
                        'id' => $event->id,
                        'aggregate_uuid' => $event->aggregate_uuid,
                        'event_type' => $event->event_type,
                        'description' => self::getEventDescription($event->event_type),
                        'payload' => $event->event_data,
                        'metadata' => $event->metadata,
                        'occurred_at' => optional($event->occurred_at)?->toISOString(),
                    ];
                }
            )->all(),
        ]);
    }

    private static function getEventDescription(string $eventType): string
    {
        return match ($eventType) {
            'order.created' => 'Order created',
            'order.prescription_created' => 'Prescription created',
            'order.fulfilled' => 'Order fulfilled',
            'order.cancelled' => 'Order cancelled',
            'order.assigned_to_doctor' => 'Order assigned to doctor',
            default => $eventType,
        };
    }
}

