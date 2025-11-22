<script setup lang="ts">
import { ref } from 'vue'
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
    submit: [data: { noteType: string; content: string; attachments: File[] }]
    cancel: []
}>()

const noteType = ref('general')
const content = ref('')
const attachments = ref<File[]>([])
const fileInput = ref<HTMLInputElement>()

const noteTypes = [
    { value: 'general', label: 'General Note' },
    { value: 'diagnosis', label: 'Diagnosis' },
    { value: 'treatment', label: 'Treatment Plan' },
    { value: 'follow_up', label: 'Follow-up' },
    { value: 'lab_results', label: 'Lab Results' },
]

const handleFileSelect = (event: Event) => {
    const target = event.target as HTMLInputElement
    if (target.files) {
        attachments.value = Array.from(target.files)
    }
}

const removeAttachment = (index: number) => {
    attachments.value.splice(index, 1)
}

const handleSubmit = () => {
    emit('submit', {
        noteType: noteType.value,
        content: content.value,
        attachments: attachments.value,
    })
}

const handleCancel = () => {
    emit('cancel')
}
</script>

<template>
    <Card class="w-full max-w-4xl">
        <CardHeader>
            <CardTitle>Record Clinical Note</CardTitle>
            <CardDescription>Document patient observations and clinical findings</CardDescription>
        </CardHeader>

        <CardContent class="space-y-6">
            <!-- Note Type -->
            <div class="space-y-2">
                <Label for="note-type">Note Type</Label>
                <select
                    id="note-type"
                    v-model="noteType"
                    class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                >
                    <option v-for="type in noteTypes" :key="type.value" :value="type.value">
                        {{ type.label }}
                    </option>
                </select>
            </div>

            <!-- Content -->
            <div class="space-y-2">
                <Label for="content">Clinical Notes</Label>
                <textarea
                    id="content"
                    v-model="content"
                    class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary font-mono text-sm"
                    rows="10"
                    placeholder="Enter clinical observations, findings, and recommendations..."
                />
            </div>

            <!-- Attachments -->
            <div class="space-y-2">
                <Label>Attachments</Label>
                <div class="border-2 border-dashed rounded-lg p-6 text-center cursor-pointer hover:bg-secondary/50 transition"
                    @click="fileInput?.click()"
                >
                    <input
                        ref="fileInput"
                        type="file"
                        multiple
                        class="hidden"
                        @change="handleFileSelect"
                    />
                    <div class="text-sm text-muted-foreground">
                        <p class="font-semibold">Click to upload or drag and drop</p>
                        <p>PDF, images, or documents</p>
                    </div>
                </div>

                <!-- Attached Files List -->
                <div v-if="attachments.length > 0" class="space-y-2">
                    <p class="text-sm font-semibold">Attached Files:</p>
                    <div v-for="(file, index) in attachments" :key="index" class="flex items-center justify-between bg-secondary p-2 rounded">
                        <span class="text-sm">{{ file.name }}</span>
                        <button
                            @click="removeAttachment(index)"
                            class="text-xs text-red-500 hover:text-red-700"
                        >
                            Remove
                        </button>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end gap-2 pt-6">
                <Button variant="outline" @click="handleCancel" :disabled="loading">
                    Cancel
                </Button>
                <Button @click="handleSubmit" :disabled="loading || !content.trim()">
                    Save Note
                </Button>
            </div>
        </CardContent>
    </Card>
</template>

