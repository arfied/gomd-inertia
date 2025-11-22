<script setup lang="ts">
import { ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { ConsultationScheduler } from '@/components/Clinical'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import type { BreadcrumbItemType } from '@/types'

interface Consultation {
    id: string
    patient_id: string
    provider_id: string
    scheduled_at: string
    duration: number
    status: 'scheduled' | 'completed' | 'cancelled'
    notes: string
}

interface Props {
    consultations: {
        data: Consultation[]
        current_page: number
        last_page: number
        per_page: number
        total: number
    }
    selectedConsultation?: Consultation | null
}

const props = withDefaults(defineProps<Props>(), {
    selectedConsultation: null,
})

const showScheduler = ref(false)

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Clinical', href: '/clinical/questionnaires' },
    { title: 'Consultations', href: '/clinical/consultations' }
]

const handleScheduleConsultation = async (data: any) => {
    try {
        const form = new FormData()
        Object.keys(data).forEach(key => {
            form.append(key, data[key])
        })
        form.append('_method', 'POST')

        const response = await fetch('/clinical/consultations', {
            method: 'POST',
            body: form,
            headers: {
                'Accept': 'application/json',
            }
        })

        if (response.ok) {
            showScheduler.value = false
            window.location.reload()
        }
    } catch (error) {
        console.error('Failed to schedule consultation:', error)
    }
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Consultations" />

        <div class="space-y-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold">Consultations</h1>
                    <p class="text-muted-foreground">Schedule and manage consultations</p>
                </div>
                <Button @click="showScheduler = true">
                    Schedule Consultation
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Upcoming Consultations</CardTitle>
                    <CardDescription>View and manage your scheduled consultations</CardDescription>
                </CardHeader>
                <CardContent>
                    <div v-if="props.consultations.data.length === 0" class="text-center py-8">
                        <p class="text-muted-foreground">No consultations scheduled. Schedule your first consultation to get started.</p>
                    </div>
                    <div v-else class="space-y-4">
                        <div v-for="consultation in props.consultations.data" :key="consultation.id" class="border rounded-lg p-4">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="font-semibold">{{ new Date(consultation.scheduled_at).toLocaleDateString() }}</p>
                                    <p class="text-sm text-muted-foreground">{{ new Date(consultation.scheduled_at).toLocaleTimeString() }}</p>
                                    <p class="text-sm mt-2">Duration: {{ consultation.duration }} minutes</p>
                                </div>
                                <span :class="[
                                    'px-3 py-1 rounded-full text-sm font-medium',
                                    consultation.status === 'scheduled' ? 'bg-blue-100 text-blue-800' :
                                    consultation.status === 'completed' ? 'bg-green-100 text-green-800' :
                                    'bg-gray-100 text-gray-800'
                                ]">
                                    {{ consultation.status }}
                                </span>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Schedule Consultation Modal -->
            <div v-if="showScheduler" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <Card class="w-full max-w-md">
                    <CardHeader>
                        <CardTitle>Schedule Consultation</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <ConsultationScheduler
                            @submit="handleScheduleConsultation"
                            @cancel="showScheduler = false"
                        />
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>

