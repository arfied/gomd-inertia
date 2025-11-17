<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

interface PatientEnrollment {
    patient_uuid: string;
    user_id: number;
    source: string;
    metadata: Record<string, unknown> | null;
    enrolled_at: string | null;
}

interface PatientSubscription {
    id: number;
    status: string;
    plan_name: string | null;
    is_trial: boolean;
    starts_at: string | null;
    ends_at: string | null;
}

interface RecentActivityEntry {
    id: number;
    type: string;
    description: string;
    metadata: Record<string, unknown> | null;
    created_at: string | null;
}

interface TimelineEventEntry {
    id: number;
    aggregate_uuid: string;
    event_type: string;
    description: string;
    source: string | null;
    payload: Record<string, unknown> | null;
    metadata: Record<string, unknown> | null;
    occurred_at: string | null;
}

const timelineEvents = ref<TimelineEventEntry[]>([]);
const loadingTimeline = ref(true);
const timelineError = ref<string | null>(null);

const selectedTimelineFilter = ref<'all' | 'enrollment' | 'other'>('all');

const filteredTimelineEvents = computed(() => {
    if (selectedTimelineFilter.value === 'enrollment') {
        return timelineEvents.value.filter(
            (event) => event.event_type === 'patient.enrolled',
        );
    }

    if (selectedTimelineFilter.value === 'other') {
        return timelineEvents.value.filter(
            (event) => event.event_type !== 'patient.enrolled',
        );
    }

    return timelineEvents.value;
});

interface TimelineEventGroup {
    date: string;
    label: string;
    events: TimelineEventEntry[];
}

const groupedTimelineEvents = computed<TimelineEventGroup[]>(() => {
    const groups = new Map<string, TimelineEventGroup>();

    for (const event of filteredTimelineEvents.value) {
        const isoString = event.occurred_at;
        let dateKey = 'unknown';
        let label = 'Unknown date';

        if (isoString) {
            const date = new Date(isoString);

            if (!Number.isNaN(date.getTime())) {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');

                dateKey = `${year}-${month}-${day}`;
                label = date.toLocaleDateString();
            }
        }

        let group = groups.get(dateKey);

        if (!group) {
            group = {
                date: dateKey,
                label,
                events: [],
            };

            groups.set(dateKey, group);
        }

        group.events.push(event);
    }

    return Array.from(groups.values());
});



const enrollment = ref<PatientEnrollment | null>(null);
const loadingEnrollment = ref(true);
const enrollmentError = ref<string | null>(null);
const startingEnrollment = ref(false);

const subscription = ref<PatientSubscription | null>(null);
const loadingSubscription = ref(true);
const subscriptionError = ref<string | null>(null);

const recentActivity = ref<RecentActivityEntry[]>([]);
const loadingRecentActivity = ref(true);
const recentActivityError = ref<string | null>(null);

const formattedEnrolledAt = computed(() => {
    if (!enrollment.value?.enrolled_at) {
        return null;
    }

    return new Date(enrollment.value.enrolled_at).toLocaleString();
});

function formatActivityTimestamp(isoString: string | null): string {
    if (!isoString) {
        return '';
    }

    const date = new Date(isoString);

    if (Number.isNaN(date.getTime())) {
        return isoString;
    }

    return date.toLocaleString();
}

function formatTimelineSource(source: string | null): string | null {
    if (!source) {
        return null;
    }

    switch (source) {
        case 'registration':
            return 'Registration';
        case 'manual':
            return 'Manual enrollment';
        default:
            return source;
    }
}

function getXsrfToken(): string | null {
    const name = 'XSRF-TOKEN';

    const cookies = document.cookie.split(';');

    for (const cookie of cookies) {
        const [key, value] = cookie.split('=');

        if (key && key.trim() === name) {
            return decodeURIComponent(value ?? '');
        }
    }

    return null;
}

