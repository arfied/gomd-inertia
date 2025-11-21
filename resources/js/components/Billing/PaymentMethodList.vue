<script setup lang="ts">
import { ref } from 'vue'
import type { PaymentMethod } from '@/stores/paymentMethodsStore'
import { usePaymentMethodsStore } from '@/stores/paymentMethodsStore'
import PaymentMethodCard from './PaymentMethodCard.vue'

interface Props {
    paymentMethods: PaymentMethod[]
}

defineProps<Props>()

const store = usePaymentMethodsStore()
const confirmDeleteId = ref<number | null>(null)
const deleteLoading = ref(false)

const handleDelete = async (id: number) => {
    if (confirmDeleteId.value === id) {
        deleteLoading.value = true
        try {
            await store.removePaymentMethod(id)
            confirmDeleteId.value = null
        } catch (err) {
            console.error('Failed to delete payment method:', err)
        } finally {
            deleteLoading.value = false
        }
    } else {
        confirmDeleteId.value = id
    }
}

const handleSetDefault = async (id: number) => {
    try {
        await store.setDefault(id)
    } catch (err) {
        console.error('Failed to set default payment method:', err)
    }
}

const cancelDelete = () => {
    confirmDeleteId.value = null
}
</script>

<template>
    <div class="divide-y divide-gray-200">
        <PaymentMethodCard
            v-for="method in paymentMethods"
            :key="method.id"
            :payment-method="method"
            :is-deleting="confirmDeleteId === method.id"
            :delete-loading="deleteLoading"
            @delete="handleDelete(method.id)"
            @cancel-delete="cancelDelete"
            @set-default="handleSetDefault(method.id)"
        />
    </div>
</template>

