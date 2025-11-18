<?php

namespace App\Services;

use App\Enums\InventoryReservationStatus;
use App\Models\Inventory;
use App\Models\InventoryReservation;
use Illuminate\Support\Str;

/**
 * InventoryReservationService
 *
 * Handles inventory reservation logic for the order fulfillment saga.
 * Reserves medications from inventory and tracks reservations.
 */
class InventoryReservationService
{
    /**
     * Reserve inventory for medications.
     *
     * @param  array<array{medication_id: int, quantity: int}>  $medications
     * @param  string|null  $warehouseId
     * @return array{success: bool, reservationId?: string, error?: string}
     */
    public function reserve(array $medications, ?string $warehouseId = null): array
    {
        try {
            // Validate medications array
            if (empty($medications)) {
                return [
                    'success' => false,
                    'error' => 'No medications provided for reservation',
                ];
            }

            // Check availability for all medications
            foreach ($medications as $med) {
                $inventory = Inventory::where('medication_id', $med['medication_id'])
                    ->first();

                if (! $inventory) {
                    return [
                        'success' => false,
                        'error' => "Medication {$med['medication_id']} not found in inventory",
                    ];
                }

                if ($inventory->quantity < $med['quantity']) {
                    return [
                        'success' => false,
                        'error' => "Insufficient quantity for medication {$med['medication_id']}. Available: {$inventory->quantity}, Requested: {$med['quantity']}",
                    ];
                }
            }

            // Create reservation record
            $reservationId = 'RES-' . Str::uuid();
            $reservation = InventoryReservation::create([
                'reservation_id' => $reservationId,
                'warehouse_id' => $warehouseId,
                'status' => InventoryReservationStatus::RESERVED->value,
                'medications' => json_encode($medications),
                'reserved_at' => now(),
            ]);

            // Deduct from inventory
            foreach ($medications as $med) {
                Inventory::where('medication_id', $med['medication_id'])
                    ->decrement('quantity', $med['quantity']);
            }

            return [
                'success' => true,
                'reservationId' => $reservationId,
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Release a reservation (compensation action).
     *
     * @return array{success: bool, error?: string}
     */
    public function release(string $reservationId): array
    {
        try {
            $reservation = InventoryReservation::where('reservation_id', $reservationId)
                ->first();

            if (! $reservation) {
                return [
                    'success' => false,
                    'error' => "Reservation {$reservationId} not found",
                ];
            }

            // Restore inventory
            $medications = json_decode($reservation->medications, true);
            foreach ($medications as $med) {
                Inventory::where('medication_id', $med['medication_id'])
                    ->increment('quantity', $med['quantity']);
            }

            // Mark reservation as released
            $reservation->update([
                'status' => InventoryReservationStatus::RELEASED->value,
                'released_at' => now(),
            ]);

            return ['success' => true];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}

