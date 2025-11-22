<script setup lang="ts">
import { ref, computed } from 'vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Badge } from '@/components/ui/badge'

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
    records: AuditRecord[]
    loading?: boolean
    total?: number
}

const props = withDefaults(defineProps<Props>(), {
    loading: false,
    total: 0,
})

const emit = defineEmits<{
    'view-details': [record: AuditRecord]
    'export': []
    'filter': [filters: Record<string, any>]
}>()

const searchQuery = ref('')
const filterAction = ref('')
const filterStatus = ref('')

const filteredRecords = computed(() => {
    return props.records.filter(record => {
        const matchesSearch = !searchQuery.value ||
            record.patient_id.includes(searchQuery.value) ||
            record.user_id.includes(searchQuery.value) ||
            record.action.toLowerCase().includes(searchQuery.value.toLowerCase())

        const matchesAction = !filterAction.value || record.action === filterAction.value
        const matchesStatus = !filterStatus.value || record.status === filterStatus.value

        return matchesSearch && matchesAction && matchesStatus
    })
})

const uniqueActions = computed(() => {
    return [...new Set(props.records.map(r => r.action))]
})

const handleExport = () => {
    emit('export')
}

const getStatusColor = (status: string) => {
    return status === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
}

const formatDate = (date: string) => {
    return new Date(date).toLocaleString()
}
</script>

<template>
    <Card class="w-full">
        <CardHeader>
            <div class="flex justify-between items-start">
                <div>
                    <CardTitle>Audit Trail</CardTitle>
                    <CardDescription>View all system access and data modifications</CardDescription>
                </div>
                <Button @click="handleExport" variant="outline" size="sm">
                    Export
                </Button>
            </div>
        </CardHeader>

        <CardContent class="space-y-6">
            <!-- Filters -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="space-y-2">
                    <Label for="search">Search</Label>
                    <Input
                        id="search"
                        v-model="searchQuery"
                        type="text"
                        placeholder="Patient ID, User ID, or Action..."
                    />
                </div>

                <div class="space-y-2">
                    <Label for="action-filter">Action</Label>
                    <select
                        id="action-filter"
                        v-model="filterAction"
                        class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                    >
                        <option value="">All Actions</option>
                        <option v-for="action in uniqueActions" :key="action" :value="action">
                            {{ action }}
                        </option>
                    </select>
                </div>

                <div class="space-y-2">
                    <Label for="status-filter">Status</Label>
                    <select
                        id="status-filter"
                        v-model="filterStatus"
                        class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                    >
                        <option value="">All Statuses</option>
                        <option value="success">Success</option>
                        <option value="failure">Failure</option>
                    </select>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b bg-secondary">
                        <tr>
                            <th class="text-left px-4 py-2 font-semibold">Timestamp</th>
                            <th class="text-left px-4 py-2 font-semibold">User</th>
                            <th class="text-left px-4 py-2 font-semibold">Action</th>
                            <th class="text-left px-4 py-2 font-semibold">Patient</th>
                            <th class="text-left px-4 py-2 font-semibold">Status</th>
                            <th class="text-left px-4 py-2 font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="record in filteredRecords" :key="record.id" class="border-b hover:bg-secondary/50">
                            <td class="px-4 py-2">{{ formatDate(record.timestamp) }}</td>
                            <td class="px-4 py-2 font-mono text-xs">{{ record.user_id }}</td>
                            <td class="px-4 py-2">{{ record.action }}</td>
                            <td class="px-4 py-2 font-mono text-xs">{{ record.patient_id }}</td>
                            <td class="px-4 py-2">
                                <Badge :class="getStatusColor(record.status)">
                                    {{ record.status }}
                                </Badge>
                            </td>
                            <td class="px-4 py-2">
                                <Button
                                    @click="emit('view-details', record)"
                                    variant="ghost"
                                    size="sm"
                                >
                                    View
                                </Button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Empty State -->
            <div v-if="filteredRecords.length === 0" class="text-center py-8 text-muted-foreground">
                <p>No audit records found</p>
            </div>

            <!-- Summary -->
            <div class="text-sm text-muted-foreground">
                Showing {{ filteredRecords.length }} of {{ total }} records
            </div>
        </CardContent>
    </Card>
</template>

