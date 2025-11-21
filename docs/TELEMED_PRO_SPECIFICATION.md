# TeleMed Pro – Event-Driven Telemedicine Platform

## Vision Statement
Design and build TeleMed Pro, a revolutionary event-driven telemedicine platform that transforms healthcare delivery through intelligent automation, real-time collaboration, and predictive insights. Unlike traditional patient management systems, TeleMed Pro acts as a living healthcare ecosystem where every action triggers intelligent workflows, commission calculations cascade automatically through hierarchies, and care coordination happens seamlessly across providers, agents, and patients. Built on event sourcing and CQRS principles optimized for cPanel hosting, the platform maintains a complete audit trail while enabling real-time analytics, predictive health interventions, and autonomous business processes.

---

## Unique Features of TeleMed Pro

### 1. **Event-Driven Health Timeline** 📊
- Complete patient journey visualization from first contact to ongoing care
- Every interaction recorded as immutable events (enrollment, orders, payments, questionnaires)
- Time-travel debugging: Replay patient state at any point in history
- Predictive analytics: Forecast medication refills, appointment needs, health risks

### 2. **Intelligent Commission Cascade** 💰
- Real-time commission calculation triggered by order events
- Automatic propagation through referral hierarchy (SFMO → MGA → Agent → LOA)
- Event-sourced commission ledger with complete audit trail
- Predictive earnings forecasts based on patient lifecycle patterns
- Automated commission disputes resolution with event replay

### 3. **Autonomous Workflow Engine** 🤖
- Self-healing workflows that adapt to failures and retries
- Medication refill automation with predictive ordering
- Subscription renewal with intelligent retry strategies
- Automated patient outreach based on health events
- Smart escalation when human intervention needed

### 4. **Real-Time Collaboration Hub** 🤝
- Live presence indicators showing who's viewing/editing patient records
- Collaborative care notes with real-time co-editing
- Instant notifications across all stakeholders (providers, agents, patients)
- Video consultation integration with automatic note generation
- Team chat embedded in patient context

### 5. **Predictive Health Intelligence** 🧠
- Medication adherence prediction with proactive interventions
- Churn risk detection for subscription patients
- Health deterioration early warning system
- Optimal medication timing recommendations
- Personalized wellness roadmaps based on patient patterns

### 6. **Unified Omnichannel Experience** 📱
- Seamless experience across web, mobile, SMS, email, voice
- Conversation history preserved across all channels
- Smart channel routing based on urgency and patient preference
- Automated appointment reminders via preferred channel
- Two-way SMS for medication confirmations and refills

### 7. **Dynamic Referral Network** 🌐
- Visual network graph of entire referral hierarchy
- Real-time performance metrics for every node in network
- Automated onboarding workflows for new agents/LOAs
- Gamification with leaderboards and achievement badges
- Smart matching: Recommend best agents for specific patient types

### 8. **Blockchain-Verified Credentials** 🔐
- Immutable credential verification for providers and agents
- License expiration tracking with automatic alerts
- Compliance audit trail for regulatory requirements
- Patient consent management with cryptographic signatures
- HIPAA-compliant data access logging

### 9. **AI-Powered Clinical Assistant** 🩺
- Natural language medication search ("blood pressure meds for diabetics")
- Drug interaction checking with severity scoring
- Contraindication warnings based on patient history
- Dosage recommendations based on patient demographics
- Clinical decision support with evidence-based guidelines

### 10. **Financial Health Dashboard** 💳
- Real-time revenue analytics with drill-down capabilities
- Subscription health metrics (MRR, churn, LTV)
- Payment failure prediction and prevention
- Automated dunning management for failed payments
- Revenue forecasting with confidence intervals

### 11. **Adaptive Form Intelligence** 📝
- Medical questionnaires that adapt based on previous answers
- Auto-save with conflict resolution for concurrent edits
- Voice-to-text for hands-free form completion
- OCR for automatic data extraction from uploaded documents
- Smart defaults based on patient history and population data

### 12. **Compliance Automation Engine** ⚖️
- Automatic HIPAA compliance checking on all operations
- State-specific regulation enforcement (prescribing rules, licensing)
- Automated reporting for regulatory bodies
- Consent management with version tracking
- Data retention policies with automatic archival

---

## Event-Driven Architecture (cPanel Optimized)

### Core Event Patterns

#### 1. **Event Sourcing (Database-Based)**
All state changes stored as immutable events in MySQL event store:

**Domain Events:**
- `PatientEnrolled` - New patient registered in system
- `MedicationOrdered` - New prescription order created
- `OrderFulfilled` - Medication shipped to patient
- `PaymentProcessed` - Payment successfully charged
- `PaymentFailed` - Payment attempt failed
- `SubscriptionRenewed` - Recurring subscription processed
- `CommissionEarned` - Commission credited to agent/LOA
- `QuestionnaireCompleted` - Patient submitted health questionnaire
- `DocumentUploaded` - New document added to patient record
- `AgentReferralCreated` - New agent joined under referrer
- `ConsentGranted` - Patient granted data access consent
- `LicenseExpiring` - Provider/agent license expiring soon

**Event Store Schema:**
```sql
CREATE TABLE event_store (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    aggregate_uuid VARCHAR(36) NOT NULL,
    aggregate_type VARCHAR(100) NOT NULL,
    event_type VARCHAR(100) NOT NULL,
    event_data JSON NOT NULL,
    metadata JSON,
    occurred_at TIMESTAMP(6) NOT NULL,
    INDEX idx_aggregate (aggregate_uuid, aggregate_type),
    INDEX idx_event_type (event_type),
    INDEX idx_occurred_at (occurred_at)
) ENGINE=InnoDB;
```

**Event Store Benefits:**
- Complete audit trail for compliance
- Time-travel debugging and analytics
- Event replay for testing and recovery
- Temporal queries (patient state at any point in time)

#### 2. **CQRS (Command Query Responsibility Segregation)**

**Command Side (Write Model):**
- Handles all state mutations via commands
- Validates business rules before emitting events
- Optimized for write consistency

**Query Side (Read Model):**
- Materialized views optimized for specific queries
- Eventually consistent with command side
- Multiple read models for different use cases:
  - `patient_list_view` - Optimized for patient search/filtering
  - `commission_dashboard_view` - Real-time commission analytics
  - `order_history_view` - Patient order timeline
  - `referral_network_view` - Hierarchical referral tree

#### 3. **Saga Pattern (Process Managers)**

**Order Fulfillment Saga:**
```
PatientOrdersRx → ValidateInventory → ProcessPayment →
NotifyPharmacy → ShipMedication → UpdatePatientRecord →
CalculateCommissions → NotifyStakeholders
```

**Subscription Renewal Saga:**
```
SubscriptionDueDate → CheckPaymentMethod → AttemptCharge →
[Success: RenewSubscription → NotifyPatient] OR
[Failure: RetryStrategy → UpdateDunning → EscalateIfNeeded]
```

**Agent Onboarding Saga:**
```
AgentRegistered → VerifyCredentials → CheckLicense →
CreateReferralLinks → SendWelcomeKit → AssignMentor →
ScheduleTraining → ActivateAccount
```

#### 4. **Event Bus Architecture (cPanel Compatible)**

**Message Broker:** Laravel Queue with Database Driver + Cron Jobs

**Event Handlers (Queue Workers):**
- `CommissionCalculator` - Listens to `OrderFulfilled`, calculates commissions
- `NotificationDispatcher` - Listens to all events, sends notifications
- `AnalyticsAggregator` - Updates real-time dashboards
- `AuditLogger` - Records all events for compliance
- `SearchIndexer` - Updates search indices
- `ReverbBroadcaster` - Pushes events to connected clients via Reverb WebSockets (with SSE/long-polling fallback)

**cPanel Queue Processing:**
```bash
# Add to cron jobs (every minute)
* * * * * cd /home/username/public_html && php artisan schedule:run >> /dev/null 2>&1

# Laravel Scheduler handles queue workers
# config/schedule.php
$schedule->command('queue:work --stop-when-empty')->everyMinute();
```

**Event Replay & Projections:**
- Rebuild read models from event history
- Create new projections without data migration
- A/B test different business logic on historical data

---

## Technical Architecture (cPanel Optimized)

### Backend Stack

#### **Core Framework**
- **Laravel 11+** (optimized for cPanel servers with WHM/root access)
- **PHP 8.1+** (most cPanel servers support this)
- **Event Sourcing:** Custom lightweight implementation or Spatie Event Sourcing
- **CQRS:** Custom implementation with separate read/write repositories
- **Message Queue:** Laravel Queue with database driver
- **Real-Time Updates:** Laravel Reverb (WebSockets) with SSE/long-polling fallback (cPanel/WHM-friendly)

#### **Database Architecture**
- **Event Store:** MySQL 8.0+ with JSON columns
- **Read Models:** MySQL with optimized indexes
- **Cache Layer:** File-based cache or Redis (if available via cPanel)
- **Search Engine:** MySQL Full-Text Search or TNTSearch (PHP-based)
- **Session Storage:** Database or file-based

#### **API & Integration**
- **Inertia.js 2+** for seamless SPA experience with modern features
- **REST API** for third-party integrations
- **Webhooks** for external system notifications
- **OAuth 2.0** (Laravel Passport) for third-party app authorization

#### **Background Processing (cPanel Compatible)**
- **Laravel Scheduler** triggered by cron jobs
- **Database Queue Driver** for job processing
- **Supervisor Alternative:** Cron-based queue workers
- **Long-Running Tasks:** Chunked processing with job batching

**Queue Configuration:**
```php
// config/queue.php
'default' => env('QUEUE_CONNECTION', 'database'),

'connections' => [
    'database' => [
        'driver' => 'database',
        'table' => 'jobs',
        'queue' => 'default',
        'retry_after' => 90,
    ],
],
```

