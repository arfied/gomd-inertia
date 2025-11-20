<template>
    <div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Subscription Analytics</h1>
                <p class="mt-2 text-gray-600">Monitor your revenue, churn, and customer lifetime value</p>
            </div>

            <!-- Month Selector -->
            <div class="mb-6 flex gap-4">
                <input
                    v-model="selectedMonth"
                    type="month"
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
                <button
                    @click="loadDashboard"
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                >
                    Load Data
                </button>
            </div>

            <!-- Loading State -->
            <div v-if="loading" class="text-center py-12">
                <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
                <p class="mt-4 text-gray-600">Loading analytics data...</p>
            </div>

            <!-- Error State -->
            <div v-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <p class="text-red-800">{{ error }}</p>
            </div>

            <!-- Analytics Cards -->
            <div v-if="!loading && dashboardData" class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- MRR Card -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Monthly Recurring Revenue</h3>
                    <p class="text-3xl font-bold text-blue-600">{{ formatCurrency(dashboardData.mrr.current_mrr) }}</p>
                    <p class="text-sm text-gray-600 mt-2">
                        <span :class="dashboardData.mrr.change_percent >= 0 ? 'text-green-600' : 'text-red-600'">
                            {{ dashboardData.mrr.change_percent >= 0 ? '+' : '' }}{{ dashboardData.mrr.change_percent.toFixed(2) }}%
                        </span>
                        from last month
                    </p>
                </div>

                <!-- Churn Rate Card -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Churn Rate</h3>
                    <p class="text-3xl font-bold text-orange-600">{{ dashboardData.churn.churn_rate.toFixed(2) }}%</p>
                    <p class="text-sm text-gray-600 mt-2">
                        {{ dashboardData.churn.churned_count }} of {{ dashboardData.churn.active_at_start }} subscriptions
                    </p>
                </div>

                <!-- Average LTV Card -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Average Lifetime Value</h3>
                    <p class="text-3xl font-bold text-green-600">{{ formatCurrency(dashboardData.ltv.average_ltv) }}</p>
                    <p class="text-sm text-gray-600 mt-2">
                        {{ dashboardData.ltv.total_subscriptions }} subscriptions
                    </p>
                </div>
            </div>

            <!-- Charts Section -->
            <div v-if="!loading && dashboardData" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- MRR Trend Chart -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">MRR Trend (12 Months)</h3>
                    <div class="h-64 flex items-end gap-1">
                        <div
                            v-for="(month, idx) in dashboardData.mrr.trend"
                            :key="idx"
                            class="flex-1 bg-blue-500 rounded-t hover:bg-blue-600 transition"
                            :style="{ height: `${(month.mrr / maxMrr) * 100}%` }"
                            :title="`${month.month}: ${formatCurrency(month.mrr)}`"
                        ></div>
                    </div>
                </div>

                <!-- Churn Reasons Chart -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Churn Reasons</h3>
                    <div class="space-y-3">
                        <div
                            v-for="(reason, idx) in dashboardData.churn.churn_reasons"
                            :key="idx"
                            class="flex items-center justify-between"
                        >
                            <span class="text-gray-700">{{ reason.reason }}</span>
                            <div class="flex items-center gap-2">
                                <div class="w-32 bg-gray-200 rounded-full h-2">
                                    <div
                                        class="bg-orange-500 h-2 rounded-full"
                                        :style="{ width: `${(reason.count / dashboardData.churn.churned_count) * 100}%` }"
                                    ></div>
                                </div>
                                <span class="text-sm text-gray-600 w-12 text-right">{{ reason.count }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- LTV Distribution Chart -->
                <div class="bg-white rounded-lg shadow p-6 lg:col-span-2">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Customer LTV Distribution</h3>
                    <div class="grid grid-cols-5 gap-4">
                        <div
                            v-for="(bucket, key) in dashboardData.ltv.distribution"
                            :key="key"
                            class="text-center"
                        >
                            <div class="text-2xl font-bold text-gray-900">{{ bucket }}</div>
                            <div class="text-xs text-gray-600 mt-1">{{ formatBucketLabel(key) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { usePage } from '@inertiajs/vue3'

const page = usePage()
const loading = ref(false)
const error = ref('')
const selectedMonth = ref(new Date().toISOString().slice(0, 7))
const dashboardData = ref(null)

const maxMrr = computed(() => {
    if (!dashboardData.value?.mrr?.trend) return 1
    return Math.max(...dashboardData.value.mrr.trend.map((m: any) => m.mrr))
})

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(value)
}

const formatBucketLabel = (key: string) => {
    const labels: Record<string, string> = {
        under_100: 'Under $100',
        '100_to_500': '$100-$500',
        '500_to_1000': '$500-$1K',
        '1000_to_5000': '$1K-$5K',
        over_5000: 'Over $5K',
    }
    return labels[key] || key
}

const loadDashboard = async () => {
    loading.value = true
    error.value = ''

    try {
        const response = await fetch(
            `/analytics/subscription/dashboard?month=${selectedMonth.value}&include_trend=true&include_reasons=true&include_by_plan=true&include_distribution=true`,
            {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            }
        )

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`)
        }

        dashboardData.value = await response.json()
    } catch (err) {
        error.value = `Failed to load analytics data: ${err instanceof Error ? err.message : 'Unknown error'}`
    } finally {
        loading.value = false
    }
}

onMounted(() => {
    loadDashboard()
})
</script>

