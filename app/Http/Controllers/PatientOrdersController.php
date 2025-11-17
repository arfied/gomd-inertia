<?php

namespace App\Http\Controllers;

use App\Application\Order\Queries\GetPatientOrdersByUserId;
use App\Application\Queries\QueryBus;
use App\Models\MedicationOrder;
use App\Models\MedicationOrderItem;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PatientOrdersController extends Controller
{
    public function index(Request $request, QueryBus $queryBus): JsonResponse
    {
        /** @var User $authUser */
        $authUser = $request->user();

        /** @var Collection<int, MedicationOrder> $orders */
        $orders = $queryBus->ask(
            new GetPatientOrdersByUserId($authUser->id),
        );

        return response()->json([
            'orders' => $orders->map(
                static function (MedicationOrder $order): array {
                    return [
                        'id' => $order->id,
                        'patient_id' => $order->patient_id,
                        'doctor_id' => $order->doctor_id,
                        'prescription_id' => $order->prescription_id,
                        'status' => $order->status,
                        'patient_notes' => $order->patient_notes,
                        'doctor_notes' => $order->doctor_notes,
                        'rejection_reason' => $order->rejection_reason,
                        'assigned_at' => optional($order->assigned_at)?->toISOString(),
                        'prescribed_at' => optional($order->prescribed_at)?->toISOString(),
                        'completed_at' => optional($order->completed_at)?->toISOString(),
                        'rejected_at' => optional($order->rejected_at)?->toISOString(),
                        'created_at' => optional($order->created_at)?->toISOString(),
                        'updated_at' => optional($order->updated_at)?->toISOString(),
                        'items' => $order->items->map(
                            static function (MedicationOrderItem $item): array {
                                return [
                                    'id' => $item->id,
                                    'medication_id' => $item->medication_id,
                                    'medication_name' => optional($item->medication)->name,
                                    'custom_medication_name' => $item->custom_medication_name,
                                    'custom_medication_details' => $item->custom_medication_details,
                                    'requested_dosage' => $item->requested_dosage,
                                    'requested_quantity' => is_numeric($item->requested_quantity)
                                        ? (int) $item->requested_quantity
                                        : $item->requested_quantity,
                                    'status' => $item->status,
                                    'rejection_reason' => $item->rejection_reason,
                                    'created_at' => optional($item->created_at)?->toISOString(),
                                    'updated_at' => optional($item->updated_at)?->toISOString(),
                                ];
                            }
                        )->all(),
                    ];
                }
            )->all(),
        ]);
    }
}

