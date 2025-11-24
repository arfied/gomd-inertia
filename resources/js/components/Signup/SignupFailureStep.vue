<script setup lang="ts">
import { useSignupStore } from '@/stores/signupStore'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'

const signupStore = useSignupStore()

function getFailureIcon(): string {
    switch (signupStore.state.failureReason) {
        case 'payment_failed':
            return '💳'
        case 'validation_error':
            return '⚠️'
        case 'system_error':
            return '🔧'
        default:
            return '❌'
    }
}

function getFailureTitle(): string {
    switch (signupStore.state.failureReason) {
        case 'payment_failed':
            return 'Payment Failed'
        case 'validation_error':
            return 'Validation Error'
        case 'system_error':
            return 'System Error'
        default:
            return 'Signup Failed'
    }
}

function getFailureDescription(): string {
    switch (signupStore.state.failureReason) {
        case 'payment_failed':
            return 'Your payment could not be processed. Please check your payment information and try again.'
        case 'validation_error':
            return 'Some of the information you provided was invalid. Please review and try again.'
        case 'system_error':
            return 'We encountered a system error. Please try again later or contact support.'
        default:
            return 'Your signup could not be completed. Please try again.'
    }
}

function startOver() {
    signupStore.reset()
    window.location.reload()
}

function contactSupport() {
    window.location.href = '/support'
}
</script>

<template>
    <div class="space-y-6">
        <!-- Failure Icon -->
        <div class="flex justify-center">
            <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center text-4xl">
                {{ getFailureIcon() }}
            </div>
        </div>

        <!-- Failure Message -->
        <div class="text-center space-y-2">
            <h2 class="text-2xl font-bold text-gray-900">{{ getFailureTitle() }}</h2>
            <p class="text-gray-600">
                {{ getFailureDescription() }}
            </p>
        </div>

        <!-- Error Details -->
        <Card class="bg-red-50 border-red-200">
            <CardHeader>
                <CardTitle class="text-red-900">Error Details</CardTitle>
            </CardHeader>
            <CardContent class="space-y-2">
                <div class="flex justify-between py-2 border-b border-red-200">
                    <span class="text-red-800">Reason</span>
                    <span class="font-mono text-sm text-red-900">{{ signupStore.state.failureReason }}</span>
                </div>
                <div class="py-2">
                    <span class="text-red-800">Message</span>
                    <p class="font-mono text-sm text-red-900 mt-1">{{ signupStore.state.failureMessage }}</p>
                </div>
            </CardContent>
        </Card>

        <!-- Troubleshooting Tips -->
        <Card>
            <CardHeader>
                <CardTitle>Troubleshooting Tips</CardTitle>
            </CardHeader>
            <CardContent class="space-y-2 text-sm">
                <div v-if="signupStore.state.failureReason === 'payment_failed'" class="space-y-2">
                    <p>• Check that your card number is correct</p>
                    <p>• Verify the expiration date and CVV</p>
                    <p>• Ensure your billing address matches your card</p>
                    <p>• Try a different payment method</p>
                </div>
                <div v-else-if="signupStore.state.failureReason === 'validation_error'" class="space-y-2">
                    <p>• Review all required fields</p>
                    <p>• Check that all information is accurate</p>
                    <p>• Ensure email address is valid</p>
                    <p>• Try again with correct information</p>
                </div>
                <div v-else class="space-y-2">
                    <p>• Try refreshing the page</p>
                    <p>• Clear your browser cache</p>
                    <p>• Try again in a few moments</p>
                    <p>• Contact support if the problem persists</p>
                </div>
            </CardContent>
        </Card>

        <!-- Action Buttons -->
        <div class="flex gap-4">
            <Button
                variant="outline"
                class="flex-1"
                @click="contactSupport"
            >
                Contact Support
            </Button>
            <Button
                class="flex-1"
                @click="startOver"
            >
                Try Again
            </Button>
        </div>
    </div>
</template>

