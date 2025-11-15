<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';
import { Card, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

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

const formattedEnrolledAt = computed(() => {
    if (!enrollment.value?.enrolled_at) {
        return null;
    }

    return new Date(enrollment.value.enrolled_at).toLocaleString();
});

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
                class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border"
            >
                <PlaceholderPattern />
            </div>
        </div>
    </AppLayout>
</template>
