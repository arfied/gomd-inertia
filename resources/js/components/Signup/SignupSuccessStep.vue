<script setup lang="ts">
import { useSignupStore } from '@/stores/signupStore'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'

const signupStore = useSignupStore()

function goToDashboard() {
    window.location.href = '/dashboard'
}
</script>

<template>
    <div class="space-y-6">
        <!-- Success Icon -->
        <div class="flex justify-center">
            <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center">
                <svg class="w-12 h-12 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
            </div>
        </div>

        <!-- Success Message -->
        <div class="text-center space-y-2">
            <h2 class="text-2xl font-bold text-gray-900">Signup Complete!</h2>
            <p class="text-gray-600">
                Your account has been successfully created and your subscription is now active.
            </p>
        </div>

        <!-- Confirmation Details -->
        <Card>
            <CardHeader>
                <CardTitle>Your Details</CardTitle>
            </CardHeader>
            <CardContent class="space-y-3">
                <div class="flex justify-between py-2 border-b">
                    <span class="text-gray-600">Subscription ID</span>
                    <span class="font-mono text-sm">{{ signupStore.state.subscriptionId }}</span>
                </div>
                <div v-if="signupStore.state.medicationId" class="flex justify-between py-2 border-b">
                    <span class="text-gray-600">Medication</span>
                    <span class="font-mono text-sm">{{ signupStore.state.medicationId }}</span>
                </div>
                <div v-if="signupStore.state.conditionId" class="flex justify-between py-2 border-b">
                    <span class="text-gray-600">Condition</span>
                    <span class="font-mono text-sm">{{ signupStore.state.conditionId }}</span>
                </div>
                <div class="flex justify-between py-2">
                    <span class="text-gray-600">Plan</span>
                    <span class="font-mono text-sm">{{ signupStore.state.planId }}</span>
                </div>
            </CardContent>
        </Card>

        <!-- Next Steps -->
        <Card class="bg-blue-50 border-blue-200">
            <CardHeader>
                <CardTitle class="text-blue-900">What's Next?</CardTitle>
            </CardHeader>
            <CardContent class="space-y-2 text-sm text-blue-800">
                <p>✓ Your subscription is now active</p>
                <p>✓ You can access your dashboard immediately</p>
                <p>✓ A confirmation email has been sent to your email address</p>
                <p>✓ You can manage your subscription in your account settings</p>
            </CardContent>
        </Card>

        <!-- Action Buttons -->
        <div class="flex gap-4">
            <Button
                variant="outline"
                class="flex-1"
                @click="() => window.location.href = '/'"
            >
                Go to Home
            </Button>
            <Button
                class="flex-1"
                @click="goToDashboard"
            >
                Go to Dashboard
            </Button>
        </div>
    </div>
</template>

