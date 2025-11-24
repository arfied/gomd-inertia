<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useSignupStore } from '@/stores/signupStore'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import axios from 'axios'

interface Plan {
    id: number
    name: string
    description?: string
    price: number
    billingCycle: 'monthly' | 'biannual' | 'annual'
    features?: string[]
    popular?: boolean
}

const signupStore = useSignupStore()
const plans = ref<Plan[]>([])
const loadingPlans = ref(false)

onMounted(async () => {
    await loadPlans()
})

async function loadPlans() {
    loadingPlans.value = true
    try {
        const response = await axios.get('/api/plans')
        plans.value = response.data.data || []
    } catch (error) {
        console.error('Failed to load plans:', error)
        signupStore.error = 'Failed to load plans'
    } finally {
        loadingPlans.value = false
    }
}

async function selectPlan(planId: number) {
    try {
        await signupStore.selectPlan(planId)
    } catch (error) {
        console.error('Failed to select plan:', error)
    }
}

function formatPrice(price: number): string {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(price)
}

function getBillingLabel(cycle: string): string {
    const labels: Record<string, string> = {
        monthly: 'per month',
        biannual: 'every 6 months',
        annual: 'per year',
    }
    return labels[cycle] || cycle
}
</script>

<template>
    <div class="space-y-4">
        <div v-if="loadingPlans" class="text-center py-8">
            <div class="inline-block animate-spin">
                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
            </div>
        </div>

        <div v-else-if="plans.length === 0" class="text-center py-8 text-gray-500">
            <p>No plans available</p>
        </div>

        <div v-else class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            <button
                v-for="plan in plans"
                :key="plan.id"
                @click="selectPlan(plan.id)"
                :disabled="signupStore.loading"
                class="text-left"
            >
                <Card
                    :class="[
                        'cursor-pointer transition-all hover:shadow-lg h-full relative',
                        signupStore.state.planId === plan.id
                            ? 'ring-2 ring-indigo-600 bg-indigo-50'
                            : 'hover:border-indigo-300',
                        plan.popular ? 'md:scale-105' : '',
                    ]"
                >
                    <div v-if="plan.popular" class="absolute -top-3 left-1/2 transform -translate-x-1/2">
                        <Badge class="bg-indigo-600">Most Popular</Badge>
                    </div>

                    <CardHeader>
                        <CardTitle>{{ plan.name }}</CardTitle>
                        <CardDescription>{{ plan.description }}</CardDescription>
                    </CardHeader>

                    <CardContent class="space-y-4">
                        <div>
                            <div class="text-3xl font-bold text-gray-900">
                                {{ formatPrice(plan.price) }}
                            </div>
                            <p class="text-sm text-gray-600">{{ getBillingLabel(plan.billingCycle) }}</p>
                        </div>

                        <div v-if="plan.features" class="space-y-2">
                            <div v-for="feature in plan.features" :key="feature" class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                <span class="text-sm text-gray-700">{{ feature }}</span>
                            </div>
                        </div>

                        <div
                            v-if="signupStore.state.planId === plan.id"
                            class="flex items-center justify-center gap-2 pt-4 border-t"
                        >
                            <svg class="w-5 h-5 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            <span class="text-sm font-medium text-indigo-600">Selected</span>
                        </div>
                    </CardContent>
                </Card>
            </button>
        </div>
    </div>
</template>