async function startEnrollment() {
    if (startingEnrollment.value || loadingEnrollment.value) {
        return;
    }

    startingEnrollment.value = true;
    enrollmentError.value = null;

    try {
        const xsrfToken = getXsrfToken();

        const response = await fetch('/patient/enrollment', {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                ...(xsrfToken ? { 'X-XSRF-TOKEN': xsrfToken } : {}),
            },
            credentials: 'same-origin',
            body: JSON.stringify({}),
        });

        if (!response.ok) {
            enrollmentError.value = 'Failed to start enrollment (' + response.status + ')';
            return;
        }

        const data = (await response.json()) as {
            enrollment: PatientEnrollment | null;
        };

        enrollment.value = data.enrollment ?? null;
    } catch {
        enrollmentError.value =
            'A network error occurred while starting your enrollment.';
    } finally {
        startingEnrollment.value = false;
    }
}

async function loadEnrollment() {
    try {
        const response = await fetch('/patient/enrollment', {
            headers: {
                Accept: 'application/json',
            },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            enrollmentError.value = `Failed to load enrollment (${response.status})`;
            return;
        }

        const data = (await response.json()) as {
            enrollment: PatientEnrollment | null;
        };

        enrollment.value = data.enrollment ?? null;
    } catch {
        enrollmentError.value =
            'A network error occurred while loading your enrollment status.';
    } finally {
        loadingEnrollment.value = false;
    }
}

async function loadSubscription() {
    try {
        const response = await fetch('/patient/subscription', {
            headers: {
                Accept: 'application/json',
            },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            subscriptionError.value = `Failed to load subscription (${response.status})`;
            return;
        }

        const data = (await response.json()) as {
            subscription: PatientSubscription | null;
        };

        subscription.value = data.subscription ?? null;
    } catch {
        subscriptionError.value =
            'A network error occurred while loading your subscription.';
    } finally {
        loadingSubscription.value = false;
    }
}

async function loadRecentActivity() {
    try {
        const response = await fetch('/patient/activity/recent', {
            headers: {
                Accept: 'application/json',
            },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            recentActivityError.value = `Failed to load recent activity (${response.status})`;
            return;
        }

        const data = (await response.json()) as {
            activities: RecentActivityEntry[] | null;
        };

        recentActivity.value = data.activities ?? [];
    } catch {
        recentActivityError.value =
            'A network error occurred while loading your recent activity.';
    } finally {
        loadingRecentActivity.value = false;
    }
}

async function loadTimeline() {
    try {
        const params = new URLSearchParams();

        if (selectedTimelineFilter.value === 'enrollment') {
            params.set('filter', 'enrollment');
        } else if (selectedTimelineFilter.value === 'other') {
            params.set('filter', 'other');
        }

        const queryString = params.toString();

        const response = await fetch(
            '/patient/events/timeline' + (queryString ? `?${queryString}` : ''),
            {
                headers: {
                    Accept: 'application/json',
                },
                credentials: 'same-origin',
            },
        );

        if (!response.ok) {
            timelineError.value = `Failed to load events timeline (${response.status})`;
            return;
        }

        const data = (await response.json()) as {
            events: TimelineEventEntry[] | null;
        };

        timelineEvents.value = data.events ?? [];
    } catch {
        timelineError.value =
            'A network error occurred while loading your events timeline.';
    } finally {
        loadingTimeline.value = false;
    }
}

async function reloadTimelineForCurrentFilter() {
    loadingTimeline.value = true;
    timelineError.value = null;

    await loadTimeline();
}

onMounted(() => {
    void loadEnrollment();
    void loadSubscription();
    void loadRecentActivity();
    void reloadTimelineForCurrentFilter();
});
</script>



<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <Card>
                    <CardHeader>
                        <CardTitle>Patient enrollment</CardTitle>
                        <CardDescription>
                            <span v-if="loadingEnrollment">
                                Loading enrollment status...
                            </span>
                            <span v-else-if="enrollmentError">
                                {{ enrollmentError }}
                            </span>
                            <span v-else-if="enrollment && formattedEnrolledAt">
                                Enrolled as patient since
                                {{ formattedEnrolledAt }} (source:
                                {{ enrollment.source }}).
                            </span>
                            <span v-else-if="enrollment">
                                Enrolled as patient (source:
                                {{ enrollment.source }}).
                            </span>
                            <span v-else>
                                You are not yet enrolled as a patient.
                            </span>
                        </CardDescription>
                    </CardHeader>
                    <CardFooter
                        v-if="!loadingEnrollment && !enrollmentError && !enrollment"
                        class="pt-0"
                    >
                        <Button
                            type="button"
                            size="sm"
                            :disabled="startingEnrollment"
                            @click="startEnrollment"
                        >
                            <Spinner
                                v-if="startingEnrollment"
                                class="mr-2 h-4 w-4"
                            />
                            <span v-if="startingEnrollment">
                                Starting enrollment…
                            </span>
                            <span v-else>
                                Start enrollment
                            </span>
                        </Button>
                    </CardFooter>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Subscription</CardTitle>
                        <CardDescription>
                            A quick view of your current TeleMed Pro subscription.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-2 text-sm text-muted-foreground">
                        <div
                            v-if="loadingSubscription"
                            class="flex items-center space-x-2"
                        >
                            <Spinner class="h-4 w-4" />
                            <span>Loading subscription…</span>
                        </div>
                        <p v-else-if="subscriptionError">
                            {{ subscriptionError }}
                        </p>
                        <p v-else-if="!subscription">
                            You don't have an active subscription yet.
                        </p>
                        <div v-else class="space-y-1">
                            <p>
                                <span class="font-medium text-foreground">
                                    {{ subscription.plan_name || 'TeleMed Pro plan' }}
                                </span>
                                <span v-if="subscription.is_trial" class="ml-1">
                                    (trial)
                                </span>
                            </p>
                            <p class="text-xs">
                                Status:
                                <span class="capitalize">
                                    {{ subscription.status.replace('_', ' ') }}
                                </span>
                            </p>
                            <p
                                v-if="subscription.starts_at || subscription.ends_at"
                                class="text-xs"
                            >
                                <span v-if="subscription.starts_at && subscription.ends_at">
                                    {{ formatActivityTimestamp(subscription.starts_at) }}
                                    –
                                    {{ formatActivityTimestamp(subscription.ends_at) }}
                                </span>
                                <span v-else-if="subscription.ends_at">
                                    Through {{ formatActivityTimestamp(subscription.ends_at) }}
                                </span>
                                <span v-else>
                                    Since {{ formatActivityTimestamp(subscription.starts_at) }}
                                </span>
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Next steps</CardTitle>
                        <CardDescription>
                            How this dashboard will evolve as more patient features are added.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-2 text-sm text-muted-foreground">
                        <p>
                            Over time, this area will surface:
                        </p>
                        <ul class="list-disc space-y-1 pl-4">
                            <li>Upcoming telemedicine visits and tasks.</li>
                            <li>Key alerts about your medications and care plan.</li>
                            <li>Shortcuts to documents and recent activity.</li>
                        </ul>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader>
                        <CardTitle>Recent activity</CardTitle>
                        <CardDescription>
                            A quick view of what has been happening in your patient record.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-3 text-sm text-muted-foreground">
                        <div
                            v-if="loadingRecentActivity"
                            class="flex items-center space-x-2"
                        >
                            <Spinner class="h-4 w-4" />
                            <span>Loading recent activity…</span>
                        </div>
                        <p v-else-if="recentActivityError">
                            {{ recentActivityError }}
                        </p>
                        <p v-else-if="!recentActivity.length">
                            No recent activity yet. As you start using TeleMed Pro, events will
                            appear here.
                        </p>
                        <ul v-else class="space-y-2">
                            <li
                                v-for="activity in recentActivity"
                                :key="activity.id"
                                class="flex flex-col"
                            >
                                <span class="font-medium text-foreground">
                                    {{ activity.description }}
                                </span>
                                <span class="text-xs text-muted-foreground">
                                    {{ formatActivityTimestamp(activity.created_at) }}
                                </span>
                            </li>
                        </ul>
                    </CardContent>
                </Card>
            </div>
            <div
                class="relative min-h-screen flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border"
            >
                <PlaceholderPattern />
                <div class="relative z-10 flex h-full flex-col bg-background/80">
                    <div class="border-b border-sidebar-border/60 px-4 py-3">
                        <h2 class="text-sm font-semibold text-foreground">
                            Patient events timeline
                        </h2>
                        <p class="text-xs text-muted-foreground">
                            A chronological view of key events in your patient record.
                        </p>
                    </div>
                    <div class="flex-1 overflow-y-auto px-4 py-3">
                        <div
                            v-if="loadingTimeline"
                            class="flex items-center space-x-2 text-sm text-muted-foreground"
                        >
                            <Spinner class="h-4 w-4" />
                            <span>Loading timeline…</span>
                        </div>
                        <p
                            v-else-if="timelineError"
                            class="text-sm text-destructive"
                        >
                            {{ timelineError }}
                        </p>
                        <div
                            v-else
                            class="flex flex-col gap-3 text-sm"
                        >
                            <div
                                class="flex items-center justify-between text-xs text-muted-foreground"
                            >
                                <span>
                                    Showing {{ filteredTimelineEvents.length }}
                                    {{ filteredTimelineEvents.length === 1 ? 'event' : 'events' }}
                                </span>
                                <div
                                    class="inline-flex items-center gap-1 rounded-md border border-border bg-background/80 p-0.5"
                                >
                                    <button
                                        type="button"
                                        class="rounded px-2 py-1"
                                        :class="selectedTimelineFilter === 'all'
                                            ? 'bg-primary text-primary-foreground'
                                            : 'text-foreground'"
                                        @click="selectedTimelineFilter = 'all'; reloadTimelineForCurrentFilter()"
                                    >
                                        All
                                    </button>
                                    <button
                                        type="button"
                                        class="rounded px-2 py-1"
                                        :class="selectedTimelineFilter === 'enrollment'
                                            ? 'bg-primary text-primary-foreground'
                                            : 'text-foreground'"
                                        @click="selectedTimelineFilter = 'enrollment'; reloadTimelineForCurrentFilter()"
                                    >
                                        Enrollment
                                    </button>
                                    <button
                                        type="button"
                                        class="rounded px-2 py-1"
                                        :class="selectedTimelineFilter === 'other'
                                            ? 'bg-primary text-primary-foreground'
                                            : 'text-foreground'"
                                        @click="selectedTimelineFilter = 'other'; reloadTimelineForCurrentFilter()"
                                    >
                                        Other
                                    </button>
                                </div>
                            </div>

                            <p
                                v-if="!filteredTimelineEvents.length && selectedTimelineFilter === 'all'"
                                class="text-sm text-muted-foreground"
                            >
                                No events yet. As your care journey progresses, events will
                                show up here in order.
                            </p>
                            <p
                                v-else-if="!filteredTimelineEvents.length"
                                class="text-sm text-muted-foreground"
                            >
                                No events match this filter yet.
                            </p>

                            <div
                                v-else
                                class="space-y-4"
                            >
                                <div
                                    v-for="group in groupedTimelineEvents"
                                    :key="group.date"
                                    class="space-y-1"
                                >
                                    <div class="text-xs font-semibold text-muted-foreground">
                                        {{ group.label }}
                                    </div>
                                    <ol class="relative space-y-4 border-l border-border pl-4 text-sm">
                                        <li
                                            v-for="event in group.events"
                                            :key="event.id"
                                            class="relative pl-2"
                                        >
                                            <span
                                                class="absolute -left-[9px] mt-1 h-2 w-2 rounded-full bg-primary"
                                            />
                                            <div class="flex flex-col">
                                                <span class="font-medium text-foreground">
                                                    {{ event.description }}
                                                </span>
                                                <span class="text-xs text-muted-foreground">
                                                    {{ formatActivityTimestamp(event.occurred_at) }}
                                                </span>
                                                <span class="mt-0.5 text-xs text-muted-foreground">
                                                    {{ event.event_type }}
                                                </span>
                                                <span
                                                    v-if="formatTimelineSource(event.source)"
                                                    class="mt-0.5 text-xs text-muted-foreground"
                                                >
                                                    Source: {{ formatTimelineSource(event.source) }}
                                                </span>
                                            </div>
                                        </li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
