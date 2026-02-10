<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Spinner } from '@/components/ui/spinner'
import axios from 'axios'

interface Plan {
    id: number
    name: string
    price: number
    duration_months: number
    features?: string[]
    benefits?: string[]
}

interface PaymentMethod {
    id: number
    type: string
    cc_last_four?: string
    cc_brand?: string
    cc_expiration_month?: string
    cc_expiration_year?: string
    ach_account_name?: string
    is_default: boolean
}

interface CreditCardData {
    cc_number: string
    cc_brand: string
    cc_expiration_month: string
    cc_expiration_year: string
    cc_cvv: string
    is_default: boolean
}

const plan = ref<Plan | null>(null)
const paymentMethods = ref<PaymentMethod[]>([])
const selectedPaymentMethodId = ref<number | null>(null)
const loadingPlan = ref(false)
const loadingPaymentMethods = ref(false)
const processingPayment = ref(false)
const error = ref<string | null>(null)
const success = ref(false)
const showAddCardForm = ref(false)
const addingCard = ref(false)

const creditCardForm = ref<CreditCardData>({
    cc_number: '',
    cc_brand: '',
    cc_expiration_month: '',
    cc_expiration_year: '',
    cc_cvv: '',
    is_default: false,
})

const cardFormErrors = ref<Record<string, string>>({})

const planId = computed(() => {
    const params = new URLSearchParams(window.location.search)
    const id = params.get('plan_id')
    return id ? parseInt(id) : null
})

onMounted(async () => {
    if (!planId.value) {
        error.value = 'No plan selected. Please select a plan first.'
        return
    }
    await loadPlan()
    await loadPaymentMethods()
})

async function loadPlan() {
    if (!planId.value) return
    loadingPlan.value = true
    error.value = null
    try {
        const response = await axios.get(`/api/plans/${planId.value}`)
        plan.value = response.data.data
    } catch (err) {
        console.error('Failed to load plan:', err)
        error.value = 'Failed to load plan details. Please try again.'
    } finally {
        loadingPlan.value = false
    }
}

