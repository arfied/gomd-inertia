<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Spinner } from '@/components/ui/spinner'
import axios from 'axios'

interface Plan {
    id: number
    name: string
    price: number
    duration_months: number
    features?: string[]
    benefits?: string[]
    is_featured?: boolean
}

const plans = ref<Plan[]>([])
const loadingPlans = ref(false)
const error = ref<string | null>(null)
const selectedPlanId = ref<number | null>(null)

onMounted(async () => {
    await loadPlans()
})

async function loadPlans() {
    loadingPlans.value = true
    error.value = null
    try {
        const response = await axios.get('/api/plans')
        plans.value = response.data.data || []
    } catch (err) {
        console.error('Failed to load plans:', err)
        error.value = 'Failed to load subscription plans. Please try again later.'
    } finally {
        loadingPlans.value = false
    }
}

function formatPrice(price: number): string {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(price)
}

function getDurationLabel(months: number): string {
    if (months === 1) return 'per month'
    if (months === 6) return 'every 6 months'
    if (months === 12) return 'per year'
    return `every ${months} months`
}

function selectPlan(planId: number) {
    selectedPlanId.value = planId
    // Redirect to checkout page with selected plan
    window.location.href = `/checkout?plan_id=${planId}`
}
</script>

<template>
    <Head title="Subscription Plans" />
    <AppLayout>
        <div class="min-h-screen bg-linear-to-b from-background to-muted/20 py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">
                <!-- Header -->
                <div class="text-center mb-12">
                    <h1 class="text-4xl font-bold tracking-tight text-foreground mb-4">
                        Choose Your Plan
                    </h1>
                    <p class="text-lg text-muted-foreground max-w-2xl mx-auto">
                        Select the perfect subscription plan for your needs. Upgrade, downgrade, or switch plans anytime.
                    </p>
                </div>

                <!-- Error State -->
                <div v-if="error" class="mb-8 p-4 bg-destructive/10 border border-destructive/20 rounded-lg text-destructive">
                    {{ error }}
                </div>

                <!-- Loading State -->
                <div v-if="loadingPlans" class="flex justify-center items-center py-12">
                    <div class="text-center">
                        <Spinner class="h-8 w-8 mx-auto mb-4" />
                        <p class="text-muted-foreground">Loading plans...</p>
                    </div>
                </div>

                <!-- Plans Grid -->
                <div v-else-if="plans.length > 0" class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                    <Card
                        v-for="plan in plans"
                        :key="plan.id"
                        :class="[
                            'relative transition-all hover:shadow-lg',
                            selectedPlanId === plan.id ? 'ring-2 ring-primary' : '',
                            plan.is_featured ? 'lg:scale-105 lg:shadow-lg' : '',
                        ]"
                    >
                        <!-- Featured Badge -->
                        <div v-if="plan.is_featured" class="absolute -top-3 left-1/2 transform -translate-x-1/2">
                            <Badge class="bg-primary">Most Popular</Badge>
                        </div>

                        <CardHeader>
                            <CardTitle>{{ plan.name }}</CardTitle>
                            <CardDescription>
                                {{ getDurationLabel(plan.duration_months) }}
                            </CardDescription>
                        </CardHeader>

                        <CardContent class="space-y-6">
                            <!-- Price -->
                            <div>
                                <div class="text-3xl font-bold text-foreground">
                                    {{ formatPrice(plan.price) }}
                                </div>
                                <p class="text-sm text-muted-foreground mt-1">
                                    {{ getDurationLabel(plan.duration_months) }}
                                </p>
                            </div>

                            <!-- Features -->
                            <div v-if="plan.features && plan.features.length > 0" class="space-y-2">
                                <p class="text-sm font-medium text-foreground">Features:</p>
                                <div v-for="feature in plan.features" :key="feature" class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-primary shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                    <span class="text-sm text-muted-foreground">{{ feature }}</span>
                                </div>
                            </div>

                            <!-- Benefits -->
                            <div v-if="plan.benefits && plan.benefits.length > 0" class="space-y-2">
                                <p class="text-sm font-medium text-foreground">Benefits:</p>
                                <div v-for="benefit in plan.benefits" :key="benefit" class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-primary shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                    <span class="text-sm text-muted-foreground">{{ benefit }}</span>
                                </div>
                            </div>

                            <!-- Select Button -->
                            <Button
                                @click="selectPlan(plan.id)"
                                :variant="selectedPlanId === plan.id ? 'default' : 'outline'"
                                class="w-full"
                            >
                                {{ selectedPlanId === plan.id ? 'Selected' : 'Select Plan' }}
                            </Button>
                        </CardContent>
                    </Card>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-12">
                    <p class="text-muted-foreground">No plans available at this time.</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