**Cron Setup:**
```bash
# cPanel Cron Jobs (every minute)
* * * * * cd /home/username/public_html && /usr/local/bin/php artisan schedule:run >> /dev/null 2>&1

# Laravel Scheduler (app/Console/Kernel.php)
protected function schedule(Schedule $schedule)
{
    // Process queued jobs
    $schedule->command('queue:work --stop-when-empty --tries=3')
             ->everyMinute()
             ->withoutOverlapping();

    // Process events and projections
    $schedule->command('events:project')->everyFiveMinutes();

    // Clean up old events
    $schedule->command('events:cleanup')->daily();

    // Generate analytics
    $schedule->command('analytics:aggregate')->hourly();
}
```

### Frontend Stack

#### **Core Framework**
- **Vue 3** with Composition API and `<script setup>`
- **TypeScript 5+** for type safety across entire frontend
- **Inertia.js 2+** for server-driven SPA with enhanced features
- **Pinia** for state management with persistence
- **VueUse** for composable utilities

#### **UI Component Library**
- **Headless/Tailwind-based UI components** for enterprise-grade interfaces
  - Rich set of components (data table, tree, chart, calendar, etc.)
  - Theming and customization via Tailwind + design tokens
  - Accessibility (WCAG) compliant patterns
  - Form components with validation
  - Advanced data components (tables, timelines)
  - Overlay components (dialogs, sidebars, toasts)
  - File upload with progress tracking
  - Charts integration

#### **Additional UI Libraries**
- **Icon library** (e.g. Lucide) for consistent UI icons
- **Tailwind CSS 4+** for utility-first styling
- **VueFlow** for visual workflow and network diagrams
- **Auto-animate** for smooth transitions

#### **Form & Validation**
- **VeeValidate 4** for form validation
- **Zod** for schema validation (shared with backend)
- **Inertia Form Helpers** for optimistic UI updates
- **UI form components** - Text inputs, dropdowns, calendars, file uploads, etc.

#### **Real-Time Features (Reverb + Fallbacks, cPanel Compatible)**
- **Primary:** Laravel Reverb (WebSockets) for real-time updates
- **Fallback 1:** Server-Sent Events (SSE) when WebSockets are not available or blocked
- **Fallback 2:** Long Polling as a last resort (e.g. legacy browsers/proxies)
- **Optimistic UI Updates** with Inertia 2+
- **Local State Sync** with periodic polling

**SSE Fallback Implementation:**
```typescript
// composables/useEventStream.ts
export function useEventStream(channel: string) {
  const events = ref<Event[]>([])
  let eventSource: EventSource | null = null

  const connect = () => {
    eventSource = new EventSource(`/api/events/stream/${channel}`)

    eventSource.onmessage = (event) => {
      const data = JSON.parse(event.data)
      events.value.unshift(data)

      // Trigger Inertia reload for specific events
      if (data.type === 'patient.updated') {
        router.reload({ only: ['patient'] })
      }
    }

    eventSource.onerror = () => {
      eventSource?.close()
      // Reconnect after 5 seconds
      setTimeout(connect, 5000)
    }
  }

  onMounted(connect)
  onUnmounted(() => eventSource?.close())

  return { events }
}
```

**Laravel SSE Fallback Controller:**
```php
// app/Http/Controllers/EventStreamController.php
public function stream(Request $request, string $channel)
{
    return response()->stream(function () use ($channel) {
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no'); // Disable nginx buffering

        $lastEventId = request()->header('Last-Event-ID', 0);

        while (true) {
            // Fetch new events since last ID
            $events = Event::where('channel', $channel)
                ->where('id', '>', $lastEventId)
                ->orderBy('id')
                ->limit(10)
                ->get();

            foreach ($events as $event) {
                echo "id: {$event->id}\n";
                echo "data: " . json_encode($event->data) . "\n\n";
                $lastEventId = $event->id;
                ob_flush();
                flush();
            }

            // Check every 2 seconds
            sleep(2);

            // Check if client disconnected
            if (connection_aborted()) {
                break;
            }
        }
    }, 200, [
        'Cache-Control' => 'no-cache',
        'Content-Type' => 'text/event-stream',
    ]);
}
```

#### **Developer Experience**
- **Vite 5+** for lightning-fast HMR
- **Vitest** for unit testing
- **Playwright** for E2E testing
- **Storybook** for component documentation
- **ESLint + Prettier** for code quality

### **Build & Deployment (cPanel)**

**Vite Configuration for cPanel:**
```javascript
// vite.config.js
import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.ts'],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    build: {
        manifest: true,
        outDir: 'public/build',
        rollupOptions: {
            output: {
                manualChunks: {
                    vendor: ['vue', '@inertiajs/vue3', 'pinia'],
                    ui: ['@/components/ui'],
                }
            }
        }
    }
})
```

**Deployment Script:**
```bash
#!/bin/bash
# deploy.sh - Run locally, then upload to cPanel

# Install dependencies
npm install
composer install --optimize-autoloader --no-dev

# Build assets
npm run build

# Optimize Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Upload to cPanel via FTP/SFTP
# Or use Git deployment if cPanel supports it
```

---

## Application Structure

### Domain-Driven Design (DDD)

#### **Bounded Contexts**

**1. Patient Management Context**
- **Aggregates:** `Patient`, `MedicalHistory`, `Document`
- **Commands:** `EnrollPatient`, `UpdateDemographics`, `UploadDocument`
- **Events:** `PatientEnrolled`, `DemographicsUpdated`, `DocumentUploaded`
- **Read Models:** `PatientListView`, `PatientDetailView`

**2. Order Management Context**
- **Aggregates:** `Order`, `Prescription`, `Shipment`
- **Commands:** `CreateOrder`, `FulfillOrder`, `CancelOrder`
- **Events:** `OrderCreated`, `OrderFulfilled`, `OrderCancelled`
- **Sagas:** `OrderFulfillmentSaga`

**3. Medication Catalog Context**
- **Aggregates:** `Medication`, `Condition`, `Formulary`
- **Commands:** `AddMedication`, `UpdatePricing`, `LinkCondition`
- **Events:** `MedicationAdded`, `PricingUpdated`, `ConditionLinked`
- **Read Models:** `MedicationSearchView`, `FormularyView`

**4. Commission Context**
- **Aggregates:** `Commission`, `ReferralHierarchy`, `Payout`
- **Commands:** `CalculateCommission`, `ProcessPayout`
- **Events:** `CommissionEarned`, `PayoutProcessed`
- **Read Models:** `CommissionDashboardView`, `PayoutHistoryView`

**5. Referral Network Context**
- **Aggregates:** `Agent`, `LOA`, `ReferralLink`
- **Commands:** `RegisterAgent`, `CreateReferralLink`, `TrackReferral`
- **Events:** `AgentRegistered`, `ReferralLinkCreated`, `ReferralTracked`
- **Read Models:** `ReferralNetworkView`, `AgentPerformanceView`

**6. Payment Context**
- **Aggregates:** `PaymentMethod`, `Transaction`, `Subscription`
- **Commands:** `AddPaymentMethod`, `ProcessPayment`, `RenewSubscription`
- **Events:** `PaymentMethodAdded`, `PaymentProcessed`, `SubscriptionRenewed`
- **Sagas:** `SubscriptionRenewalSaga`, `DunningManagementSaga`
- **Subscription Modeling (Patients, Employees, Businesses):**
  - **Conceptual fields:**
    - `Subscription.subject_type`: `'patient' | 'employee' | 'business'`
    - `Subscription.subject_id`: links to `patients`, `business_employees`, or `business_accounts` based on `subject_type`
    - `Subscription.payer_type`: `'patient' | 'employee' | 'business_admin'`
    - `Subscription.payer_id`: `users.id` of the financial decision-maker
  - **Current DB mapping (implementation detail):**
    - `subscriptions.user_id` → the covered user's `users.id`
    - `subscriptions.user_type` → e.g. `'business_employee'` for employer-covered employees
    - `subscriptions.meta_data->employee_id` → `business_employees.id` when `subject_type = 'employee'`
    - Business-sponsored coverage and seat usage are modeled via `business_plans` and `business_employees` records
  - **Payer semantics:**
    - Direct patients: patient is both subject and payer (`subject_type = 'patient'`, `payer_type = 'patient'`, `payer_id = user_id`)
    - Business-paid employees: subject is the employee, but the payer is the business admin (`payer_type = 'business_admin'`), tied to the active business plan
    - Self-paid employees: subject remains the employee, but payer becomes the employee (`payer_type = 'employee'`, `payer_id = employee.user_id`), recorded today via a self-payment record and a conversion of the subscription from business-paid to individual coverage
  - **Role alignment:**
    - **Business Admin:** owns the `BusinessAccount` and its payment methods; pays for business plans and employees by default
    - **Business HR:** enrolls employees but always uses the business admin as payer; cannot change core billing settings
    - **Employee:** always covered under a family plan; may “take over” payment from the business when eligibility rules pass (see *Autonomous Subscription Management → Employee Self‑Pay Flow*)
  - **Migration Strategy (Current Schema → Event-Driven Model):**
    - **Phase 1 – Mirror the current behavior:** keep using `subscriptions.user_id`, `user_type` and `meta_data` as the source of truth while introducing the **conceptual** API (`subject_type`, `subject_id`, `payer_type`, `payer_id`) in code (DTOs, aggregates, read models).
    - **Phase 2 – Add non-breaking columns:** add nullable `subject_type`, `subject_id`, `payer_type`, `payer_id` columns to `subscriptions` and start populating them **only for new subscriptions** using the mapping described above.
    - **Phase 3 – Backfill existing data:** run an idempotent console command/queued job that reads existing subscriptions and derives subject/payer into the new columns (patients from `user_id`, employees from `meta_data->employee_id` + business plans, payer from business admin vs self-pay records).
    - **Phase 4 – Switch reads, keep writes dual:** application reads should prefer the new fields, with a fallback to the legacy mapping; writes should continue to populate **both** for one or more release cycles.
    - **Phase 5 – Optional cleanup:** once all active subscriptions are guaranteed to have subject/payer set and monitoring shows no fallbacks, you can mark `user_type` and most of the `meta_data` mapping as deprecated or remove them in a later schema revision.
  - **Subject/Payer quick reference:**

    | Scenario                   | `subject_type` | `subject_id` source                            | `payer_type`        | `payer_id` source                                        |
    | -------------------------- | -------------- | ----------------------------------------------- | ------------------- | -------------------------------------------------------- |
    | Direct patient             | `"patient"`   | Patient profile linked to `subscriptions.user_id` | `"patient"`        | `subscriptions.user_id`                                  |
    | Business-paid employee     | `"employee"`  | `business_employees.id` (via `meta_data->employee_id`) | `"business_admin"` | Linked business admin's `users.id` via the `BusinessAccount` |
    | Self-paid employee         | `"employee"`  | `business_employees.id`                          | `"employee"`       | Employee's own `users.id` (e.g. `BusinessPlanSelfPayment.user_id`) |



