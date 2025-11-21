<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { usePaymentMethodsStore } from '@/stores/paymentMethodsStore'
import AppLayout from '@/layouts/AppLayout.vue'
import PaymentMethodList from '@/components/Billing/PaymentMethodList.vue'
import AddPaymentMethodModal from '@/components/Billing/AddPaymentMethodModal.vue'
import { Button } from '@/components/ui/button'
import { AlertCircle, Plus } from 'lucide-vue-next'

const store = usePaymentMethodsStore()
const showAddModal = ref(false)
const loading = ref(true)
const error = ref<string | null>(null)

const paymentMethods = computed(() => store.paymentMethods)
const hasPaymentMethods = computed(() => paymentMethods.value.length > 0)

onMounted(async () => {
    try {
        loading.value = true
        await store.fetchPaymentMethods()
    } catch (err) {
        error.value = 'Failed to load payment methods'
        console.error(err)
    } finally {
        loading.value = false
    }
})

const handleAddPaymentMethod = async (data: any) => {
    try {
        await store.addPaymentMethod(data)
        showAddModal.value = false
    } catch (err) {
        console.error('Failed to add payment method:', err)
    }
}
</script>

<template>
    <AppLayout>
        <div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-4xl mx-auto">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">Billing & Payment Methods</h1>
                    <p class="mt-2 text-gray-600">Manage your payment methods and billing information</p>
                </div>

                <!-- Error Alert -->
                <div v-if="error" class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg flex items-start gap-3">
                    <AlertCircle class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" />
                    <div>
                        <h3 class="font-semibold text-red-900">Error</h3>
                        <p class="text-red-700">{{ error }}</p>
                    </div>
                </div>

                <!-- Loading State -->
                <div v-if="loading" class="space-y-4">
                    <div class="h-32 bg-gray-200 rounded-lg animate-pulse"></div>
                    <div class="h-32 bg-gray-200 rounded-lg animate-pulse"></div>
                </div>

                <!-- Content -->
                <div v-else class="space-y-6">
                    <!-- Payment Methods Section -->
                    <div class="bg-white rounded-lg shadow">
                        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-gray-900">Payment Methods</h2>
                            <Button
                                @click="showAddModal = true"
                                class="flex items-center gap-2"
                            >
                                <Plus class="w-4 h-4" />
                                Add Payment Method
                            </Button>
                        </div>

                        <!-- Empty State -->
                        <div v-if="!hasPaymentMethods" class="px-6 py-12 text-center">
                            <div class="text-gray-400 mb-4">
                                <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h10m4 0a1 1 0 11-2 0 1 1 0 012 0z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No payment methods</h3>
                            <p class="text-gray-600 mb-6">Add a payment method to get started</p>
                            <Button @click="showAddModal = true" variant="outline">
                                Add Your First Payment Method
                            </Button>
                        </div>

                        <!-- Payment Methods List -->
                        <PaymentMethodList v-else :payment-methods="paymentMethods" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Payment Method Modal -->
        <AddPaymentMethodModal
            v-model:open="showAddModal"
            @submit="handleAddPaymentMethod"
        />
    </AppLayout>
</template>

