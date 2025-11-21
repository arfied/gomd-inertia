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
    ach_account_name: '',
    ach_account_type: 'checking',
    ach_routing_number_last_four: '',
    ach_account_number_last_four: '',
    ach_token: '',
    is_default: false,
})

const errors = ref<Record<string, string>>({})

const validateForm = () => {
    errors.value = {}

    if (!form.value.ach_account_name) {
        errors.value.ach_account_name = 'Account holder name is required'
    }

    if (!form.value.ach_routing_number_last_four || form.value.ach_routing_number_last_four.length !== 4) {
        errors.value.ach_routing_number_last_four = 'Last 4 digits of routing number must be exactly 4 characters'
    }

    if (!form.value.ach_account_number_last_four || form.value.ach_account_number_last_four.length !== 4) {
        errors.value.ach_account_number_last_four = 'Last 4 digits of account number must be exactly 4 characters'
    }

    if (!form.value.ach_token) {
        errors.value.ach_token = 'Bank account token is required'
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
        <!-- Account Holder Name -->
        <div>
            <Label for="ach_account_name">Account Holder Name</Label>
            <Input
                id="ach_account_name"
                v-model="form.ach_account_name"
                type="text"
                placeholder="John Doe"
                class="mt-1"
            />
            <InputError :message="errors.ach_account_name" />
        </div>

        <!-- Account Type -->
        <div>
            <Label for="ach_account_type">Account Type</Label>
            <select
                id="ach_account_type"
                v-model="form.ach_account_type"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            >
                <option value="checking">Checking</option>
                <option value="savings">Savings</option>
            </select>
            <InputError :message="errors.ach_account_type" />
        </div>

        <!-- Routing Number Last 4 -->
        <div>
            <Label for="ach_routing_number_last_four">Routing Number (Last 4 Digits)</Label>
            <Input
                id="ach_routing_number_last_four"
                v-model="form.ach_routing_number_last_four"
                type="text"
                placeholder="1234"
                maxlength="4"
                class="mt-1"
            />
            <InputError :message="errors.ach_routing_number_last_four" />
        </div>

        <!-- Account Number Last 4 -->
        <div>
            <Label for="ach_account_number_last_four">Account Number (Last 4 Digits)</Label>
            <Input
                id="ach_account_number_last_four"
                v-model="form.ach_account_number_last_four"
                type="text"
                placeholder="5678"
                maxlength="4"
                class="mt-1"
            />
            <InputError :message="errors.ach_account_number_last_four" />
        </div>

        <!-- Token (Hidden) -->
        <input v-model="form.ach_token" type="hidden" />

        <!-- Info Alert -->
        <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <p class="text-sm text-blue-800">
                Your bank account will need to be verified with micro-deposits before it can be used for payments.
            </p>
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
                {{ loading ? 'Adding...' : 'Add Bank Account' }}
            </Button>
        </div>
    </form>
</template>

