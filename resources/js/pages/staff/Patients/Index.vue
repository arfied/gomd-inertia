<script setup lang="ts">
import { onMounted, ref, computed } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import type { BreadcrumbItem } from '@/types'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Spinner } from '@/components/ui/spinner'
import { Badge } from '@/components/ui/badge'

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Patients', href: '/dashboard/patients' },
]

interface PatientSubscriptionSummary {
    status: string
    plan_name: string | null
    is_trial: boolean
}

interface PatientListItem {
    patient_uuid: string
    user_id: number
    fname: string | null
    lname: string | null
    email: string | null
    status: string | null
    enrolled_at: string | null
    subscription: PatientSubscriptionSummary | null
    has_documents: boolean
    has_medical_history: boolean
    has_active_subscription: boolean
}

interface PatientDemographics {
    gender: string | null
    dob: string | null
    address1: string | null
    address2: string | null
    city: string | null
    state: string | null
    zip: string | null
    phone: string | null
    mobile_phone: string | null
}

interface PatientEnrollmentDetail {
    source: string | null
    metadata: Record<string, unknown> | null
    enrolled_at: string | null
}

interface PatientSubscriptionDetail extends PatientSubscriptionSummary {
    id: number | null
    starts_at: string | null
    ends_at: string | null
}

interface PatientDocument {
    id: number
    patient_id: number | null
    doctor_id: number | null
    record_type: string | null
    description: string | null
    record_date: string | null
    file_path: string | null
    created_at: string | null
    updated_at: string | null
}

interface PatientMedicalHistorySnapshot {
    allergies: Array<{
        id: number
        allergen: string | null
        reaction: string | null
        severity: string | null
        notes: string | null
    }>
    conditions: Array<{
        id: number
        condition_name: string | null
        diagnosed_at: string | null
        had_condition_before: boolean
        is_chronic: boolean
        notes: string | null
    }>
    medications: Array<{
        id: number
        medication_id: number | null
        medication_name: string | null
        start_date: string | null
        end_date: string | null
        dosage: string | null
        frequency: string | null
        notes: string | null
    }>
    surgical_history: {
        past_injuries: boolean
        past_injuries_details: string | null
        surgery: boolean
        surgery_details: string | null
        chronic_conditions_details: string | null
    } | null
    family_history: {
        chronic_pain: boolean
        chronic_pain_details: string | null
        conditions: Array<{
            id: number
            name: string | null
        }>
    } | null
}

interface PatientDetail extends Omit<PatientListItem, 'subscription'> {
    demographics: PatientDemographics | null
    enrollment: PatientEnrollmentDetail | null
    subscription: PatientSubscriptionDetail | null
    medical_history: PatientMedicalHistorySnapshot | null
}

type BadgeVariant = 'default' | 'secondary' | 'destructive' | 'outline'

function statusBadgeVariant(status: string | null): BadgeVariant {
    if (!status) return 'outline'
    const s = status.toLowerCase()
    if (s === 'active') return 'default'
    if (s.includes('trial') || s.startsWith('pending')) return 'secondary'
    if (s === 'cancelled' || s === 'canceled' || s === 'expired') return 'destructive'
    return 'outline'
}

function subscriptionBadgeVariant(status: string | null): BadgeVariant {
    if (!status) return 'outline'
    const s = status.toLowerCase()
    if (s === 'active') return 'default'
    if (s === 'pending_payment') return 'secondary'
    if (s === 'cancelled' || s === 'canceled' || s === 'expired') return 'destructive'
    return 'outline'
}

const patients = ref<PatientListItem[]>([])
const meta = ref<{ current_page: number; per_page: number; next_page_url: string | null; prev_page_url: string | null } | null>(null)
const loadingList = ref(true)
const listError = ref<string | null>(null)

const search = ref('')
const perPage = ref(15)

const filterHasDocuments = ref(false)
const filterHasMedicalHistory = ref(false)
const filterHasActiveSubscription = ref(false)

const count = ref<number | null>(null)
const loadingCount = ref(false)
const countError = ref<string | null>(null)

const selectedPatient = ref<PatientDetail | null>(null)
const loadingDetail = ref(false)
const detailError = ref<string | null>(null)
const showAllergyForm = ref(false)
const allergyForm = ref({
    allergen: '',
    reaction: '',
    severity: '',
    notes: '',
})
const submittingAllergy = ref(false)
const allergyError = ref<string | null>(null)

const showConditionForm = ref(false)
const conditionForm = ref({
    condition_name: '',
    diagnosed_at: '',
    notes: '',
    had_condition_before: false,
    is_chronic: false,
})
const submittingCondition = ref(false)
const conditionError = ref<string | null>(null)

const showMedicationForm = ref(false)
const medicationForm = ref({
    medication_id: '',
    dosage: '',
    frequency: '',
    start_date: '',
    end_date: '',
    notes: '',
})
const submittingMedication = ref(false)
const medicationError = ref<string | null>(null)

const showVisitSummaryForm = ref(false)
const visitSummaryForm = ref({
    past_injuries: false,
    past_injuries_details: '',
    surgery: false,
    surgery_details: '',
    chronic_conditions_details: '',
    chronic_pain: false,
    chronic_pain_details: '',
    family_history_conditions_text: '',
})
const submittingVisitSummary = ref(false)
const visitSummaryError = ref<string | null>(null)

const patientDocuments = ref<PatientDocument[]>([])
const loadingDocuments = ref(false)
const documentsError = ref<string | null>(null)

const showDocumentUploadForm = ref(false)
const uploadingDocument = ref(false)
const documentUploadError = ref<string | null>(null)

const documentUploadForm = ref<{
    record_type: string
    description: string
    record_date: string
    file: File | null
}>({
    record_type: '',
    description: '',
    record_date: '',
    file: null,
})

interface OrderTimelineEventEntry {
    id: number
    aggregate_uuid: string
    event_type: string
    description: string
    payload: Record<string, unknown> | null
    metadata: Record<string, unknown> | null
    occurred_at: string | null
}

const patientOrderTimelineEvents = ref<OrderTimelineEventEntry[]>([])
const loadingOrderTimeline = ref(false)
const orderTimelineError = ref<string | null>(null)
const selectedOrderTimelineFilter = ref<'all' | 'created' | 'prescribed' | 'fulfilled' | 'cancelled'>('all')

