<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';

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

interface PatientDocument {
    id: number;
    patient_id: number | null;
    doctor_id: number | null;
    record_type: string | null;
    description: string | null;
    record_date: string | null;
    file_path: string | null;
    created_at: string | null;
    updated_at: string | null;
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

const filteredOrderTimelineEvents = computed(() => {
    if (selectedOrderTimelineFilter.value === 'all') {
        return orderTimelineEvents.value;
    }

    return orderTimelineEvents.value.filter(
        (event) => event.event_type === `order.${selectedOrderTimelineFilter.value}`,
    );
});

const groupedOrderTimelineEvents = computed<TimelineEventGroup[]>(() => {
    const groups = new Map<string, TimelineEventGroup>();

    for (const event of filteredOrderTimelineEvents.value) {
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
const cancellingSubscription = ref(false);
const cancelSubscriptionError = ref<string | null>(null);

const canCancelSubscription = computed(() => {
    if (!subscription.value) {
        return false;
    }

    return (
        subscription.value.status === 'active' ||
        subscription.value.status === 'pending_payment'
    );
});

const documents = ref<PatientDocument[]>([]);
const loadingDocuments = ref(true);
const documentsError = ref<string | null>(null);

const showUploadForm = ref(false);
const uploadingDocument = ref(false);
const uploadError = ref<string | null>(null);

const uploadForm = ref<{
    record_type: string;
    description: string;
    record_date: string;
    file: File | null;
}>({
    record_type: '',
    description: '',
    record_date: '',
    file: null,
});

const recentActivity = ref<RecentActivityEntry[]>([]);
const loadingRecentActivity = ref(true);
const recentActivityError = ref<string | null>(null);

interface OrderTimelineEventEntry {
    id: number;
    aggregate_uuid: string;
    event_type: string;
    description: string;
    payload: Record<string, unknown> | null;
    metadata: Record<string, unknown> | null;
    occurred_at: string | null;
}

const orderTimelineEvents = ref<OrderTimelineEventEntry[]>([]);
const loadingOrderTimeline = ref(true);
const orderTimelineError = ref<string | null>(null);
const selectedOrderTimelineFilter = ref<'all' | 'created' | 'prescribed' | 'fulfilled' | 'cancelled'>('all');

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

function formatDate(isoString: string | null): string {
    if (!isoString) {
        return '';
    }

    const date = new Date(isoString);

    if (Number.isNaN(date.getTime())) {
        return isoString;
    }

    return date.toLocaleDateString();
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

async function extractErrorMessage(defaultMessage: string, response: Response): Promise<string> {
    try {
        const data = (await response.json()) as any;

        if (data && typeof data.message === 'string') {
            return data.message;
        }

        if (data && data.errors && typeof data.errors === 'object') {
            const firstError = Object.values(data.errors as Record<string, string[]>)[0]?.[0];

            if (typeof firstError === 'string') {
                return firstError;
            }
        }
    } catch {
        // ignore
    }

    return defaultMessage;
}

interface PatientMedicalHistorySnapshot {
    allergies: Array<{
        id: number;
        allergen: string | null;
        reaction: string | null;
        severity: string | null;
        notes: string | null;
    }>;
    conditions: Array<{
        id: number;
        condition_name: string | null;
        diagnosed_at: string | null;
        had_condition_before: boolean;
        is_chronic: boolean;
        notes: string | null;
    }>;
    medications: Array<{
        id: number;
        medication_id: number | null;
        medication_name: string | null;
        start_date: string | null;
        end_date: string | null;
        dosage: string | null;
        frequency: string | null;
        notes: string | null;
    }>;
    surgical_history: {
        past_injuries: boolean;
        past_injuries_details: string | null;
        surgery: boolean;
        surgery_details: string | null;
        chronic_conditions_details: string | null;
    } | null;
    family_history: {
        chronic_pain: boolean;
        chronic_pain_details: string | null;
        conditions: Array<{
            id: number;
            name: string | null;
        }>;
    } | null;
}

const medicalHistory = ref<PatientMedicalHistorySnapshot | null>(null);
const loadingMedicalHistory = ref(false);
const medicalHistoryError = ref<string | null>(null);

const showAllergyForm = ref(false);
const showConditionForm = ref(false);
const showMedicationForm = ref(false);
const showVisitSummaryForm = ref(false);

const submittingAllergy = ref(false);
const submittingCondition = ref(false);
const submittingMedication = ref(false);
const submittingVisitSummary = ref(false);

const allergyError = ref<string | null>(null);
const conditionError = ref<string | null>(null);
const medicationError = ref<string | null>(null);
const visitSummaryError = ref<string | null>(null);

const allergyForm = ref({
    allergen: '',
    reaction: '',
    severity: '',
    notes: '',
});

const conditionForm = ref({
    condition_name: '',
    diagnosed_at: '',
    had_condition_before: false,
    is_chronic: false,
    notes: '',
});

const medicationForm = ref({
    medication_id: '',
    dosage: '',
    frequency: '',
    start_date: '',
    end_date: '',
    notes: '',
});

const visitSummaryForm = ref({
    past_injuries: false,
    past_injuries_details: '',
    surgery: false,
    surgery_details: '',
    chronic_conditions_details: '',
    chronic_pain: false,
    chronic_pain_details: '',
    family_history_conditions_text: '',
});

async function loadMedicalHistory(): Promise<void> {
    loadingMedicalHistory.value = true;
    medicalHistoryError.value = null;

    try {
        const response = await fetch('/patient/medical-history', {
            headers: {
                Accept: 'application/json',
            },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            medicalHistoryError.value = `Failed to load medical history (${response.status})`;
            return;
        }

        const data = (await response.json()) as { medical_history: PatientMedicalHistorySnapshot | null };
        medicalHistory.value = data.medical_history ?? null;
    } catch {
        medicalHistoryError.value = 'A network error occurred while loading your medical history.';
    } finally {
        loadingMedicalHistory.value = false;
    }
}

async function submitAllergy(): Promise<void> {
    submittingAllergy.value = true;
    allergyError.value = null;

    try {
        const xsrfToken = getXsrfToken();

        const response = await fetch('/patient/medical-history/allergies', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                ...(xsrfToken ? { 'X-XSRF-TOKEN': xsrfToken } : {}),
            },
            credentials: 'same-origin',
            body: JSON.stringify(allergyForm.value),
        });

        if (!response.ok) {
            allergyError.value = await extractErrorMessage('Failed to save allergy.', response);
            return;
        }

        const data = (await response.json()) as { medical_history: PatientMedicalHistorySnapshot | null };
        medicalHistory.value = data.medical_history ?? null;

        showAllergyForm.value = false;
        allergyForm.value = {
            allergen: '',
            reaction: '',
            severity: '',
            notes: '',
        };
    } catch {
        allergyError.value = 'A network error occurred while saving allergy.';
    } finally {
        submittingAllergy.value = false;
    }
}

async function submitCondition(): Promise<void> {
    submittingCondition.value = true;
    conditionError.value = null;

    try {
        const xsrfToken = getXsrfToken();

        const response = await fetch('/patient/medical-history/conditions', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                ...(xsrfToken ? { 'X-XSRF-TOKEN': xsrfToken } : {}),
            },
            credentials: 'same-origin',
            body: JSON.stringify(conditionForm.value),
        });

        if (!response.ok) {
            conditionError.value = await extractErrorMessage('Failed to save condition.', response);
            return;
        }

        const data = (await response.json()) as { medical_history: PatientMedicalHistorySnapshot | null };
        medicalHistory.value = data.medical_history ?? null;

        showConditionForm.value = false;
        conditionForm.value = {
            condition_name: '',
            diagnosed_at: '',
            had_condition_before: false,
            is_chronic: false,
            notes: '',
        };
    } catch {
        conditionError.value = 'A network error occurred while saving condition.';
    } finally {
        submittingCondition.value = false;
    }
}

async function submitMedication(): Promise<void> {
    submittingMedication.value = true;
    medicationError.value = null;

    const payload = {
        ...medicationForm.value,
        medication_id: medicationForm.value.medication_id
            ? Number(medicationForm.value.medication_id)
            : null,
    };

    try {
        const xsrfToken = getXsrfToken();

        const response = await fetch('/patient/medical-history/medications', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                ...(xsrfToken ? { 'X-XSRF-TOKEN': xsrfToken } : {}),
            },
            credentials: 'same-origin',
            body: JSON.stringify(payload),
        });

        if (!response.ok) {
            medicationError.value = await extractErrorMessage('Failed to save medication.', response);
            return;
        }

        const data = (await response.json()) as { medical_history: PatientMedicalHistorySnapshot | null };
        medicalHistory.value = data.medical_history ?? null;

        showMedicationForm.value = false;
        medicationForm.value = {
            medication_id: '',
            dosage: '',
            frequency: '',
            start_date: '',
            end_date: '',
            notes: '',
        };
    } catch {
        medicationError.value = 'A network error occurred while saving medication.';
    } finally {
        submittingMedication.value = false;
    }
}

