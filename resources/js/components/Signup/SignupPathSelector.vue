<script setup lang="ts">
import { useSignupStore } from '@/stores/signupStore'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'

const signupStore = useSignupStore()

const paths = [
    {
        id: 'medication_first',
        title: 'Start with Medication',
        description: 'Select your medication first, then choose your plan',
        steps: ['Medication', 'Plan', 'Questionnaire', 'Payment'],
        icon: '💊',
    },
    {
        id: 'condition_first',
        title: 'Start with Condition',
        description: 'Tell us about your condition first, then select a plan',
        steps: ['Condition', 'Plan', 'Questionnaire', 'Payment'],
        icon: '🏥',
    },
    {
        id: 'plan_first',
        title: 'Start with Plan',
        description: 'Choose your plan first, then provide health information',
        steps: ['Plan', 'Payment'],
        icon: '📋',
    },
]

async function selectPath(pathId: 'medication_first' | 'condition_first' | 'plan_first') {
    try {
        await signupStore.startSignup(pathId)
    } catch (error) {
        console.error('Failed to start signup:', error)
    }
}
</script>

<template>
    <div class="space-y-4">
        <p class="text-gray-600 mb-6">
            Choose the path that best fits your needs. Each path has a different order of steps.
        </p>

        <div class="grid gap-4">
            <div
                v-for="path in paths"
                :key="path.id"
                class="relative"
            >
                <button
                    @click="selectPath(path.id as any)"
                    :disabled="signupStore.loading"
                    class="w-full text-left"
                >
                    <Card
                        :class="[
                            'cursor-pointer transition-all hover:shadow-lg',
                            signupStore.state.signupPath === path.id
                                ? 'ring-2 ring-indigo-600 bg-indigo-50'
                                : 'hover:border-indigo-300',
                        ]"
                    >
                        <CardHeader>
                            <div class="flex items-start justify-between">
                                <div class="flex items-start gap-4">
                                    <span class="text-4xl">{{ path.icon }}</span>
                                    <div>
                                        <CardTitle>{{ path.title }}</CardTitle>
                                        <CardDescription>{{ path.description }}</CardDescription>
                                    </div>
                                </div>
                                <div
                                    v-if="signupStore.state.signupPath === path.id"
                                    class="w-6 h-6 rounded-full bg-indigo-600 flex items-center justify-center"
                                >
                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div class="flex flex-wrap gap-2">
                                <span
                                    v-for="step in path.steps"
                                    :key="step"
                                    class="px-3 py-1 bg-gray-100 text-gray-700 text-sm rounded-full"
                                >
                                    {{ step }}
                                </span>
                            </div>
                        </CardContent>
                    </Card>
                </button>
            </div>
        </div>

        <div v-if="signupStore.loading" class="text-center py-4">
            <div class="inline-block animate-spin">
                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
            </div>
        </div>
    </div>
</template>

