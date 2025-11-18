<?php

namespace App\Application\Order\Handlers;

use App\Application\Commands\CommandHandler;
use App\Application\Order\Commands\ReserveInventory;
use App\Domain\Order\Events\InventoryReserved;
use App\Domain\Order\Events\InventoryReservationFailed;
use App\Domain\Shared\Commands\Command;
use App\Services\EventStoreContract;
use App\Services\InventoryReservationService;
use Illuminate\Contracts\Events\Dispatcher;
use InvalidArgumentException;
use Throwable;

/**
 * ReserveInventoryHandler
 *
 * Handles the ReserveInventory command.
 * Part of the order fulfillment saga (Step 2).
 *
 * Reserves inventory for prescribed medications.
 * Emits either InventoryReserved or InventoryReservationFailed event.
 */
class ReserveInventoryHandler implements CommandHandler
{
    public function __construct(
        private EventStoreContract $eventStore,
        private Dispatcher $events,
        private InventoryReservationService $inventoryService,
    ) {
    }

    public function handle(Command $command): void
    {
        if (! $command instanceof ReserveInventory) {
            throw new InvalidArgumentException('ReserveInventoryHandler can only handle ReserveInventory commands');
        }

        try {
            // Call inventory service to reserve medications
            $reservationResult = $this->inventoryService->reserve(
                medications: $command->medications,
                warehouseId: $command->warehouseId,
            );

            // Emit appropriate event based on result
            if ($reservationResult['success']) {
                $payload = [
                    'order_uuid' => $command->orderUuid,
                    'saga_uuid' => $command->sagaUuid,
                    'medications' => $command->medications,
                    'warehouse_id' => $command->warehouseId,
                    'reservation_id' => $reservationResult['reservationId'],
                    'reserved_at' => now()->toIso8601String(),
                    'status' => 'reserved',
                ];

                $event = new InventoryReserved(
                    $command->orderUuid,
                    $payload,
                    array_merge($command->metadata, ['saga_uuid' => $command->sagaUuid])
                );
            } else {
                $payload = [
                    'order_uuid' => $command->orderUuid,
                    'saga_uuid' => $command->sagaUuid,
                    'medications' => $command->medications,
                    'warehouse_id' => $command->warehouseId,
                    'reason' => $reservationResult['error'],
                    'failed_at' => now()->toIso8601String(),
                ];

                $event = new InventoryReservationFailed(
                    $command->orderUuid,
                    $payload,
                    array_merge($command->metadata, ['saga_uuid' => $command->sagaUuid])
                );
            }

            $this->eventStore->store($event);
            $this->events->dispatch($event);

        } catch (Throwable $e) {
            // Emit failure event on exception
            $payload = [
                'order_uuid' => $command->orderUuid,
                'saga_uuid' => $command->sagaUuid,
                'medications' => $command->medications,
                'reason' => $e->getMessage(),
                'failed_at' => now()->toIso8601String(),
            ];

            $event = new InventoryReservationFailed(
                $command->orderUuid,
                $payload,
                array_merge($command->metadata, ['saga_uuid' => $command->sagaUuid, 'exception' => get_class($e)])
            );

            $this->eventStore->store($event);
            $this->events->dispatch($event);

            throw $e;
        }
    }
}

