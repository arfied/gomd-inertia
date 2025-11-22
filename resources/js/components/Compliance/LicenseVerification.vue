<script setup lang="ts">
import { computed } from 'vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'

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

interface Props {
    licenses: License[]
    loading?: boolean
}

withDefaults(defineProps<Props>(), {
    loading: false,
})

const emit = defineEmits<{
    'verify': [license: License]
    'view-details': [license: License]
}>()

const activeLicenses = computed(() => props.licenses.filter(l => l.status === 'active'))
const expiredLicenses = computed(() => props.licenses.filter(l => l.status === 'expired'))
const suspendedLicenses = computed(() => props.licenses.filter(l => l.status === 'suspended'))

const getStatusColor = (status: string): string => {
    const colors: Record<string, string> = {
        active: 'bg-green-100 text-green-800',
        expired: 'bg-red-100 text-red-800',
        suspended: 'bg-yellow-100 text-yellow-800',
        pending: 'bg-blue-100 text-blue-800',
    }
    return colors[status] || 'bg-gray-100 text-gray-800'
}

const isExpiringSoon = (expirationDate: string): boolean => {
    const expDate = new Date(expirationDate)
    const today = new Date()
    const daysUntilExpiry = Math.floor((expDate.getTime() - today.getTime()) / (1000 * 60 * 60 * 24))
    return daysUntilExpiry <= 30 && daysUntilExpiry > 0
}

const formatDate = (date: string): string => {
    return new Date(date).toLocaleDateString()
}

const daysUntilExpiry = (expirationDate: string): number => {
    const expDate = new Date(expirationDate)
    const today = new Date()
    return Math.floor((expDate.getTime() - today.getTime()) / (1000 * 60 * 60 * 24))
}
</script>

<template>
    <div class="w-full space-y-6">
        <!-- Active Licenses -->
        <Card>
            <CardHeader>
                <CardTitle>Active Licenses</CardTitle>
                <CardDescription>{{ activeLicenses.length }} active provider licenses</CardDescription>
            </CardHeader>
            <CardContent>
                <div v-if="activeLicenses.length > 0" class="space-y-4">
                    <div v-for="license in activeLicenses" :key="license.id" class="border rounded-lg p-4">
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <h3 class="font-semibold">{{ license.provider_name }}</h3>
                                <p class="text-sm text-muted-foreground">{{ license.license_type }}</p>
                            </div>
                            <Badge :class="getStatusColor(license.status)">
                                {{ license.status }}
                            </Badge>
                        </div>

                        <div class="grid grid-cols-2 gap-4 text-sm mb-3">
                            <div>
                                <p class="text-muted-foreground">License Number</p>
                                <p class="font-mono">{{ license.license_number }}</p>
                            </div>
                            <div>
                                <p class="text-muted-foreground">State</p>
                                <p>{{ license.issuing_state }}</p>
                            </div>
                            <div>
                                <p class="text-muted-foreground">Issued</p>
                                <p>{{ formatDate(license.issued_date) }}</p>
                            </div>
                            <div>
                                <p class="text-muted-foreground">Expires</p>
                                <p>{{ formatDate(license.expiration_date) }}</p>
                            </div>
                        </div>

                        <div class="flex gap-2">
                            <Button @click="emit('view-details', license)" variant="outline" size="sm">
                                View Details
                            </Button>
                            <Button @click="emit('verify', license)" variant="ghost" size="sm">
                                Verify
                            </Button>
                        </div>
                    </div>
                </div>
                <div v-else class="text-center py-8 text-muted-foreground">
                    No active licenses
                </div>
            </CardContent>
        </Card>

        <!-- Expiring Soon -->
        <Card v-if="expiredLicenses.length > 0 || suspendedLicenses.length > 0">
            <CardHeader>
                <CardTitle class="text-red-600">Attention Required</CardTitle>
                <CardDescription>Expired or suspended licenses</CardDescription>
            </CardHeader>
            <CardContent class="space-y-4">
                <div v-for="license in [...expiredLicenses, ...suspendedLicenses]" :key="license.id" class="border rounded-lg p-4 bg-red-50">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <h3 class="font-semibold">{{ license.provider_name }}</h3>
                            <p class="text-sm text-muted-foreground">{{ license.license_type }}</p>
                        </div>
                        <Badge :class="getStatusColor(license.status)">
                            {{ license.status }}
                        </Badge>
                    </div>
                    <p class="text-sm text-red-600 mb-3">
                        {{ license.status === 'expired' ? 'License expired on' : 'License suspended since' }}
                        {{ formatDate(license.expiration_date) }}
                    </p>
                    <Button @click="emit('verify', license)" size="sm">
                        Renew License
                    </Button>
                </div>
            </CardContent>
        </Card>
    </div>
</template>

