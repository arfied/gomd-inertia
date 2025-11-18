<?php

namespace App\Application\Order\Handlers;

use App\Application\Commands\CommandHandler;
use App\Application\Order\Commands\ReserveInventory;
use App\Domain\Order\Events\InventoryReserved;
use App\Domain\Order\Events\InventoryReservationFailed;
use App\Domain\Shared\Commands\Command;
use App\Services\EventStoreContract;
use Illuminate\Contracts\Events\Dispatcher;
use InvalidArgumentException;

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
    ) {
    }

    public function handle(Command $command): void
    {
        if (! $command instanceof ReserveInventory) {
            throw new InvalidArgumentException('ReserveInventoryHandler can only handle ReserveInventory commands');
        }

        // TODO: Implement inventory reservation logic
        // This should call your inventory service/API to reserve medications
        // Example:
        // $inventoryService = app(InventoryService::class);
        // $reservationResult = $inventoryService->reserve(
        //     medications: $command->medications,
        //     warehouseId: $command->warehouseId,
        // );

        // For now, we'll emit a success event
        // In production, wrap this in try-catch and emit InventoryReservationFailed on error
        $payload = [
            'order_uuid' => $command->orderUuid,
            'saga_uuid' => $command->sagaUuid,
            'medications' => $command->medications,
            'warehouse_id' => $command->warehouseId,
            'reserved_at' => now()->toIso8601String(),
            'status' => 'reserved',
        ];

        $event = new InventoryReserved(
            $command->orderUuid,
            $payload,
            array_merge($command->metadata, ['saga_uuid' => $command->sagaUuid])
        );

        $this->eventStore->store($event);
        $this->events->dispatch($event);
    }
}

