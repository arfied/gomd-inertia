<script setup lang="ts">
import { ref } from 'vue'
import { useSignupStore } from '@/stores/signupStore'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'

const signupStore = useSignupStore()

const paymentForm = ref({
    cardNumber: '',
    cardName: '',
    expiryDate: '',
    cvv: '',
})

const paymentMethods = [
    { id: 'credit_card', name: 'Credit Card', icon: '💳' },
    { id: 'ach', name: 'Bank Transfer', icon: '🏦' },
]

const selectedMethod = ref('credit_card')

function formatCardNumber(value: string): string {
    return value.replace(/\s/g, '').replace(/(\d{4})/g, '$1 ').trim()
}

function formatExpiryDate(value: string): string {
    const cleaned = value.replace(/\D/g, '')
    if (cleaned.length >= 2) {
        return cleaned.slice(0, 2) + '/' + cleaned.slice(2, 4)
    }
    return cleaned
}

async function processPayment() {
    try {
        // Generate a mock payment ID
        const paymentId = `pay_${Date.now()}`
        const amount = 99.99 // This should come from the selected plan
        
        await signupStore.processPayment(paymentId, amount, 'success')
    } catch (error) {
        console.error('Failed to process payment:', error)
    }
}
</script>

<template>
    <div class="space-y-6">
        <!-- Payment Method Selection -->
        <div class="space-y-3">
            <Label class="text-base font-semibold">Payment Method</Label>
            <div class="grid gap-3 md:grid-cols-2">
                <button
                    v-for="method in paymentMethods"
                    :key="method.id"
                    @click="selectedMethod = method.id"
                    :class="[
                        'p-4 border rounded-lg transition-all text-left',
                        selectedMethod === method.id
                            ? 'border-indigo-600 bg-indigo-50'
                            : 'border-gray-200 hover:border-indigo-300',
                    ]"
                >
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">{{ method.icon }}</span>
                        <span class="font-medium">{{ method.name }}</span>
                    </div>
                </button>
            </div>
        </div>

        <!-- Credit Card Form -->
        <div v-if="selectedMethod === 'credit_card'" class="space-y-4">
            <Card>
                <CardHeader>
                    <CardTitle>Credit Card Information</CardTitle>
                    <CardDescription>Enter your credit card details</CardDescription>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div>
                        <Label for="cardName">Cardholder Name</Label>
                        <Input
                            id="cardName"
                            v-model="paymentForm.cardName"
                            type="text"
                            placeholder="John Doe"
                            class="mt-1"
                        />
                    </div>

                    <div>
                        <Label for="cardNumber">Card Number</Label>
                        <Input
                            id="cardNumber"
                            v-model="paymentForm.cardNumber"
                            type="text"
                            placeholder="1234 5678 9012 3456"
                            maxlength="19"
                            @input="paymentForm.cardNumber = formatCardNumber(paymentForm.cardNumber)"
                            class="mt-1"
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <Label for="expiryDate">Expiry Date</Label>
                            <Input
                                id="expiryDate"
                                v-model="paymentForm.expiryDate"
                                type="text"
                                placeholder="MM/YY"
                                maxlength="5"
                                @input="paymentForm.expiryDate = formatExpiryDate(paymentForm.expiryDate)"
                                class="mt-1"
                            />
                        </div>
                        <div>
                            <Label for="cvv">CVV</Label>
                            <Input
                                id="cvv"
                                v-model="paymentForm.cvv"
                                type="text"
                                placeholder="123"
                                maxlength="4"
                                class="mt-1"
                            />
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Bank Transfer Form -->
        <div v-if="selectedMethod === 'ach'" class="space-y-4">
            <Card>
                <CardHeader>
                    <CardTitle>Bank Account Information</CardTitle>
                    <CardDescription>Enter your bank account details</CardDescription>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div>
                        <Label for="accountName">Account Holder Name</Label>
                        <Input
                            id="accountName"
                            type="text"
                            placeholder="John Doe"
                            class="mt-1"
                        />
                    </div>

                    <div>
                        <Label for="routingNumber">Routing Number</Label>
                        <Input
                            id="routingNumber"
                            type="text"
                            placeholder="123456789"
                            class="mt-1"
                        />
                    </div>

                    <div>
                        <Label for="accountNumber">Account Number</Label>
                        <Input
                            id="accountNumber"
                            type="text"
                            placeholder="9876543210"
                            class="mt-1"
                        />
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Order Summary -->
        <Card class="bg-gray-50">
            <CardHeader>
                <CardTitle>Order Summary</CardTitle>
            </CardHeader>
            <CardContent class="space-y-2">
                <div class="flex justify-between">
                    <span class="text-gray-600">Plan</span>
                    <span class="font-medium">{{ signupStore.state.planId || 'Not selected' }}</span>
                </div>
                <div class="flex justify-between pt-2 border-t">
                    <span class="font-semibold">Total</span>
                    <span class="font-semibold text-lg">$99.99</span>
                </div>
            </CardContent>
        </Card>

        <!-- Submit Button -->
        <button
            @click="processPayment"
            :disabled="signupStore.loading || !paymentForm.cardNumber"
            class="w-full px-4 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 font-medium"
        >
            {{ signupStore.loading ? 'Processing...' : 'Complete Payment' }}
        </button>
    </div>
</template>

