# Event Sourcing & CQRS Foundation

This document describes the core event sourcing and CQRS infrastructure
implemented for TeleMed Pro, and how it aligns with the
`TELEMED_PRO_SPECIFICATION.md`.

## Goals

- Provide an **append-only event store** for full auditability.
- Introduce a minimal **event-sourced AggregateRoot** base.
- Separate **write (commands)** from **read (queries)** via CQRS.
- Keep the implementation **lightweight and cPanel-friendly**
  (no external event-sourcing packages).

## Core Components

### Event Store

- **Migration**: `database/migrations/2025_11_15_000000_create_event_store_table.php`
  - Table: `event_store`
  - Columns:
    - `id` (BIGINT, auto-increment)
    - `aggregate_uuid` (UUID)
    - `aggregate_type` (string)
    - `event_type` (string)
    - `event_data` (JSON)
    - `metadata` (JSON, nullable)
    - `occurred_at` (timestamp, microsecond precision)
- **Model**: `App\Models\StoredEvent`
  - Maps to the `event_store` table.
  - Casts `event_data` and `metadata` to arrays.
- **Base Event**: `App\Domain\Events\DomainEvent`
  - Encapsulates:
    - `aggregateUuid` (string)
    - `payload` (array)
    - `metadata` (array)
    - `occurredAt` (`DateTimeImmutable`)
  - Provides `toStoredEventAttributes()` to convert to
    `StoredEvent` attributes.
  - Can persist itself via `store()`.
- **Service**: `App\Services\EventStore`
  - Persists `DomainEvent` instances to the `event_store` table.

### Aggregate Root

- **Class**: `App\Domain\Shared\AggregateRoot`
- Responsibilities:
  - Track a list of **recorded domain events**.
  - Apply events to mutate in-memory state.
  - Reconstitute from a history of events.

Key API:

- `recordThat(DomainEvent $event): void`
  - Adds an event to the internal list and calls `apply($event)`.
- `reconstituteFromHistory(iterable $events): static`
  - Creates a new instance and applies all given events.
- `releaseEvents(): array`
  - Returns recorded events and clears the internal buffer.
- `apply(DomainEvent $event): void`
  - Abstract method implemented by concrete aggregates to
    update their state.

### Commands & Command Bus (Write Model)

- **Marker Interface**: `App\Domain\Shared\Commands\Command`
- **Handler Interface**: `App\Application\Commands\CommandHandler`
  - `handle(Command $command): void`.
- **Bus**: `App\Application\Commands\CommandBus`
  - In-memory registry of command handlers.
  - `register(string $commandClass, CommandHandler $handler): void`
  - `dispatch(Command $command): void`
  - Throws `InvalidArgumentException` if no handler is found for a
    command type.

This fulfills the spec's requirement for a dedicated **command side**
(write model) that validates business rules and emits domain events.

### Queries & Query Bus (Read Model)

- **Marker Interface**: `App\Domain\Shared\Queries\Query`
- **Handler Interface**: `App\Application\Queries\QueryHandler`
  - `handle(Query $query): mixed`.
- **Bus**: `App\Application\Queries\QueryBus`
  - In-memory registry of query handlers.
  - `register(string $queryClass, QueryHandler $handler): void`
  - `ask(Query $query): mixed`

This provides a dedicated **query side** (read model) that can be backed
by optimized Eloquent queries or custom read models / projections.

### Bounded Context Aggregates (Skeletons)

To align with the TeleMed Pro bounded contexts, we introduced minimal
aggregate classes, each extending `AggregateRoot` and currently acting as
placeholders:

- `App\Domain\Patient\PatientAggregate`
- `App\Domain\Order\OrderAggregate`
- `App\Domain\Commission\CommissionAggregate`
- `App\Domain\Referral\ReferralAggregate`
- `App\Domain\Payment\PaymentAggregate`

Each aggregate:

- Declares a `uuid` property.
- Implements `apply(DomainEvent $event): void` with an empty body for
  now. Domain-specific events (e.g. `PatientEnrolled`, `OrderCreated`)
  will be handled here in future iterations.

## Service Container Wiring

- **Provider**: `App\Providers\AppServiceProvider`

Registers the core services as singletons:

- `EventStore`
- `CommandBus`
- `QueryBus`

This allows controllers, jobs, or domain services to type-hint these
classes and have them resolved automatically by Laravel's IoC container.

## Design Decisions

- **Custom, minimal infrastructure** rather than a full event-sourcing
  package to keep deployment simple on cPanel.
- **Single event store table** shared across all bounded contexts
  (distinguished by `aggregate_type` and `event_type`).
- **UUID-based aggregates** to avoid coupling to auto-increment IDs and
  support multi-tenant or distributed scenarios.
- **In-memory buses** (CommandBus/QueryBus) to keep the mental model
  simple while still enforcing a clean separation between
  commands, queries, and their handlers.

## Usage Examples (Future)

Examples of typical usage that will be introduced in future work:

- **Defining a domain event** by extending `DomainEvent`.
- **Creating a command DTO** (e.g. `EnrollPatient`) implementing
  `Command`.
- **Implementing a command handler** that loads an aggregate, calls
  behavior methods, and persists new events via `EventStore`.
- **Defining a query** (e.g. `GetPatientDetail`) and a corresponding
  query handler backed by Eloquent read models.

These patterns will be wired into concrete features (patient
enrollment, order creation, commission calculation, etc.) in subsequent
iterations.


## Example: Patient Enrollment CQRS Flow

The first concrete feature built on top of this foundation is a minimal
patient enrollment flow that demonstrates the full path from command to
stored event.

### New Components

- **Domain Event**: `App\\Domain\\Patient\\Events\\PatientEnrolled`
  - `aggregate_type`: `patient`
  - `event_type`: `patient.enrolled`
  - Payload: `['user_id' => int]` plus optional metadata.
- **Command DTO**: `App\\Application\\Patient\\Commands\\EnrollPatient`
  - Carries `patientUuid`, `userId`, and optional `metadata`.
  - Intentionally contains only primitive data and no behavior.
- **Command Handler**: `App\\Application\\Patient\\Handlers\\EnrollPatientHandler`
  - Validates that it received an `EnrollPatient` command.
  - Instantiates a `PatientEnrolled` event from the command data.
  - Persists the event via `App\\Services\\EventStore`.
- **Wiring**: `App\\Providers\\AppServiceProvider`
  - Registers `EventStore`, `CommandBus`, and `QueryBus` as singletons.
  - In `boot()`, hooks into the container's `resolving` callback for
    `CommandBus` to register the mapping:
    - `EnrollPatient` → `EnrollPatientHandler`.

### Usage Pattern (High Level)

1. Application code (e.g. a controller, job, or saga) constructs an
   `EnrollPatient` command with the relevant data.
2. It resolves `CommandBus` from the container and calls
   `dispatch($command)`.
3. The `CommandBus` routes the command to `EnrollPatientHandler`.
4. The handler creates and stores a `PatientEnrolled` event via the
   `EventStore`.
5. Downstream components (listeners, projections, analytics) can
   subscribe to `patient.enrolled` events and build read models or
   trigger side effects, as described in `TELEMED_PRO_SPECIFICATION.md`.

This flow is intentionally minimal and does not yet mutate existing
Eloquent models. It provides a clear, testable template for implementing
other commands and events (e.g. order creation, payment processing,
commission calculation) in later iterations.
