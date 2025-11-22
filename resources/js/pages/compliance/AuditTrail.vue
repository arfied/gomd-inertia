<script setup lang="ts">
import { ref, computed } from 'vue'
import { AuditTrailTable, AuditTrailTimeline } from '@/components/Compliance'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import AppLayout from '@/layouts/AppLayout.vue'
import type { BreadcrumbItemType } from '@/types'

interface AuditRecord {
    id: string
    user_id: string
    action: string
    resource_type: string
    resource_id: string
    patient_id: string
    timestamp: string
    ip_address: string
    user_agent: string
    status: 'success' | 'failure'
}

interface Props {
    auditTrail: {
        data: AuditRecord[]
        current_page: number
        last_page: number
        per_page: number
        total: number
    }
    selectedAudit?: AuditRecord | null
}

const props = withDefaults(defineProps<Props>(), {
    selectedAudit: null,
})

const viewMode = ref<'table' | 'timeline'>('table')
const selectedRecord = ref<AuditRecord | null>(props.selectedAudit)
const showDetails = ref(false)
const auditTrail = ref(props.auditTrail)
const meta = ref(props.auditTrail)
const loadingList = ref(false)
const listError = ref<string | null>(null)

const search = ref('')
const perPage = ref(25)
const filterByAccessType = ref('')

const hasNextPage = computed(() => !!meta.value?.last_page && meta.value.current_page < meta.value.last_page)
const hasPrevPage = computed(() => meta.value?.current_page > 1)

function buildQuery(base: string): string {
    const params = new URLSearchParams()
    if (search.value.trim() !== '') params.set('search', search.value.trim())
    params.set('per_page', String(perPage.value))
    if (filterByAccessType.value) params.set('access_type', filterByAccessType.value)
    const q = params.toString()
    return q ? `${base}?${q}` : base
}

async function loadAuditTrail(url?: string) {
    loadingList.value = true
    listError.value = null

    try {
        const response = await fetch(url ?? buildQuery('/compliance/audit-trail'), {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        })

        if (!response.ok) {
            listError.value = `Failed to load audit trail (${response.status})`
            auditTrail.value = { data: [], current_page: 1, last_page: 1, per_page: 25, total: 0 }
            meta.value = { data: [], current_page: 1, last_page: 1, per_page: 25, total: 0 }
            return
        }

        const data = (await response.json()) as any
        auditTrail.value = data.auditTrail
        meta.value = data.auditTrail
    } catch {
        listError.value = 'A network error occurred while loading audit trail.'
    } finally {
        loadingList.value = false
    }
}

function applyFilters() {
    void loadAuditTrail()
}

function goToNextPage() {
    if (meta.value?.last_page && meta.value.current_page < meta.value.last_page) {
        void loadAuditTrail(`${buildQuery('/compliance/audit-trail')}&page=${meta.value.current_page + 1}`)
    }
}

function goToPrevPage() {
    if (meta.value?.current_page > 1) {
        void loadAuditTrail(`${buildQuery('/compliance/audit-trail')}&page=${meta.value.current_page - 1}`)
    }
}

const handleExport = async () => {
    try {
        const response = await fetch('/compliance/audit-trail/export', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
            }
        })
        if (response.ok) {
            const data = await response.json()
            const csv = convertToCSV(data.records)
            downloadCSV(csv, `audit-trail-${new Date().toISOString().split('T')[0]}.csv`)
        }
    } catch (error) {
        console.error('Failed to export audit trail:', error)
    }
}

const convertToCSV = (records: AuditRecord[]): string => {
    const headers = ['Timestamp', 'User', 'Action', 'Resource', 'Patient', 'Status']
    const rows = records.map(r => [
        r.timestamp,
        r.user_id,
        r.action,
        `${r.resource_type}:${r.resource_id}`,
        r.patient_id,
        r.status
    ])
    return [headers, ...rows].map(row => row.join(',')).join('\n')
}

const downloadCSV = (csv: string, filename: string) => {
    const blob = new Blob([csv], { type: 'text/csv' })
    const url = window.URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = filename
    a.click()
    window.URL.revokeObjectURL(url)
}

const viewDetails = (record: AuditRecord) => {
    selectedRecord.value = record
    showDetails.value = true
}

const closeDetails = () => {
    showDetails.value = false
    selectedRecord.value = null
}

