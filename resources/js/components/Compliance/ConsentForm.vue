<script lang="ts">
export interface ConsentType {
    id: string
    name: string
    description: string
    required: boolean
}

const defaultConsentTypes: ConsentType[] = [
    {
        id: 'treatment',
        name: 'Treatment Consent',
        description: 'I consent to receive medical treatment and procedures as recommended by my healthcare provider.',
        required: true,
    },
    {
        id: 'data_sharing',
        name: 'Data Sharing',
        description: 'I consent to share my medical data with authorized healthcare providers for continuity of care.',
        required: false,
    },
    {
        id: 'research',
        name: 'Research Participation',
        description: 'I consent to participate in medical research studies (optional).',
        required: false,
    },
    {
        id: 'telehealth',
        name: 'Telehealth Services',
        description: 'I consent to receive telehealth services and understand the limitations of remote consultations.',
        required: false,
    },
]
</script>

<script setup lang="ts">
import { ref } from 'vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Checkbox } from '@/components/ui/checkbox'
import { Label } from '@/components/ui/label'

interface Props {
    patientId?: string
    consentTypes?: ConsentType[]
    loading?: boolean
}

const props = withDefaults(defineProps<Props>(), {
    consentTypes: () => defaultConsentTypes,
    loading: false,
})

const emit = defineEmits<{
    submit: [consents: Record<string, boolean>]
    cancel: []
}>()

const consents = ref<Record<string, boolean>>({})

const handleSubmit = () => {
    const allRequired = props.consentTypes
        .filter(ct => ct.required)
        .every(ct => consents.value[ct.id])

    if (!allRequired) {
        alert('Please accept all required consents')
        return
    }

    emit('submit', consents.value)
}

const handleCancel = () => {
    emit('cancel')
}
</script>

<template>
    <Card class="w-full max-w-2xl">
        <CardHeader>
            <CardTitle>Patient Consent</CardTitle>
            <CardDescription>Please review and accept the following consent forms</CardDescription>
        </CardHeader>

        <CardContent class="space-y-6">
            <!-- Consent Items -->
            <div class="space-y-4">
                <div v-for="consentType in consentTypes" :key="consentType.id" class="border rounded-lg p-4 space-y-3">
                    <div class="flex items-start gap-3">
                        <Checkbox
                            :id="`consent-${consentType.id}`"
                            v-model:checked="consents[consentType.id]"
                            :disabled="loading"
                        />
                        <div class="flex-1">
                            <Label :for="`consent-${consentType.id}`" class="font-semibold cursor-pointer">
                                {{ consentType.name }}
                                <span v-if="consentType.required" class="text-red-500 ml-1">*</span>
                            </Label>
                            <p class="text-sm text-muted-foreground mt-1">
                                {{ consentType.description }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Legal Notice -->
            <div class="bg-secondary/50 p-4 rounded-lg text-xs text-muted-foreground space-y-2">
                <p class="font-semibold">Important Notice:</p>
                <p>
                    By providing consent, you acknowledge that you have read and understood the above statements.
                    Your consent is voluntary and you may withdraw it at any time by contacting our office.
                </p>
                <p>
                    This consent is governed by applicable healthcare privacy laws including HIPAA.
                </p>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end gap-2 pt-6">
                <Button variant="outline" @click="handleCancel" :disabled="loading">
                    Cancel
                </Button>
                <Button @click="handleSubmit" :disabled="loading">
                    Accept Consents
                </Button>
            </div>
        </CardContent>
    </Card>
</template>