**7. Clinical Context**


- **Aggregates:** `Questionnaire`, `ClinicalNote`, `Consultation`
- **Commands:** `CreateQuestionnaire`, `SubmitResponse`, `ScheduleConsultation`
- **Events:** `QuestionnaireCreated`, `ResponseSubmitted`, `ConsultationScheduled`

**8. Compliance Context**
- **Aggregates:** `Consent`, `AuditLog`, `License`
- **Commands:** `GrantConsent`, `VerifyLicense`, `LogAccess`
- **Events:** `ConsentGranted`, `LicenseVerified`, `AccessLogged`

### User Roles & Domain Mapping

TeleMed Pro uses a single `users` table with `spatie/laravel-permission` for identity and coarse-grained access control. Each role maps to one or more domain profiles/aggregates inside the bounded contexts above.

- **Admin (`admin`)**
  - Scope: all contexts.
  - Domain: usually no dedicated aggregate; uses `User` identity to perform system-level management and configuration.

- **Staff (`staff`)**
  - Contexts: support/backoffice across Patient, Payment, Referral, etc.
  - Domain: optional `StaffProfile`; can assist other roles (except admins) according to authorization policies, but does not own core financial or clinical entities.

- **Doctor (`doctor`)**
  - Context: Clinical.
  - Domain: represented by a `Provider` aggregate (type `doctor`) linked via `user_id`; can conduct consultations, sign prescriptions, and author clinical notes.

- **Pharmacist (`pharmacist`)**
  - Contexts: Medication Catalog, Order Management.
  - Domain: represented as a `Provider` of type `pharmacist` or a dedicated `Pharmacist` aggregate; manages dispensing, verification, and medication inventory.

- **Patient (`patient`)**
  - Context: Patient Management (and referenced by Payment, Clinical).
  - Domain: represented by `Patient` aggregate linked to `user_id`; receives services, completes questionnaires, and may own direct subscriptions.

- **Business Admin (`business_admin`)**
  - Context: Business/Employer, Payment, Referral.
  - Domain: owner of a `BusinessAccount` aggregate; manages employer plans, business-level payment methods, and is the default payer for employees covered under the business.

- **Business HR (`business_hr`)**
  - Context: Business/Employer.
  - Domain: represented by `BusinessHrProfile` linked to a `BusinessAccount`; can enroll employees and manage their coverage but always uses the business admin as payer and cannot change core billing settings.

- **Employee (`employee`)**
  - Contexts: Business/Employer and Patient Management.
  - Domain: has a `Patient` profile and an `EmployeeEnrollment` linking to a `BusinessAccount`; always on a family plan, with the option (when configured) to shift from employer-paid to self-paid subscriptions.

- **Agent (`agent`)**
  - Contexts: Referral Network, Commission.
  - Domain: represented by `Agent` aggregate (with tier info such as AGENT/MGA/SFMO) linked to `user_id`; can have downline agents/LOAs and receives commissions for enrolled patients and businesses according to the hierarchical rules.

- **LOA (`loa`)**
  - Context: Referral Network.
  - Domain: represented by `Loa` aggregate attached to a parent `Agent`; can enroll patients and businesses but never receives commission directlycredit flows to the creating agent and up the hierarchy.

Across all contexts, domain aggregates reference `user_id` from the identity layer for linkage, while roles control access to commands and UI, and the aggregates themselves enforce the detailed business rules.


---

### Pagination Strategy

To keep backend performance predictable (especially on shared cPanel/MySQL), all API endpoints that support pagination MUST:

- Use Laravel's `simplePaginate()` instead of `paginate()`. The main list query MUST NOT calculate total row counts.
- Expose a separate `GET /.../count` (or equivalent) endpoint that returns `{ "count": <int> }` for UIs that need totals (e.g. a dedicated "Count" button).
- Avoid relying on `total`, `last_page`, etc. from pagination metadata; the UI should only rely on cursors/next/previous links from `simplePaginate()`.

Existing and future list endpoints (patient lists, document lists, dashboards, etc.) must follow this pattern.
## Feature Implementation Details

### 1. Event-Driven Patient Dashboard

**Real-Time Patient Card:**
```typescript
// Automatically updates via Reverb WebSockets (SSE fallback) when any patient event occurs
interface PatientCard {
  id: string
  name: string
  status: 'active' | 'inactive' | 'suspended'
  lastActivity: Date
  activeOrders: number
  upcomingRefills: Date[]
  healthScore: number // Calculated from events
  riskFlags: string[] // Predicted from patterns
  assignedProvider: Provider
  liveIndicators: {
    viewingNow: User[] // Via Reverb WebSockets, SSE/polling fallback
    recentActivity: Event[] // Last 10 events
  }
}
```

**UI components used (example):**
- `DataTable` - Patient list with sorting, filtering, simple pagination (backed by Laravel `simplePaginate()` + separate `/.../count` endpoint)
- `Timeline` - Event stream visualization
- `Card` - Patient information cards
- `Tag` - Status indicators and risk flags
- `Avatar` - User presence indicators
- `Chip` - Quick stats and metrics

**Event Stream Visualization:**
- Timeline view of all patient events
- Filter by event type, date range, actor
- Drill-down into event payload and metadata
- Export event stream for analysis

### 2. Intelligent Medication Ordering

**Context-Aware Search:**
```typescript
interface MedicationSearch {
  query: string // Natural language: "diabetes medication for elderly"
  filters: {
    conditions: string[]
    contraindications: string[] // From patient history
    ageRange: [number, number]
    interactions: string[] // Check against current meds
  }
  results: {
    medication: Medication
    relevanceScore: number
    warnings: Warning[]
    alternatives: Medication[]
    costComparison: PricePoint[]
  }[]
}
```

**UI components used (example):**
- `AutoComplete` - Natural language medication search
- `DataTable` - Medication results with advanced filtering
- `Stepper` - Multi-step order wizard
- `Dropdown` - Condition selection, dosage selection
- `InputNumber` - Quantity, refills
- `Message` - Warnings and alerts
- `ConfirmDialog` - Order confirmation
- `Badge` - Interaction severity indicators

**Smart Order Workflow:**
1. **Condition Selection** - AI suggests based on patient history
2. **Medication Recommendation** - Ranked by efficacy, cost, interactions
3. **Dosage Intelligence** - Auto-calculate based on weight, age, condition
4. **Interaction Check** - Real-time warnings with severity levels
5. **Payment Optimization** - Suggest best payment method based on history
6. **Fulfillment Prediction** - Estimated delivery date with confidence

**Event Flow:**
```
OrderInitiated → ConditionSelected → MedicationChosen →
DosageCalculated → InteractionsChecked → PaymentMethodSelected →
OrderValidated → OrderSubmitted → [OrderFulfillmentSaga begins]
```

### 3. Commission Intelligence Dashboard

**Real-Time Commission Widgets:**

**Earnings Overview:**
- Today's earnings with live updates (via Reverb WebSockets, SSE fallback)
- Week/Month/Year comparisons with trends
- Projected earnings based on pipeline
- Commission breakdown by product type

**UI components used (example):**
- `Chart` - Earnings trends, forecasts, breakdowns
- `Knob` - Circular progress indicators for goals
- `ProgressBar` - Monthly targets
- `DataTable` - Commission transactions with export
- `OrganizationChart` - Referral hierarchy visualization
- `Tree` - Hierarchical downline view
- `Panel` - Collapsible sections for different metrics
- `Skeleton` - Loading states for async data

**Referral Network Visualization:**
```typescript
interface NetworkNode {
  id: string
  type: 'sfmo' | 'fmo' | 'svg' | 'mga' | 'agent' | 'associate' | 'loa'
  name: string
  metrics: {
    totalEarnings: number
    activePatients: number
    ordersThisMonth: number
    conversionRate: number
  }
  children: NetworkNode[]
  status: 'active' | 'inactive' | 'pending'
  liveActivity: Event[] // Real-time event feed via Reverb WebSockets (SSE/polling fallback)
}
```

**Commission Event Sourcing:**
```typescript
// Every commission calculation is an event
interface CommissionEarned {
  commissionId: string
  orderId: string
  patientId: string
  recipientId: string
  recipientType: 'sfmo' | 'mga' | 'agent' | 'loa'
  amount: number
  rate: number
  calculatedAt: Date
  paidAt: Date | null
  metadata: {
    orderTotal: number
    productType: string
    referralChain: string[] // Full hierarchy
  }
}
```

**Predictive Analytics:**
- Forecast next month's earnings based on patient lifecycle
- Churn prediction for subscription patients
- Optimal patient acquisition cost calculation
- Lifetime value projections

### 4. Collaborative Care Coordination (Polling-Based)

**Presence State (via periodic polling):**
```typescript
interface PresenceState {
  patientId: string
  activeUsers: {
    userId: string
    name: string
    role: string
    viewing: 'demographics' | 'orders' | 'history' | 'documents'
    editing: string | null // Field being edited
    lastSeen: Date
  }[]
}

// Poll every 5 seconds
const { data: presence } = usePolling(`/api/patients/${patientId}/presence`, 5000)
```

