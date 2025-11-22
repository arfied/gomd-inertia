<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { ConsultationScheduler } from '@/components/Clinical'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Spinner } from '@/components/ui/spinner'
import type { BreadcrumbItemType } from '@/types'

interface Consultation {
    id: string
    patient_id: string
    provider_id: string
    scheduled_at: string
    reason: string
    status: 'scheduled' | 'completed' | 'cancelled'
    notes: string
}

interface Props {
    consultations: {
        data: Consultation[]
        current_page: number
        last_page: number
        per_page: number
        total: number
    }
    selectedConsultation?: Consultation | null
}

const props = withDefaults(defineProps<Props>(), {
    selectedConsultation: null,
})

const showScheduler = ref(false)
const consultations = ref(props.consultations)
const meta = ref(props.consultations)
const loadingList = ref(false)
const listError = ref<string | null>(null)

const search = ref('')
const perPage = ref(15)
const filterByStatus = ref('')

const hasNextPage = computed(() => !!meta.value?.last_page && meta.value.current_page < meta.value.last_page)
const hasPrevPage = computed(() => meta.value?.current_page > 1)

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Clinical', href: '/clinical/questionnaires' },
    { title: 'Consultations', href: '/clinical/consultations' }
]

function buildQuery(base: string): string {
    const params = new URLSearchParams()
    if (search.value.trim() !== '') params.set('search', search.value.trim())
    params.set('per_page', String(perPage.value))
    if (filterByStatus.value) params.set('status', filterByStatus.value)
    const q = params.toString()
    return q ? `${base}?${q}` : base
}

async function loadConsultations(url?: string) {
    loadingList.value = true
    listError.value = null

    try {
        const response = await fetch(url ?? buildQuery('/clinical/consultations'), {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        })

        if (!response.ok) {
            listError.value = `Failed to load consultations (${response.status})`
            consultations.value = { data: [], current_page: 1, last_page: 1, per_page: 15, total: 0 }
            meta.value = { data: [], current_page: 1, last_page: 1, per_page: 15, total: 0 }
            return
        }

        const data = (await response.json()) as any
        consultations.value = data.consultations
        meta.value = data.consultations
    } catch {
        listError.value = 'A network error occurred while loading consultations.'
    } finally {
        loadingList.value = false
    }
}

function applyFilters() {
    void loadConsultations()
}

function goToNextPage() {
    if (meta.value?.last_page && meta.value.current_page < meta.value.last_page) {
        void loadConsultations(`${buildQuery('/clinical/consultations')}&page=${meta.value.current_page + 1}`)
    }
}

function goToPrevPage() {
    if (meta.value?.current_page > 1) {
        void loadConsultations(`${buildQuery('/clinical/consultations')}&page=${meta.value.current_page - 1}`)
    }
}

const handleScheduleConsultation = async (data: any) => {
    try {
        const form = new FormData()
        Object.keys(data).forEach(key => {
            form.append(key, data[key])
        })
        form.append('_method', 'POST')

        const response = await fetch('/clinical/consultations', {
            method: 'POST',
            body: form,
            headers: {
                'Accept': 'application/json',
            }
        })

        if (response.ok) {
            showScheduler.value = false
            void loadConsultations()
        }
    } catch (error) {
        console.error('Failed to schedule consultation:', error)
    }
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Consultations" />

        <div class="space-y-6 p-4">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold">Consultations</h1>
                    <p class="text-muted-foreground">Schedule and manage consultations</p>
                </div>
                <Button @click="showScheduler = true">
                    Schedule Consultation
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Consultations</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                        <div class="flex flex-1 flex-col gap-2">
                            <div class="flex gap-2">
                                <Input v-model="search" type="search" placeholder="Search by reason" class="max-w-xs" />
                                <Input v-model.number="perPage" type="number" min="1" max="100" class="w-24" />
                                <Button type="button" size="sm" @click="applyFilters">Apply</Button>
                            </div>
                            <div class="flex flex-wrap items-center gap-2 text-xs text-muted-foreground">
                                <label class="inline-flex items-center gap-1">
                                    <select v-model="filterByStatus" class="h-6 rounded border border-input bg-background px-2 text-xs">
                                        <option value="">All statuses</option>
                                        <option value="scheduled">Scheduled</option>
                                        <option value="completed">Completed</option>
                                        <option value="cancelled">Cancelled</option>
                                    </select>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="border rounded-md">
                        <div v-if="loadingList" class="flex items-center gap-2 p-4 text-sm text-muted-foreground">
                            <Spinner class="h-4 w-4" />
                            <span>Loading consultations…</span>
                        </div>
                        <p v-else-if="listError" class="p-4 text-sm text-destructive">{{ listError }}</p>
                        <p v-else-if="!consultations.data.length" class="p-4 text-sm text-muted-foreground">No consultations found.</p>
                        <div v-else class="space-y-4 p-4">
                            <div v-for="consultation in consultations.data" :key="consultation.id" class="border rounded-lg p-4">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="font-semibold">{{ new Date(consultation.scheduled_at).toLocaleDateString() }}</p>
                                        <p class="text-sm text-muted-foreground">{{ new Date(consultation.scheduled_at).toLocaleTimeString() }}</p>
                                        <p class="text-sm mt-2">Reason: {{ consultation.reason }}</p>
                                    </div>
                                    <span :class="[
                                        'px-3 py-1 rounded-full text-sm font-medium',
                                        consultation.status === 'scheduled' ? 'bg-blue-100 text-blue-800' :
                                        consultation.status === 'completed' ? 'bg-green-100 text-green-800' :
                                        'bg-gray-100 text-gray-800'
                                    ]">
                                        {{ consultation.status }}
                                    </span>
                                </div>
                            </div>
                        </div>
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

            <!-- Schedule Consultation Modal -->
            <div v-if="showScheduler" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <Card class="w-full max-w-md">
                    <CardHeader>
                        <CardTitle>Schedule Consultation</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <ConsultationScheduler
                            @submit="handleScheduleConsultation"
                            @cancel="showScheduler = false"
                        />
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>

