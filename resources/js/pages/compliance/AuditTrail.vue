<script setup lang="ts">
import { ref } from 'vue'
import { AuditTrailTable, AuditTrailTimeline } from '@/components/Compliance'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
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
        <div class="space-y-6">
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

        <!-- Table View -->
        <div v-if="viewMode === 'table'">
            <AuditTrailTable
                :records="props.auditTrail.data"
                :total="props.auditTrail.total"
                @export="handleExport"
                @view-details="viewDetails"
            />
        </div>

        <!-- Timeline View -->
        <div v-else>
            <AuditTrailTimeline
                :records="props.auditTrail.data"
                @view-details="viewDetails"
            />
        </div>

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

