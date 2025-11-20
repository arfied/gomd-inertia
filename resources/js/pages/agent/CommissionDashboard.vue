<script setup lang="ts">
import { computed, ref } from 'vue'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { dashboard } from '@/routes/agent/commission'
import { type BreadcrumbItem } from '@/types'
import { Head, router } from '@inertiajs/vue3'
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Spinner } from '@/components/ui/spinner'

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Agent',
        href: '/agent',
    },
    {
        title: 'Commission Dashboard',
        href: dashboard().url,
    },
]

interface EarningsOverview {
    current: number
    previous: number
    change: number
    period: string
}

interface Commission {
    id: number
    commission_amount: number
    status: string
    created_at: string
}

interface CommissionsData {
    data: Commission[]
    total: number
    per_page: number
    current_page: number
    last_page: number
}

interface ReferralHierarchy {
    upline: any
    downline: any[]
}

interface Props {
    earnings_overview: EarningsOverview
    recent_commissions: CommissionsData
    referral_hierarchy: ReferralHierarchy
}

const props = defineProps<Props>()

const period = ref<string>('month')
const loading = ref(false)

const formattedCurrent = computed(() => {
    if (!props.earnings_overview) return '$0.00'
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(props.earnings_overview.current)
})

const formattedPrevious = computed(() => {
    if (!props.earnings_overview) return '$0.00'
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(props.earnings_overview.previous)
})

const changeIndicator = computed(() => {
    if (!props.earnings_overview) return '0%'
    const change = props.earnings_overview.change
    return `${change > 0 ? '+' : ''}${change.toFixed(2)}%`
})

const changeColor = computed(() => {
    if (!props.earnings_overview) return 'text-muted-foreground'
    return props.earnings_overview.change >= 0 ? 'text-green-600' : 'text-red-600'
})

function changePeriod(newPeriod: string) {
    period.value = newPeriod
    loading.value = true
    router.get('/agent/commission/dashboard', { period: newPeriod }, {
        onFinish: () => {
            loading.value = false
        },
    })
}

function formatCurrency(amount: number): string {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(amount)
}

function formatDate(dateString: string): string {
    return new Date(dateString).toLocaleDateString()
}

function getStatusBadgeClass(status: string): string {
    switch (status) {
        case 'pending':
            return 'bg-yellow-100 text-yellow-800'
        case 'paid':
            return 'bg-green-100 text-green-800'
        case 'cancelled':
            return 'bg-red-100 text-red-800'
        default:
            return 'bg-gray-100 text-gray-800'
    }
}
</script>

<template>
    <Head title="Commission Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <!-- Period Selector -->
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">Commission Dashboard</h1>
                <div class="flex gap-2">
                    <Button
                        v-for="p in ['day', 'week', 'month', 'year']"
                        :key="p"
                        :variant="period === p ? 'default' : 'outline'"
                        @click="changePeriod(p)"
                    >
                        {{ p === 'day' ? 'Today' : p === 'week' ? 'This Week' : p === 'month' ? 'This Month' : 'This Year' }}
                    </Button>
                </div>
            </div>

            <!-- Loading State -->
            <div v-if="loading" class="flex items-center justify-center gap-2 py-8">
                <Spinner class="h-5 w-5" />
                <span>Loading commission dashboard...</span>
            </div>

            <!-- Dashboard Content -->
            <div v-else class="grid gap-4 md:grid-cols-3">
                <!-- Earnings Overview Card -->
                <Card>
                    <CardHeader>
                        <CardTitle>Current Earnings</CardTitle>
                        <CardDescription>
                            {{ props.earnings_overview?.period === 'day' ? 'Today' : props.earnings_overview?.period === 'week' ? 'This week' : props.earnings_overview?.period === 'month' ? 'This month' : 'This year' }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-2">
                        <div class="text-3xl font-bold">{{ formattedCurrent }}</div>
                        <div class="text-sm text-muted-foreground">
                            Previous: {{ formattedPrevious }}
                        </div>
                        <div :class="['text-sm font-medium', changeColor]">
                            {{ changeIndicator }}
                        </div>
                    </CardContent>
                </Card>

                <!-- Total Commissions Card -->
                <Card>
                    <CardHeader>
                        <CardTitle>Total Commissions</CardTitle>
                        <CardDescription>
                            All-time commission count
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold">{{ props.recent_commissions?.total || 0 }}</div>
                    </CardContent>
                </Card>

                <!-- Downline Count Card -->
                <Card>
                    <CardHeader>
                        <CardTitle>Downline Agents</CardTitle>
                        <CardDescription>
                            Agents in your network
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold">{{ props.referral_hierarchy?.downline?.length || 0 }}</div>
                    </CardContent>
                </Card>

                <!-- Recent Commissions Table -->
                <Card class="md:col-span-3">
                    <CardHeader>
                        <CardTitle>Recent Commissions</CardTitle>
                        <CardDescription>
                            Your latest commission transactions
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div v-if="!props.recent_commissions?.data?.length" class="text-center py-8 text-muted-foreground">
                            No commissions yet
                        </div>
                        <div v-else class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="border-b">
                                    <tr>
                                        <th class="text-left py-2 px-4">Date</th>
                                        <th class="text-left py-2 px-4">Amount</th>
                                        <th class="text-left py-2 px-4">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="commission in props.recent_commissions.data" :key="commission.id" class="border-b hover:bg-muted/50">
                                        <td class="py-2 px-4">{{ formatDate(commission.created_at) }}</td>
                                        <td class="py-2 px-4 font-medium">{{ formatCurrency(commission.commission_amount) }}</td>
                                        <td class="py-2 px-4">
                                            <span :class="['px-2 py-1 rounded text-xs font-medium', getStatusBadgeClass(commission.status)]">
                                                {{ commission.status }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>