async function extractErrorMessage(defaultMessage: string, response: Response): Promise<string> {
    try {
        const data = (await response.json()) as any

        if (data && typeof data.message === 'string') {
            return data.message
        }

        if (data && data.errors && typeof data.errors === 'object') {
            const firstError = Object.values(data.errors as Record<string, string[]>)[0]?.[0]
            if (typeof firstError === 'string') {
                return firstError
            }
        }
    } catch {
        // ignore
    }

    return defaultMessage
}


const hasNextPage = computed(() => !!meta.value?.next_page_url)
const hasPrevPage = computed(() => !!meta.value?.prev_page_url)

const filteredOrderTimelineEvents = computed(() => {
    if (selectedOrderTimelineFilter.value === 'all') {
        return patientOrderTimelineEvents.value
    }
    return patientOrderTimelineEvents.value.filter(
        event => event.event_type === selectedOrderTimelineFilter.value
    )
})

const groupedOrderTimelineEvents = computed(() => {
    const grouped: Record<string, { label: string; date: string; events: OrderTimelineEventEntry[] }> = {}

    filteredOrderTimelineEvents.value.forEach((event) => {
        const date = event.occurred_at ? new Date(event.occurred_at) : new Date()
        const dateKey = date.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })

        if (!grouped[dateKey]) {
            grouped[dateKey] = {
                label: dateKey,
                date: dateKey,
                events: [],
            }
        }

        grouped[dateKey].events.push(event)
    })

    return Object.values(grouped).sort((a, b) => new Date(b.date).getTime() - new Date(a.date).getTime())
})

function formatDate(iso: string | null): string {
    if (!iso) return ''
    const date = new Date(iso)
    if (Number.isNaN(date.getTime())) return iso
    return date.toLocaleDateString()
}

function formatDateTime(iso: string | null): string {
    if (!iso) return ''
    const date = new Date(iso)
    if (Number.isNaN(date.getTime())) return iso
    return date.toLocaleString()
}

function buildQuery(base: string): string {
    const params = new URLSearchParams()
    if (search.value.trim() !== '') params.set('search', search.value.trim())
    params.set('per_page', String(perPage.value))
    if (filterHasDocuments.value) params.set('has_documents', '1')
    if (filterHasMedicalHistory.value) params.set('has_medical_history', '1')
    if (filterHasActiveSubscription.value) params.set('has_active_subscription', '1')
    const q = params.toString()
    return q ? `${base}?${q}` : base
}

async function loadPatients(url?: string) {
    loadingList.value = true
    listError.value = null

    try {
        const response = await fetch(url ?? buildQuery('/patients'), {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        })

        if (!response.ok) {
            listError.value = `Failed to load patients (${response.status})`
            patients.value = []
            meta.value = null
            return
        }

        const data = (await response.json()) as {
            patients: PatientListItem[]
            meta: { current_page: number; per_page: number; next_page_url: string | null; prev_page_url: string | null }
        }

        patients.value = data.patients
        meta.value = data.meta
    } catch {
        listError.value = 'A network error occurred while loading patients.'
    } finally {
        loadingList.value = false
    }
}

async function fetchCount() {
    loadingCount.value = true
    countError.value = null

    try {
        const response = await fetch(buildQuery('/patients/count'), {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        })

        if (!response.ok) {
            countError.value = `Failed to load count (${response.status})`
            count.value = null
            return
        }

        const data = (await response.json()) as { count: number }
        count.value = data.count
    } catch {
        countError.value = 'A network error occurred while loading count.'
    } finally {
        loadingCount.value = false
    }
}

async function loadDetail(patientUuid: string) {
    loadingDetail.value = true
    detailError.value = null
    selectedPatient.value = null

    void loadPatientDocuments(patientUuid)
    void loadOrderTimeline(patientUuid)

    try {
        const response = await fetch(`/patients/${patientUuid}`, {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        })

        if (!response.ok) {
            detailError.value = `Failed to load patient detail (${response.status})`
            selectedPatient.value = null
            return
        }

        const data = (await response.json()) as { patient: PatientDetail }
        selectedPatient.value = data.patient
    } catch {
        detailError.value = 'A network error occurred while loading patient detail.'
    } finally {
        loadingDetail.value = false
    }
}

async function loadPatientDocuments(patientUuid: string) {
    loadingDocuments.value = true
    documentsError.value = null
    patientDocuments.value = []

    try {
        const response = await fetch(`/patients/${patientUuid}/documents`, {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        })

        if (!response.ok) {
            documentsError.value = `Failed to load documents (${response.status})`
            return
        }

        const data = (await response.json()) as { documents: PatientDocument[] | null }
        patientDocuments.value = data.documents ?? []
    } catch {
        documentsError.value = 'A network error occurred while loading documents.'
    } finally {
        loadingDocuments.value = false
    }
}

async function loadOrderTimeline(patientUuid: string) {
    loadingOrderTimeline.value = true
    orderTimelineError.value = null
    patientOrderTimelineEvents.value = []

    try {
        const response = await fetch(`/patients/${patientUuid}/orders/timeline`, {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        })

        if (!response.ok) {
            orderTimelineError.value = `Failed to load order timeline (${response.status})`
            return
        }

        const data = (await response.json()) as { events: OrderTimelineEventEntry[] | null }
        patientOrderTimelineEvents.value = data.events ?? []
    } catch {
        orderTimelineError.value = 'A network error occurred while loading order timeline.'
    } finally {
        loadingOrderTimeline.value = false
    }
}

function getCsrfToken(): string {
    return document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? ''
}

async function submitAllergy() {
    if (!selectedPatient.value) return

    const patientUuid = selectedPatient.value.patient_uuid

    submittingAllergy.value = true
    allergyError.value = null

    try {
        const response = await fetch(`/patients/${patientUuid}/medical-history/allergies`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
            },
            credentials: 'same-origin',
            body: JSON.stringify(allergyForm.value),
        })

        if (!response.ok) {
            allergyError.value = await extractErrorMessage('Failed to save allergy.', response)
            return
        }

        await loadDetail(patientUuid)

        showAllergyForm.value = false
        allergyForm.value = {
            allergen: '',
            reaction: '',
            severity: '',
            notes: '',
        }
    } catch {
        allergyError.value = 'A network error occurred while saving allergy.'
    } finally {
        submittingAllergy.value = false
    }
}