**UI components used (example):**
- `AvatarGroup` - Show active users viewing patient
- `Editor` - Rich text editor for clinical notes
- `Mention` - @mention team members in notes
- `Toast` - Real-time notifications
- `Badge` - Unread message counts
- `TabView` - Different sections of patient record

**Collaborative Notes:**
- Optimistic updates with conflict detection
- Last-write-wins with user notification
- Version history with event sourcing
- @mentions for team collaboration

**Activity Feed:**
```typescript
interface ActivityFeed {
  patientId: string
  events: {
    type: string
    actor: User
    timestamp: Date
    description: string
    metadata: Record<string, any>
    relatedEntities: { type: string, id: string }[]
  }[]
  filters: {
    eventTypes: string[]
    actors: string[]
    dateRange: [Date, Date]
  }
}
```

### 5. Autonomous Subscription Management

**Intelligent Renewal Engine:**

**Event-Driven Renewal Flow:**
```
SubscriptionDueDate (7 days before) →
  PreRenewalCheck →
  ValidatePaymentMethod →
  [Valid: ScheduleRenewal] OR [Invalid: RequestUpdate] →
RenewalDate →
  AttemptPayment →
  [Success: RenewSubscription → NotifyPatient → CalculateCommission] OR
  [Failure: DunningSaga begins]
```

**Employee Self-pay Flow (Business → Individual Coverage):**

1. **Business enrollment:** `business_admin` or `business_hr` enrolls the employee under a business plan; the employee gets a subscription with `subject = employee`, and the business admin is the implicit payer.
2. **Self-pay eligibility:** the system checks that the employee is active, the business plan is active, and the business allows self-pay takeover for that slot.
3. **Self-pay request:** the employee chooses to “take over” payment for their plan (e.g. from the employee portal or enrollment flow).
4. **Payment capture:** a self-payment record is created (modeled today via `business_plan_self_payments`) with `user_id = employee.user_id` and the billed amount for the plan.
5. **Subscription conversion:** the subscription is converted from business-paid to individual/self-paid, keeping the same covered person but changing the payer (`payer_type` from `'business_admin'` → `'employee'`).
6. **Future renewals & dunning:** renewal and dunning logic treat the employee as the payer for subsequent billing cycles while still respecting business-level reporting.

**Smart Dunning Management:**
```typescript
interface DunningStrategy {
  attempt: number
  maxAttempts: 5
  retrySchedule: [1, 3, 7, 14, 30] // Days between retries
  actions: {
    attempt1: ['email', 'sms']
    attempt2: ['email', 'sms', 'phone']
    attempt3: ['email', 'sms', 'phone', 'updatePaymentMethod']
    attempt4: ['email', 'sms', 'phone', 'pauseService']
    attempt5: ['email', 'sms', 'cancelSubscription']
  }
  escalation: {
    notifyAgent: boolean
    notifyPatient: boolean
    offerPaymentPlan: boolean
  }
}
```

**UI components used (example):**
- `DataTable` - Subscription list with status
- `Tag` - Subscription status (active, past_due, cancelled)
- `Timeline` - Payment attempt history
- `Button` - Retry payment, update method
- `Dialog` - Payment method update modal
- `Calendar` - Next billing date picker

**Churn Prevention:**
- Predict payment failures before they happen
- Proactive payment method updates
- Personalized retention offers
- Win-back campaigns for cancelled subscriptions

### 6. Adaptive Medical Questionnaires

**Dynamic Form Engine:**

```typescript
interface AdaptiveQuestionnaire {
  id: string
  title: string
  version: number
  questions: Question[]
  logic: ConditionalLogic[]
  scoring: ScoringRules[]
  integrations: {
    autoPopulate: string[] // Fields from patient record
    triggerEvents: string[] // Events to emit on completion
    updateFields: string[] // Patient fields to update
  }
}

interface Question {
  id: string
  type: 'text' | 'number' | 'select' | 'multiselect' | 'date' | 'file' | 'signature'
  text: string
  required: boolean
  validation: ValidationRule[]
  dependencies: string[] // Question IDs this depends on
  aiAssist: {
    suggestions: boolean // AI-powered answer suggestions
    voiceInput: boolean
    ocrExtract: boolean // Extract from uploaded documents
  }
}

interface ConditionalLogic {
  condition: string // "question_5 === 'yes' && question_7 > 50"
  action: 'show' | 'hide' | 'require' | 'skip'
  targets: string[] // Question IDs affected
}
```

**UI components used (example):**
- `InputText` - Text questions
- `InputNumber` - Numeric questions
- `Dropdown` / `MultiSelect` - Selection questions
- `Calendar` - Date questions
- `FileUpload` - Document upload with drag-drop
- `Signature` - Digital signature capture (custom component)
- `ProgressBar` - Questionnaire completion progress
- `Steps` - Multi-page questionnaire navigation
- `Panel` - Collapsible question sections
- `Accordion` - Grouped questions

**Event-Driven Questionnaire Flow:**
```
QuestionnaireAssigned → PatientNotified →
QuestionnaireStarted → ProgressTracked (auto-save) →
QuestionnaireCompleted → ResponseValidated →
ScoreCalculated → RiskAssessed →
[HighRisk: AlertProvider] →
PatientRecordUpdated → FollowUpScheduled
```

### 7. Predictive Health Intelligence

**Health Risk Scoring:**

```typescript
interface HealthRiskAssessment {
  patientId: string
  calculatedAt: Date
  scores: {
    overall: number // 0-100
    categories: {
      medicationAdherence: number
      chronicConditionManagement: number
      preventiveCare: number
      lifestyleFactors: number
    }
  }
  predictions: {
    hospitalizationRisk: { probability: number, timeframe: string }
    medicationNonAdherence: { probability: number, interventions: string[] }
    conditionDeterioration: { probability: number, indicators: string[] }
  }
  recommendations: {
    priority: 'high' | 'medium' | 'low'
    action: string
    expectedImpact: number
    automatable: boolean
  }[]
}
```

**UI components used (example):**
- `Knob` - Overall health score visualization
- `Chart` - Risk trends over time (Line, Radar charts)
- `ProgressBar` - Category scores
- `DataTable` - Recommendations list
- `Tag` - Priority indicators
- `Message` - Risk alerts and warnings
- `Panel` - Expandable prediction details

**Medication Adherence Tracking:**
```
RefillDue → PatientNotified →
[Refilled: AdherenceScoreUp] OR
[NotRefilled: AdherenceScoreDown →
  [Score < 70: TriggerIntervention →
    ContactPatient → IdentifyBarriers →
    OfferSolutions → ScheduleFollowUp]]
```

**Proactive Interventions:**
- Automated refill reminders via preferred channel
- Medication timing optimization based on patient routine
- Side effect monitoring with early detection
- Provider alerts for concerning patterns

### 8. Omnichannel Communication Hub

**Unified Conversation Thread:**

```typescript
interface ConversationThread {
  patientId: string
  participants: User[]
  messages: Message[]
  channels: ('web' | 'sms' | 'email' | 'voice' | 'video')[]
  context: {
    relatedOrders: string[]
    relatedAppointments: string[]
    relatedDocuments: string[]
  }
}

interface Message {
  id: string
  channel: string
  direction: 'inbound' | 'outbound'
  sender: User | Patient
  content: string
  metadata: {
    deliveryStatus: 'sent' | 'delivered' | 'read' | 'failed'
    sentiment: 'positive' | 'neutral' | 'negative'
    intent: string // AI-detected intent
    urgency: 'high' | 'medium' | 'low'
  }
  timestamp: Date
}
```

**UI components used (example):**
- `Chat` - Conversation interface (custom component using UI primitives)
- `InputText` - Message input
- `Button` - Send, attach files
- `FileUpload` - Attachment handling
- `Tag` - Channel indicators (SMS, Email, Web)
- `Badge` - Unread message count
- `Timeline` - Message history
- `Chip` - Sentiment and urgency indicators

**Smart Channel Routing:**
- Urgent messages → SMS + Push notification
- Appointment reminders → SMS or Email based on preference
- Marketing → Email with unsubscribe option
- Support → Web chat with escalation to phone
- Prescriptions → Secure portal notification

**Event-Driven Communication:**
```
OrderShipped →
  DeterminePreferredChannel →
  GenerateMessage →
  SendNotification →
  TrackDelivery →
  [NotDelivered: RetryAlternateChannel]
```

### 9. Advanced Analytics & Reporting

**Real-Time Dashboards:**

**Executive Dashboard:**
- Revenue metrics (MRR, ARR, growth rate)
- Patient acquisition funnel
- Subscription health (churn, LTV, CAC)
- Commission payouts
- Operational metrics (order fulfillment time, support tickets)

**Clinical Dashboard:**
- Patient population health metrics
- Medication adherence rates
- Chronic condition management outcomes
- Preventive care completion rates
- Risk stratification distribution

**Agent Performance Dashboard:**
- Enrollment metrics (patients, businesses)
- Conversion rates by referral source
- Commission earnings with trends
- Downline performance
- Leaderboard rankings

**UI components used (example):**
- `Chart` - All chart types (Line, Bar, Pie, Doughnut, Radar, Polar Area)
- `DataTable` - Detailed data with export to CSV/Excel
- `Card` - Metric cards with icons
- `Knob` - Goal progress indicators
- `ProgressBar` - KPI achievement
- `Calendar` - Date range selection for reports
- `MultiSelect` - Filter selection
- `Button` - Export, refresh, drill-down actions
- `Skeleton` - Loading states