async function submitVisitSummary(): Promise<void> {
    submittingVisitSummary.value = true;
    visitSummaryError.value = null;

    const familyConditions = visitSummaryForm.value.family_history_conditions_text
        .split('\n')
        .map((line) => line.trim())
        .filter((line) => line.length > 0);

    const payload: Record<string, unknown> = {
        past_injuries: visitSummaryForm.value.past_injuries,
        past_injuries_details: visitSummaryForm.value.past_injuries_details || null,
        surgery: visitSummaryForm.value.surgery,
        surgery_details: visitSummaryForm.value.surgery_details || null,
        chronic_conditions_details: visitSummaryForm.value.chronic_conditions_details || null,
        chronic_pain: visitSummaryForm.value.chronic_pain,
        chronic_pain_details: visitSummaryForm.value.chronic_pain_details || null,
    };

    if (familyConditions.length > 0) {
        payload.family_history_conditions = familyConditions;
    }

    try {
        const xsrfToken = getXsrfToken();

        const response = await fetch('/patient/medical-history/visit-summary', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                ...(xsrfToken ? { 'X-XSRF-TOKEN': xsrfToken } : {}),
            },
            credentials: 'same-origin',
            body: JSON.stringify(payload),
        });

        if (!response.ok) {
            visitSummaryError.value = await extractErrorMessage('Failed to save visit summary.', response);
            return;
        }

        const data = (await response.json()) as { medical_history: PatientMedicalHistorySnapshot | null };
        medicalHistory.value = data.medical_history ?? null;

        showVisitSummaryForm.value = false;
        visitSummaryForm.value = {
            past_injuries: false,
            past_injuries_details: '',
            surgery: false,
            surgery_details: '',
            chronic_conditions_details: '',
            chronic_pain: false,
            chronic_pain_details: '',
            family_history_conditions_text: '',
        };
    } catch {
        visitSummaryError.value = 'A network error occurred while saving visit summary.';
    } finally {
        submittingVisitSummary.value = false;
    }
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
async function loadDocuments(): Promise<void> {
    try {
        const response = await fetch('/patient/documents', {
            headers: {
                Accept: 'application/json',
            },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            documentsError.value = `Failed to load documents (${response.status})`;
            return;
        }

        const data = (await response.json()) as {
            documents: PatientDocument[] | null;
        };

        documents.value = data.documents ?? [];
    } catch {
        documentsError.value =
            'A network error occurred while loading your documents.';
    } finally {
        loadingDocuments.value = false;
    }
}

function onDocumentFileSelected(event: Event): void {
    const target = event.target as HTMLInputElement | null;

    if (!target || !target.files || target.files.length === 0) {
        uploadForm.value.file = null;
        return;
    }

    uploadForm.value.file = target.files[0] ?? null;
}

async function submitDocument(): Promise<void> {
    if (uploadingDocument.value) {
        return;
    }

    if (!uploadForm.value.file) {
        uploadError.value = 'Please choose a file to upload.';
        return;
    }

    uploadingDocument.value = true;
    uploadError.value = null;

    try {
        const xsrfToken = getXsrfToken();

        const formData = new FormData();
        formData.append('record_type', uploadForm.value.record_type);
        formData.append('description', uploadForm.value.description);

        if (uploadForm.value.record_date) {
            formData.append('record_date', uploadForm.value.record_date);
        }

        formData.append('file', uploadForm.value.file);

        const response = await fetch('/patient/documents', {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                ...(xsrfToken ? { 'X-XSRF-TOKEN': xsrfToken } : {}),
            },
            credentials: 'same-origin',
            body: formData,
        });

        if (!response.ok) {
            uploadError.value = await extractErrorMessage(
                'Failed to upload document.',
                response,
            );
            return;
        }

        const data = (await response.json()) as { document: PatientDocument | null };

        if (data.document) {
            documents.value = [
                data.document,
                ...documents.value.filter((existing) => existing.id !== data.document!.id),
            ];
        } else {
            await loadDocuments();
        }

        showUploadForm.value = false;
        uploadForm.value = {
            record_type: '',
            description: '',
            record_date: '',
            file: null,
        };
    } catch {
        uploadError.value =
            'A network error occurred while uploading your document.';
    } finally {
        uploadingDocument.value = false;
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

async function cancelSubscription() {
    if (!subscription.value || cancellingSubscription.value) {
        return;
    }

    cancellingSubscription.value = true;
    cancelSubscriptionError.value = null;

    try {
        const xsrfToken = getXsrfToken();

        const response = await fetch('/patient/subscription/cancel', {
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
            cancelSubscriptionError.value = `Failed to cancel subscription (${response.status})`;
            return;
        }

        const data = (await response.json()) as {
            subscription: PatientSubscription | null;
        };

        subscription.value = data.subscription ?? null;
    } catch {
        cancelSubscriptionError.value =
            'A network error occurred while cancelling your subscription.';
    } finally {
        cancellingSubscription.value = false;
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

async function loadOrderTimeline() {
    try {
        const response = await fetch('/patient/orders/timeline', {
            headers: {
                Accept: 'application/json',
            },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            orderTimelineError.value = `Failed to load order timeline (${response.status})`;
            return;
        }

        const data = (await response.json()) as {
            events: OrderTimelineEventEntry[] | null;
        };

        orderTimelineEvents.value = data.events ?? [];
    } catch {
        orderTimelineError.value =
            'A network error occurred while loading your order timeline.';
    } finally {
        loadingOrderTimeline.value = false;
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
    void loadDocuments();
    void loadMedicalHistory();
    void loadRecentActivity();
    void loadOrderTimeline();
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
                            <p v-if="cancelSubscriptionError" class="text-xs text-destructive">
                                {{ cancelSubscriptionError }}
                            </p>
                        </div>
                    </CardContent>
                    <CardFooter v-if="subscription && canCancelSubscription">
                        <Dialog>
                            <DialogTrigger as-child>
                                <Button
                                    type="button"
                                    size="sm"
                                    variant="outline"
                                    :disabled="cancellingSubscription"
                                >
                                    <Spinner
                                        v-if="cancellingSubscription"
                                        class="mr-2 h-4 w-4"
                                    />
                                    <span v-if="cancellingSubscription">
                                        Cancelling…
                                    </span>
                                    <span v-else>
                                        Cancel subscription
                                    </span>
                                </Button>
                            </DialogTrigger>
                            <DialogContent>
                                <DialogHeader>
                                    <DialogTitle>Cancel subscription</DialogTitle>
                                    <DialogDescription>
                                        This will cancel your current TeleMed Pro subscription. You may lose access at the end of your current billing period depending on your plan.
                                    </DialogDescription>
                                </DialogHeader>
                                <DialogFooter>
                                    <DialogClose as-child>
                                        <Button
                                            type="button"
                                            variant="outline"
                                        >
                                            Keep subscription
                                        </Button>
                                    </DialogClose>
                                    <DialogClose as-child>
                                        <Button
                                            type="button"
                                            variant="destructive"
                                            :disabled="cancellingSubscription"
                                            @click="cancelSubscription"
                                        >
                                            <Spinner
                                                v-if="cancellingSubscription"
                                                class="mr-2 h-4 w-4"
                                            />
                                            <span v-if="cancellingSubscription">
                                                Cancelling…
                                            </span>
                                            <span v-else>
                                                Confirm cancel
                                            </span>
                                        </Button>
                                    </DialogClose>
                                </DialogFooter>
                            </DialogContent>
                        </Dialog>
                    </CardFooter>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Documents</CardTitle>
                        <CardDescription>
                            Upload and manage your medical documents.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-3 text-sm text-muted-foreground">
                        <div class="flex items-center justify-between">
                            <p class="font-medium text-foreground">
                                Your documents
                            </p>
                            <Button
                                type="button"
                                size="sm"
                                variant="outline"
                                @click="showUploadForm = !showUploadForm"
                            >
                                {{ showUploadForm ? 'Cancel' : 'Upload' }}
                            </Button>
                        </div>
                        <p
                            v-if="uploadError"
                            class="text-xs text-destructive"
                        >
                            {{ uploadError }}
                        </p>
                        <form
                            v-if="showUploadForm"
                            class="space-y-2"
                            @submit.prevent="submitDocument"
                        >
                            <div class="space-y-1">
                                <Label for="document-record-type">Type</Label>
                                <Input
                                    id="document-record-type"
                                    v-model="uploadForm.record_type"
                                    type="text"
                                    required
                                />
                            </div>
                            <div class="space-y-1">
                                <Label for="document-description">Description</Label>
                                <Input
                                    id="document-description"
                                    v-model="uploadForm.description"
                                    type="text"
                                    required
                                />
                            </div>
                            <div class="space-y-1">
                                <Label for="document-record-date">Date</Label>
                                <Input
                                    id="document-record-date"
                                    v-model="uploadForm.record_date"
                                    type="date"
                                />
                            </div>
                            <div class="space-y-1">
                                <Label for="document-file">File</Label>
                                <Input
                                    id="document-file"
                                    type="file"
                                    required
                                    @change="onDocumentFileSelected"
                                />
                            </div>
                            <div class="flex justify-end">
                                <Button
                                    type="submit"
                                    size="sm"
                                    :disabled="uploadingDocument"
                                >
                                    <Spinner
                                        v-if="uploadingDocument"
                                        class="mr-2 h-4 w-4"
                                    />
                                    <span v-if="uploadingDocument">Uploading…</span>
                                    <span v-else>Upload document</span>
                                </Button>
                            </div>
                        </form>
                        <div
                            v-if="loadingDocuments"
                            class="flex items-center space-x-2"
                        >
                            <Spinner class="h-4 w-4" />
                            <span>Loading documents</span>
                        </div>
                        <p
                            v-else-if="documentsError"
                            class="text-sm text-destructive"
                        >
                            {{ documentsError }}
                        </p>
                        <p
                            v-else-if="!documents.length"
                            class="text-xs text-muted-foreground"
                        >
                            You haven't uploaded any documents yet.
                        </p>
                        <ul
                            v-else
                            class="space-y-1 text-xs"
                        >
                            <li
                                v-for="document in documents"
                                :key="document.id"
                                class="flex items-center justify-between gap-2"
                            >
                                <div class="flex flex-col">
                                    <span class="font-medium text-foreground">
                                        {{ document.record_type || 'Document' }}
                                    </span>
                                    <span
                                        v-if="document.description"
                                        class="text-xs text-muted-foreground"
                                    >
                                        {{ document.description }}
                                    </span>
                                    <span
                                        v-if="document.record_date"
                                        class="text-xs text-muted-foreground"
                                    >
                                        {{ formatDate(document.record_date) }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <a
                                        v-if="document.file_path"
                                        :href="`/storage/${document.file_path}`"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="text-xs text-primary hover:underline"
                                    >
                                        View
                                    </a>
                                </div>
                            </li>
                        </ul>
                    </CardContent>
                </Card>
                <Card class="md:col-span-3">
                    <CardHeader>
                        <CardTitle>Medical history</CardTitle>
                        <CardDescription>
                            View and update key parts of your medical history.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4 text-sm text-muted-foreground">
                        <div
                            v-if="loadingMedicalHistory"
                            class="flex items-center space-x-2"
                        >
                            <Spinner class="h-4 w-4" />
                            <span>Loading medical history…</span>
                        </div>
                        <p
                            v-else-if="medicalHistoryError"
                            class="text-sm text-destructive"
                        >
                            {{ medicalHistoryError }}
                        </p>
                        <p v-else-if="!medicalHistory">
                            No medical history recorded yet.
                        </p>
                        <div v-else class="space-y-4">
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <p class="font-semibold text-foreground">Allergies</p>
                                    <Button
                                        type="button"
                                        size="sm"
                                        variant="outline"
                                        @click="showAllergyForm = !showAllergyForm"
                                    >
                                        {{ showAllergyForm ? 'Cancel' : 'Add' }}
                                    </Button>
                                </div>
                                <p
                                    v-if="allergyError"
                                    class="text-xs text-destructive"
                                >
                                    {{ allergyError }}
                                </p>
                                <form
                                    v-if="showAllergyForm"
                                    class="space-y-2"
                                    @submit.prevent="submitAllergy"
                                >
                                    <div class="space-y-1">
                                        <Label for="self-allergen">Allergen</Label>
                                        <Input
                                            id="self-allergen"
                                            v-model="allergyForm.allergen"
                                            type="text"
                                            required
                                        />
                                    </div>
                                    <div class="space-y-1">
                                        <Label for="self-reaction">Reaction</Label>
                                        <Input
                                            id="self-reaction"
                                            v-model="allergyForm.reaction"
                                            type="text"
                                        />
                                    </div>
                                    <div class="space-y-1">
                                        <Label for="self-severity">Severity</Label>
                                        <Input
                                            id="self-severity"
                                            v-model="allergyForm.severity"
                                            type="text"
                                        />
                                    </div>
                                    <div class="space-y-1">
                                        <Label for="self-allergy-notes">Notes</Label>
                                        <Input
                                            id="self-allergy-notes"
                                            v-model="allergyForm.notes"
                                            type="text"
                                        />
                                    </div>
                                    <div class="flex justify-end">
                                        <Button
                                            type="submit"
                                            size="sm"
                                            :disabled="submittingAllergy"
                                        >
                                            <Spinner
                                                v-if="submittingAllergy"
                                                class="mr-2 h-4 w-4"
                                            />
                                            <span v-if="submittingAllergy">Saving…</span>
                                            <span v-else>Save allergy</span>
                                        </Button>
                                    </div>
                                </form>
                                <ul
                                    v-if="medicalHistory.allergies.length"
                                    class="space-y-1 text-xs"
                                >
                                    <li
                                        v-for="allergy in medicalHistory.allergies"
                                        :key="allergy.id"
                                    >
                                        <span class="font-medium text-foreground">
                                            {{ allergy.allergen || 'Unknown allergen' }}
                                        </span>
                                        <span v-if="allergy.reaction">
                                            – {{ allergy.reaction }}
                                        </span>
                                        <span
                                            v-if="allergy.severity"
                                            class="ml-1 uppercase"
                                        >
                                            ({{ allergy.severity }})
                                        </span>
                                        <span
                                            v-if="allergy.notes"
                                            class="block text-muted-foreground"
                                        >
                                            {{ allergy.notes }}
                                        </span>
                                    </li>
                                </ul>
                                <p v-else class="text-xs text-muted-foreground">
                                    No allergies recorded.
                                </p>
                            </div>

                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <p class="font-semibold text-foreground">Conditions</p>
                                    <Button
                                        type="button"
                                        size="sm"
                                        variant="outline"
                                        @click="showConditionForm = !showConditionForm"
                                    >
                                        {{ showConditionForm ? 'Cancel' : 'Add' }}
                                    </Button>
                                </div>
                                <p
                                    v-if="conditionError"
                                    class="text-xs text-destructive"
                                >
                                    {{ conditionError }}
                                </p>
                                <form
                                    v-if="showConditionForm"
                                    class="space-y-2"
                                    @submit.prevent="submitCondition"
                                >
                                    <div class="space-y-1">
                                        <Label for="self-condition-name">Condition</Label>
                                        <Input
                                            id="self-condition-name"
                                            v-model="conditionForm.condition_name"
                                            type="text"
                                            required
                                        />
                                    </div>
                                    <div class="space-y-1">
                                        <Label for="self-diagnosed-at">Diagnosed at</Label>
                                        <Input
                                            id="self-diagnosed-at"
                                            v-model="conditionForm.diagnosed_at"
                                            type="date"
                                        />
                                    </div>
                                    <div class="flex flex-wrap gap-4 text-xs">
                                        <label class="inline-flex items-center gap-2">
                                            <input
                                                v-model="conditionForm.had_condition_before"
                                                type="checkbox"
                                            >
                                            <span>Had condition before</span>
                                        </label>
                                        <label class="inline-flex items-center gap-2">
                                            <input
                                                v-model="conditionForm.is_chronic"
                                                type="checkbox"
                                            >
                                            <span>Chronic</span>
                                        </label>
                                    </div>
                                    <div class="space-y-1">
                                        <Label for="self-condition-notes">Notes</Label>
                                        <Input
                                            id="self-condition-notes"
                                            v-model="conditionForm.notes"
                                            type="text"
                                        />
                                    </div>
                                    <div class="flex justify-end">
                                        <Button
                                            type="submit"
                                            size="sm"
                                            :disabled="submittingCondition"
                                        >
                                            <Spinner
                                                v-if="submittingCondition"
                                                class="mr-2 h-4 w-4"
                                            />
                                            <span v-if="submittingCondition">Saving…</span>
                                            <span v-else>Save condition</span>
                                        </Button>
                                    </div>
                                </form>
                                <ul
                                    v-if="medicalHistory.conditions.length"
                                    class="space-y-1 text-xs"
                                >
                                    <li
                                        v-for="condition in medicalHistory.conditions"
                                        :key="condition.id"
                                    >
                                        <span class="font-medium text-foreground">
                                            {{ condition.condition_name || 'Condition' }}
                                        </span>
                                        <span
                                            v-if="condition.diagnosed_at"
                                            class="ml-1 text-muted-foreground"
                                        >
                                            (diagnosed {{ formatDate(condition.diagnosed_at) }})
                                        </span>
                                        <span class="block text-muted-foreground">
                                            <span v-if="condition.had_condition_before">Had before. </span>
                                            <span v-if="condition.is_chronic">Chronic.</span>
                                            <span v-if="condition.notes">
                                                {{ condition.notes }}
                                            </span>
                                        </span>
                                    </li>
                                </ul>
                                <p v-else class="text-xs text-muted-foreground">
                                    No conditions recorded.
                                </p>
                            </div>

                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <p class="font-semibold text-foreground">Medications</p>
                                    <Button
                                        type="button"
                                        size="sm"
                                        variant="outline"
                                        @click="showMedicationForm = !showMedicationForm"
                                    >
                                        {{ showMedicationForm ? 'Cancel' : 'Add' }}
                                    </Button>
                                </div>
                                <p
                                    v-if="medicationError"
                                    class="text-xs text-destructive"
                                >
                                    {{ medicationError }}
                                </p>
                                <form
                                    v-if="showMedicationForm"
                                    class="space-y-2"
                                    @submit.prevent="submitMedication"
                                >
                                    <div class="space-y-1">
                                        <Label for="self-medication-id">Medication ID</Label>
                                        <Input
                                            id="self-medication-id"
                                            v-model="medicationForm.medication_id"
                                            type="number"
                                            min="1"
                                            required
                                        />
                                    </div>
                                    <div class="space-y-1">
                                        <Label for="self-dosage">Dosage</Label>
                                        <Input
                                            id="self-dosage"
                                            v-model="medicationForm.dosage"
                                            type="text"
                                            required
                                        />
                                    </div>
                                    <div class="space-y-1">
                                        <Label for="self-frequency">Frequency</Label>
                                        <Input
                                            id="self-frequency"
                                            v-model="medicationForm.frequency"
                                            type="text"
                                            required
                                        />
                                    </div>
                                    <div class="grid gap-2 md:grid-cols-2">
                                        <div class="space-y-1">
                                            <Label for="self-start-date">Start date</Label>
                                            <Input
                                                id="self-start-date"
                                                v-model="medicationForm.start_date"
                                                type="date"
                                            />
                                        </div>
                                        <div class="space-y-1">
                                            <Label for="self-end-date">End date</Label>
                                            <Input
                                                id="self-end-date"
                                                v-model="medicationForm.end_date"
                                                type="date"
                                            />
                                        </div>
                                    </div>
                                    <div class="space-y-1">
                                        <Label for="self-medication-notes">Notes</Label>
                                        <Input
                                            id="self-medication-notes"
                                            v-model="medicationForm.notes"
                                            type="text"
                                        />
                                    </div>
                                    <div class="flex justify-end">
                                        <Button
                                            type="submit"
                                            size="sm"
                                            :disabled="submittingMedication"
                                        >
                                            <Spinner
                                                v-if="submittingMedication"
                                                class="mr-2 h-4 w-4"
                                            />
                                            <span v-if="submittingMedication">Saving…</span>
                                            <span v-else>Save medication</span>
                                        </Button>
                                    </div>
                                </form>
                                <ul
                                    v-if="medicalHistory.medications.length"
                                    class="space-y-1 text-xs"
                                >
                                    <li
                                        v-for="medication in medicalHistory.medications"
                                        :key="medication.id"
                                    >
                                        <span class="font-medium text-foreground">
                                            {{ medication.medication_name || `Medication #${medication.medication_id}` }}
                                        </span>
                                        <span class="block text-muted-foreground">
                                            <span v-if="medication.dosage">
                                                {{ medication.dosage }}
                                            </span>
                                            <span v-if="medication.frequency">
                                                · {{ medication.frequency }}
                                            </span>
                                            <span v-if="medication.start_date">
                                                · From {{ formatDate(medication.start_date) }}
                                            </span>
                                            <span v-if="medication.end_date">
                                                · Until {{ formatDate(medication.end_date) }}
                                            </span>
                                        </span>
                                        <span
                                            v-if="medication.notes"
                                            class="block text-muted-foreground"
                                        >
                                            {{ medication.notes }}
                                        </span>
                                    </li>
                                </ul>
                                <p v-else class="text-xs text-muted-foreground">
                                    No medications recorded.
                                </p>
                            </div>

                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <p class="font-semibold text-foreground">Visit summary</p>
                                    <Button
                                        type="button"
                                        size="sm"
                                        variant="outline"
                                        @click="showVisitSummaryForm = !showVisitSummaryForm"
                                    >
                                        {{ showVisitSummaryForm ? 'Cancel' : 'Edit summary' }}
                                    </Button>
                                </div>
                                <p
                                    v-if="visitSummaryError"
                                    class="text-xs text-destructive"
                                >
                                    {{ visitSummaryError }}
                                </p>
                                <div class="space-y-1 text-xs text-muted-foreground">
                                    <p>
                                        <span class="font-medium text-foreground">Surgical history:</span>
                                        <span v-if="medicalHistory.surgical_history">
                                            <span v-if="medicalHistory.surgical_history.past_injuries">
                                                Past injuries;
                                            </span>
                                            <span v-if="medicalHistory.surgical_history.surgery">
                                                past surgeries.
                                            </span>
                                            <span
                                                v-if="medicalHistory.surgical_history.past_injuries_details"
                                            >
                                                {{ medicalHistory.surgical_history.past_injuries_details }}
                                            </span>
                                            <span
                                                v-if="medicalHistory.surgical_history.surgery_details"
                                            >
                                                {{ medicalHistory.surgical_history.surgery_details }}
                                            </span>
                                            <span
                                                v-if="medicalHistory.surgical_history.chronic_conditions_details"
                                            >
                                                {{ medicalHistory.surgical_history.chronic_conditions_details }}
                                            </span>
                                        </span>
                                        <span v-else>No surgical history recorded yet.</span>
                                    </p>
                                    <p>
                                        <span class="font-medium text-foreground">Family history:</span>
                                        <span v-if="medicalHistory.family_history">
                                            <span>
                                                Chronic pain in family:
                                                {{ medicalHistory.family_history.chronic_pain ? 'yes' : 'no' }}.
                                            </span>
                                            <span
                                                v-if="medicalHistory.family_history.chronic_pain_details"
                                            >
                                                {{ medicalHistory.family_history.chronic_pain_details }}
                                            </span>
                                            <span
                                                v-if="medicalHistory.family_history.conditions.length"
                                            >
                                                Conditions:
                                                {{ medicalHistory.family_history.conditions.map((c) => c.name).join(', ') }}
                                            </span>
                                        </span>
                                        <span v-else>No family history recorded yet.</span>
                                    </p>
                                </div>
                                <form
                                    v-if="showVisitSummaryForm"
                                    class="space-y-2"
                                    @submit.prevent="submitVisitSummary"
                                >
                                    <div class="grid gap-2 md:grid-cols-2">
                                        <div class="space-y-1">
                                            <label class="inline-flex items-center gap-2 text-xs">
                                                <input
                                                    v-model="visitSummaryForm.past_injuries"
                                                    type="checkbox"
                                                >
                                                <span>Past injuries</span>
                                            </label>
                                            <Label for="self-past-injuries-details">Details</Label>
                                            <Input
                                                id="self-past-injuries-details"
                                                v-model="visitSummaryForm.past_injuries_details"
                                                type="text"
                                            />
                                        </div>
                                        <div class="space-y-1">
                                            <label class="inline-flex items-center gap-2 text-xs">
                                                <input
                                                    v-model="visitSummaryForm.surgery"
                                                    type="checkbox"
                                                >
                                                <span>Past surgeries</span>
                                            </label>
                                            <Label for="self-surgery-details">Details</Label>
                                            <Input
                                                id="self-surgery-details"
                                                v-model="visitSummaryForm.surgery_details"
                                                type="text"
                                            />
                                        </div>
                                    </div>
                                    <div class="space-y-1">
                                        <Label for="self-chronic-conditions-details">
                                            Chronic conditions details
                                        </Label>
                                        <Input
                                            id="self-chronic-conditions-details"
                                            v-model="visitSummaryForm.chronic_conditions_details"
                                            type="text"
                                        />
                                    </div>
                                    <div class="space-y-1">
                                        <label class="inline-flex items-center gap-2 text-xs">
                                            <input
                                                v-model="visitSummaryForm.chronic_pain"
                                                type="checkbox"
                                            >
                                            <span>Chronic pain in family</span>
                                        </label>
                                        <Label for="self-chronic-pain-details">Details</Label>
                                        <Input
                                            id="self-chronic-pain-details"
                                            v-model="visitSummaryForm.chronic_pain_details"
                                            type="text"
                                        />
                                    </div>
                                    <div class="space-y-1">
                                        <Label for="self-family-conditions">
                                            Family conditions (one per line)
                                        </Label>
                                        <textarea
                                            id="self-family-conditions"
                                            v-model="visitSummaryForm.family_history_conditions_text"
                                            rows="3"
                                            class="mt-1 w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-sm outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                                        />
                                    </div>
                                    <div class="flex justify-end">
                                        <Button
                                            type="submit"
                                            size="sm"
                                            :disabled="submittingVisitSummary"
                                        >
                                            <Spinner
                                                v-if="submittingVisitSummary"
                                                class="mr-2 h-4 w-4"
                                            />
                                            <span v-if="submittingVisitSummary">Saving…</span>
                                            <span v-else>Save summary</span>
                                        </Button>
                                    </div>
                                </form>
                            </div>
                        </div>
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
            <div
                class="relative min-h-screen flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border"
            >
                <PlaceholderPattern />
                <div class="relative z-10 flex h-full flex-col bg-background/80">
                    <div class="border-b border-sidebar-border/60 px-4 py-3">
                        <h2 class="text-sm font-semibold text-foreground">
                            Order history timeline
                        </h2>
                        <p class="text-xs text-muted-foreground">
                            A chronological view of your medication orders and their status.
                        </p>
                    </div>
                    <div class="flex-1 overflow-y-auto px-4 py-3">
                        <div
                            v-if="loadingOrderTimeline"
                            class="flex items-center space-x-2 text-sm text-muted-foreground"
                        >
                            <Spinner class="h-4 w-4" />
                            <span>Loading order timeline…</span>
                        </div>
                        <p
                            v-else-if="orderTimelineError"
                            class="text-sm text-destructive"
                        >
                            {{ orderTimelineError }}
                        </p>
                        <div
                            v-else
                            class="flex flex-col gap-3 text-sm"
                        >
                            <div
                                class="flex items-center justify-between text-xs text-muted-foreground"
                            >
                                <span>
                                    Showing {{ filteredOrderTimelineEvents.length }}
                                    {{ filteredOrderTimelineEvents.length === 1 ? 'event' : 'events' }}
                                </span>
                                <div
                                    class="inline-flex items-center gap-1 rounded-md border border-border bg-background/80 p-0.5"
                                >
                                    <button
                                        type="button"
                                        class="rounded px-2 py-1"
                                        :class="selectedOrderTimelineFilter === 'all'
                                            ? 'bg-primary text-primary-foreground'
                                            : 'text-foreground'"
                                        @click="selectedOrderTimelineFilter = 'all'"
                                    >
                                        All
                                    </button>
                                    <button
                                        type="button"
                                        class="rounded px-2 py-1"
                                        :class="selectedOrderTimelineFilter === 'created'
                                            ? 'bg-primary text-primary-foreground'
                                            : 'text-foreground'"
                                        @click="selectedOrderTimelineFilter = 'created'"
                                    >
                                        Created
                                    </button>
                                    <button
                                        type="button"
                                        class="rounded px-2 py-1"
                                        :class="selectedOrderTimelineFilter === 'prescribed'
                                            ? 'bg-primary text-primary-foreground'
                                            : 'text-foreground'"
                                        @click="selectedOrderTimelineFilter = 'prescribed'"
                                    >
                                        Prescribed
                                    </button>
                                    <button
                                        type="button"
                                        class="rounded px-2 py-1"
                                        :class="selectedOrderTimelineFilter === 'fulfilled'
                                            ? 'bg-primary text-primary-foreground'
                                            : 'text-foreground'"
                                        @click="selectedOrderTimelineFilter = 'fulfilled'"
                                    >
                                        Fulfilled
                                    </button>
                                    <button
                                        type="button"
                                        class="rounded px-2 py-1"
                                        :class="selectedOrderTimelineFilter === 'cancelled'
                                            ? 'bg-primary text-primary-foreground'
                                            : 'text-foreground'"
                                        @click="selectedOrderTimelineFilter = 'cancelled'"
                                    >
                                        Cancelled
                                    </button>
                                </div>
                            </div>

                            <p
                                v-if="!filteredOrderTimelineEvents.length && selectedOrderTimelineFilter === 'all'"
                                class="text-sm text-muted-foreground"
                            >
                                No orders yet. When you place medication orders, they will
                                appear here in chronological order.
                            </p>
                            <p
                                v-else-if="!filteredOrderTimelineEvents.length"
                                class="text-sm text-muted-foreground"
                            >
                                No events match this filter yet.
                            </p>

                            <div
                                v-else
                                class="space-y-4"
                            >
                                <div
                                    v-for="group in groupedOrderTimelineEvents"
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
