<script setup lang="ts">
import { ref, computed } from 'vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'

interface Props {
    patientId?: string
    doctorId?: string
    loading?: boolean
}

withDefaults(defineProps<Props>(), {
    loading: false,
})

const emit = defineEmits<{
    submit: [data: { scheduledAt: string; reason: string; notes: string }]
    cancel: []
}>()

const scheduledAt = ref('')
const reason = ref('')
const notes = ref('')

const minDateTime = computed(() => {
    const now = new Date()
    now.setHours(now.getHours() + 1)
    return now.toISOString().slice(0, 16)
})

const handleSubmit = () => {
    emit('submit', {
        scheduledAt: scheduledAt.value,
        reason: reason.value,
        notes: notes.value,
    })
}

const handleCancel = () => {
    emit('cancel')
}
</script>

<template>
    <Card class="w-full max-w-2xl">
        <CardHeader>
            <CardTitle>Schedule Consultation</CardTitle>
            <CardDescription>Book a consultation appointment with the patient</CardDescription>
        </CardHeader>

        <CardContent class="space-y-6">
            <!-- Date & Time -->
            <div class="space-y-2">
                <Label for="scheduled-at">Date & Time</Label>
                <Input
                    id="scheduled-at"
                    v-model="scheduledAt"
                    type="datetime-local"
                    :min="minDateTime"
                    required
                />
                <p class="text-xs text-muted-foreground">
                    Minimum 1 hour from now
                </p>
            </div>

            <!-- Reason -->
            <div class="space-y-2">
                <Label for="reason">Reason for Consultation</Label>
                <Input
                    id="reason"
                    v-model="reason"
                    type="text"
                    placeholder="e.g., Follow-up, Initial Assessment, Medication Review"
                    required
                />
            </div>

            <!-- Notes -->
            <div class="space-y-2">
                <Label for="notes">Additional Notes</Label>
                <textarea
                    id="notes"
                    v-model="notes"
                    class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                    rows="4"
                    placeholder="Any additional information for the consultation..."
                />
            </div>

            <!-- Summary -->
            <div class="bg-secondary p-4 rounded-lg space-y-2">
                <p class="text-sm font-semibold">Consultation Summary:</p>
                <div class="text-sm space-y-1">
                    <p v-if="scheduledAt">
                        <span class="text-muted-foreground">Date & Time:</span>
                        {{ new Date(scheduledAt).toLocaleString() }}
                    </p>
                    <p v-if="reason">
                        <span class="text-muted-foreground">Reason:</span>
                        {{ reason }}
                    </p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end gap-2 pt-6">
                <Button variant="outline" @click="handleCancel" :disabled="loading">
                    Cancel
                </Button>
                <Button
                    @click="handleSubmit"
                    :disabled="loading || !scheduledAt || !reason"
                >
                    Schedule Consultation
                </Button>
            </div>
        </CardContent>
    </Card>
</template>