async function submitCondition() {
    if (!selectedPatient.value) return

    const patientUuid = selectedPatient.value.patient_uuid

    submittingCondition.value = true
    conditionError.value = null

    try {
        const response = await fetch(`/patients/${patientUuid}/medical-history/conditions`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
            },
            credentials: 'same-origin',
            body: JSON.stringify(conditionForm.value),
        })

        if (!response.ok) {
            conditionError.value = await extractErrorMessage('Failed to save condition.', response)
            return
        }

        await loadDetail(patientUuid)

        showConditionForm.value = false
        conditionForm.value = {
            condition_name: '',
            diagnosed_at: '',
            notes: '',
            had_condition_before: false,
            is_chronic: false,
        }
    } catch {
        conditionError.value = 'A network error occurred while saving condition.'
    } finally {
        submittingCondition.value = false
    }
}

async function submitMedication() {
    if (!selectedPatient.value) return

    const patientUuid = selectedPatient.value.patient_uuid

    submittingMedication.value = true
    medicationError.value = null

    const payload = {
        ...medicationForm.value,
        medication_id: medicationForm.value.medication_id
            ? Number(medicationForm.value.medication_id)
            : null,
    }

    try {
        const response = await fetch(`/patients/${patientUuid}/medical-history/medications`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
            },
            credentials: 'same-origin',
            body: JSON.stringify(payload),
        })

        if (!response.ok) {
            medicationError.value = await extractErrorMessage('Failed to save medication.', response)
            return
        }

        await loadDetail(patientUuid)

        showMedicationForm.value = false
        medicationForm.value = {
            medication_id: '',
            dosage: '',
            frequency: '',
            start_date: '',
            end_date: '',
            notes: '',
        }
    } catch {
        medicationError.value = 'A network error occurred while saving medication.'
    } finally {
        submittingMedication.value = false
    }
}

async function submitVisitSummary() {
    if (!selectedPatient.value) return

    const patientUuid = selectedPatient.value.patient_uuid

    submittingVisitSummary.value = true
    visitSummaryError.value = null

    const familyConditions = visitSummaryForm.value.family_history_conditions_text
        .split('\n')
        .map((line) => line.trim())
        .filter((line) => line.length > 0)

    const payload: Record<string, unknown> = {
        past_injuries: visitSummaryForm.value.past_injuries,
        past_injuries_details: visitSummaryForm.value.past_injuries_details || null,
        surgery: visitSummaryForm.value.surgery,
        surgery_details: visitSummaryForm.value.surgery_details || null,
        chronic_conditions_details: visitSummaryForm.value.chronic_conditions_details || null,
        chronic_pain: visitSummaryForm.value.chronic_pain,
        chronic_pain_details: visitSummaryForm.value.chronic_pain_details || null,
    }

    if (familyConditions.length > 0) {
        payload.family_history_conditions = familyConditions
    }

    try {
        const response = await fetch(`/patients/${patientUuid}/medical-history/visit-summary`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
            },
            credentials: 'same-origin',
            body: JSON.stringify(payload),
        })

        if (!response.ok) {
            visitSummaryError.value = await extractErrorMessage('Failed to save visit summary.', response)
            return
        }

        await loadDetail(patientUuid)

        showVisitSummaryForm.value = false
        visitSummaryForm.value = {
            past_injuries: false,
            past_injuries_details: '',
            surgery: false,
            surgery_details: '',
            chronic_conditions_details: '',
            chronic_pain: false,
            chronic_pain_details: '',
            family_history_conditions_text: '',
        }
    } catch {
        visitSummaryError.value = 'A network error occurred while saving visit summary.'
    } finally {
        submittingVisitSummary.value = false
    }
}

function onDocumentFileSelected(event: Event) {
    const target = event.target as HTMLInputElement | null

    if (!target || !target.files || target.files.length === 0) {
        documentUploadForm.value.file = null
        return
    }

    documentUploadForm.value.file = target.files[0] ?? null
}

async function submitPatientDocument() {
    if (!selectedPatient.value) return
    if (uploadingDocument.value) return

    if (!documentUploadForm.value.file) {
        documentUploadError.value = 'Please choose a file to upload.'
        return
    }

    const patientUuid = selectedPatient.value.patient_uuid

    uploadingDocument.value = true
    documentUploadError.value = null

    try {
        const formData = new FormData()
        formData.append('record_type', documentUploadForm.value.record_type)
        formData.append('description', documentUploadForm.value.description)

        if (documentUploadForm.value.record_date) {
            formData.append('record_date', documentUploadForm.value.record_date)
        }

        formData.append('file', documentUploadForm.value.file)

        const response = await fetch(`/patients/${patientUuid}/documents`, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
            },
            credentials: 'same-origin',
            body: formData,
        })

        if (!response.ok) {
            documentUploadError.value = await extractErrorMessage('Failed to upload document.', response)
            return
        }

        const data = (await response.json()) as { document: PatientDocument | null }

        if (data.document) {
            patientDocuments.value = [
                data.document,
                ...patientDocuments.value.filter((existing) => existing.id !== data.document!.id),
            ]
        } else {
            await loadPatientDocuments(patientUuid)
        }

        showDocumentUploadForm.value = false
        documentUploadForm.value = {
            record_type: '',
            description: '',
            record_date: '',
            file: null,
        }
    } catch {
        documentUploadError.value = 'A network error occurred while uploading document.'
    } finally {
        uploadingDocument.value = false
    }
}

function applyFilters() {
    count.value = null
    void loadPatients()
}

function goToNextPage() {
    if (meta.value?.next_page_url) void loadPatients(meta.value.next_page_url)
}

function goToPrevPage() {
    if (meta.value?.prev_page_url) void loadPatients(meta.value.prev_page_url)
}

onMounted(() => {
    void loadPatients()
})
</script>

