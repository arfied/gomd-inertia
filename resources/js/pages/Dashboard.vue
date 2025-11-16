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

interface RecentActivityEntry {
    id: number;
    type: string;
    description: string;
    metadata: Record<string, unknown> | null;
    created_at: string | null;
}

const enrollment = ref<PatientEnrollment | null>(null);
const loadingEnrollment = ref(true);
const enrollmentError = ref<string | null>(null);
const startingEnrollment = ref(false);

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

onMounted(() => {
    void loadEnrollment();
    void loadRecentActivity();
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
                <div
                    class="pointer-events-none absolute inset-0 flex items-center justify-center"
                >
                    <div
                        class="rounded-lg bg-background/80 px-4 py-2 text-sm text-muted-foreground shadow-sm"
                    >
                        Patient events timeline and richer dashboard details will appear here
                        as more projections and UI are implemented.
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
