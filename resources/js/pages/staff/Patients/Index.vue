<script setup lang="ts">
import { onMounted, ref, computed } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import type { BreadcrumbItem } from '@/types'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
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

const count = ref<number | null>(null)
const loadingCount = ref(false)
const countError = ref<string | null>(null)

const selectedPatient = ref<PatientDetail | null>(null)
const loadingDetail = ref(false)
const detailError = ref<string | null>(null)

const hasNextPage = computed(() => !!meta.value?.next_page_url)
const hasPrevPage = computed(() => !!meta.value?.prev_page_url)

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
            <div class="flex flex-1 gap-2">
              <Input v-model="search" type="search" placeholder="Search by name or email" class="max-w-xs" />
              <Input v-model.number="perPage" type="number" min="1" max="100" class="w-24" />
              <Button type="button" size="sm" @click="applyFilters">Apply</Button>
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
                  <td class="px-3 py-2 font-medium text-foreground">{{ patient.fname }} {{ patient.lname }}</td>
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

            <div v-if="selectedPatient.medical_history" class="space-y-3">
              <p class="text-sm font-medium text-foreground">Medical history</p>

              <div v-if="selectedPatient.medical_history.allergies.length">
                <p class="font-semibold">Allergies</p>
                <ul class="list-disc pl-4">
                  <li v-for="allergy in selectedPatient.medical_history.allergies" :key="allergy.id">
                    <span class="font-medium">{{ allergy.allergen }}</span>
                    <span v-if="allergy.reaction"> – {{ allergy.reaction }}</span>
                    <Badge v-if="allergy.severity" variant="outline" class="ml-1 capitalize">
                      {{ allergy.severity }}
                    </Badge>
                    <span v-if="allergy.notes" class="block text-muted-foreground">{{ allergy.notes }}</span>
                  </li>
                </ul>
              </div>

              <div v-if="selectedPatient.medical_history.conditions.length">
                <p class="font-semibold">Conditions</p>
                <ul class="list-disc pl-4">
                  <li v-for="condition in selectedPatient.medical_history.conditions" :key="condition.id">
                    <span class="font-medium">{{ condition.condition_name }}</span>
                    <span v-if="condition.diagnosed_at"> – diagnosed {{ formatDate(condition.diagnosed_at) }}</span>
                    <span v-if="condition.is_chronic" class="ml-1 text-xs text-muted-foreground">(chronic)</span>
                    <span v-if="condition.notes" class="block text-muted-foreground">{{ condition.notes }}</span>
                  </li>
                </ul>
              </div>

              <div v-if="selectedPatient.medical_history.medications.length">
                <p class="font-semibold">Medications</p>
                <ul class="list-disc pl-4">
                  <li v-for="med in selectedPatient.medical_history.medications" :key="med.id">
                    <span class="font-medium">{{ med.medication_name || 'Medication #' + med.medication_id }}</span>
                    <span v-if="med.dosage"> – {{ med.dosage }}</span>
                    <span v-if="med.frequency" class="ml-1 text-xs text-muted-foreground">({{ med.frequency }})</span>
                    <div class="text-muted-foreground">
                      <span v-if="med.start_date">{{ formatDate(med.start_date) }}</span>
                      <span v-if="med.start_date && med.end_date">
                        –
                      </span>
                      <span v-if="med.end_date">{{ formatDate(med.end_date) }}</span>
                    </div>
                    <span v-if="med.notes" class="block text-muted-foreground">{{ med.notes }}</span>
                  </li>
                </ul>
              </div>

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

              <div v-if="selectedPatient.medical_history.family_history">
                <p class="font-semibold">Family history</p>
                <p>
                  Chronic pain:
                  <span>
                    {{ selectedPatient.medical_history.family_history.chronic_pain ? 'yes' : 'no' }}
                  </span>
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
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>

