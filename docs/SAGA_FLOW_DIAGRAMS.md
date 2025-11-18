# Order Fulfillment Saga - Flow Diagrams & State Transitions

## Happy Path Flow

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                         HAPPY PATH (SUCCESS)                                │
└─────────────────────────────────────────────────────────────────────────────┘

1. USER CREATES ORDER
   │
   ├─ HTTP POST /orders
   ├─ CreateOrderHandler dispatches OrderCreated event
   └─ Event stored in event_store table

2. SAGA STARTS
   │
   ├─ OrderFulfillmentSagaHandler listens to OrderCreated
   ├─ Dispatches CreatePrescriptionJob to queue
   └─ State: PENDING_PRESCRIPTION

3. PRESCRIPTION CREATED
   │
   ├─ CreatePrescriptionJob processes
   ├─ Calls external pharmacy service
   ├─ Publishes PrescriptionCreated event
   └─ State: PENDING_INVENTORY_RESERVATION

4. INVENTORY RESERVED
   │
   ├─ ReserveInventoryJob processes
   ├─ Calls inventory management service
   ├─ Publishes InventoryReserved event
   └─ State: PENDING_SHIPMENT

5. SHIPMENT INITIATED
   │
   ├─ InitiateShipmentJob processes
   ├─ Calls shipping provider API
   ├─ Publishes ShipmentInitiated event
   └─ State: COMPLETED

6. SAGA COMPLETES
   │
   ├─ All events stored in event_store
   ├─ Read models updated
   ├─ Notifications sent
   └─ Analytics recorded
```

## Failure Path 1: Prescription Creation Fails

```
┌─────────────────────────────────────────────────────────────────────────────┐
│              FAILURE PATH 1: PRESCRIPTION CREATION FAILS                     │
└─────────────────────────────────────────────────────────────────────────────┘

1. ORDER CREATED
   └─ State: PENDING_PRESCRIPTION

2. PRESCRIPTION CREATION FAILS
   │
   ├─ CreatePrescriptionJob throws exception
   ├─ Publishes PrescriptionFailed event
   └─ Compensation triggered

3. COMPENSATION: CANCEL ORDER
   │
   ├─ CancelOrderJob dispatched
   ├─ Order status updated to CANCELLED
   ├─ Publishes OrderCancelled event
   └─ State: CANCELLED

4. SAGA COMPLETES (FAILED)
   │
   ├─ All events stored
   ├─ Customer notified of cancellation
   └─ Reason: Prescription creation failed
```

## Failure Path 2: Inventory Reservation Fails

```
┌─────────────────────────────────────────────────────────────────────────────┐
│           FAILURE PATH 2: INVENTORY RESERVATION FAILS                        │
└─────────────────────────────────────────────────────────────────────────────┘

1. ORDER CREATED → PRESCRIPTION CREATED
   └─ State: PENDING_INVENTORY_RESERVATION

2. INVENTORY RESERVATION FAILS
   │
   ├─ ReserveInventoryJob throws exception
   ├─ Publishes InventoryReservationFailed event
   └─ Compensation triggered

3. COMPENSATION CHAIN (LIFO - Last In First Out)
   │
   ├─ Step 1: CancelPrescriptionJob
   │  ├─ Prescription cancelled
   │  ├─ Publishes PrescriptionCancelled event
   │  └─ Dispatches next compensation
   │
   └─ Step 2: CancelOrderJob
      ├─ Order cancelled
      ├─ Publishes OrderCancelled event
      └─ State: CANCELLED

4. SAGA COMPLETES (FAILED)
   │
   ├─ All events stored
   ├─ Customer notified
   └─ Reason: Inventory not available
```

## Failure Path 3: Shipment Initiation Fails

```
┌─────────────────────────────────────────────────────────────────────────────┐
│          FAILURE PATH 3: SHIPMENT INITIATION FAILS                           │
└─────────────────────────────────────────────────────────────────────────────┘

1. ORDER → PRESCRIPTION → INVENTORY RESERVED
   └─ State: PENDING_SHIPMENT

2. SHIPMENT INITIATION FAILS
   │
   ├─ InitiateShipmentJob throws exception
   ├─ Publishes ShipmentInitiationFailed event
   └─ Compensation triggered

