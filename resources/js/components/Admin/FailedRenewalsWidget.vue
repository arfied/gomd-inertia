<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { AlertCircle, Loader2, AlertTriangle } from 'lucide-vue-next'

interface FailedRenewal {
    saga_uuid: string
    subscription_id: number
    user_id: number
    user_name: string
    user_email: string
    amount: number
    reason: string
    error_message: string | null
    failed_at: string
    attempts_made: number
}

interface Summary {
    total_failures: number
    total_amount: number
    period_days: number
    period_start: string
    period_end: string
}

const loading = ref(true)
const error = ref<string | null>(null)
const failedRenewals = ref<FailedRenewal[]>([])
const summary = ref<Summary | null>(null)
const days = ref(7)

const fetchFailedRenewals = async () => {
    loading.value = true
    error.value = null

    try {
        const response = await fetch(`/admin/failed-renewals?days=${days.value}&limit=10`, {
            headers: { 'Accept': 'application/json' },
        })

        if (!response.ok) {
            throw new Error('Failed to fetch failed renewals')
        }

        const data = await response.json()
        failedRenewals.value = data.data
        summary.value = data.summary
    } catch (err) {
        error.value = err instanceof Error ? err.message : 'An error occurred'
    } finally {
        loading.value = false
    }
}

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(amount)
}

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    })
}

onMounted(() => {
    fetchFailedRenewals()
})
</script>

<template>
    <Card class="w-full">
        <CardHeader>
            <div class="flex items-center justify-between">
                <div>
                    <CardTitle class="flex items-center gap-2">
                        <AlertTriangle class="w-5 h-5 text-red-600" />
                        Failed Renewals
                    </CardTitle>
                    <CardDescription>Recent subscription renewal failures</CardDescription>
                </div>
                <select
                    v-model.number="days"
                    @change="fetchFailedRenewals"
                    class="px-3 py-1 border border-gray-300 rounded text-sm"
                >
                    <option :value="7">Last 7 days</option>
                    <option :value="14">Last 14 days</option>
                    <option :value="30">Last 30 days</option>
                </select>
            </div>
        </CardHeader>

        <CardContent>
            <!-- Error State -->
            <div v-if="error" class="p-4 bg-red-50 border border-red-200 rounded-lg flex gap-3 mb-4">
                <AlertCircle class="text-red-600 flex-shrink-0 w-5 h-5" />
                <p class="text-red-800 text-sm">{{ error }}</p>
            </div>

            <!-- Loading State -->
            <div v-if="loading" class="flex justify-center py-8">
                <Loader2 class="animate-spin text-blue-600 w-6 h-6" />
            </div>

            <!-- Summary Stats -->
            <div v-else-if="summary" class="grid grid-cols-3 gap-4 mb-6">
                <div class="bg-red-50 p-4 rounded-lg">
                    <p class="text-sm text-gray-600">Total Failures</p>
                    <p class="text-2xl font-bold text-red-600">{{ summary.total_failures }}</p>
                </div>
                <div class="bg-orange-50 p-4 rounded-lg">
                    <p class="text-sm text-gray-600">Total Amount</p>
                    <p class="text-2xl font-bold text-orange-600">{{ formatCurrency(summary.total_amount) }}</p>
                </div>
                <div class="bg-blue-50 p-4 rounded-lg">
                    <p class="text-sm text-gray-600">Period</p>
                    <p class="text-sm font-semibold text-blue-600">{{ summary.period_start }} to {{ summary.period_end }}</p>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="!loading && failedRenewals.length === 0" class="text-center py-8">
                <p class="text-gray-500">No failed renewals in the selected period</p>
            </div>

            <!-- Failed Renewals Table -->
            <div v-else-if="!loading && failedRenewals.length > 0" class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b border-gray-200">
                        <tr>
                            <th class="text-left py-2 px-3 font-semibold text-gray-700">User</th>
                            <th class="text-left py-2 px-3 font-semibold text-gray-700">Amount</th>
                            <th class="text-left py-2 px-3 font-semibold text-gray-700">Reason</th>
                            <th class="text-left py-2 px-3 font-semibold text-gray-700">Attempts</th>
                            <th class="text-left py-2 px-3 font-semibold text-gray-700">Failed At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="renewal in failedRenewals" :key="renewal.saga_uuid" class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-3 px-3">
                                <div>
                                    <p class="font-medium text-gray-900">{{ renewal.user_name }}</p>
                                    <p class="text-xs text-gray-500">{{ renewal.user_email }}</p>
                                </div>
                            </td>
                            <td class="py-3 px-3 font-semibold text-red-600">{{ formatCurrency(renewal.amount) }}</td>
                            <td class="py-3 px-3">
                                <span class="inline-block bg-red-100 text-red-800 text-xs px-2 py-1 rounded">
                                    {{ renewal.reason }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-center">{{ renewal.attempts_made }}</td>
                            <td class="py-3 px-3 text-gray-600 text-xs">{{ formatDate(renewal.failed_at) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </CardContent>
    </Card>
</template>

