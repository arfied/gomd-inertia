<script setup lang="ts">
import { ref, computed } from 'vue'
import { ConsentForm, LicenseVerification } from '@/components/Compliance'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import AppLayout from '@/layouts/AppLayout.vue'
import type { BreadcrumbItemType } from '@/types'

interface License {
    id: string
    provider_id: string
    provider_name: string
    license_number: string
    license_type: string
    issuing_state: string
    issued_date: string
    expiration_date: string
    status: 'active' | 'expired' | 'suspended' | 'pending'
    verified_at: string
}

interface Consent {
    id: string
    patient_id: string
    consent_type: string
    status: string
    granted_at: string
}

interface Props {
    licenses?: {
        data: License[]
        current_page: number
        last_page: number
        per_page: number
        total: number
    }
    consents?: {
        data: Consent[]
        current_page: number
        last_page: number
        per_page: number
        total: number
    }
}

const props = withDefaults(defineProps<Props>(), {
    licenses: () => ({ data: [], current_page: 1, last_page: 1, per_page: 10, total: 0 }),
    consents: () => ({ data: [], current_page: 1, last_page: 1, per_page: 10, total: 0 }),
})

const showConsentForm = ref(false)

const handleConsentSubmit = async (consents: Record<string, boolean>) => {
    try {
        const form = new FormData()
        Object.keys(consents).forEach(key => {
            form.append(key, consents[key])
        })

        const response = await fetch('/compliance/consents', {
            method: 'POST',
            body: form,
            headers: {
                'Accept': 'application/json',
            }
        })

        if (response.ok) {
            showConsentForm.value = false
            window.location.reload()
        }
    } catch (error) {
        console.error('Failed to submit consents:', error)
    }
}

const activeLicenses = computed(() => props.licenses.data.filter(l => l.status === 'active').length)
const expiredLicenses = computed(() => props.licenses.data.filter(l => l.status === 'expired').length)

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Compliance', href: '/compliance' },
    { title: 'Dashboard', href: '/compliance/dashboard' }
]
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <div>
                <h1 class="text-3xl font-bold">Compliance Dashboard</h1>
                <p class="text-muted-foreground">Manage patient consents and provider licenses</p>
            </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <Card>
                <CardHeader class="pb-2">
                    <CardTitle class="text-sm font-medium">Active Licenses</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">{{ activeLicenses }}</div>
                    <p class="text-xs text-muted-foreground">Provider licenses</p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="pb-2">
                    <CardTitle class="text-sm font-medium">Expired Licenses</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold text-red-600">{{ expiredLicenses }}</div>
                    <p class="text-xs text-muted-foreground">Require renewal</p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="pb-2">
                    <CardTitle class="text-sm font-medium">Total Licenses</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">{{ props.licenses.data.length }}</div>
                    <p class="text-xs text-muted-foreground">All providers</p>
                </CardContent>
            </Card>
        </div>

        <!-- Consent Management -->
        <Card>
            <CardHeader>
                <div class="flex justify-between items-start">
                    <div>
                        <CardTitle>Patient Consent Management</CardTitle>
                        <CardDescription>Manage patient consents for treatment and data sharing</CardDescription>
                    </div>
                    <Button @click="showConsentForm = true">
                        Grant Consent
                    </Button>
                </div>
            </CardHeader>
            <CardContent>
                <div v-if="showConsentForm" class="mb-6">
                    <ConsentForm
                        @submit="handleConsentSubmit"
                        @cancel="showConsentForm = false"
                    />
                </div>
                <div v-else class="text-center py-8 text-muted-foreground">
                    <p>Click "Grant Consent" to manage patient consents</p>
                </div>
            </CardContent>
        </Card>

            <!-- License Verification -->
            <LicenseVerification
                :licenses="props.licenses.data"
                @verify="handleVerifyLicense"
            />
        </div>
    </AppLayout>
</template>

