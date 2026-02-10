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
    cc_number: '',
    cc_brand: '',
    cc_expiration_month: '',
    cc_expiration_year: '',
    cc_cvv: '',
    is_default: false,
})

const errors = ref<Record<string, string>>({})

const formatCardNumber = (value: string): string => {
    return value.replace(/\s/g, '').replace(/(\d{4})/g, '$1 ').trim()
}

const validateForm = () => {
    errors.value = {}

    if (!form.value.cc_number || form.value.cc_number.replace(/\s/g, '').length < 13) {
        errors.value.cc_number = 'Valid card number is required'
    }

    if (!form.value.cc_brand) {
        errors.value.cc_brand = 'Card brand is required'
    }

    if (!form.value.cc_expiration_month) {
        errors.value.cc_expiration_month = 'Expiration month is required'
    }

    if (!form.value.cc_expiration_year) {
        errors.value.cc_expiration_year = 'Expiration year is required'
    }

    if (!form.value.cc_cvv || form.value.cc_cvv.length < 3) {
        errors.value.cc_cvv = 'Valid CVV is required'
    }

    return Object.keys(errors.value).length === 0
}

const handleSubmit = () => {
    if (validateForm()) {
        const cardNumber = form.value.cc_number.replace(/\s/g, '')
        const lastFour = cardNumber.slice(-4)

        emit('submit', {
            ...form.value,
            cc_number: cardNumber,  // Full number for tokenization
            cc_last_four: lastFour,  // Only last 4 for storage
        })
    }
}
</script>

<template>
    <form @submit.prevent="handleSubmit" class="space-y-6">
        <!-- Card Number -->
        <div>
            <Label for="cc_number">Card Number</Label>
            <Input
                id="cc_number"
                v-model="form.cc_number"
                @input="form.cc_number = formatCardNumber($event.target.value)"
                type="text"
                placeholder="1234 5678 9012 3456"
                maxlength="19"
                class="mt-1"
            />
            <InputError :message="errors.cc_number" />
        </div>

        <!-- Card Brand -->
        <div>
            <Label for="cc_brand">Card Brand</Label>
            <select
                id="cc_brand"
                v-model="form.cc_brand"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            >
                <option value="">Select a brand</option>
                <option value="Visa">Visa</option>
                <option value="Mastercard">Mastercard</option>
                <option value="American Express">American Express</option>
                <option value="Discover">Discover</option>
                <option value="Unknown">Unknown</option>
            </select>
            <InputError :message="errors.cc_brand" />
        </div>

        <!-- Expiration Month -->
        <div class="grid grid-cols-2 gap-4">
            <div>
                <Label for="cc_expiration_month">Expiration Month</Label>
                <select
                    id="cc_expiration_month"
                    v-model="form.cc_expiration_month"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                >
                    <option value="">Select month</option>
                    <option v-for="month in 12" :key="month" :value="String(month).padStart(2, '0')">{{ String(month).padStart(2, '0') }}</option>
                </select>
                <InputError :message="errors.cc_expiration_month" />
            </div>

            <!-- Expiration Year -->
            <div>
                <Label for="cc_expiration_year">Expiration Year</Label>
                <select
                    id="cc_expiration_year"
                    v-model="form.cc_expiration_year"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                >
                    <option value="">Select year</option>
                    <option v-for="year in 20" :key="year" :value="String(new Date().getFullYear() + year - 1)">
                        {{ new Date().getFullYear() + year - 1 }}
                    </option>
                </select>
                <InputError :message="errors.cc_expiration_year" />
            </div>
        </div>

        <!-- CVV -->
        <div>
            <Label for="cc_cvv">CVV</Label>
            <Input
                id="cc_cvv"
                v-model="form.cc_cvv"
                type="text"
                placeholder="123"
                maxlength="4"
                class="mt-1"
            />
            <InputError :message="errors.cc_cvv" />
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
                {{ loading ? 'Adding...' : 'Add Credit Card' }}
            </Button>
        </div>
    </form>
</template>

