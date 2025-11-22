<script setup lang="ts">
import { computed } from 'vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
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
}

defineProps<Props>()

const emit = defineEmits<{
    'view-details': [record: AuditRecord]
}>()

const sortedRecords = computed(() => {
    return [...props.records].sort((a, b) =>
        new Date(b.timestamp).getTime() - new Date(a.timestamp).getTime()
    )
})

const getActionIcon = (action: string): string => {
    const iconMap: Record<string, string> = {
        'CREATE': '➕',
        'READ': '👁️',
        'UPDATE': '✏️',
        'DELETE': '🗑️',
        'EXPORT': '📤',
        'IMPORT': '📥',
        'LOGIN': '🔓',
        'LOGOUT': '🔒',
    }
    return iconMap[action.toUpperCase()] || '📋'
}

const getStatusColor = (status: string): string => {
    return status === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
}

const formatDate = (date: string): string => {
    return new Date(date).toLocaleString()
}

const formatTime = (date: string): string => {
    return new Date(date).toLocaleTimeString()
}
</script>

<template>
    <Card class="w-full">
        <CardHeader>
            <CardTitle>Audit Timeline</CardTitle>
            <CardDescription>Chronological view of all system activities</CardDescription>
        </CardHeader>

        <CardContent>
            <div class="space-y-0">
                <div v-for="(record, index) in sortedRecords" :key="record.id" class="relative">
                    <!-- Timeline Line -->
                    <div v-if="index < sortedRecords.length - 1" class="absolute left-6 top-12 w-0.5 h-12 bg-border" />

                    <!-- Timeline Item -->
                    <div class="flex gap-4 pb-8">
                        <!-- Timeline Dot -->
                        <div class="flex flex-col items-center">
                            <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center text-lg relative z-10">
                                {{ getActionIcon(record.action) }}
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="flex-1 pt-1">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="font-semibold">{{ record.action }}</span>
                                        <Badge :class="getStatusColor(record.status)" class="text-xs">
                                            {{ record.status }}
                                        </Badge>
                                    </div>
                                    <p class="text-sm text-muted-foreground">
                                        {{ record.resource_type }} ({{ record.resource_id }})
                                    </p>
                                    <p class="text-xs text-muted-foreground mt-1">
                                        Patient: {{ record.patient_id }}
                                    </p>
                                </div>

                                <!-- Time -->
                                <div class="text-right">
                                    <p class="text-sm font-mono">{{ formatTime(record.timestamp) }}</p>
                                    <p class="text-xs text-muted-foreground">{{ formatDate(record.timestamp) }}</p>
                                </div>
                            </div>

                            <!-- Details -->
                            <div class="mt-3 p-3 bg-secondary/50 rounded text-xs space-y-1">
                                <p><span class="font-semibold">User:</span> {{ record.user_id }}</p>
                                <p><span class="font-semibold">IP:</span> {{ record.ip_address }}</p>
                                <button
                                    @click="emit('view-details', record)"
                                    class="text-primary hover:underline mt-2"
                                >
                                    View Full Details →
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="sortedRecords.length === 0" class="text-center py-12 text-muted-foreground">
                <p>No audit records found</p>
            </div>
        </CardContent>
    </Card>
</template>

