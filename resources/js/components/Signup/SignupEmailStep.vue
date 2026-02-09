<script setup lang="ts">
import { ref, watch, onMounted } from 'vue'
import { useSignupStore } from '@/stores/signupStore'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import InputError from '@/components/InputError.vue'
import axios from 'axios'

const signupStore = useSignupStore()
const email = ref('')
const loading = ref(false)
const error = ref('')
const userCreated = ref(false)
const emailSubmitted = ref(false)

// Restore email from store when component mounts
onMounted(() => {
    if (signupStore.state.email) {
        email.value = signupStore.state.email
        // If email is already in store, it means it was already submitted
        emailSubmitted.value = true
    }
})

// Watch for changes to email in store (from parent)
watch(() => signupStore.state.email, (newEmail) => {
    if (newEmail) {
        email.value = newEmail
    }
})

async function createPatientUser() {
    // If email was already submitted in this session, don't try again
    if (emailSubmitted.value) {
        return
    }

    error.value = ''
    loading.value = true

    try {
        const response = await axios.post('/signup/create-patient-user', {
            signup_id: signupStore.state.signupId,
            email: email.value,
        })

        if (response.data.success) {
            // Update store with email
            signupStore.state.email = email.value
            userCreated.value = true
            emailSubmitted.value = true
        } else {
            error.value = response.data.message || 'Failed to create patient user'
        }
    } catch (err: any) {
        if (err.response?.data?.message) {
            error.value = err.response.data.message
        } else if (err.response?.status === 422) {
            const errors = err.response.data.errors
            if (errors?.email) {
                error.value = errors.email[0]
            } else {
                error.value = 'Validation failed'
            }
        } else {
            error.value = 'Failed to create patient user'
        }
    } finally {
        loading.value = false
    }
}

function handleKeydown(event: KeyboardEvent) {
    if (event.key === 'Enter' && !loading.value && email.value) {
        createPatientUser()
    }
}

// Expose the createPatientUser function and email ref to parent
defineExpose({
    createPatientUser,
    email,
})
</script>

<template>
    <div class="space-y-4">
        <p class="text-gray-600 mb-6">
            Please enter your email address to create your account. You can change your password later.
        </p>

        <div class="space-y-2">
            <Label for="email">Email Address</Label>
            <Input
                id="email"
                v-model="email"
                type="email"
                placeholder="your@email.com"
                :disabled="loading"
                @keydown="handleKeydown"
                required
            />
            <InputError v-if="error" :message="error" />
        </div>
    </div>
</template>

