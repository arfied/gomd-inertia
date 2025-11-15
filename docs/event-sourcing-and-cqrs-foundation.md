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


## Bridging to the existing User model

The application layer provides a small service to bridge the existing `User` Eloquent model
into the event-sourced patient domain:

- **`App\Application\Patient\PatientEnrollmentService`**
  - Depends on the in-memory `CommandBus`.
  - Exposes a single method `enroll(User $user, array $metadata = []): void`.
  - Internally creates an `EnrollPatient` command with a freshly generated patient UUID
    and the given user's ID, then dispatches it through the `CommandBus`.

This allows controllers, jobs, or existing onboarding flows to start emitting
`PatientEnrolled` events for real users without changing the underlying `users`
table or existing models. Over time, additional projectors can consume these
patient events to build dedicated patient read models or dashboards.


## Automatic patient enrollment on user registration

To connect the CQRS-based patient enrollment flow to a real application flow,
newly registered users are automatically enrolled as patients.

- Laravel fires `Illuminate\Auth\Events\Registered` whenever a user is
  created via the standard registration flow.
- A listener, `App\Listeners\EnrollRegisteredUserAsPatient`, handles this
  event and delegates to `PatientEnrollmentService`.
- The listener calls `enroll($user, ['source' => 'registration'])`, which
  dispatches an `EnrollPatient` command and ultimately stores a
  `PatientEnrolled` event in the event store.

This integration relies on Laravel 11's **automatic event discovery**: placing
`EnrollRegisteredUserAsPatient` in the `app/Listeners` directory with a
`handle(Registered $event)` method is enough for it to be discovered and
executed, so no manual registration in `bootstrap/app.php` is required.


## Patient enrollment read model / projection

To support fast querying of which users are enrolled as patients, we introduce a
simple read model:

- **Migration**: `database/migrations/2025_11_15_010000_create_patient_enrollments_table.php`
  - Table: `patient_enrollments`
  - Columns:
    - `id` (BIGINT, auto-increment)
    - `patient_uuid` (UUID, unique)
    - `user_id` (unsigned BIGINT, foreign key to `users.id`)
    - `source` (string, nullable)
    - `enrolled_at` (timestamp)
    - `metadata` (JSON, nullable)
- **Model**: `App\Models\PatientEnrollment`
  - Casts `metadata` to array.
- **Event publication**:
  - `App\Application\Patient\Handlers\EnrollPatientHandler` now **both**:
    - stores a `PatientEnrolled` domain event via the `EventStore`, and
    - dispatches that `PatientEnrolled` instance via Laravel's event bus
      so in-process listeners and projectors can react immediately.
  - This allows in-process projectors to react to the domain event immediately,
    while the persisted event remains the system of record.
- **Projector (listener)**: `App\Listeners\ProjectPatientEnrollment`
  - Handle signature: `handle(PatientEnrolled $event): void`.
  - Delegates to an application-layer projector service,
    `App\Application\Patient\PatientEnrollmentProjector`.
  - The default implementation,
    `App\Application\Patient\EloquentPatientEnrollmentProjector`, uses
    `PatientEnrollment::updateOrCreate(...)` to upsert the read model by
    `patient_uuid`.

This pattern demonstrates the "event-store as source of truth + projections for
queries" model:

1. Commands append domain events to the `event_store` table.
2. Those domain events are dispatched through Laravel's event bus.
3. Lightweight projectors update denormalized read models (like
   `patient_enrollments`) suited for dashboards and API queries.



## Patient enrollment queries

Once the `patient_enrollments` read model is in place, the query side of CQRS can expose
simple query objects and handlers to fetch enrollment information for use in controllers,
API endpoints, or background jobs.

- **Query DTO**: `App\Application\Patient\Queries\GetPatientEnrollmentByUserId`
  - Implements the `App\Domain\Shared\Queries\Query` marker interface.
  - Carries a single scalar: `public int $userId`.
  - Represents the question: *"Is this user enrolled as a patient, and if so, what is
    their patient UUID and metadata?"*
- **Query Handler**: `App\Application\Patient\Queries\GetPatientEnrollmentByUserIdHandler`
  - Implements `App\Application\Queries\QueryHandler`.
  - Depends on a small read-side abstraction,
    `App\Application\Patient\PatientEnrollmentFinder`, to look up data in the
    `patient_enrollments` table.
  - Returns either a `App\Models\PatientEnrollment` instance or `null` if the user is
    not currently enrolled.
- **Finder abstraction**: `App\Application\Patient\PatientEnrollmentFinder`
  - Encapsulates the read-model access behind an interface so query handlers can be unit
    tested without touching the database.
  - Default implementation `App\Application\Patient\EloquentPatientEnrollmentFinder`
    uses the `PatientEnrollment` Eloquent model to query by `user_id`.
- **QueryBus wiring**:
  - In `App\Providers\AppServiceProvider::boot()`, the `QueryBus` is configured to
    route `GetPatientEnrollmentByUserId` to `GetPatientEnrollmentByUserIdHandler`.
  - Application code can resolve `QueryBus` from the container and call:

    - `$result = $queryBus->ask(new GetPatientEnrollmentByUserId($userId));`

      where `$result` is either a `PatientEnrollment` model or `null`.

This pattern keeps the **write model** (commands and events) and the **read model**
(queries and projections) cleanly separated, while still being lightweight and
framework-friendly for a cPanel deployment.



## End-to-end patient enrollment flow (test)

To verify the entire write + read pipeline for patient enrollment, there is a feature test
`tests/Feature/PatientEnrollmentFlowTest.php` that:

1. Creates a real `User` via the model factory.
2. Fires Laravel's `Illuminate\Auth\Events\Registered` event for that user.
3. Asserts that a `patient.enrolled` domain event has been stored in the `event_store` table.
4. Asserts that a `patient_enrollments` row has been projected for that user.
5. Uses the `QueryBus` with `GetPatientEnrollmentByUserId` to read the enrollment back.

The test uses `RefreshDatabase` so migrations run on the test connection (`sqlite` in-memory by
default via `phpunit.xml`). On environments where the `pdo_sqlite` extension is not available, the
test marks itself as skipped instead of failing; on a properly configured test environment, it runs
as a true end-to-end check of the registration → command → event store → projection → query flow.