**Event-Based Analytics:**
```typescript
interface AnalyticsQuery {
  metric: string
  aggregation: 'sum' | 'avg' | 'count' | 'min' | 'max'
  groupBy: string[]
  filters: {
    eventTypes: string[]
    dateRange: [Date, Date]
    dimensions: Record<string, any>
  }
  timeGrain: 'hour' | 'day' | 'week' | 'month'
}

// Example: Calculate average time from order to fulfillment
{
  metric: 'fulfillment_time',
  aggregation: 'avg',
  groupBy: ['medication_type', 'region'],
  filters: {
    eventTypes: ['OrderCreated', 'OrderFulfilled'],
    dateRange: [lastMonth, today]
  },
  timeGrain: 'day'
}
```

### 10. Compliance & Audit Automation

**Immutable Audit Trail:**

```typescript
interface AuditEvent {
  id: string
  timestamp: Date
  actor: {
    userId: string
    role: string
    ipAddress: string
    userAgent: string
  }
  action: string
  resource: {
    type: 'patient' | 'order' | 'payment' | 'document'
    id: string
  }
  changes: {
    field: string
    oldValue: any
    newValue: any
  }[]
  justification: string | null // Required for sensitive operations
  complianceFlags: {
    hipaa: boolean
    gdpr: boolean
    stateRegulation: string[]
  }
}
```

**UI components used (example):**
- `DataTable` - Audit log with advanced filtering
- `Timeline` - Chronological audit trail
- `Tag` - Compliance flags
- `Dialog` - Audit event details
- `InputText` - Justification input for sensitive actions
- `Checkbox` - Compliance acknowledgments
- `FileUpload` - Consent form uploads
- `Button` - Export audit reports

**Automated Compliance Checks:**

**HIPAA Compliance:**
- Automatic encryption of PHI at rest and in transit
- Access logging for all patient data views
- Minimum necessary access enforcement
- Business associate agreement tracking
- Breach notification automation

**State Prescribing Rules:**
```typescript
interface PrescribingRule {
  state: string
  medicationType: string
  restrictions: {
    maxQuantity: number
    maxDaySupply: number
    refillLimit: number
    requiresPriorAuth: boolean
    controlledSubstance: boolean
    telemedicineAllowed: boolean
  }
  licenseRequirements: {
    providerType: string[]
    licenseState: string[]
    deaRequired: boolean
  }
}

// Event: OrderValidated
// Check: Validate against state rules before allowing order
```

**Consent Management:**
```typescript
interface ConsentRecord {
  patientId: string
  consentType: 'treatment' | 'privacy' | 'marketing' | 'research'
  version: string
  grantedAt: Date
  expiresAt: Date | null
  revokedAt: Date | null
  signature: string // Cryptographic signature
  ipAddress: string
  document: string // PDF of consent form
}

// Events: ConsentGranted, ConsentRevoked, ConsentExpired
```

---

## User Interface Design (example)

### Design System

**UI Theme Configuration (example):**
```typescript
// app.ts - UI library setup with custom theme
import { createApp } from 'vue'
import App from './App.vue'
import { createDesignSystem } from '@/ui/design-system'

const app = createApp(App)

app.use(
    createDesignSystem({
        prefix: 'ui',
        darkModeSelector: '.dark',
        cssLayer: {
            name: 'ui',
            order: 'tailwind-base, ui, tailwind-utilities',
        },
    }),
)

app.mount('#app')
```

**Custom Theme Tokens:**
```javascript
// Custom color palette for medical theme
const customPreset = {
    semantic: {
        primary: {
            50: '#e6f2ff',
            100: '#b3d9ff',
            200: '#80bfff',
            300: '#4da6ff',
            400: '#1a8cff',
            500: '#0066cc', // Primary Medical Blue
            600: '#0052a3',
            700: '#003d7a',
            800: '#002952',
            900: '#001429',
            950: '#000a14'
        },
        success: {
            500: '#00a86b' // Healing Green
        },
        warn: {
            500: '#ff6b35' // Energy Orange
        }
    }
}
```

**Typography:**
- **Headings:** Inter (700, 600, 500)
- **Body:** Inter (400, 500)
- **Monospace:** JetBrains Mono (code, IDs, technical data)
- **Scale:** 12px, 14px, 16px, 18px, 20px, 24px, 30px, 36px, 48px

**Spacing System:**
- Based on 4px grid: 4, 8, 12, 16, 20, 24, 32, 40, 48, 64, 80, 96
- Spacing utilities align with Tailwind

### Page Layouts (example)

**1. Patient Dashboard Layout**

```vue
<template>
  <div class="patient-dashboard">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <div class="flex items-center gap-4">
        <Avatar :label="patient.initials" size="xlarge" shape="circle" />
        <div>
          <h1 class="text-3xl font-bold">{{ patient.name }}</h1>
          <Tag :value="patient.status" :severity="getStatusSeverity(patient.status)" />
        </div>
      </div>
      <div class="flex gap-2">
        <Button label="Create Order" icon="pi pi-plus" @click="showOrderModal = true" />
        <Button label="Add Document" icon="pi pi-upload" outlined />
        <Button label="Send Message" icon="pi pi-envelope" outlined />
      </div>
    </div>

    <!-- Metrics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
      <Card>
        <template #title>Health Score</template>
        <template #content>
          <Knob v-model="patient.healthScore" :size="120" valueColor="#00a86b" />
        </template>
      </Card>
      <Card>
        <template #title>Active Orders</template>
        <template #content>
          <div class="text-4xl font-bold">{{ patient.activeOrders }}</div>
          <Chip :label="`${patient.pendingOrders} pending`" class="mt-2" />
        </template>
      </Card>
      <Card>
        <template #title>Next Refill</template>
        <template #content>
          <div class="text-2xl">{{ formatDate(patient.nextRefill) }}</div>
          <Tag :value="getDaysUntil(patient.nextRefill) + ' days'" severity="info" class="mt-2" />
        </template>
      </Card>
    </div>

    <!-- Event Timeline -->
    <Panel header="Recent Activity" :toggleable="true" class="mb-6">
      <Timeline :value="recentEvents" align="left">
        <template #marker="{ item }">
          <span class="flex items-center justify-center w-8 h-8 rounded-full bg-primary text-white">
            <i :class="getEventIcon(item.type)"></i>
          </span>
        </template>
        <template #content="{ item }">
          <Card>
            <template #title>{{ item.description }}</template>
            <template #subtitle>{{ formatDateTime(item.timestamp) }} by {{ item.actor.name }}</template>
            <template #content>
              <Tag :value="item.type" class="mr-2" />
              <Button label="View Details" text @click="viewEventDetails(item)" />
            </template>
          </Card>
        </template>
      </Timeline>
    </Panel>

    <!-- Tabbed Content -->
    <TabView>
      <TabPanel header="Demographics">
        <PatientDemographics :patient="patient" @update="handleUpdate" />
      </TabPanel>
      <TabPanel header="Orders">
        <DataTable :value="patient.orders" paginator :rows="10">
          <Column field="id" header="Order ID" sortable />
          <Column field="medication" header="Medication" sortable />
          <Column field="status" header="Status">
            <template #body="{ data }">
              <Tag :value="data.status" :severity="getOrderStatusSeverity(data.status)" />
            </template>
          </Column>
          <Column field="createdAt" header="Date" sortable />
          <Column header="Actions">
            <template #body="{ data }">
              <Button icon="pi pi-eye" text @click="viewOrder(data)" />
              <Button icon="pi pi-pencil" text @click="editOrder(data)" />
            </template>
          </Column>
        </DataTable>
      </TabPanel>
      <TabPanel header="Medical History">
        <MedicalHistory :patient="patient" />
      </TabPanel>
      <TabPanel header="Documents">
        <FileUpload
          mode="advanced"
          :multiple="true"
          accept="image/*,application/pdf"
          :maxFileSize="10000000"
          @upload="handleDocumentUpload"
        >
          <template #empty>
            <p>Drag and drop files here to upload.</p>
          </template>
        </FileUpload>
        <DataTable :value="patient.documents" class="mt-4">
          <Column field="name" header="Document" />
          <Column field="type" header="Type" />
          <Column field="uploadedAt" header="Uploaded" />
          <Column header="Actions">
            <template #body="{ data }">
              <Button icon="pi pi-download" text @click="downloadDocument(data)" />
              <Button icon="pi pi-trash" text severity="danger" @click="deleteDocument(data)" />
            </template>
          </Column>
        </DataTable>
      </TabPanel>
    </TabView>

    <!-- Order Creation Modal -->
    <Dialog v-model:visible="showOrderModal" header="Create Medication Order" :modal="true" :style="{ width: '50vw' }">
      <MedicationOrderWizard :patient="patient" @complete="handleOrderComplete" />
    </Dialog>
  </div>
</template>
```

**2. Commission Dashboard Layout**