async function loadPaymentMethods() {
    loadingPaymentMethods.value = true
    try {
        const response = await axios.get('/api/patient/payment-methods')
        paymentMethods.value = response.data.data || []
        // Auto-select default payment method
        const defaultMethod = paymentMethods.value.find(m => m.is_default)
        if (defaultMethod) {
            selectedPaymentMethodId.value = defaultMethod.id
        }
    } catch (err) {
        console.error('Failed to load payment methods:', err)
    } finally {
        loadingPaymentMethods.value = false
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

function navigateToBilling() {
    window.location.href = '/billing'
}

function formatCardNumber(value: string): string {
    return value.replace(/\s/g, '').replace(/(\d{4})/g, '$1 ').trim()
}

function validateCardForm(): boolean {
    cardFormErrors.value = {}

    if (!creditCardForm.value.cc_number || creditCardForm.value.cc_number.replace(/\s/g, '').length < 13) {
        cardFormErrors.value.cc_number = 'Valid card number is required'
    }

    if (!creditCardForm.value.cc_brand) {
        cardFormErrors.value.cc_brand = 'Card brand is required'
    }

    if (!creditCardForm.value.cc_expiration_month) {
        cardFormErrors.value.cc_expiration_month = 'Expiration month is required'
    }

    if (!creditCardForm.value.cc_expiration_year) {
        cardFormErrors.value.cc_expiration_year = 'Expiration year is required'
    }

    if (!creditCardForm.value.cc_cvv || creditCardForm.value.cc_cvv.length < 3) {
        cardFormErrors.value.cc_cvv = 'Valid CVV is required'
    }

    return Object.keys(cardFormErrors.value).length === 0
}

async function submitCreditCard() {
    if (!validateCardForm()) {
        return
    }

    addingCard.value = true
    error.value = null

    try {
        // Extract last 4 digits
        const cardNumber = creditCardForm.value.cc_number.replace(/\s/g, '')
        const lastFour = cardNumber.slice(-4)

        // In a real implementation, you would tokenize with Authorize.Net here
        // For now, we'll use a mock token
        const mockToken = `tok_${Date.now()}`

        // Add the payment method
        const response = await axios.post('/api/patient/payment-methods', {
            type: 'credit_card',
            cc_number: cardNumber,
            cc_last_four: lastFour,
            cc_brand: creditCardForm.value.cc_brand,
            cc_expiration_month: creditCardForm.value.cc_expiration_month,
            cc_expiration_year: creditCardForm.value.cc_expiration_year,
            cc_token: mockToken,
            is_default: creditCardForm.value.is_default || paymentMethods.value.length === 0,
        })

        // Add the new payment method to the list
        if (response.data.data) {
            paymentMethods.value.push(response.data.data)
            selectedPaymentMethodId.value = response.data.data.id
        }

        // Reset form and close
        showAddCardForm.value = false
        creditCardForm.value = {
            cc_number: '',
            cc_brand: '',
            cc_expiration_month: '',
            cc_expiration_year: '',
            cc_cvv: '',
            is_default: false,
        }
    } catch (err: any) {
        console.error('Failed to add card:', err)
        error.value = err.response?.data?.message || 'Failed to add payment method'
    } finally {
        addingCard.value = false
    }
}

function getPaymentMethodLabel(method: PaymentMethod): string {
    if (method.type === 'credit_card') {
        return `${method.cc_brand} ending in ${method.cc_last_four}`
    } else if (method.type === 'ach') {
        return `Bank account: ${method.ach_account_name}`
    }
    return 'Unknown payment method'
}

async function processCheckout() {
    if (!plan.value || !selectedPaymentMethodId.value) {
        error.value = 'Please select a payment method.'
        return
    }

    processingPayment.value = true
    error.value = null

    try {
        // TODO: Implement actual payment processing with Authorize.Net
        // For now, simulate successful payment
        const paymentId = `pay_${Date.now()}`

        // Create subscription
        const response = await axios.post('/patient/subscription', {
            plan_id: plan.value.id,
            payment_method_id: selectedPaymentMethodId.value,
            payment_id: paymentId,
            amount: plan.value.price,
        })

        success.value = true
        // Redirect to dashboard after 2 seconds
        setTimeout(() => {
            window.location.href = '/dashboard'
        }, 2000)
    } catch (err: any) {
        console.error('Failed to process checkout:', err)
        error.value = err.response?.data?.message || 'Failed to process payment. Please try again.'
    } finally {
        processingPayment.value = false
    }
}
</script>

<template>
    <Head title="Checkout" />
    <AppLayout>
        <div class="min-h-screen bg-linear-to-b from-background to-muted/20 py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-4xl mx-auto">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold tracking-tight text-foreground mb-2">
                        Complete Your Purchase
                    </h1>
                    <p class="text-muted-foreground">
                        Review your plan and select a payment method to complete your subscription.
                    </p>
                </div>

                <!-- Error State -->
                <div v-if="error" class="mb-8 p-4 bg-destructive/10 border border-destructive/20 rounded-lg text-destructive">
                    {{ error }}
                </div>

                <!-- Success State -->
                <div v-if="success" class="mb-8 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800">
                    <p class="font-medium">Payment successful! Redirecting to dashboard...</p>
                </div>

                <div class="grid gap-8 lg:grid-cols-3">
                    <!-- Plan Summary -->
                    <div class="lg:col-span-1">
                        <Card v-if="loadingPlan" class="h-full flex items-center justify-center">
                            <Spinner class="h-8 w-8" />
                        </Card>
                        <Card v-else-if="plan" class="h-full">
                            <CardHeader>
                                <CardTitle>Order Summary</CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div>
                                    <p class="text-sm text-muted-foreground">Plan</p>
                                    <p class="font-medium text-foreground">{{ plan.name }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-muted-foreground">Billing Cycle</p>
                                    <p class="font-medium text-foreground">{{ getDurationLabel(plan.duration_months) }}</p>
                                </div>
                                <div class="border-t pt-4">
                                    <div class="flex justify-between items-center">
                                        <p class="text-sm text-muted-foreground">Total</p>
                                        <p class="text-2xl font-bold text-foreground">{{ formatPrice(plan.price) }}</p>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <!-- Payment Form -->
                    <div class="lg:col-span-2">
                        <Card>
                            <CardHeader>
                                <CardTitle>Payment Method</CardTitle>
                                <CardDescription>
                                    Select a payment method or add a new one
                                </CardDescription>
                            </CardHeader>
                            <CardContent class="space-y-6">
                                <!-- Existing Payment Methods -->
                                <div v-if="!loadingPaymentMethods && paymentMethods.length > 0" class="space-y-3">
                                    <div class="flex items-center justify-between mb-3">
                                        <p class="text-sm font-medium text-foreground">Your Payment Methods</p>
                                        <Button
                                            v-if="!showAddCardForm"
                                            variant="outline"
                                            size="sm"
                                            @click="showAddCardForm = true"
                                        >
                                            Add Credit Card
                                        </Button>
                                    </div>
                                    <div class="space-y-2">
                                        <label
                                            v-for="method in paymentMethods"
                                            :key="method.id"
                                            class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-muted transition-colors"
                                            :class="selectedPaymentMethodId === method.id ? 'border-primary bg-primary/5' : 'border-border'"
                                        >
                                            <input
                                                type="radio"
                                                :value="method.id"
                                                v-model="selectedPaymentMethodId"
                                                class="mr-3"
                                            />
                                            <div class="flex-1">
                                                <p class="text-sm font-medium">{{ getPaymentMethodLabel(method) }}</p>
                                                <p v-if="method.is_default" class="text-xs text-muted-foreground">Default</p>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <!-- No Payment Methods -->
                                <div v-else-if="!loadingPaymentMethods && paymentMethods.length === 0 && !showAddCardForm" class="p-4 bg-muted rounded-lg text-center">
                                    <p class="text-sm text-muted-foreground mb-4">
                                        No payment methods found. Add a credit card to continue.
                                    </p>
                                    <Button @click="showAddCardForm = true">
                                        Add Credit Card
                                    </Button>
                                </div>

                                <!-- Credit Card Form -->
                                <div v-if="showAddCardForm" class="p-4 border border-border rounded-lg bg-muted/30 space-y-4">
                                    <div class="flex items-center justify-between mb-4">
                                        <h3 class="text-sm font-semibold text-foreground">Add Credit Card</h3>
                                        <button
                                            @click="showAddCardForm = false"
                                            class="text-muted-foreground hover:text-foreground transition-colors"
                                        >
                                            ✕
                                        </button>
                                    </div>

                                    <!-- Card Number -->
                                    <div>
                                        <Label for="cc_number" class="text-sm font-medium">Card Number</Label>
                                        <Input
                                            id="cc_number"
                                            v-model="creditCardForm.cc_number"
                                            @input="creditCardForm.cc_number = formatCardNumber($event.target.value)"
                                            type="text"
                                            placeholder="1234 5678 9012 3456"
                                            maxlength="19"
                                            class="mt-1"
                                        />
                                        <p v-if="cardFormErrors.cc_number" class="text-xs text-destructive mt-1">
                                            {{ cardFormErrors.cc_number }}
                                        </p>
                                    </div>

                                    <!-- Card Brand -->
                                    <div>
                                        <Label for="cc_brand" class="text-sm font-medium">Card Brand</Label>
                                        <select
                                            id="cc_brand"
                                            v-model="creditCardForm.cc_brand"
                                            class="mt-1 block w-full px-3 py-2 border border-border rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                                        >
                                            <option value="">Select a brand</option>
                                            <option value="Visa">Visa</option>
                                            <option value="Mastercard">Mastercard</option>
                                            <option value="American Express">American Express</option>
                                            <option value="Discover">Discover</option>
                                        </select>
                                        <p v-if="cardFormErrors.cc_brand" class="text-xs text-destructive mt-1">
                                            {{ cardFormErrors.cc_brand }}
                                        </p>
                                    </div>

                                    <!-- Expiration and CVV -->
                                    <div class="grid grid-cols-3 gap-3">
                                        <div>
                                            <Label for="cc_expiration_month" class="text-sm font-medium">Month</Label>
                                            <select
                                                id="cc_expiration_month"
                                                v-model="creditCardForm.cc_expiration_month"
                                                class="mt-1 block w-full px-3 py-2 border border-border rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                                            >
                                                <option value="">MM</option>
                                                <option v-for="month in 12" :key="month" :value="String(month).padStart(2, '0')">
                                                    {{ String(month).padStart(2, '0') }}
                                                </option>
                                            </select>
                                            <p v-if="cardFormErrors.cc_expiration_month" class="text-xs text-destructive mt-1">
                                                {{ cardFormErrors.cc_expiration_month }}
                                            </p>
                                        </div>

                                        <div>
                                            <Label for="cc_expiration_year" class="text-sm font-medium">Year</Label>
                                            <select
                                                id="cc_expiration_year"
                                                v-model="creditCardForm.cc_expiration_year"
                                                class="mt-1 block w-full px-3 py-2 border border-border rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                                            >
                                                <option value="">YY</option>
                                                <option v-for="year in 20" :key="year" :value="String(new Date().getFullYear() + year - 1)">
                                                    {{ new Date().getFullYear() + year - 1 }}
                                                </option>
                                            </select>
                                            <p v-if="cardFormErrors.cc_expiration_year" class="text-xs text-destructive mt-1">
                                                {{ cardFormErrors.cc_expiration_year }}
                                            </p>
                                        </div>

                                        <div>
                                            <Label for="cc_cvv" class="text-sm font-medium">CVV</Label>
                                            <Input
                                                id="cc_cvv"
                                                v-model="creditCardForm.cc_cvv"
                                                type="text"
                                                placeholder="123"
                                                maxlength="4"
                                                class="mt-1"
                                            />
                                            <p v-if="cardFormErrors.cc_cvv" class="text-xs text-destructive mt-1">
                                                {{ cardFormErrors.cc_cvv }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Set as Default -->
                                    <div class="flex items-center">
                                        <input
                                            id="is_default"
                                            v-model="creditCardForm.is_default"
                                            type="checkbox"
                                            class="h-4 w-4 rounded border-border"
                                        />
                                        <Label for="is_default" class="ml-2 text-sm text-foreground">
                                            Set as default payment method
                                        </Label>
                                    </div>

                                    <!-- Form Actions -->
                                    <div class="flex gap-3 justify-end pt-2">
                                        <Button
                                            variant="outline"
                                            @click="showAddCardForm = false"
                                            :disabled="addingCard"
                                        >
                                            Cancel
                                        </Button>
                                        <Button
                                            @click="submitCreditCard"
                                            :disabled="addingCard"
                                        >
                                            <Spinner v-if="addingCard" class="mr-2 h-4 w-4" />
                                            {{ addingCard ? 'Adding...' : 'Add Card' }}
                                        </Button>
                                    </div>
                                </div>

                                <!-- Loading State -->
                                <div v-else-if="loadingPaymentMethods" class="flex items-center justify-center py-8">
                                    <Spinner class="h-6 w-6" />
                                </div>

                                <!-- Checkout Button -->
                                <Button
                                    v-if="paymentMethods.length > 0"
                                    @click="processCheckout"
                                    :disabled="processingPayment || !selectedPaymentMethodId"
                                    class="w-full"
                                    size="lg"
                                >
                                    <Spinner v-if="processingPayment" class="mr-2 h-4 w-4" />
                                    {{ processingPayment ? 'Processing...' : `Pay ${formatPrice(plan?.price || 0)}` }}
                                </Button>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

