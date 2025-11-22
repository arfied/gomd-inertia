<script setup lang="ts">
import { ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import Layout from '@/layouts/AppLayout.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { ConsentForm } from '@/components/Compliance'

interface Consent {
    id: string
    patient_id: string
    consent_type: string
    status: string
    granted_at: string
}

interface Props {
    consents: {
        data: Consent[]
        current_page: number
        last_page: number
        per_page: number
        total: number
    }
    selectedConsent?: Consent | null
}

const props = withDefaults(defineProps<Props>(), {
    selectedConsent: null,
})

const showCreateModal = ref(false)

const handleCreateConsent = () => {
    showCreateModal.value = true
}

const handleConsentCreated = () => {
    showCreateModal.value = false
    window.location.reload()
}
</script>

<template>
    <Layout>
        <Head title="Consents" />

        <div class="space-y-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold">Consents</h1>
                    <p class="text-muted-foreground">Manage patient consents and permissions</p>
                </div>
                <Button @click="handleCreateConsent">
                    Grant Consent
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Active Consents</CardTitle>
                    <CardDescription>View and manage patient consents</CardDescription>
                </CardHeader>
                <CardContent>
                    <div v-if="props.consents.data.length > 0" class="space-y-4">
                        <div v-for="consent in props.consents.data" :key="consent.id" class="border-b pb-4 last:border-b-0">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="font-semibold">{{ consent.consent_type }}</p>
                                    <p class="text-sm text-muted-foreground">Patient: {{ consent.patient_id }}</p>
                                    <p class="text-xs text-muted-foreground mt-2">{{ consent.granted_at }}</p>
                                </div>
                                <span :class="[
                                    'px-3 py-1 rounded-full text-sm font-medium',
                                    consent.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
                                ]">
                                    {{ consent.status }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <p v-else class="text-muted-foreground">No consents found. Grant your first consent to get started.</p>
                </CardContent>
            </Card>
        </div>

        <!-- Create Consent Modal would go here -->
    </Layout>
</template>
