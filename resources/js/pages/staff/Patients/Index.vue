<script setup lang="ts">
import { onMounted, ref, computed } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import type { BreadcrumbItem } from '@/types'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Spinner } from '@/components/ui/spinner'

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Patients', href: '/dashboard/patients' },
]

interface PatientListItem {
  patient_uuid: string
  user_id: number
  fname: string | null
  lname: string | null
  email: string | null
  status: string | null
  enrolled_at: string | null
}

interface PatientDetail extends PatientListItem {
  demographics: { gender: string | null; dob: string | null } | null
  enrollment: {
    source: string | null
    metadata: Record<string, unknown> | null
    enrolled_at: string | null
  } | null
  subscription: {
    status: string
    plan_name: string | null
    is_trial: boolean
    starts_at: string | null
    ends_at: string | null
  } | null
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
                  <th class="px-3 py-2">Enrolled at</th>
                  <th class="px-3 py-2" />
                </tr>
              </thead>
              <tbody>
                <tr v-for="patient in patients" :key="patient.patient_uuid" class="border-b last:border-b-0">
                  <td class="px-3 py-2 font-medium text-foreground">{{ patient.fname }} {{ patient.lname }}</td>
                  <td class="px-3 py-2">{{ patient.email }}</td>
                  <td class="px-3 py-2 capitalize">{{ patient.status || 'unknown' }}</td>
                  <td class="px-3 py-2 text-xs text-muted-foreground">{{ patient.enrolled_at }}</td>
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
          <div v-else class="space-y-1 text-xs">
            <p class="text-sm font-medium text-foreground">{{ selectedPatient.fname }} {{ selectedPatient.lname }}</p>
            <p>{{ selectedPatient.email }}</p>
            <p>Status: <span class="capitalize">{{ selectedPatient.status || 'unknown' }}</span></p>
            <p v-if="selectedPatient.demographics?.dob">DOB: {{ selectedPatient.demographics.dob }}</p>
            <p v-if="selectedPatient.subscription">Plan: {{ selectedPatient.subscription.plan_name || 'TeleMed Pro plan' }}</p>
          </div>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>

