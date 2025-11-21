import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'

export interface PaymentMethod {
    id: number
    type: 'credit_card' | 'ach' | 'invoice'
    is_default: boolean
    verification_status: 'pending' | 'verified' | 'failed' | 'active'
    display_name: string
    created_at: string
    updated_at: string
    cc_last_four?: string
    cc_brand?: string
    cc_expiration_month?: string
    cc_expiration_year?: string
    ach_account_name?: string
    ach_account_type?: string
    ach_routing_number_last_four?: string
    ach_account_number_last_four?: string
    invoice_company_name?: string
    invoice_contact_name?: string
    invoice_email?: string
    invoice_phone?: string
}

export const usePaymentMethodsStore = defineStore('paymentMethods', () => {
    const paymentMethods = ref<PaymentMethod[]>([])
    const loading = ref(false)
    const error = ref<string | null>(null)

    const defaultPaymentMethod = computed(() =>
        paymentMethods.value.find(pm => pm.is_default)
    )

    const creditCards = computed(() =>
        paymentMethods.value.filter(pm => pm.type === 'credit_card')
    )

    const achAccounts = computed(() =>
        paymentMethods.value.filter(pm => pm.type === 'ach')
    )

    const invoiceMethods = computed(() =>
        paymentMethods.value.filter(pm => pm.type === 'invoice')
    )

    const unverifiedAchMethods = computed(() =>
        achAccounts.value.filter(pm => pm.verification_status === 'pending')
    )

    async function fetchPaymentMethods() {
        loading.value = true
        error.value = null
        try {
            const response = await axios.get('/api/patient/payment-methods')
            paymentMethods.value = response.data.data
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Failed to fetch payment methods'
            throw err
        } finally {
            loading.value = false
        }
    }

    async function addPaymentMethod(data: any) {
        loading.value = true
        error.value = null
        try {
            const response = await axios.post('/api/patient/payment-methods', data)
            paymentMethods.value.push(response.data.data)
            return response.data.data
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Failed to add payment method'
            throw err
        } finally {
            loading.value = false
        }
    }

    async function updatePaymentMethod(id: number, data: any) {
        loading.value = true
        error.value = null
        try {
            const response = await axios.patch(`/api/patient/payment-methods/${id}`, data)
            const index = paymentMethods.value.findIndex(pm => pm.id === id)
            if (index !== -1) {
                paymentMethods.value[index] = response.data.data
            }
            return response.data.data
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Failed to update payment method'
            throw err
        } finally {
            loading.value = false
        }
    }

    async function removePaymentMethod(id: number) {
        loading.value = true
        error.value = null
        try {
            await axios.delete(`/api/patient/payment-methods/${id}`)
            paymentMethods.value = paymentMethods.value.filter(pm => pm.id !== id)
        } catch (err: any) {
            error.value = err.response?.data?.error || 'Failed to remove payment method'
            throw err
        } finally {
            loading.value = false
        }
    }

    async function setDefault(id: number) {
        loading.value = true
        error.value = null
        try {
            const response = await axios.post(`/api/patient/payment-methods/${id}/set-default`)
            // Update all payment methods
            paymentMethods.value = paymentMethods.value.map(pm => ({
                ...pm,
                is_default: pm.id === id
            }))
            return response.data.data
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Failed to set default payment method'
            throw err
        } finally {
            loading.value = false
        }
    }

    return {
        paymentMethods,
        loading,
        error,
        defaultPaymentMethod,
        creditCards,
        achAccounts,
        invoiceMethods,
        unverifiedAchMethods,
        fetchPaymentMethods,
        addPaymentMethod,
        updatePaymentMethod,
        removePaymentMethod,
        setDefault,
    }
})

