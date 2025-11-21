<script setup lang="ts">
import type { PaymentMethod } from '@/stores/paymentMethodsStore'
import { CreditCard, Landmark, FileText, Check, AlertCircle, Trash2 } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'

interface Props {
    paymentMethod: PaymentMethod
    isDeleting: boolean
    deleteLoading: boolean
}

interface Emits {
    (e: 'delete'): void
    (e: 'cancelDelete'): void
    (e: 'setDefault', id: number): void
}

defineProps<Props>()
defineEmits<Emits>()

const getIcon = (type: string) => {
    switch (type) {
        case 'credit_card':
            return CreditCard
        case 'ach':
            return Landmark
        case 'invoice':
            return FileText
        default:
            return CreditCard
    }
}

const getStatusBadgeClass = (status: string) => {
    switch (status) {
        case 'verified':
            return 'bg-green-100 text-green-800'
        case 'pending':
            return 'bg-yellow-100 text-yellow-800'
        case 'failed':
            return 'bg-red-100 text-red-800'
        default:
            return 'bg-gray-100 text-gray-800'
    }
}

const getStatusLabel = (status: string) => {
    return status.charAt(0).toUpperCase() + status.slice(1)
}
</script>

<template>
    <div class="px-6 py-4 hover:bg-gray-50 transition-colors">
        <div class="flex items-start justify-between">
            <div class="flex items-start gap-4 flex-1">
                <!-- Icon -->
                <div class="flex-shrink-0 mt-1">
                    <component :is="getIcon(paymentMethod.type)" class="w-6 h-6 text-gray-400" />
                </div>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-3 mb-1">
                        <h3 class="text-sm font-medium text-gray-900">{{ paymentMethod.display_name }}</h3>
                        <div v-if="paymentMethod.is_default" class="flex items-center gap-1 px-2 py-1 bg-blue-100 rounded text-xs font-medium text-blue-800">
                            <Check class="w-3 h-3" />
                            Default
                        </div>
                        <div :class="['px-2 py-1 rounded text-xs font-medium flex items-center gap-1', getStatusBadgeClass(paymentMethod.verification_status)]">
                            <AlertCircle v-if="paymentMethod.verification_status === 'pending'" class="w-3 h-3" />
                            {{ getStatusLabel(paymentMethod.verification_status) }}
                        </div>
                    </div>
                    <p class="text-xs text-gray-500">Added {{ new Date(paymentMethod.created_at).toLocaleDateString() }}</p>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex-shrink-0 ml-4">
                <div v-if="!isDeleting" class="flex items-center gap-2">
                    <Button
                        v-if="!paymentMethod.is_default"
                        @click="$emit('setDefault', paymentMethod.id)"
                        variant="outline"
                        size="sm"
                    >
                        Set as Default
                    </Button>
                    <Button
                        @click="$emit('delete')"
                        variant="ghost"
                        size="sm"
                        class="text-red-600 hover:text-red-700 hover:bg-red-50"
                    >
                        <Trash2 class="w-4 h-4" />
                    </Button>
                </div>

                <!-- Delete Confirmation -->
                <div v-else class="flex items-center gap-2">
                    <span class="text-sm text-gray-600">Delete this payment method?</span>
                    <Button
                        @click="$emit('delete')"
                        :disabled="deleteLoading"
                        variant="destructive"
                        size="sm"
                    >
                        {{ deleteLoading ? 'Deleting...' : 'Confirm' }}
                    </Button>
                    <Button
                        @click="$emit('cancelDelete')"
                        :disabled="deleteLoading"
                        variant="outline"
                        size="sm"
                    >
                        Cancel
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>