```vue
<template>
  <div class="commission-dashboard">
    <!-- Earnings Overview -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
      <Card v-for="period in earningsPeriods" :key="period.label">
        <template #title>{{ period.label }}</template>
        <template #content>
          <div class="text-3xl font-bold text-primary">{{ formatCurrency(period.amount) }}</div>
          <div class="flex items-center gap-2 mt-2">
            <Tag :value="period.change" :severity="period.change > 0 ? 'success' : 'danger'" />
            <span class="text-sm text-gray-500">vs last {{ period.label.toLowerCase() }}</span>
          </div>
        </template>
      </Card>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
      <!-- Earnings Trend -->
      <Card>
        <template #title>Earnings Trend</template>
        <template #content>
          <Chart type="line" :data="earningsTrendData" :options="chartOptions" />
        </template>
      </Card>

      <!-- Commission Breakdown -->
      <Card>
        <template #title>Commission by Product Type</template>
        <template #content>
          <Chart type="doughnut" :data="commissionBreakdownData" :options="doughnutOptions" />
        </template>
      </Card>
    </div>

    <!-- Referral Network & Recent Commissions -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
      <!-- Referral Network -->
      <Card>
        <template #title>Referral Network</template>
        <template #subtitle>Your downline hierarchy</template>
        <template #content>
          <OrganizationChart :value="referralHierarchy" :collapsible="true">
            <template #default="{ node }">
              <div class="flex flex-col items-center">
                <Avatar :label="node.data.initials" size="large" shape="circle" />
                <div class="mt-2 text-center">
                  <div class="font-bold">{{ node.data.name }}</div>
                  <Tag :value="node.data.type" class="mt-1" />
                  <div class="text-sm text-gray-500 mt-1">
                    {{ formatCurrency(node.data.earnings) }}
                  </div>
                </div>
              </div>
            </template>
          </OrganizationChart>
        </template>
      </Card>

      <!-- Recent Commissions -->
      <Card>
        <template #title>Recent Commissions</template>
        <template #subtitle>Live updates via Reverb WebSockets (SSE fallback)</template>
        <template #content>
          <DataTable :value="recentCommissions" :rows="10" paginator>
            <Column field="orderId" header="Order" />
            <Column field="patient" header="Patient" />
            <Column field="amount" header="Amount">
              <template #body="{ data }">
                <span class="font-bold text-success">{{ formatCurrency(data.amount) }}</span>
              </template>
            </Column>
            <Column field="rate" header="Rate">
              <template #body="{ data }">
                <Tag :value="data.rate + '%'" />
              </template>
            </Column>
            <Column field="calculatedAt" header="Date" />
            <Column header="Actions">
              <template #body="{ data }">
                <Button icon="pi pi-eye" text @click="viewCommissionDetails(data)" />
              </template>
            </Column>
          </DataTable>
        </template>
      </Card>
    </div>

    <!-- Earnings Forecast -->
    <Card>
      <template #title>30-Day Earnings Forecast</template>
      <template #subtitle>Predictive analytics based on patient lifecycle</template>
      <template #content>
        <Chart type="line" :data="forecastData" :options="forecastOptions" />
        <div class="grid grid-cols-3 gap-4 mt-4">
          <div class="text-center">
            <div class="text-sm text-gray-500">Conservative</div>
            <div class="text-2xl font-bold">{{ formatCurrency(forecast.conservative) }}</div>
          </div>
          <div class="text-center">
            <div class="text-sm text-gray-500">Expected</div>
            <div class="text-2xl font-bold text-primary">{{ formatCurrency(forecast.expected) }}</div>
          </div>
          <div class="text-center">
            <div class="text-sm text-gray-500">Optimistic</div>
            <div class="text-2xl font-bold">{{ formatCurrency(forecast.optimistic) }}</div>
          </div>
        </div>
      </template>
    </Card>
  </div>
</template>
```

**3. Medication Order Wizard (Multi-Step)**

```vue
<template>
  <div class="medication-order-wizard">
    <Stepper v-model:activeStep="activeStep" linear>
      <!-- Step 1: Select Condition -->
      <StepperPanel header="Select Condition">
        <template #content="{ nextCallback }">
          <div class="flex flex-col gap-4">
            <AutoComplete
              v-model="selectedCondition"
              :suggestions="conditionSuggestions"
              @complete="searchConditions"
              field="name"
              placeholder="Search conditions..."
            >
              <template #item="{ item }">
                <div class="flex flex-col">
                  <span class="font-bold">{{ item.name }}</span>
                  <span class="text-sm text-gray-500">ICD: {{ item.icdCode }}</span>
                </div>
              </template>
            </AutoComplete>

            <Message v-if="aiSuggestion" severity="info">
              <template #icon>
                <i class="pi pi-sparkles"></i>
              </template>
              AI Suggestion: Based on patient history, consider {{ aiSuggestion.name }}
            </Message>

            <DataTable :value="patientConditions" selectionMode="single" v-model:selection="selectedCondition">
              <Column field="name" header="Existing Conditions" />
              <Column field="icdCode" header="ICD Code" />
            </DataTable>

            <div class="flex justify-end">
              <Button label="Next" icon="pi pi-arrow-right" iconPos="right" @click="nextCallback" :disabled="!selectedCondition" />
            </div>
          </div>
        </template>
      </StepperPanel>

      <!-- Step 2: Select Medication -->
      <StepperPanel header="Select Medication">
        <template #content="{ prevCallback, nextCallback }">
          <div class="flex flex-col gap-4">
            <div class="text-lg font-bold mb-2">Medications for {{ selectedCondition?.name }}</div>

            <DataTable :value="availableMedications" selectionMode="single" v-model:selection="selectedMedication">
              <Column field="name" header="Medication" sortable />
              <Column field="strength" header="Strength" />
              <Column field="useType" header="Use">
                <template #body="{ data }">
                  <Tag :value="data.useType" :severity="data.useType === 'primary' ? 'success' : 'info'" />
                </template>
              </Column>
              <Column field="price" header="Price/Month">
                <template #body="{ data }">
                  {{ formatCurrency(data.price) }}
                </template>
              </Column>
              <Column header="Warnings">
                <template #body="{ data }">
                  <Badge v-if="data.interactions.length > 0" :value="data.interactions.length" severity="warning" />
                </template>
              </Column>
            </DataTable>

            <Panel v-if="selectedMedication && selectedMedication.interactions.length > 0" header="Interaction Warnings" toggleable>
              <Message v-for="interaction in selectedMedication.interactions" :key="interaction.id" :severity="interaction.severity">
                {{ interaction.description }}
              </Message>
            </Panel>

            <div class="flex justify-between">
              <Button label="Back" icon="pi pi-arrow-left" @click="prevCallback" outlined />
              <Button label="Next" icon="pi pi-arrow-right" iconPos="right" @click="nextCallback" :disabled="!selectedMedication" />
            </div>
          </div>
        </template>
      </StepperPanel>

      <!-- Step 3: Configure & Review -->
      <StepperPanel header="Configure & Review">
        <template #content="{ prevCallback }">
          <div class="flex flex-col gap-4">
            <div class="grid grid-cols-2 gap-4">
              <div class="flex flex-col gap-2">
                <label>Dosage</label>
                <Dropdown v-model="orderConfig.dosage" :options="dosageOptions" optionLabel="label" />
              </div>
              <div class="flex flex-col gap-2">
                <label>Quantity</label>
                <InputNumber v-model="orderConfig.quantity" :min="1" :max="90" />
              </div>
              <div class="flex flex-col gap-2">
                <label>Refills</label>
                <InputNumber v-model="orderConfig.refills" :min="0" :max="12" />
              </div>
              <div class="flex flex-col gap-2">
                <label>Days Supply</label>
                <InputNumber v-model="orderConfig.daysSupply" :min="1" :max="90" disabled />
              </div>
            </div>

            <Panel header="Safety Checks" :toggleable="false">
              <div class="flex flex-col gap-2">
                <div class="flex items-center gap-2">
                  <i class="pi pi-check-circle text-success"></i>
                  <span>No drug interactions detected</span>
                </div>
                <div class="flex items-center gap-2">
                  <i class="pi pi-check-circle text-success"></i>
                  <span>Covered by insurance</span>
                </div>
                <div class="flex items-center gap-2">
                  <i class="pi pi-exclamation-triangle text-warning"></i>
                  <span>Patient copay: {{ formatCurrency(10) }}</span>
                </div>
              </div>
            </Panel>

            <div class="flex flex-col gap-2">
              <label>Payment Method</label>
              <div class="flex items-center gap-2">
                <Dropdown v-model="orderConfig.paymentMethod" :options="paymentMethods" optionLabel="label" class="flex-1" />
                <Button label="Add New" icon="pi pi-plus" outlined @click="showAddPaymentMethod = true" />
              </div>
            </div>

            <Divider />

            <div class="flex justify-between items-center">
              <div>
                <div class="text-sm text-gray-500">Total</div>
                <div class="text-2xl font-bold">{{ formatCurrency(orderTotal) }}</div>
              </div>
              <div class="flex gap-2">
                <Button label="Back" icon="pi pi-arrow-left" @click="prevCallback" outlined />
                <Button label="Submit Order" icon="pi pi-check" @click="submitOrder" :loading="submitting" />
              </div>
            </div>
          </div>
        </template>
      </StepperPanel>
    </Stepper>
  </div>
</template>
```

---

## Implementation Roadmap

### Phase 1: Foundation (Weeks 1-4)

**Week 1-2: Event Sourcing Infrastructure**
- Set up event store (MySQL with JSON columns)
- Implement base event sourcing classes
- Create event bus with Laravel Event system
- Set up database queue driver
- Configure cron jobs for queue processing
- Implement basic projections

**Week 3-4: CQRS Architecture & Frontend Setup**
- Separate command and query models
- Implement command handlers
- Create read model projections
- Set up repository patterns
- Build aggregate roots for core domains
- **Install and configure Inertia.js 2+**
- **Install and configure frontend UI components and theme**
- **Set up TypeScript, Pinia, VueUse**
- **Create base layouts and shared components**

### Phase 2: Core Domains (Weeks 5-10)

**Week 5-6: Patient Management**
- Patient aggregate with event sourcing
- Patient enrollment saga
- Patient dashboard with Reverb WebSocket updates (SSE fallback) using data tables, timelines, and cards
- Document management with events (file upload UI)
- Medical history tracking

**Week 7-8: Order Management**
- Order aggregate and fulfillment saga
- Medication catalog with MySQL full-text search
- Condition-medication relationships
- Order wizard with AI assistance (stepper and auto-complete UI)
- Inventory management events

**Week 9-10: Commission System**
- Commission calculation engine
- Hierarchical commission cascade
- Real-time commission dashboard (charts and organization charts)
- Payout processing saga
- Commission audit trail

### Phase 3: Advanced Features (Weeks 11-16)

**Week 11-12: Payment & Subscriptions**
- Payment method management (modal dialogs and input components)
- Subscription renewal automation
- Dunning management saga
- Payment failure prediction
- Revenue analytics (charts)

**Week 13-14: Referral Network**
- Agent/LOA onboarding saga
- Referral link generation
- Network visualization (organization charts and trees)
- Performance tracking
- Gamification system (badges and tags)

