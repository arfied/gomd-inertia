<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import Layout from '@/layouts/AppLayout.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Spinner } from '@/components/ui/spinner'

interface Consent {
    id: string
    patient_id: string
    consent_type: string
    status: string
    granted_at: string
}

interface Props {
    consents: {
        data: Consent[]
        current_page: number
        last_page: number
        per_page: number
        total: number
    }
    selectedConsent?: Consent | null
}

const props = withDefaults(defineProps<Props>(), {
    selectedConsent: null,
})

const showCreateModal = ref(false)
const consents = ref(props.consents)
const meta = ref(props.consents)
const loadingList = ref(false)
const listError = ref<string | null>(null)

const search = ref('')
const perPage = ref(15)
const filterByStatus = ref('')

const hasNextPage = computed(() => !!meta.value?.last_page && meta.value.current_page < meta.value.last_page)
const hasPrevPage = computed(() => meta.value?.current_page > 1)

function buildQuery(base: string): string {
    const params = new URLSearchParams()
    if (search.value.trim() !== '') params.set('search', search.value.trim())
    params.set('per_page', String(perPage.value))
    if (filterByStatus.value) params.set('status', filterByStatus.value)
    const q = params.toString()
    return q ? `${base}?${q}` : base
}

async function loadConsents(url?: string) {
    loadingList.value = true
    listError.value = null

    try {
        const response = await fetch(url ?? buildQuery('/compliance/consents'), {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        })

        if (!response.ok) {
            listError.value = `Failed to load consents (${response.status})`
            consents.value = { data: [], current_page: 1, last_page: 1, per_page: 15, total: 0 }
            meta.value = { data: [], current_page: 1, last_page: 1, per_page: 15, total: 0 }
            return
        }

        const data = (await response.json()) as any
        consents.value = data.consents
        meta.value = data.consents
    } catch {
        listError.value = 'A network error occurred while loading consents.'
    } finally {
        loadingList.value = false
    }
}

function applyFilters() {
    void loadConsents()
}

function goToNextPage() {
    if (meta.value?.last_page && meta.value.current_page < meta.value.last_page) {
        void loadConsents(`${buildQuery('/compliance/consents')}&page=${meta.value.current_page + 1}`)
    }
}

function goToPrevPage() {
    if (meta.value?.current_page > 1) {
        void loadConsents(`${buildQuery('/compliance/consents')}&page=${meta.value.current_page - 1}`)
    }
}

const handleCreateConsent = () => {
    showCreateModal.value = true
}
</script>

<template>
    <Layout>
        <Head title="Consents" />

        <div class="space-y-6 p-4">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold">Consents</h1>
                    <p class="text-muted-foreground">Manage patient consents and permissions</p>
                </div>
                <Button @click="handleCreateConsent">
                    Grant Consent
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Consents</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                        <div class="flex flex-1 flex-col gap-2">
                            <div class="flex gap-2">
                                <Input v-model="search" type="search" placeholder="Search by consent type" class="max-w-xs" />
                                <Input v-model.number="perPage" type="number" min="1" max="100" class="w-24" />
                                <Button type="button" size="sm" @click="applyFilters">Apply</Button>
                            </div>
                            <div class="flex flex-wrap items-center gap-2 text-xs text-muted-foreground">
                                <label class="inline-flex items-center gap-1">
                                    <select v-model="filterByStatus" class="h-6 rounded border border-input bg-background px-2 text-xs">
                                        <option value="">All statuses</option>
                                        <option value="active">Active</option>
                                        <option value="revoked">Revoked</option>
                                        <option value="expired">Expired</option>
                                    </select>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="border rounded-md">
                        <div v-if="loadingList" class="flex items-center gap-2 p-4 text-sm text-muted-foreground">
                            <Spinner class="h-4 w-4" />
                            <span>Loading consents…</span>
                        </div>
                        <p v-else-if="listError" class="p-4 text-sm text-destructive">{{ listError }}</p>
                        <p v-else-if="!consents.data.length" class="p-4 text-sm text-muted-foreground">No consents found.</p>
                        <div v-else class="space-y-4 p-4">
                            <div v-for="consent in consents.data" :key="consent.id" class="border-b pb-4 last:border-b-0">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="font-semibold">{{ consent.consent_type }}</p>
                                        <p class="text-sm text-muted-foreground">Patient: {{ consent.patient_id }}</p>
                                        <p class="text-xs text-muted-foreground mt-2">{{ consent.granted_at }}</p>
                                    </div>
                                    <span :class="[
                                        'px-3 py-1 rounded-full text-sm font-medium',
                                        consent.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
                                    ]">
                                        {{ consent.status }}
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
        </div>

        <!-- Create Consent Modal would go here -->
    </Layout>
</template>