const breadcrumbs: BreadcrumbItemType[] = [
  { title: 'Compliance', href: '/compliance' },
  { title: 'Audit Trail', href: '/compliance/audit-trail' }
]
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4">
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-3xl font-bold">Audit Trail</h1>
                    <p class="text-muted-foreground">Monitor all system access and data modifications</p>
                </div>
                <div class="flex gap-2">
                    <Button
                        :variant="viewMode === 'table' ? 'default' : 'outline'"
                        @click="viewMode = 'table'"
                    >
                        Table View
                    </Button>
                    <Button
                        :variant="viewMode === 'timeline' ? 'default' : 'outline'"
                        @click="viewMode = 'timeline'"
                    >
                        Timeline View
                    </Button>
                </div>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Audit Records</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                        <div class="flex flex-1 flex-col gap-2">
                            <div class="flex gap-2">
                                <Input v-model="search" type="search" placeholder="Search by patient ID" class="max-w-xs" />
                                <Input v-model.number="perPage" type="number" min="1" max="100" class="w-24" />
                                <Button type="button" size="sm" @click="applyFilters">Apply</Button>
                            </div>
                            <div class="flex flex-wrap items-center gap-2 text-xs text-muted-foreground">
                                <label class="inline-flex items-center gap-1">
                                    <select v-model="filterByAccessType" class="h-6 rounded border border-input bg-background px-2 text-xs">
                                        <option value="">All access types</option>
                                        <option value="read">Read</option>
                                        <option value="write">Write</option>
                                        <option value="delete">Delete</option>
                                    </select>
                                </label>
                            </div>
                        </div>
                        <Button type="button" size="sm" @click="handleExport">Export CSV</Button>
                    </div>

                    <div class="border rounded-md">
                        <div v-if="loadingList" class="flex items-center gap-2 p-4 text-sm text-muted-foreground">
                            <Spinner class="h-4 w-4" />
                            <span>Loading audit trail…</span>
                        </div>
                        <p v-else-if="listError" class="p-4 text-sm text-destructive">{{ listError }}</p>
                        <p v-else-if="!auditTrail.data.length" class="p-4 text-sm text-muted-foreground">No audit records found.</p>
                        <div v-else>
                            <!-- Table View -->
                            <div v-if="viewMode === 'table'">
                                <AuditTrailTable
                                    :records="auditTrail.data"
                                    :total="auditTrail.total"
                                    @export="handleExport"
                                    @view-details="viewDetails"
                                />
                            </div>

                            <!-- Timeline View -->
                            <div v-else>
                                <AuditTrailTimeline
                                    :records="auditTrail.data"
                                    @view-details="viewDetails"
                                />
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

        <!-- Details Modal -->
        <div v-if="showDetails && selectedRecord" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <Card class="w-full max-w-2xl mx-4">
                <CardHeader>
                    <CardTitle>Audit Record Details</CardTitle>
                    <button @click="closeDetails" class="absolute top-4 right-4 text-muted-foreground hover:text-foreground">
                        ✕
                    </button>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-muted-foreground">Timestamp</p>
                            <p class="font-mono">{{ selectedRecord.timestamp }}</p>
                        </div>
                        <div>
                            <p class="text-muted-foreground">User ID</p>
                            <p class="font-mono">{{ selectedRecord.user_id }}</p>
                        </div>
                        <div>
                            <p class="text-muted-foreground">Action</p>
                            <p>{{ selectedRecord.action }}</p>
                        </div>
                        <div>
                            <p class="text-muted-foreground">Status</p>
                            <p>{{ selectedRecord.status }}</p>
                        </div>
                        <div>
                            <p class="text-muted-foreground">Resource</p>
                            <p class="font-mono">{{ selectedRecord.resource_type }}:{{ selectedRecord.resource_id }}</p>
                        </div>
                        <div>
                            <p class="text-muted-foreground">Patient ID</p>
                            <p class="font-mono">{{ selectedRecord.patient_id }}</p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-muted-foreground">IP Address</p>
                            <p class="font-mono">{{ selectedRecord.ip_address }}</p>
                        </div>
                    </div>
                    <Button @click="closeDetails" class="w-full">Close</Button>
                </CardContent>
            </Card>
            </div>
        </div>
    </AppLayout>
</template>