**Week 15-16: Clinical Intelligence**
- Medical questionnaire engine (dynamic forms)
- Adaptive form logic
- Health risk scoring (gauges/knobs and charts)
- Medication adherence tracking
- Predictive interventions

### Phase 4: Real-Time & Collaboration (Weeks 17-20)

**Week 17-18: Reverb WebSocket Infrastructure & SSE Fallback**
- Install and configure Laravel Reverb (WebSockets) on cPanel/WHM server
- Configure WebSocket broadcasting and authentication
- Implement SSE fallback endpoints for environments/browsers without WebSocket support
- Optimistic UI updates with Inertia 2+
- Polling fallback for presence (avatar group UI)

**Week 19-20: Omnichannel Communication**
- Unified conversation thread (timeline and chat components)
- SMS/Email integration (Twilio/SendGrid)
- Smart channel routing
- Notification preferences (toast notifications)
- Communication analytics

### Phase 5: Analytics & Compliance (Weeks 21-24)

**Week 21-22: Analytics Engine**
- Real-time dashboards (charts - all types)
- Event-based metrics
- Predictive analytics
- Custom report builder (data table with export)
- Data export functionality

**Week 23-24: Compliance Automation**
- Audit trail implementation (data table and timeline)
- HIPAA compliance checks
- Consent management (file uploads and checkboxes)
- License verification
- Regulatory reporting

### Phase 6: Polish & Launch (Weeks 25-28)

**Week 25-26: Testing & Optimization**
- Unit tests for all aggregates
- Integration tests for sagas
- E2E tests for critical workflows (Playwright)
- Performance optimization for cPanel
- Security audit

**Week 27-28: Documentation & Training**
- User documentation
- Developer documentation
- Component library documentation (Storybook)
- Video tutorials
- Launch preparation

## System Implementation Task List

> This checklist summarizes the major implementation work across the platform.
> It mirrors the "Implementation Roadmap" and DDD bounded contexts, and tracks
> current progress at a high level.

### 1. Foundation & Infrastructure

- [x] MySQL event store schema with JSON columns and indexes.
- [x] Core event-sourcing primitives (`DomainEvent`, `EventStore`, `AggregateRoot`).
- [x] CQRS infrastructure (`CommandBus`, `QueryBus`, handler interfaces).
- [x] Laravel event publication pattern from stored events to projectors.
- [x] First projections and read-model pattern (patient enrollment).
- [x] Generic projection replay tooling for rebuilding read models from the event store.
- [ ] (Minor) Future projection replay enhancements (richer config, time-window filters, dedicated registry tests).
- [x] Monitoring/observability for event store and queues (metrics, logs, alerts).
- [ ] (Minor) Add a simple CLI or dashboard endpoint to inspect cached event store and queue metrics (processed/failed jobs, events stored by type) for quick operational checks.

### 2. Patient Management

- [x] `PatientEnrolled` domain event, enrollment command handler, and aggregate.
- [x] `patient_enrollments` projection and finder APIs.
- [x] Patient enrollment endpoints (`GET/POST /patient/enrollment`).
- [x] Patient dashboard cards: enrollment, recent activity, events timeline, subscription status.
- [x] Patient demographics aggregate (`UpdateDemographics` command, `DemographicsUpdated` events) projecting into existing `users` table.
- [x] Patient document upload flow (`DocumentUploaded` events, projections, UI) reusing legacy document/record tables.
- [x] Medical history tracking (events, read models, patient and staff dashboard/clinical UI).
- [x] Patient list and detail read models (`PatientListView`, `PatientDetailView`) for staff/admin, backed by existing tables with simple pagination and separate count endpoints.
- [ ] (Minor) Refine patient subscription cancel dialog copy to show explicit end date / plan details when available.
- [ ] (Minor) Show “Cancelled on … / Ends on …” badges on the patient subscription card based on subscription status.

### 3. Order Management & Medication Catalog

> **Medication data note:** The legacy system exposed medications through two table groups (`medications` and `medication_bases` plus related tables). For all new Order Management & Medication Catalog work, treat the `medications` table as the single source of truth and do not depend on `medication_bases` or its related tables.


- [x] Order aggregate (`CreateOrder`, `FulfillOrder`, `CancelOrder`) and events, with projections into the legacy `medication_orders` table and read-side query endpoints for patient and staff order lists.
- [x] Order fulfillment saga wired through event store and queues.
- [x] Medication catalog aggregates (`Medication`, `Condition`, `Formulary`) and events.
- [x] Medication search and formulary read models.
- [x] Patient order history timeline on patient and staff dashboards.
- [x] Doctor-facing endpoint to create prescriptions for patient orders (event-sourced via `CreatePrescription` / `PrescriptionCreated`, projecting into the legacy `prescriptions` table).
- [x] Linking prescriptions back to orders on `PrescriptionCreated` projection (sets `medication_orders.prescription_id`, updates status to `prescribed`, and notifies the patient).

### 4. Commission & Referral Network

- [x] Commission aggregates, events, and calculation engine.
- [x] Hierarchical commission cascade through referral hierarchy.
- [x] Commission dashboard read models and UI.
- [x] Agent/LOA onboarding flow and events.
- [x] Referral link generation, tracking, and network visualization.

### 5. Payment & Subscriptions

- [x] Read-side subscription status query and patient dashboard subscription card.
- [x] Patient-facing subscription cancel endpoint and dashboard cancel control (simple status update prior to full event-sourced subscription model).
- [x] Event-sourced subscription aggregate and `SubscriptionRenewed` events.
- [x] Payment method management and `PaymentMethodAdded` events.
- [x] Payment processing and dunning management sagas.
- [x] Revenue and subscription analytics (MRR, churn, LTV dashboards).
- [x] (Minor) Refine patient subscription cancel dialog copy to show explicit end date / plan details when available.
- [x] (Minor) Show "Cancelled on … / Ends on …" badges on subscription cards based on subscription status (patient and, later, staff views).
- [x] Payment method validation (credit card expiration, token validation, ACH account validation).
- [x] Retry logic with exponential backoff for failed payments (1, 3, 7, 14, 30 days).
- [x] Idempotency checks to prevent duplicate renewals.
- [x] Enhanced logging with correlation IDs for renewal flow tracking.
- [x] Rate limiting on renewal endpoints (5/hour, 20/day per user).
- [ ] **Implement Billing Page for Payment Method Management** - Comprehensive patient-facing billing page with:
  - [ ] Payment method display (credit card, ACH, invoice) with icons and status badges
  - [ ] Add payment method functionality (credit card form, ACH form, invoice form)
  - [ ] Edit payment method (non-sensitive fields only)
  - [ ] Remove payment method with confirmation and safeguards
  - [ ] ACH verification flow (micro-deposit verification)
  - [ ] Set payment method as default
  - [ ] Security & validation (client-side and server-side)
  - [ ] User experience (loading states, toasts, empty states, responsive design)
  - [ ] Frontend components: `BillingPage.vue`, `PaymentMethodList.vue`, `PaymentMethodCard.vue`, `AddPaymentMethodModal.vue`, `CreditCardForm.vue`, `AchForm.vue`, `InvoiceForm.vue`, `VerificationFlow.vue`
  - [ ] State management for payment methods with Pinia
  - [ ] Comprehensive tests (unit, feature, browser tests)
  - [ ] User and developer documentation
  - **Success Metrics**: 95%+ success rate, <5% support tickets, 4.5+ star rating, 99.9% uptime
- [ ] **Make Idempotency Cache TTL Configurable** - Move hardcoded 30-day TTL to configuration:
  - [ ] Add `RENEWAL_IDEMPOTENCY_TTL_DAYS` to `.env` configuration
  - [ ] Update `ProcessSubscriptionRenewalJob` to use configurable TTL
  - [ ] Add configuration validation in `AppServiceProvider`
  - [ ] Update documentation with recommended TTL values
  - [ ] Add tests for different TTL configurations
- [ ] **Add Retry Schedule Flexibility** - Make retry schedule and max attempts configurable:
  - [ ] Add `RENEWAL_MAX_ATTEMPTS` and `RENEWAL_RETRY_SCHEDULE` to `.env`
  - [ ] Create `RenewalConfiguration` class to manage retry settings
  - [ ] Update `ProcessSubscriptionRenewalJob` to use configuration
  - [ ] Add validation for retry schedule (ensure delays are in ascending order)
  - [ ] Add admin UI to view/modify retry configuration
  - [ ] Add tests for different retry schedules
- [ ] **Implement Rate Limit Customization** - Make rate limits configurable per user role/plan tier:
  - [ ] Create `RateLimitConfiguration` model to store per-role/tier limits
  - [ ] Update `RateLimitSubscriptionRenewal` middleware to check user role/plan
  - [ ] Add admin UI to configure rate limits by role/tier
  - [ ] Add migration for rate limit configuration table
  - [ ] Implement caching for rate limit configuration
  - [ ] Add tests for different role/tier configurations
- [ ] **Add Payment Method Verification Status Validation** - Validate ACH micro-deposit verification:
  - [ ] Add `verification_status` field to `payment_methods` table (pending, verified, failed)
  - [ ] Add `verification_attempts` and `last_verification_attempt_at` fields
  - [ ] Update `PaymentMethod` model with verification status methods
  - [ ] Prevent payment processing for unverified ACH accounts
  - [ ] Add verification status display in billing page
  - [ ] Add tests for verification status validation
- [ ] **Implement Monitoring Alerts for Failed Renewals** - Add guidance and tooling for renewal failure monitoring:
  - [ ] Create `RenewalFailureAlert` event for failed renewals after all retries
  - [ ] Add alert configuration to `.env` (email recipients, Slack webhook, etc.)
  - [ ] Create `RenewalFailureAlertHandler` to send notifications
  - [ ] Add admin dashboard widget showing failed renewals
  - [ ] Create CLI command to check renewal failure status
  - [ ] Add documentation for setting up alerts (email, Slack, PagerDuty)
  - [ ] Add tests for alert triggering and delivery

### 6. Clinical & Compliance

