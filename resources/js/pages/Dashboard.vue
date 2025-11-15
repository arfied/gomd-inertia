<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import ProgressSpinner from 'primevue/progressspinner';

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

const enrollment = ref<PatientEnrollment | null>(null);
const loadingEnrollment = ref(true);
const enrollmentError = ref<string | null>(null);
const startingEnrollment = ref(false);


const formattedEnrolledAt = computed(() => {
    if (!enrollment.value?.enrolled_at) {
        return null;
    }

    return new Date(enrollment.value.enrolled_at).toLocaleString();
});

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

onMounted(async () => {
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
                    <template #title>
                        Patient enrollment
                    </template>

                    <template #content>
                        <p v-if="loadingEnrollment">
                            Loading enrollment status...
                        </p>
                        <p v-else-if="enrollmentError" class="text-red-600">
                            {{ enrollmentError }}
                        </p>
                        <p v-else-if="enrollment && formattedEnrolledAt">
                            Enrolled as patient since
                            {{ formattedEnrolledAt }} (source:
                            {{ enrollment.source }}).
                        </p>
                        <p v-else-if="enrollment">
                            Enrolled as patient (source:
                            {{ enrollment.source }}).
                        </p>
                        <p v-else>
                            You are not yet enrolled as a patient.
                        </p>
                    </template>

                    <template #footer>
                        <div
                            v-if="!loadingEnrollment && !enrollmentError && !enrollment"
                            class="flex justify-end pt-2"
                        >
                            <Button
                                type="button"
                                size="small"
                                :disabled="startingEnrollment"
                                @click="startEnrollment"
                            >
                                <ProgressSpinner
                                    v-if="startingEnrollment"
                                    style="width: 1rem; height: 1rem"
                                    strokeWidth="8"
                                    animationDuration="1s"
                                    class="mr-2"
                                />
                                <span v-if="startingEnrollment">
                                    Starting enrollment…
                                </span>
                                <span v-else>
                                    Start enrollment
                                </span>
                            </Button>
                        </div>
                    </template>
                </Card>

                <div
                    class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
                >
                    <PlaceholderPattern />
                </div>
                <div
                    class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
                >
                    <PlaceholderPattern />
                </div>
            </div>
            <div
                class="relative min-h-screen flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border"
            >
                <PlaceholderPattern />
            </div>
        </div>
    </AppLayout>
</template>