3. COMPENSATION CHAIN (LIFO)
   │
   ├─ Step 1: ReleaseInventoryJob
   │  ├─ Inventory reservation released
   │  ├─ Publishes InventoryReleased event
   │  └─ Dispatches next compensation
   │
   ├─ Step 2: CancelPrescriptionJob
   │  ├─ Prescription cancelled
   │  ├─ Publishes PrescriptionCancelled event
   │  └─ Dispatches next compensation
   │
   └─ Step 3: CancelOrderJob
      ├─ Order cancelled
      ├─ Publishes OrderCancelled event
      └─ State: CANCELLED

4. SAGA COMPLETES (FAILED)
   │
   ├─ All events stored
   ├─ Customer notified
   └─ Reason: Shipment provider unavailable
```

## State Transition Diagram

```
                    ┌─────────────────────────────────────┐
                    │   PENDING_PRESCRIPTION              │
                    │   (Waiting for prescription)        │
                    └──────────────┬──────────────────────┘
                                   │
                    ┌──────────────┴──────────────┐
                    │                             │
                    ▼                             ▼
        ┌──────────────────────┐    ┌──────────────────────┐
        │ PrescriptionCreated  │    │ PrescriptionFailed   │
        └──────────┬───────────┘    └──────────┬───────────┘
                   │                           │
                   │                    CancelOrderJob
                   │                           │
                   ▼                           ▼
        ┌──────────────────────────────────────────────┐
        │ PENDING_INVENTORY_RESERVATION                │
        │ (Waiting for inventory reservation)          │
        └──────────┬──────────────────────────────────┘
                   │
        ┌──────────┴──────────┐
        │                     │
        ▼                     ▼
┌──────────────────┐  ┌──────────────────────┐
│InventoryReserved│  │InventoryReservation  │
│                 │  │Failed                │
└────────┬────────┘  └──────────┬───────────┘
         │                      │
         │              CancelPrescriptionJob
         │                      │
         ▼                      ▼
┌──────────────────────────────────────────┐
│ PENDING_SHIPMENT                         │
│ (Waiting for shipment initiation)        │
└──────────┬───────────────────────────────┘
           │
    ┌──────┴──────┐
    │             │
    ▼             ▼
┌──────────────┐  ┌──────────────────────┐
│ShipmentInit. │  │ShipmentInitiation    │
│              │  │Failed                │
└────┬─────────┘  └──────────┬───────────┘
     │                       │
     │              ReleaseInventoryJob
     │                       │
     ▼                       ▼
┌──────────────────────────────────────────┐
│ COMPLETED                                │
│ (Order fulfilled successfully)           │
└──────────────────────────────────────────┘
```

## Event Sequence Diagram (Happy Path)

```
User    Controller    Handler    EventStore    Queue    Job1    Job2    Job3
 │          │            │           │          │        │       │       │
 ├─POST /orders──────────┤            │          │        │       │       │
 │          │            │            │          │        │       │       │
 │          ├─CreateOrder─────────────┤          │        │       │       │
 │          │            │            │          │        │       │       │
 │          │            ├─OrderCreated event────┤        │       │       │
 │          │            │            │          │        │       │       │
 │          │            │            ├─dispatch─┤        │       │       │
 │          │            │            │          │        │       │       │
 │          │            │            │          ├─CreatePrescriptionJob
 │          │            │            │          │        │       │       │
 │          │            │            │          │        ├─execute
 │          │            │            │          │        │       │       │
 │          │            │            │          │        ├─PrescriptionCreated
 │          │            │            │          │        │       │       │
 │          │            │            │          │        ├─dispatch
 │          │            │            │          │        │       │       │
 │          │            │            │          │        │       ├─ReserveInventoryJob
 │          │            │            │          │        │       │       │
 │          │            │            │          │        │       ├─execute
 │          │            │            │          │        │       │       │
 │          │            │            │          │        │       ├─InventoryReserved
 │          │            │            │          │        │       │       │
 │          │            │            │          │        │       ├─dispatch
 │          │            │            │          │        │       │       │
 │          │            │            │          │        │       │       ├─InitiateShipmentJob
 │          │            │            │          │        │       │       │
 │          │            │            │          │        │       │       ├─execute
 │          │            │            │          │        │       │       │
 │          │            │            │          │        │       │       ├─ShipmentInitiated
 │          │            │            │          │        │       │       │
 │          │            │            │          │        │       │       ├─SAGA COMPLETE
 │          │            │            │          │        │       │       │
```
