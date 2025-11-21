<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { AlertCircle, CheckCircle2, Loader2, Plus, Trash2 } from 'lucide-vue-next'

interface Configuration {
    renewal: {
        idempotency_ttl_days: number
        max_attempts: number
        retry_schedule: number[]
    }
    rate_limiting: {
        hourly_limit: number
        daily_limit: number
    }
    failure_alerts: {
        enabled: boolean
        email_recipients: string[]
        slack_webhook: boolean
        pagerduty_key: boolean
    }
}

const configuration = ref<Configuration | null>(null)
const loading = ref(true)
const saving = ref(false)
const error = ref('')
const success = ref('')

// Retry configuration form
const retryForm = ref({
    idempotency_ttl_days: 30,
    max_attempts: 5,
    retry_schedule: [1, 3, 7, 14, 30],
})

// Rate limiting form
const rateLimitForm = ref({
    hourly_limit: 5,
    daily_limit: 20,
})

const scheduleError = computed(() => {
    const schedule = retryForm.value.retry_schedule
    for (let i = 1; i < schedule.length; i++) {
        if (schedule[i] <= schedule[i - 1]) {
            return 'Schedule must be in ascending order'
        }
    }
    if (schedule.length < retryForm.value.max_attempts - 1) {
        return `Need at least ${retryForm.value.max_attempts - 1} schedule entries`
    }
    return ''
})

onMounted(async () => {
    try {
        const response = await fetch('/admin/subscription-configuration/config', {
            headers: { 'Accept': 'application/json' },
        })
        const data = await response.json()
        configuration.value = data
        retryForm.value = { ...data.renewal }
        rateLimitForm.value = { ...data.rate_limiting }
    } catch (err) {
        error.value = 'Failed to load configuration'
    } finally {
        loading.value = false
    }
})

const addScheduleEntry = () => {
    const lastValue = retryForm.value.retry_schedule[retryForm.value.retry_schedule.length - 1] || 0
    retryForm.value.retry_schedule.push(lastValue + 7)
}

const removeScheduleEntry = (index: number) => {
    retryForm.value.retry_schedule.splice(index, 1)
}

const updateScheduleEntry = (index: number, value: number) => {
    retryForm.value.retry_schedule[index] = value
}

const saveRetryConfiguration = async () => {
    if (scheduleError.value) return

    saving.value = true
    error.value = ''
    success.value = ''

    try {
        const response = await fetch('/admin/subscription-configuration/retry', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify(retryForm.value),
        })

        const data = await response.json()

        if (!response.ok) {
            error.value = data.message || 'Failed to update configuration'
            return
        }

        success.value = 'Retry configuration updated successfully'
        setTimeout(() => success.value = '', 3000)
    } catch (err) {
        error.value = 'Failed to save configuration'
    } finally {
        saving.value = false
    }
}

const saveRateLimitConfiguration = async () => {
    saving.value = true
    error.value = ''
    success.value = ''

    try {
        const response = await fetch('/admin/subscription-configuration/rate-limits', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify(rateLimitForm.value),
        })

        const data = await response.json()

        if (!response.ok) {
            error.value = data.message || 'Failed to update configuration'
            return
        }

        success.value = 'Rate limit configuration updated successfully'
        setTimeout(() => success.value = '', 3000)
    } catch (err) {
        error.value = 'Failed to save configuration'
    } finally {
        saving.value = false
    }
}
</script>