<template>
    <Head title="Patients" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <Card>
                <CardHeader>
                    <CardTitle>Patients</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                        <div class="flex flex-1 flex-col gap-2">
                            <div class="flex gap-2">
                                <Input v-model="search" type="search" placeholder="Search by name or email" class="max-w-xs" />
                                <Input v-model.number="perPage" type="number" min="1" max="100" class="w-24" />
                                <Button type="button" size="sm" @click="applyFilters">Apply</Button>
                            </div>
                            <div class="flex flex-wrap items-center gap-2 text-xs text-muted-foreground">
                                <label class="inline-flex items-center gap-1">
                                    <input
                                        v-model="filterHasDocuments"
                                        type="checkbox"
                                        class="h-3 w-3 border-input text-primary"
                                    />
                                    <span>Has documents</span>
                                </label>
                                <label class="inline-flex items-center gap-1">
                                    <input
                                        v-model="filterHasMedicalHistory"
                                        type="checkbox"
                                        class="h-3 w-3 border-input text-primary"
                                    />
                                    <span>Has medical history</span>
                                </label>
                                <label class="inline-flex items-center gap-1">
                                    <input
                                        v-model="filterHasActiveSubscription"
                                        type="checkbox"
                                        class="h-3 w-3 border-input text-primary"
                                    />
                                    <span>Active subscription</span>
                                </label>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <Button type="button" size="sm" variant="outline" :disabled="loadingCount" @click="fetchCount">
                                <Spinner v-if="loadingCount" class="mr-2 h-4 w-4" />
                                <span v-if="loadingCount">Counting…</span>
                                <span v-else>Count</span>
                            </Button>
                            <span v-if="count !== null" class="text-sm text-muted-foreground">Total: {{ count }}</span>
                            <span v-else-if="countError" class="text-sm text-destructive">{{ countError }}</span>
                        </div>
                    </div>

                    <div class="border rounded-md">
                        <div v-if="loadingList" class="flex items-center gap-2 p-4 text-sm text-muted-foreground">
                            <Spinner class="h-4 w-4" />
                            <span>Loading patients…</span>
                        </div>
                        <p v-else-if="listError" class="p-4 text-sm text-destructive">{{ listError }}</p>
                        <p v-else-if="!patients.length" class="p-4 text-sm text-muted-foreground">No patients found.</p>
                        <table v-else class="min-w-full text-left text-sm">
                            <thead class="border-b bg-muted/50 text-xs uppercase text-muted-foreground">
                                <tr>
                                    <th class="px-3 py-2">Name</th>
                                    <th class="px-3 py-2">Email</th>
                                    <th class="px-3 py-2">Status</th>
                                    <th class="px-3 py-2">Plan</th>
                                    <th class="px-3 py-2">Subscription</th>
                                    <th class="px-3 py-2">Enrolled at</th>
                                    <th class="px-3 py-2" />
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="patient in patients" :key="patient.patient_uuid" class="border-b last:border-b-0">
                                    <td class="px-3 py-2 font-medium text-foreground">
                                        <div class="flex items-center gap-2">
                                            <span>{{ patient.fname }} {{ patient.lname }}</span>
                                            <div class="flex flex-wrap gap-1">
                                                <Badge
                                                    v-if="patient.has_documents"
                                                    variant="outline"
                                                    class="border-emerald-500/70 bg-emerald-50 text-[10px] uppercase tracking-wide text-emerald-700"
                                                >
                                                    Docs
                                                </Badge>
                                                <Badge
                                                    v-if="patient.has_medical_history"
                                                    variant="outline"
                                                    class="border-sky-500/70 bg-sky-50 text-[10px] uppercase tracking-wide text-sky-700"
                                                >
                                                    History
                                                </Badge>
                                                <Badge
                                                    v-if="patient.has_active_subscription"
                                                    variant="outline"
                                                    class="border-indigo-500/70 bg-indigo-50 text-[10px] uppercase tracking-wide text-indigo-700"
                                                >
                                                    Active
                                                </Badge>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-3 py-2">{{ patient.email }}</td>
                                    <td class="px-3 py-2">
                                        <Badge :variant="statusBadgeVariant(patient.status)" class="capitalize">
                                            {{ patient.status || 'unknown' }}
                                        </Badge>
                                    </td>
                                    <td class="px-3 py-2 text-xs text-muted-foreground">
                                        <span v-if="patient.subscription">
                                            {{ patient.subscription.plan_name || 'TeleMed Pro plan' }}
                                            <span v-if="patient.subscription.is_trial">(trial)</span>


                                        </span>
                                        <span v-else>—</span>
                                    </td>
                                    <td class="px-3 py-2">
                                        <Badge
                                            v-if="patient.subscription"
                                            :variant="subscriptionBadgeVariant(patient.subscription.status)"
                                            class="capitalize"
                                        >
                                            {{ patient.subscription.status }}
                                        </Badge>
                                        <span v-else class="text-xs text-muted-foreground">No subscription</span>
                                    </td>
                                    <td class="px-3 py-2 text-xs text-muted-foreground">
                                        {{ formatDate(patient.enrolled_at) || '—' }}
                                    </td>
                                    <td class="px-3 py-2 text-right">
                                        <Button type="button" size="sm" variant="outline" @click="loadDetail(patient.patient_uuid)">View</Button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="flex items-center justify-between text-xs text-muted-foreground">
                        <div>
                            <span v-if="meta">Page {{ meta.current_page }}</span>
                        </div>
                        <div class="flex gap-2">
                            <Button type="button" size="sm" variant="outline" :disabled="!hasPrevPage" @click="goToPrevPage">Previous</Button>
                            <Button type="button" size="sm" variant="outline" :disabled="!hasNextPage" @click="goToNextPage">Next</Button>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Patient detail</CardTitle>
                </CardHeader>
                <CardContent class="space-y-2 text-sm text-muted-foreground">
                    <div v-if="loadingDetail" class="flex items-center gap-2">
                        <Spinner class="h-4 w-4" />
                        <span>Loading detail…</span>
                    </div>
                    <p v-else-if="detailError" class="text-destructive">{{ detailError }}</p>
                    <p v-else-if="!selectedPatient">Select a patient to view details.</p>
                    <div v-else class="space-y-4 text-xs">
                        <div>
                            <p class="text-sm font-medium text-foreground">
                                {{ selectedPatient.fname }} {{ selectedPatient.lname }}
                            </p>
                            <p>{{ selectedPatient.email }}</p>
                            <p class="flex items-center gap-1">
                                <span>Status:</span>
                                <Badge :variant="statusBadgeVariant(selectedPatient.status)" class="capitalize">
                                    {{ selectedPatient.status || 'unknown' }}
                                </Badge>
                            </p>
                        </div>

                        <div v-if="selectedPatient.enrollment">
                            <p>
                                Enrolled:
                                <span v-if="selectedPatient.enrollment.enrolled_at">
                                    {{ formatDateTime(selectedPatient.enrollment.enrolled_at) }}
                                </span>
                                <span v-else>
                                    (date unknown)
                                </span>
                            </p>
                            <p>
                                Source:
                                <span>{{ selectedPatient.enrollment.source || 'unknown' }}</span>
                            </p>
                        </div>

                        <div v-if="selectedPatient.demographics">
                            <p v-if="selectedPatient.demographics.dob">
                                DOB: {{ formatDate(selectedPatient.demographics.dob) }}
                            </p>
                            <p v-if="selectedPatient.demographics.gender">
                                Gender: {{ selectedPatient.demographics.gender }}
                            </p>
                            <p v-if="selectedPatient.demographics.address1">
                                Address:
                                {{ selectedPatient.demographics.address1 }}
                                <span v-if="selectedPatient.demographics.address2">
                                    , {{ selectedPatient.demographics.address2 }}
                                </span>
                            </p>
                            <p v-if="selectedPatient.demographics.city || selectedPatient.demographics.state || selectedPatient.demographics.zip">
                                {{ selectedPatient.demographics.city }}
                                <span v-if="selectedPatient.demographics.state">
                                    , {{ selectedPatient.demographics.state }}
                                </span>
                                <span v-if="selectedPatient.demographics.zip">
                                    {{ ' ' + selectedPatient.demographics.zip }}
                                </span>
                            </p>
                            <p v-if="selectedPatient.demographics.phone">
                                Phone: {{ selectedPatient.demographics.phone }}
                            </p>
                            <p v-if="selectedPatient.demographics.mobile_phone">
                                Mobile: {{ selectedPatient.demographics.mobile_phone }}
                            </p>
                        </div>

                        <div v-if="selectedPatient.subscription">
                            <p>
                                Plan:
                                {{ selectedPatient.subscription.plan_name || 'TeleMed Pro plan' }}
                                <span v-if="selectedPatient.subscription.is_trial">(trial)</span>
                            </p>
                            <p class="flex items-center gap-1">
                                <span>Status:</span>
                                <Badge
                                    :variant="subscriptionBadgeVariant(selectedPatient.subscription.status)"
                                    class="capitalize"
                                >
                                    {{ selectedPatient.subscription.status }}
                                </Badge>
                            </p>
                            <p v-if="selectedPatient.subscription.starts_at || selectedPatient.subscription.ends_at">
                                <span v-if="selectedPatient.subscription.starts_at">
                                    {{ formatDate(selectedPatient.subscription.starts_at) }}
                                </span>
                                <span v-if="selectedPatient.subscription.starts_at && selectedPatient.subscription.ends_at">
                                    –
                                </span>
                                <span v-if="selectedPatient.subscription.ends_at">
                                    {{ formatDate(selectedPatient.subscription.ends_at) }}
                                </span>
                            </p>
                        </div>


	                        <div class="space-y-2">
	                            <div class="flex items-center justify-between">
	                                <p class="text-sm font-medium text-foreground">Documents</p>
	                                <Button
	                                    type="button"
	                                    size="sm"
	                                    variant="outline"
	                                    @click="showDocumentUploadForm = !showDocumentUploadForm"
	                                >
	                                    {{ showDocumentUploadForm ? 'Cancel' : 'Upload' }}
	                                </Button>
	                            </div>

	                            <p v-if="documentUploadError" class="text-xs text-destructive">
	                                {{ documentUploadError }}
	                            </p>

	                            <form
	                                v-if="showDocumentUploadForm"
	                                class="space-y-2"
	                                @submit.prevent="submitPatientDocument"
	                            >
	                                <div class="grid gap-1">
	                                    <Label for="document-record-type">Type</Label>
	                                    <Input
	                                        id="document-record-type"
	                                        v-model="documentUploadForm.record_type"
	                                        type="text"
	                                        required
	                                        class="h-8"
	                                    />
	                                </div>
	                                <div class="grid gap-1">
	                                    <Label for="document-description">Description</Label>
	                                    <Input
	                                        id="document-description"
	                                        v-model="documentUploadForm.description"
	                                        type="text"
	                                        required
	                                        class="h-8"
	                                    />
	                                </div>
	                                <div class="grid gap-1">
	                                    <Label for="document-record-date">Record date</Label>
	                                    <Input
	                                        id="document-record-date"
	                                        v-model="documentUploadForm.record_date"
	                                        type="date"
	                                        class="h-8"
	                                    />
	                                </div>
	                                <div class="grid gap-1">
	                                    <Label for="document-file">File</Label>
	                                    <Input
	                                        id="document-file"
	                                        type="file"
	                                        @change="onDocumentFileSelected"
	                                        class="h-8"
	                                    />
	                                </div>
	                                <div class="flex gap-2">
	                                    <Button type="submit" size="sm" :disabled="uploadingDocument">
	                                        <Spinner v-if="uploadingDocument" class="mr-1 h-3 w-3" />
	                                        <span>{{ uploadingDocument ? 'Uploading…' : 'Upload document' }}</span>
	                                    </Button>
	                                    <Button
	                                        type="button"
	                                        size="sm"
	                                        variant="ghost"
	                                        @click="showDocumentUploadForm = false"
	                                    >
	                                        Cancel
	                                    </Button>
	                                </div>
	                            </form>

	                            <div v-if="loadingDocuments" class="flex items-center gap-2">
	                                <Spinner class="h-3 w-3" />
	                                <span>Loading documents…</span>
	                            </div>
	                            <p v-else-if="documentsError" class="text-xs text-destructive">
	                                {{ documentsError }}
	                            </p>
	                            <p
	                                v-else-if="!patientDocuments.length"
	                                class="text-xs text-muted-foreground"
	                            >
	                                No documents uploaded for this patient.
	                            </p>
	                            <ul v-else class="space-y-1">
	                                <li
	                                    v-for="document in patientDocuments"
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
	                        </div>


                        <div v-if="selectedPatient.medical_history" class="space-y-4">
                                <p class="text-sm font-medium text-foreground">Medical history</p>

                                <div class="space-y-2">
                                        <div class="flex items-center justify-between">
                                                <p class="font-semibold">Allergies</p>
                                                <Button type="button" size="sm" variant="outline" @click="showAllergyForm = !showAllergyForm">
                                                        {{ showAllergyForm ? 'Cancel' : 'Add' }}
                                                </Button>
                                        </div>

                                <p v-if="allergyError" class="text-xs text-destructive">{{ allergyError }}</p>

                                <form v-if="showAllergyForm" class="space-y-2" @submit.prevent="submitAllergy">
                                    <div class="grid gap-1">
                                        <Label for="allergen">Allergen</Label>
                                        <Input id="allergen" v-model="allergyForm.allergen" type="text" required class="h-8" />
                                    </div>
                                    <div class="grid gap-1">
                                        <Label for="reaction">Reaction</Label>
                                        <Input id="reaction" v-model="allergyForm.reaction" type="text" class="h-8" />
                                    </div>
                                    <div class="grid gap-1">
                                        <Label for="severity">Severity</Label>
                                        <Input id="severity" v-model="allergyForm.severity" type="text" class="h-8" />
                                    </div>
                                    <div class="grid gap-1">
                                        <Label for="allergy-notes">Notes</Label>
                                        <textarea
                                            id="allergy-notes"
                                            v-model="allergyForm.notes"
                                            rows="2"
                                            class="min-h-[60px] w-full rounded-md border border-input bg-background px-3 py-2 text-xs shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring/50"
                                        />
                                    </div>
                                    <div class="flex gap-2">
                                        <Button type="submit" size="sm" :disabled="submittingAllergy">
                                            <Spinner v-if="submittingAllergy" class="mr-1 h-3 w-3" />
                                            <span>{{ submittingAllergy ? 'Saving…' : 'Save allergy' }}</span>
                                        </Button>
                                        <Button type="button" size="sm" variant="ghost" @click="showAllergyForm = false">
                                            Cancel
                                        </Button>
                                    </div>
                                </form>

                                <ul v-if="selectedPatient.medical_history.allergies.length" class="list-disc pl-4">
                                    <li v-for="allergy in selectedPatient.medical_history.allergies" :key="allergy.id">
                                        <span class="font-medium">{{ allergy.allergen }}</span>
                                        <span v-if="allergy.reaction"> – {{ allergy.reaction }}</span>
                                        <Badge v-if="allergy.severity" variant="outline" class="ml-1 capitalize">
                                            {{ allergy.severity }}
                                        </Badge>
                                        <span v-if="allergy.notes" class="block text-muted-foreground">{{ allergy.notes }}</span>
                                    </li>
                                </ul>
                                <p v-else class="text-xs text-muted-foreground">No allergies recorded.</p>
                            </div>

                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <p class="font-semibold">Conditions</p>
                                    <Button type="button" size="sm" variant="outline" @click="showConditionForm = !showConditionForm">
                                        {{ showConditionForm ? 'Cancel' : 'Add' }}
                                    </Button>
                                </div>

                                <p v-if="conditionError" class="text-xs text-destructive">{{ conditionError }}</p>

                                <form v-if="showConditionForm" class="space-y-2" @submit.prevent="submitCondition">
                                    <div class="grid gap-1">
                                        <Label for="condition-name">Condition</Label>
                                        <Input
                                            id="condition-name"
                                            v-model="conditionForm.condition_name"
                                            type="text"
                                            required
                                            class="h-8"
                                        />
                                    </div>
                                    <div class="grid gap-1">
                                        <Label for="condition-diagnosed-at">Diagnosed at</Label>
                                        <Input
                                            id="condition-diagnosed-at"
                                            v-model="conditionForm.diagnosed_at"
                                            type="date"
                                            class="h-8"
                                        />
                                    </div>
                                    <div class="flex flex-wrap gap-4">
                                        <label class="flex items-center gap-2 text-xs">
                                            <input
                                                v-model="conditionForm.had_condition_before"
                                                type="checkbox"
                                                class="h-3 w-3 border-input text-primary"
                                            />
                                            <span>Had condition before</span>
                                        </label>
                                        <label class="flex items-center gap-2 text-xs">
                                            <input
                                                v-model="conditionForm.is_chronic"
                                                type="checkbox"
                                                class="h-3 w-3 border-input text-primary"
                                            />
                                            <span>Chronic</span>
                                        </label>
                                    </div>
                                    <div class="grid gap-1">
                                        <Label for="condition-notes">Notes</Label>
                                        <textarea
                                            id="condition-notes"
                                            v-model="conditionForm.notes"
                                            rows="2"
                                            class="min-h-[60px] w-full rounded-md border border-input bg-background px-3 py-2 text-xs shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring/50"
                                        />
                                    </div>
                                    <div class="flex gap-2">
                                        <Button type="submit" size="sm" :disabled="submittingCondition">
                                            <Spinner v-if="submittingCondition" class="mr-1 h-3 w-3" />
                                            <span>{{ submittingCondition ? 'Saving…' : 'Save condition' }}</span>
                                        </Button>
                                        <Button type="button" size="sm" variant="ghost" @click="showConditionForm = false">
                                            Cancel
                                        </Button>
                                    </div>
                                </form>

                                <ul v-if="selectedPatient.medical_history.conditions.length" class="list-disc pl-4">
                                    <li v-for="condition in selectedPatient.medical_history.conditions" :key="condition.id">
                                        <span class="font-medium">{{ condition.condition_name }}</span>
                                        <span v-if="condition.diagnosed_at"> – diagnosed {{ formatDate(condition.diagnosed_at) }}</span>
                                        <span v-if="condition.is_chronic" class="ml-1 text-xs text-muted-foreground">(chronic)</span>
                                        <span v-if="condition.notes" class="block text-muted-foreground">{{ condition.notes }}</span>
                                    </li>
                                </ul>
                                <p v-else class="text-xs text-muted-foreground">No conditions recorded.</p>
                            </div>

                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <p class="font-semibold">Medications</p>
                                    <Button type="button" size="sm" variant="outline" @click="showMedicationForm = !showMedicationForm">
                                        {{ showMedicationForm ? 'Cancel' : 'Add' }}
                                    </Button>
                                </div>

                                <p v-if="medicationError" class="text-xs text-destructive">{{ medicationError }}</p>

                                <form v-if="showMedicationForm" class="space-y-2" @submit.prevent="submitMedication">
                                    <div class="grid gap-1">
                                        <Label for="medication-id">Medication ID</Label>
                                        <Input
                                            id="medication-id"
                                            v-model="medicationForm.medication_id"
                                            type="number"
                                            min="1"
                                            required
                                            class="h-8"
                                        />
                                    </div>
                                    <div class="grid gap-1">
                                        <Label for="medication-dosage">Dosage</Label>
                                        <Input
                                            id="medication-dosage"
                                            v-model="medicationForm.dosage"
                                            type="text"
                                            required
                                            class="h-8"
                                        />
                                    </div>
                                    <div class="grid gap-1">
                                        <Label for="medication-frequency">Frequency</Label>
                                        <Input
                                            id="medication-frequency"
                                            v-model="medicationForm.frequency"
                                            type="text"
                                            required
                                            class="h-8"
                                        />
                                    </div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div class="grid gap-1">
                                            <Label for="medication-start-date">Start date</Label>
                                            <Input
                                                id="medication-start-date"
                                                v-model="medicationForm.start_date"
                                                type="date"
                                                class="h-8"
                                            />
                                        </div>
                                        <div class="grid gap-1">
                                            <Label for="medication-end-date">End date</Label>
                                            <Input
                                                id="medication-end-date"
                                                v-model="medicationForm.end_date"
                                                type="date"
                                                class="h-8"
                                            />
                                        </div>
                                    </div>
                                    <div class="grid gap-1">
                                        <Label for="medication-notes">Notes</Label>
                                        <textarea
                                            id="medication-notes"
                                            v-model="medicationForm.notes"
                                            rows="2"
                                            class="min-h-[60px] w-full rounded-md border border-input bg-background px-3 py-2 text-xs shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring/50"
                                        />
                                    </div>
                                    <div class="flex gap-2">
                                        <Button type="submit" size="sm" :disabled="submittingMedication">
                                            <Spinner v-if="submittingMedication" class="mr-1 h-3 w-3" />
                                            <span>{{ submittingMedication ? 'Saving…' : 'Save medication' }}</span>
                                        </Button>
                                        <Button type="button" size="sm" variant="ghost" @click="showMedicationForm = false">
                                            Cancel
                                        </Button>
                                    </div>
                                </form>

                                <ul v-if="selectedPatient.medical_history.medications.length" class="list-disc pl-4">
                                    <li v-for="med in selectedPatient.medical_history.medications" :key="med.id">
                                        <span class="font-medium">{{ med.medication_name || 'Medication #' + med.medication_id }}</span>
                                        <span v-if="med.dosage"> – {{ med.dosage }}</span>
                                        <span v-if="med.frequency" class="ml-1 text-xs text-muted-foreground">({{ med.frequency }})</span>
                                        <div class="text-muted-foreground">
                                            <span v-if="med.start_date">{{ formatDate(med.start_date) }}</span>
                                            <span v-if="med.start_date && med.end_date"> – </span>
                                            <span v-if="med.end_date">{{ formatDate(med.end_date) }}</span>
                                        </div>
                                        <span v-if="med.notes" class="block text-muted-foreground">{{ med.notes }}</span>
                                    </li>
                                </ul>
                                <p v-else class="text-xs text-muted-foreground">No medications recorded.</p>
                            </div>

                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <p class="font-semibold">Visit summary</p>
                                    <Button type="button" size="sm" variant="outline" @click="showVisitSummaryForm = !showVisitSummaryForm">
                                        {{ showVisitSummaryForm ? 'Cancel' : 'Edit summary' }}
                                    </Button>
                                </div>

                                <p v-if="visitSummaryError" class="text-xs text-destructive">{{ visitSummaryError }}</p>

                                <div class="space-y-1">
                                    <div v-if="selectedPatient.medical_history.surgical_history">
                                        <p class="font-semibold">Surgical / injuries</p>
                                        <p v-if="selectedPatient.medical_history.surgical_history.past_injuries">
                                            Past injuries:
                                            <span>{{ selectedPatient.medical_history.surgical_history.past_injuries_details || 'details not provided' }}</span>
                                        </p>
                                        <p v-if="selectedPatient.medical_history.surgical_history.surgery">
                                            Surgeries:
                                            <span>{{ selectedPatient.medical_history.surgical_history.surgery_details || 'details not provided' }}</span>
                                        </p>
                                        <p v-if="selectedPatient.medical_history.surgical_history.chronic_conditions_details">
                                            Chronic conditions:
                                            <span>{{ selectedPatient.medical_history.surgical_history.chronic_conditions_details }}</span>
                                        </p>
                                    </div>
                                    <p v-else class="text-xs text-muted-foreground">No surgical history recorded.</p>

                                    <div v-if="selectedPatient.medical_history.family_history">
                                        <p class="font-semibold">Family history</p>
                                        <p>
                                            Chronic pain:
                                            <span>{{ selectedPatient.medical_history.family_history.chronic_pain ? 'yes' : 'no' }}</span>
                                        </p>
                                        <p v-if="selectedPatient.medical_history.family_history.chronic_pain_details">
                                            Details:
                                            <span>{{ selectedPatient.medical_history.family_history.chronic_pain_details }}</span>
                                        </p>
                                        <ul v-if="selectedPatient.medical_history.family_history.conditions.length" class="list-disc pl-4">
                                            <li
                                                v-for="condition in selectedPatient.medical_history.family_history.conditions"
                                                :key="condition.id"
                                            >
                                                {{ condition.name }}
                                            </li>
                                        </ul>
                                    </div>
                                    <p v-else class="text-xs text-muted-foreground">No family history recorded.</p>
                                </div>

                                <form v-if="showVisitSummaryForm" class="space-y-2" @submit.prevent="submitVisitSummary">
                                    <div class="grid gap-1">
                                        <Label for="past-injuries">Past injuries</Label>
                                        <label class="flex items-center gap-2 text-xs">
                                            <input
                                                id="past-injuries"
                                                v-model="visitSummaryForm.past_injuries"
                                                type="checkbox"
                                                class="h-3 w-3 border-input text-primary"
                                            />
                                            <span>Patient reports past injuries</span>
                                        </label>
                                    </div>
                                    <div class="grid gap-1">
                                        <Label for="past-injuries-details">Injuries details</Label>
                                        <textarea
                                            id="past-injuries-details"
                                            v-model="visitSummaryForm.past_injuries_details"
                                            rows="2"
                                            class="min-h-[60px] w-full rounded-md border border-input bg-background px-3 py-2 text-xs shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring/50"
                                        />
                                    </div>
                                    <div class="grid gap-1">
                                        <Label for="surgery">Surgeries</Label>
                                        <label class="flex items-center gap-2 text-xs">
                                            <input
                                                id="surgery"
                                                v-model="visitSummaryForm.surgery"
                                                type="checkbox"
                                                class="h-3 w-3 border-input text-primary"
                                            />
                                            <span>Patient has had surgery</span>
                                        </label>
                                    </div>
                                    <div class="grid gap-1">
                                        <Label for="surgery-details">Surgery details</Label>
                                        <textarea
                                            id="surgery-details"
                                            v-model="visitSummaryForm.surgery_details"
                                            rows="2"
                                            class="min-h-[60px] w-full rounded-md border border-input bg-background px-3 py-2 text-xs shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring/50"
                                        />
                                    </div>
                                    <div class="grid gap-1">
                                        <Label for="chronic-conditions-details">Chronic conditions details</Label>
                                        <textarea
                                            id="chronic-conditions-details"
                                            v-model="visitSummaryForm.chronic_conditions_details"
                                            rows="2"
                                            class="min-h-[60px] w-full rounded-md border border-input bg-background px-3 py-2 text-xs shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring/50"
                                        />
                                    </div>
                                    <div class="grid gap-1">
                                        <Label for="chronic-pain">Chronic pain</Label>
                                        <label class="flex items-center gap-2 text-xs">
                                            <input
                                                id="chronic-pain"
                                                v-model="visitSummaryForm.chronic_pain"
                                                type="checkbox"
                                                class="h-3 w-3 border-input text-primary"
                                            />
                                            <span>Family history of chronic pain</span>
                                        </label>
                                    </div>
                                    <div class="grid gap-1">
                                        <Label for="chronic-pain-details">Chronic pain details</Label>
                                        <textarea
                                            id="chronic-pain-details"
                                            v-model="visitSummaryForm.chronic_pain_details"
                                            rows="2"
                                            class="min-h-[60px] w-full rounded-md border border-input bg-background px-3 py-2 text-xs shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring/50"
                                        />
                                    </div>
                                    <div class="grid gap-1">
                                        <Label for="family-history-conditions">Family history conditions (one per line)</Label>
                                        <textarea
                                            id="family-history-conditions"
                                            v-model="visitSummaryForm.family_history_conditions_text"
                                            rows="3"
                                            class="min-h-[60px] w-full rounded-md border border-input bg-background px-3 py-2 text-xs shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring/50"
                                        />
                                    </div>
                                    <div class="flex gap-2">
                                        <Button type="submit" size="sm" :disabled="submittingVisitSummary">
                                            <Spinner v-if="submittingVisitSummary" class="mr-1 h-3 w-3" />
                                            <span>{{ submittingVisitSummary ? 'Saving…' : 'Save summary' }}</span>
                                        </Button>
                                        <Button type="button" size="sm" variant="ghost" @click="showVisitSummaryForm = false">
                                            Cancel
                                        </Button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card v-if="selectedPatient">
                <CardHeader>
                    <CardTitle>Order history timeline</CardTitle>
                </CardHeader>
                <CardContent class="space-y-3 text-sm text-muted-foreground">
                    <div
                        v-if="loadingOrderTimeline"
                        class="flex items-center space-x-2"
                    >
                        <Spinner class="h-4 w-4" />
                        <span>Loading order timeline…</span>
                    </div>
                    <p v-else-if="orderTimelineError">
                        {{ orderTimelineError }}
                    </p>
                    <div v-else class="space-y-3">
                        <div class="flex items-center justify-between text-xs text-muted-foreground">
                            <span>
                                Showing {{ filteredOrderTimelineEvents.length }}
                                {{ filteredOrderTimelineEvents.length === 1 ? 'event' : 'events' }}
                            </span>
                            <div class="inline-flex items-center gap-1 rounded-md border border-border bg-background/80 p-0.5">
                                <button
                                    type="button"
                                    class="rounded px-2 py-1 text-xs"
                                    :class="selectedOrderTimelineFilter === 'all'
                                        ? 'bg-primary text-primary-foreground'
                                        : 'text-foreground'"
                                    @click="selectedOrderTimelineFilter = 'all'"
                                >
                                    All
                                </button>
                                <button
                                    type="button"
                                    class="rounded px-2 py-1 text-xs"
                                    :class="selectedOrderTimelineFilter === 'created'
                                        ? 'bg-primary text-primary-foreground'
                                        : 'text-foreground'"
                                    @click="selectedOrderTimelineFilter = 'created'"
                                >
                                    Created
                                </button>
                                <button
                                    type="button"
                                    class="rounded px-2 py-1 text-xs"
                                    :class="selectedOrderTimelineFilter === 'prescribed'
                                        ? 'bg-primary text-primary-foreground'
                                        : 'text-foreground'"
                                    @click="selectedOrderTimelineFilter = 'prescribed'"
                                >
                                    Prescribed
                                </button>
                                <button
                                    type="button"
                                    class="rounded px-2 py-1 text-xs"
                                    :class="selectedOrderTimelineFilter === 'fulfilled'
                                        ? 'bg-primary text-primary-foreground'
                                        : 'text-foreground'"
                                    @click="selectedOrderTimelineFilter = 'fulfilled'"
                                >
                                    Fulfilled
                                </button>
                                <button
                                    type="button"
                                    class="rounded px-2 py-1 text-xs"
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
                            No orders yet. When the patient places medication orders, they will appear here.
                        </p>
                        <p
                            v-else-if="!filteredOrderTimelineEvents.length"
                            class="text-sm text-muted-foreground"
                        >
                            No events match this filter yet.
                        </p>

                        <div v-else class="space-y-4">
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
                                                {{ formatDateTime(event.occurred_at) }}
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
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