- [ ] Questionnaire, clinical note, and consultation aggregates and events.
- [ ] Adaptive questionnaire engine and read models.
- [ ] Consent, audit log, and license aggregates (`ConsentGranted`, `AccessLogged`, `LicenseVerified`).
- [ ] Compliance automation (audit trail UI, HIPAA checks, regulatory reporting).

### 7. Real-Time, Omnichannel & Analytics

- [ ] Laravel Reverb WebSocket + SSE fallback for live dashboards and patient views.
- [ ] Presence, collaborative notes, and real-time chat in patient context.
- [ ] Unified conversation thread across SMS, email, and in-app messaging.
- [ ] Notification dispatcher for event-driven alerts and reminders.
- [ ] Analytics engine: real-time dashboards, event-based metrics, predictive models.

### 8. Testing, Documentation & Launch

- [x] Unit and feature tests for patient enrollment, dashboard read models, and subscription status.
- [ ] Unit and integration tests for all new aggregates, sagas, and read models.
- [x] Event-sourcing and CQRS foundation documentation.
- [ ] Comprehensive user and developer documentation (including component library).
- [ ] Performance, security, and launch-readiness checks for cPanel deployment.

---

## cPanel-Specific Optimizations

### 1. **Performance Tuning**

**PHP Configuration (.htaccess):**
```apache
# Increase PHP limits
php_value memory_limit 256M
php_value max_execution_time 300
php_value upload_max_filesize 20M
php_value post_max_size 25M

# Enable OPcache
php_value opcache.enable 1
php_value opcache.memory_consumption 128
php_value opcache.max_accelerated_files 10000
```

**Laravel Optimization:**
```bash
# Run these after deployment
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# For production
composer install --optimize-autoloader --no-dev
```

**Database Optimization:**
```sql
-- Add indexes for event store queries
CREATE INDEX idx_event_aggregate ON event_store(aggregate_uuid, aggregate_type);
CREATE INDEX idx_event_type ON event_store(event_type);
CREATE INDEX idx_event_occurred ON event_store(occurred_at);

-- Add indexes for read models
CREATE INDEX idx_patient_search ON patients(name, email, phone);
CREATE INDEX idx_order_patient ON orders(patient_id, created_at);
CREATE INDEX idx_commission_agent ON commissions(agent_id, created_at);
```

### 2. **Caching Strategy**

**File-Based Cache (if Redis not available):**
```php
// config/cache.php
'default' => env('CACHE_DRIVER', 'file'),

'stores' => [
    'file' => [
        'driver' => 'file',
        'path' => storage_path('framework/cache/data'),
    ],
],
```

**Cache Read Models:**
```php
// Cache patient dashboard for 5 minutes
$patientData = Cache::remember("patient.{$id}.dashboard", 300, function () use ($id) {
    return PatientDashboardView::find($id);
});

// Invalidate cache on events
Event::listen(PatientUpdated::class, function ($event) {
    Cache::forget("patient.{$event->patientId}.dashboard");
});
```

### 3. **Queue Processing**

**Cron Job Setup (cPanel):**
```bash
# Add in cPanel Cron Jobs interface
# Run every minute
* * * * * cd /home/username/public_html && /usr/local/bin/php artisan schedule:run >> /dev/null 2>&1
```

**Laravel Scheduler:**
```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    // Process queue jobs
    $schedule->command('queue:work --stop-when-empty --tries=3 --timeout=60')
             ->everyMinute()
             ->withoutOverlapping()
             ->runInBackground();

    // Process event projections
    $schedule->command('events:project')
             ->everyFiveMinutes()
             ->withoutOverlapping();

    // Send scheduled notifications
    $schedule->command('notifications:send')
             ->everyMinute();

    // Calculate daily analytics
    $schedule->command('analytics:daily')
             ->dailyAt('01:00');

    // Clean up old events (keep 1 year)
    $schedule->command('events:cleanup --days=365')
             ->weekly();
}
```

### 4. **Asset Optimization**

**Vite Build for Production:**
```bash
# Build optimized assets
npm run build

# Output will be in public/build/
# Laravel will automatically use these in production
```

**UI Library Tree Shaking (example):**
```javascript
// Only import components you use
import { DataTable } from 'your-ui-library'
import { Column } from 'your-ui-library'
import { Button } from 'your-ui-library'

// Instead of importing entire library
```

**Image Optimization:**
```php
// Use intervention/image for image processing
composer require intervention/image

// Optimize uploaded images
$image = Image::make($request->file('photo'))
    ->resize(800, null, function ($constraint) {
        $constraint->aspectRatio();
        $constraint->upsize();
    })
    ->encode('jpg', 80);
```

### 5. **Security Hardening**

**.htaccess Security:**
```apache
# Disable directory browsing
Options -Indexes

# Protect .env file
<Files .env>
    Order allow,deny
    Deny from all
</Files>

# Force HTTPS
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Prevent access to sensitive files
<FilesMatch "^\.">
    Order allow,deny
    Deny from all
</FilesMatch>
```

**Laravel Security:**
```php
// config/session.php
'secure' => env('SESSION_SECURE_COOKIE', true),
'http_only' => true,
'same_site' => 'strict',

// Force HTTPS in production
// app/Providers/AppServiceProvider.php
if ($this->app->environment('production')) {
    URL::forceScheme('https');
}
```

---

## Success Metrics

### Technical Metrics
- **Event Processing Latency:** < 500ms p95 (cPanel optimized)
- **Page Load Time:** < 3s (First Contentful Paint)
- **Real-Time Update Latency (Reverb/SSE):** < 2s
- **Event Store Write Throughput:** > 100 events/sec
- **Query Response Time:** < 500ms p95
- **Uptime:** 99.5% SLA (cPanel shared hosting)

### Business Metrics
- **Patient Enrollment Time:** < 5 minutes (50% reduction)
- **Order Completion Rate:** > 95% (20% increase)
- **Commission Calculation Accuracy:** 100% (zero disputes)
- **Subscription Renewal Rate:** > 90% (15% increase)
- **Agent Productivity:** 40% more enrollments per agent
- **Customer Satisfaction (NPS):** > 60

### Clinical Metrics
- **Medication Adherence Rate:** > 85%
- **Questionnaire Completion Rate:** > 90%
- **Health Risk Detection:** 30% earlier intervention
- **Provider Response Time:** < 4 hours for urgent issues

---

## Technology Stack Summary (cPanel Optimized)

### Backend
- **Laravel 11**
- **PHP 8.1+** (cPanel compatible)
- **MySQL 8.0** (Event Store + Read Models)
- **File-based Cache** (or Redis if available)
- **Database Queue Driver**
- **Cron-based Queue Processing**
- **Custom Event Sourcing Implementation**
- **MySQL Full-Text Search**

### Frontend
- **Vue 3** with Composition API and `<script setup>`
- **TypeScript 5+**
- **Inertia.js 2+** with enhanced features
- **Pinia** for state management
- **Headless/Tailwind-based UI components** for enterprise-ready UIs
- **Tailwind CSS 4+** for utility styling
- **VueUse** for composables
- **VueFlow** for network diagrams
- **Vite 5+** for build tooling

### Real-Time (cPanel Compatible)
- **Laravel Reverb (WebSockets)** as primary real-time transport
- **Server-Sent Events (SSE)** as first-class fallback
- **Long Polling** as last-resort fallback
- **Optimistic UI Updates**
- **Periodic Polling for Presence**

### Infrastructure
- **cPanel server with WHM/root access**
- **MySQL Database**
- **File Storage** (cPanel file system)
- **Cron Jobs** for scheduling
- **Let's Encrypt SSL**
- **CloudFlare** (optional CDN)

### Third-Party Integrations
- **Stripe/Authorize.net** - Payments
- **Twilio** - SMS
- **SendGrid** - Email
- **Zoom API** - Telemedicine (optional)

---

## UI Component Mapping (example)

### Data Display
- **DataTable** - Patient lists, orders, commissions, audit logs
- **Timeline** - Event streams, activity feeds, payment history
- **Card** - Metric cards, patient cards, dashboard widgets
- **OrganizationChart** - Referral hierarchy visualization
- **Tree** - Hierarchical data (downline, categories)
- **Panel** - Collapsible sections
- **Accordion** - Grouped content

### Forms & Input
- **InputText** - Text fields
- **InputNumber** - Numeric inputs
- **Dropdown** - Single selection
- **MultiSelect** - Multiple selection
- **AutoComplete** - Search with suggestions
- **Calendar** - Date/time selection
- **FileUpload** - Document uploads with drag-drop
- **Editor** - Rich text editing
- **Checkbox/RadioButton** - Boolean/option selection
- **Slider** - Range selection

### Overlays
- **Dialog** - Modals for forms and details
- **Sidebar** - Slide-out panels
- **Toast** - Notifications
- **ConfirmDialog** - Confirmation prompts
- **OverlayPanel** - Contextual overlays

### Data Visualization
- **Chart** - All chart types (Line, Bar, Pie, Doughnut, Radar, Polar Area)
- **Knob** - Circular progress/score indicators
- **ProgressBar** - Linear progress indicators

### Navigation
- **TabView** - Tabbed content
- **Stepper** - Multi-step wizards
- **Breadcrumb** - Navigation trail
- **Menu/Menubar** - Navigation menus

### Misc
- **Tag** - Status indicators, labels
- **Badge** - Counts, notifications
- **Chip** - Removable items, filters
- **Avatar/AvatarGroup** - User representation
- **Skeleton** - Loading states
- **Message** - Inline messages
- **Divider** - Content separation

---

This architecture creates a truly event-driven, intelligent telemedicine platform optimized for cPanel hosting with modern Inertia.js 2+ and a modern component library, while maintaining scalability, complete audit trails, predictive insights, and automated workflows. Every action in the system is an event, every event tells a story, and every story drives better healthcare outcomes—all running efficiently on affordable shared hosting infrastructure with a beautiful, accessible, and professional user interface.