<template>
    <Head title="Subscription Configuration" />
    <AppLayout :breadcrumbs="[{ title: 'Subscription Configuration', href: '#' }]">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Subscription Configuration</h1>
                <p class="text-gray-600 mt-2">Manage renewal retry schedules and rate limits</p>
            </div>

            <!-- Alerts -->
            <div v-if="error" class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg flex gap-3">
                <AlertCircle class="text-red-600 flex-shrink-0" />
                <p class="text-red-800">{{ error }}</p>
            </div>

            <div v-if="success" class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg flex gap-3">
                <CheckCircle2 class="text-green-600 flex-shrink-0" />
                <p class="text-green-800">{{ success }}</p>
            </div>

            <div v-if="loading" class="flex justify-center py-12">
                <Loader2 class="animate-spin text-blue-600" />
            </div>

            <div v-else class="space-y-6">
                <!-- Retry Configuration Card -->
                <Card>
                    <CardHeader>
                        <CardTitle>Retry Configuration</CardTitle>
                        <CardDescription>Configure renewal retry attempts and schedule</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <Label for="idempotency_ttl">Idempotency TTL (days)</Label>
                                <Input
                                    id="idempotency_ttl"
                                    v-model.number="retryForm.idempotency_ttl_days"
                                    type="number"
                                    min="1"
                                    max="365"
                                    class="mt-2"
                                />
                            </div>
                            <div>
                                <Label for="max_attempts">Max Attempts</Label>
                                <Input
                                    id="max_attempts"
                                    v-model.number="retryForm.max_attempts"
                                    type="number"
                                    min="1"
                                    max="10"
                                    class="mt-2"
                                />
                            </div>
                        </div>

                        <div>
                            <div class="flex justify-between items-center mb-3">
                                <Label>Retry Schedule (days)</Label>
                                <Button
                                    @click="addScheduleEntry"
                                    variant="outline"
                                    size="sm"
                                    class="gap-2"
                                >
                                    <Plus class="w-4 h-4" />
                                    Add
                                </Button>
                            </div>

                            <div class="space-y-2">
                                <div
                                    v-for="(value, index) in retryForm.retry_schedule"
                                    :key="index"
                                    class="flex gap-2 items-center"
                                >
                                    <span class="text-sm text-gray-600 w-8">{{ index + 1 }}.</span>
                                    <Input
                                        :value="value"
                                        @input="updateScheduleEntry(index, Number($event.target.value))"
                                        type="number"
                                        min="1"
                                        max="365"
                                        class="flex-1"
                                    />
                                    <span class="text-sm text-gray-600">days</span>
                                    <Button
                                        @click="removeScheduleEntry(index)"
                                        variant="ghost"
                                        size="sm"
                                        :disabled="retryForm.retry_schedule.length === 1"
                                    >
                                        <Trash2 class="w-4 h-4" />
                                    </Button>
                                </div>
                            </div>

                            <p v-if="scheduleError" class="text-sm text-red-600 mt-2">{{ scheduleError }}</p>
                        </div>

                        <Button
                            @click="saveRetryConfiguration"
                            :disabled="saving || !!scheduleError"
                            class="w-full"
                        >
                            <Loader2 v-if="saving" class="w-4 h-4 mr-2 animate-spin" />
                            Save Retry Configuration
                        </Button>
                    </CardContent>
                </Card>

                <!-- Rate Limiting Card -->
                <Card>
                    <CardHeader>
                        <CardTitle>Rate Limiting</CardTitle>
                        <CardDescription>Configure renewal endpoint rate limits</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <Label for="hourly_limit">Hourly Limit</Label>
                                <Input
                                    id="hourly_limit"
                                    v-model.number="rateLimitForm.hourly_limit"
                                    type="number"
                                    min="1"
                                    max="100"
                                    class="mt-2"
                                />
                            </div>
                            <div>
                                <Label for="daily_limit">Daily Limit</Label>
                                <Input
                                    id="daily_limit"
                                    v-model.number="rateLimitForm.daily_limit"
                                    type="number"
                                    min="1"
                                    max="1000"
                                    class="mt-2"
                                />
                            </div>
                        </div>

                        <Button
                            @click="saveRateLimitConfiguration"
                            :disabled="saving"
                            class="w-full"
                        >
                            <Loader2 v-if="saving" class="w-4 h-4 mr-2 animate-spin" />
                            Save Rate Limit Configuration
                        </Button>
                    </CardContent>
                </Card>

                <!-- Current Configuration Display -->
                <Card v-if="configuration">
                    <CardHeader>
                        <CardTitle>Current Configuration</CardTitle>
                        <CardDescription>Active configuration values</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <pre class="bg-gray-100 p-4 rounded text-sm overflow-auto">{{ JSON.stringify(configuration, null, 2) }}</pre>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>

