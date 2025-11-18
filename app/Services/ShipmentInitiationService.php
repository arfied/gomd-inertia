<?php

namespace App\Services;

use App\Enums\ShipmentStatus;
use App\Models\Shipment;
use Illuminate\Support\Str;

/**
 * ShipmentInitiationService
 *
 * Handles shipment initiation logic for the order fulfillment saga.
 * Creates shipment records and tracks shipment status.
 */
class ShipmentInitiationService
{
    /**
     * Initiate shipment for an order.
     *
     * @return array{success: bool, shipmentId?: string, trackingNumber?: string, error?: string}
     */
    public function initiate(
        string $orderUuid,
        string $shippingAddress,
        ?string $shippingMethod = null,
        ?string $trackingNumber = null,
    ): array {
        try {
            // Validate shipping address
            if (empty($shippingAddress)) {
                return [
                    'success' => false,
                    'error' => 'Shipping address is required',
                ];
            }

            // Default shipping method
            $shippingMethod = $shippingMethod ?? 'standard';

            // Generate shipment ID and tracking number if not provided
            $shipmentId = 'SHIP-' . Str::uuid();
            $trackingNumber = $trackingNumber ?? 'TRACK-' . Str::random(12);

            // Create shipment record
            $shipment = Shipment::create([
                'shipment_id' => $shipmentId,
                'order_uuid' => $orderUuid,
                'shipping_address' => $shippingAddress,
                'shipping_method' => $shippingMethod,
                'tracking_number' => $trackingNumber,
                'status' => ShipmentStatus::INITIATED->value,
                'initiated_at' => now(),
            ]);

            return [
                'success' => true,
                'shipmentId' => $shipmentId,
                'trackingNumber' => $trackingNumber,
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Cancel a shipment (compensation action).
     *
     * @return array{success: bool, error?: string}
     */
    public function cancel(string $shipmentId): array
    {
        try {
            $shipment = Shipment::where('shipment_id', $shipmentId)->first();

            if (! $shipment) {
                return [
                    'success' => false,
                    'error' => "Shipment {$shipmentId} not found",
                ];
            }

            // Only cancel if not already shipped
            $currentStatus = ShipmentStatus::tryFrom($shipment->status);
            if ($currentStatus && !$currentStatus->canBeCancelled()) {
                return [
                    'success' => false,
                    'error' => "Cannot cancel shipment with status: {$shipment->status}",
                ];
            }

            // Mark shipment as cancelled
            $shipment->update([
                'status' => ShipmentStatus::CANCELLED->value,
                'cancelled_at' => now(),
            ]);

            return ['success' => true];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get shipment details.
     *
     * @return array|null
     */
    public function getShipment(string $shipmentId): ?array
    {
        $shipment = Shipment::where('shipment_id', $shipmentId)->first();

        if (! $shipment) {
            return null;
        }

        return [
            'shipment_id' => $shipment->shipment_id,
            'order_uuid' => $shipment->order_uuid,
            'tracking_number' => $shipment->tracking_number,
            'status' => $shipment->status,
            'shipping_address' => $shipment->shipping_address,
            'shipping_method' => $shipment->shipping_method,
        ];
    }
}

