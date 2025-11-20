<script setup lang="ts">
import { computed, ref } from 'vue'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { type BreadcrumbItem } from '@/types'
import { Head, router } from '@inertiajs/vue3'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Copy, Check } from 'lucide-vue-next'

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Agent',
        href: '/agent',
    },
    {
        title: 'Referral Links',
        href: '/agent/referral-links',
    },
]

interface ReferralLink {
    id: number
    referral_code: string
    referral_type: string
    clicks_count: number
    conversions_count: number
    conversion_rate: number
    status: string
    created_at: string
}

interface PerformanceSummary {
    total_links: number
    total_clicks: number
    total_conversions: number
    overall_conversion_rate: number
}

interface PerformanceByType {
    [key: string]: {
        clicks: number
        conversions: number
        conversion_rate: number
        count: number
    }
}

interface Props {
    referral_links: {
        data: ReferralLink[]
        per_page: number
        current_page: number
        last_page: number
    }
    performance_summary: PerformanceSummary
    performance_by_type: PerformanceByType
}

const props = defineProps<Props>()

const copiedId = ref<number | null>(null)
const selectedType = ref<string>('all')

const filteredLinks = computed(() => {
    if (selectedType.value === 'all') {
        return props.referral_links.data
    }
    return props.referral_links.data.filter(link => link.referral_type === selectedType.value)
})

const typeLabels: Record<string, string> = {
    'patient': 'Patient Referral',
    'agent': 'Agent Referral',
    'business': 'Business Referral',
}

function getTypeLabel(type: string): string {
    return typeLabels[type] || type
}

function copyToClipboard(link: ReferralLink) {
    const url = `${window.location.origin}/ref/${link.referral_code}`
    navigator.clipboard.writeText(url)
    copiedId.value = link.id
    setTimeout(() => {
        copiedId.value = null
    }, 2000)
}

function formatDate(dateString: string): string {
    return new Date(dateString).toLocaleDateString()
}

function formatConversionRate(rate: number): string {
    return `${rate.toFixed(2)}%`
}
</script>

<template>
    <Head title="Referral Links" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-3xl font-bold">Referral Links</h1>
            </div>

            <!-- Performance Summary Cards -->
            <div class="grid gap-4 md:grid-cols-4">
                <Card>
                    <CardHeader>
                        <CardTitle class="text-sm font-medium">Total Links</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ performance_summary.total_links }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle class="text-sm font-medium">Total Clicks</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ performance_summary.total_clicks }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle class="text-sm font-medium">Total Conversions</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ performance_summary.total_conversions }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle class="text-sm font-medium">Conversion Rate</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatConversionRate(performance_summary.overall_conversion_rate) }}</div>
                    </CardContent>
                </Card>
            </div>

            <!-- Referral Links Table -->
            <Card>
                <CardHeader>
                    <CardTitle>Your Referral Links</CardTitle>
                    <CardDescription>
                        Manage and track your referral links
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="border-b">
                                <tr>
                                    <th class="text-left py-2 px-4">Type</th>
                                    <th class="text-left py-2 px-4">Code</th>
                                    <th class="text-left py-2 px-4">Clicks</th>
                                    <th class="text-left py-2 px-4">Conversions</th>
                                    <th class="text-left py-2 px-4">Rate</th>
                                    <th class="text-left py-2 px-4">Created</th>
                                    <th class="text-left py-2 px-4">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="link in filteredLinks" :key="link.id" class="border-b hover:bg-muted/50">
                                    <td class="py-2 px-4">{{ getTypeLabel(link.referral_type) }}</td>
                                    <td class="py-2 px-4 font-mono font-medium">{{ link.referral_code }}</td>
                                    <td class="py-2 px-4">{{ link.clicks_count }}</td>
                                    <td class="py-2 px-4">{{ link.conversions_count }}</td>
                                    <td class="py-2 px-4">{{ formatConversionRate(link.conversion_rate) }}</td>
                                    <td class="py-2 px-4">{{ formatDate(link.created_at) }}</td>
                                    <td class="py-2 px-4">
                                        <Button
                                            size="sm"
                                            variant="outline"
                                            @click="copyToClipboard(link)"
                                            class="gap-2"
                                        >
                                            <Copy v-if="copiedId !== link.id" class="h-4 w-4" />
                                            <Check v-else class="h-4 w-4 text-green-600" />
                                            {{ copiedId === link.id ? 'Copied' : 'Copy' }}
                                        </Button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

