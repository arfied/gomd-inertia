<script setup lang="ts">
import { ref, computed } from 'vue'
import { useSignupStore } from '@/stores/signupStore'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import AlertError from '@/components/AlertError.vue'
import {
    SignupPathSelector,
    SignupMedicationStep,
    SignupConditionStep,
    SignupPlanStep,
    SignupQuestionnaireStep,
    SignupPaymentStep,
    SignupSuccessStep,
    SignupFailureStep,
} from '@/components/Signup'

const signupStore = useSignupStore()

const currentStep = ref(0)

const steps = computed(() => {
    if (!signupStore.state.signupPath) return ['path-selection']

    const pathSteps: Record<string, string[]> = {
        medication_first: ['path-selection', 'medication', 'plan', 'questionnaire', 'payment', 'completion'],
        condition_first: ['path-selection', 'condition', 'plan', 'questionnaire', 'payment', 'completion'],
        plan_first: ['path-selection', 'plan', 'payment', 'completion'],
    }

    return pathSteps[signupStore.state.signupPath] || []
})

const currentStepName = computed(() => steps.value[currentStep.value])

const canGoBack = computed(() => currentStep.value > 0)
const canGoForward = computed(() => {
    switch (currentStepName.value) {
        case 'path-selection':
            return signupStore.state.signupPath !== null
        case 'medication':
            return signupStore.state.medicationId !== null
        case 'condition':
            return signupStore.state.conditionId !== null
        case 'plan':
            return signupStore.state.planId !== null
        case 'questionnaire':
            return Object.keys(signupStore.state.questionnaireResponses).length > 0
        case 'payment':
            return signupStore.state.paymentStatus === 'success'
        default:
            return false
    }
})

const progressPercentage = computed(() => {
    return ((currentStep.value + 1) / steps.value.length) * 100
})

function getStepTitle(step: string): string {
    const titles: Record<string, string> = {
        'path-selection': 'Choose Your Path',
        'medication': 'Select Medication',
        'condition': 'Select Condition',
        'plan': 'Choose Your Plan',
        'questionnaire': 'Health Questionnaire',
        'payment': 'Payment Information',
        'completion': 'Signup Complete',
    }
    return titles[step] || 'Signup'
}

function getStepDescription(step: string): string {
    const descriptions: Record<string, string> = {
        'path-selection': 'Choose how you\'d like to start your signup process',
        'medication': 'Select the medication you\'re interested in',
        'condition': 'Tell us about your health condition',
        'plan': 'Choose the plan that works best for you',
        'questionnaire': 'Answer a few health-related questions',
        'payment': 'Enter your payment information',
        'completion': 'Your signup is complete',
    }
    return descriptions[step] || ''
}

function goBack() {
    if (canGoBack.value) {
        currentStep.value--
    }
}

function goForward() {
    if (canGoForward.value && currentStep.value < steps.value.length - 1) {
        currentStep.value++
    }
}

function handleReset() {
    signupStore.reset()
    currentStep.value = 0
}
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 py-12 px-4">
        <div class="max-w-2xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-4xl font-bold text-gray-900 mb-2">Get Started</h1>
                <p class="text-lg text-gray-600">Complete your signup in just a few steps</p>
            </div>

            <!-- Progress Bar -->
            <div class="mb-8">
                <div class="flex justify-between mb-2">
                    <span class="text-sm font-medium text-gray-700">
                        Step {{ currentStep + 1 }} of {{ steps.length }}
                    </span>
                    <span class="text-sm font-medium text-gray-700">{{ Math.round(progressPercentage) }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div
                        class="bg-indigo-600 h-2 rounded-full transition-all duration-300"
                        :style="{ width: `${progressPercentage}%` }"
                    />
                </div>
            </div>

            <!-- Error Alert -->
            <AlertError
                v-if="signupStore.error"
                :errors="[signupStore.error]"
                title="Signup Error"
                class="mb-6"
            />

            <!-- Step Content -->
            <Card class="mb-6">
                <CardHeader>
                    <CardTitle>{{ getStepTitle(currentStepName) }}</CardTitle>
                    <CardDescription>{{ getStepDescription(currentStepName) }}</CardDescription>
                </CardHeader>
                <CardContent>
                    <!-- Path Selection -->
                    <SignupPathSelector v-if="currentStepName === 'path-selection'" />

                    <!-- Medication Selection -->
                    <SignupMedicationStep v-if="currentStepName === 'medication'" />

                    <!-- Condition Selection -->
                    <SignupConditionStep v-if="currentStepName === 'condition'" />

                    <!-- Plan Selection -->
                    <SignupPlanStep v-if="currentStepName === 'plan'" />

                    <!-- Questionnaire -->
                    <SignupQuestionnaireStep v-if="currentStepName === 'questionnaire'" />

                    <!-- Payment -->
                    <SignupPaymentStep v-if="currentStepName === 'payment'" />

                    <!-- Success -->
                    <SignupSuccessStep v-if="currentStepName === 'completion' && signupStore.isCompleted" />

                    <!-- Failure -->
                    <SignupFailureStep v-if="currentStepName === 'completion' && signupStore.isFailed" />
                </CardContent>
            </Card>

            <!-- Navigation Buttons -->
            <div class="flex justify-between gap-4">
                <Button
                    variant="outline"
                    @click="goBack"
                    :disabled="!canGoBack || signupStore.loading"
                >
                    Back
                </Button>

                <div class="flex gap-2">
                    <Button
                        variant="outline"
                        @click="handleReset"
                        :disabled="signupStore.loading"
                    >
                        Start Over
                    </Button>
                    <Button
                        @click="goForward"
                        :disabled="!canGoForward || signupStore.loading || currentStep === steps.length - 1"
                        :loading="signupStore.loading"
                    >
                        {{ currentStep === steps.length - 1 ? 'Complete' : 'Next' }}
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>

