<script setup lang="ts">
import { ref } from 'vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import InputError from '@/components/InputError.vue'

interface Props {
    loading: boolean
}

interface Emits {
    (e: 'submit', data: any): void
}

defineProps<Props>()
const emit = defineEmits<Emits>()

const form = ref({
    invoice_company_name: '',
    invoice_contact_name: '',
    invoice_email: '',
    invoice_phone: '',
    invoice_billing_address: '',
    invoice_payment_terms: 'net_30',
    is_default: false,
})

const errors = ref<Record<string, string>>({})

const validateForm = () => {
    errors.value = {}

    if (!form.value.invoice_company_name) {
        errors.value.invoice_company_name = 'Company name is required'
    }

    if (!form.value.invoice_contact_name) {
        errors.value.invoice_contact_name = 'Contact name is required'
    }

    if (!form.value.invoice_email || !form.value.invoice_email.includes('@')) {
        errors.value.invoice_email = 'Valid email is required'
    }

    if (!form.value.invoice_phone) {
        errors.value.invoice_phone = 'Phone number is required'
    }

    if (!form.value.invoice_billing_address) {
        errors.value.invoice_billing_address = 'Billing address is required'
    }

    return Object.keys(errors.value).length === 0
}

const handleSubmit = () => {
    if (validateForm()) {
        emit('submit', form.value)
    }
}
</script>

<template>
    <form @submit.prevent="handleSubmit" class="space-y-6">
        <!-- Company Name -->
        <div>
            <Label for="invoice_company_name">Company Name</Label>
            <Input
                id="invoice_company_name"
                v-model="form.invoice_company_name"
                type="text"
                placeholder="Acme Corp"
                class="mt-1"
            />
            <InputError :message="errors.invoice_company_name" />
        </div>

        <!-- Contact Name -->
        <div>
            <Label for="invoice_contact_name">Contact Name</Label>
            <Input
                id="invoice_contact_name"
                v-model="form.invoice_contact_name"
                type="text"
                placeholder="John Doe"
                class="mt-1"
            />
            <InputError :message="errors.invoice_contact_name" />
        </div>

        <!-- Email -->
        <div>
            <Label for="invoice_email">Email Address</Label>
            <Input
                id="invoice_email"
                v-model="form.invoice_email"
                type="email"
                placeholder="billing@acme.com"
                class="mt-1"
            />
            <InputError :message="errors.invoice_email" />
        </div>

        <!-- Phone -->
        <div>
            <Label for="invoice_phone">Phone Number</Label>
            <Input
                id="invoice_phone"
                v-model="form.invoice_phone"
                type="tel"
                placeholder="(555) 123-4567"
                class="mt-1"
            />
            <InputError :message="errors.invoice_phone" />
        </div>

        <!-- Billing Address -->
        <div>
            <Label for="invoice_billing_address">Billing Address</Label>
            <textarea
                id="invoice_billing_address"
                v-model="form.invoice_billing_address"
                placeholder="123 Main St, City, State 12345"
                rows="3"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            ></textarea>
            <InputError :message="errors.invoice_billing_address" />
        </div>

        <!-- Payment Terms -->
        <div>
            <Label for="invoice_payment_terms">Payment Terms</Label>
            <select
                id="invoice_payment_terms"
                v-model="form.invoice_payment_terms"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            >
                <option value="net_15">Net 15</option>
                <option value="net_30">Net 30</option>
                <option value="net_45">Net 45</option>
                <option value="net_60">Net 60</option>
            </select>
        </div>

        <!-- Default Checkbox -->
        <div class="flex items-center">
            <input
                id="is_default"
                v-model="form.is_default"
                type="checkbox"
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
            />
            <Label for="is_default" class="ml-2 block text-sm text-gray-700">
                Set as default payment method
            </Label>
        </div>

        <!-- Submit Button -->
        <div class="flex gap-3 justify-end">
            <Button type="submit" :disabled="loading">
                {{ loading ? 'Adding...' : 'Add Invoice Method' }}
            </Button>
        </div>
    </form>
</template>

