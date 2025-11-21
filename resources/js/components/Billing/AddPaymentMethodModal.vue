<script setup lang="ts">
import { ref } from 'vue'
import { X } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import CreditCardForm from './Forms/CreditCardForm.vue'
import AchForm from './Forms/AchForm.vue'
import InvoiceForm from './Forms/InvoiceForm.vue'

interface Props {
    open: boolean
}

interface Emits {
    (e: 'update:open', value: boolean): void
    (e: 'submit', data: any): void
}

defineProps<Props>()
const emit = defineEmits<Emits>()

const activeTab = ref<'credit_card' | 'ach' | 'invoice'>('credit_card')
const loading = ref(false)

const tabs = [
    { id: 'credit_card', label: 'Credit Card', icon: '💳' },
    { id: 'ach', label: 'Bank Account', icon: '🏦' },
    { id: 'invoice', label: 'Invoice', icon: '📄' },
]

const handleSubmit = async (data: any) => {
    loading.value = true
    try {
        emit('submit', { ...data, type: activeTab.value })
        emit('update:open', false)
    } finally {
        loading.value = false
    }
}

const closeModal = () => {
    emit('update:open', false)
}
</script>

<template>
    <div v-if="open" class="fixed inset-0 z-50 overflow-y-auto">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" @click="closeModal"></div>

        <!-- Modal -->
        <div class="flex items-center justify-center min-h-screen px-4 py-8">
            <div class="relative bg-white rounded-lg shadow-xl max-w-2xl w-full">
                <!-- Header -->
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Add Payment Method</h2>
                    <button
                        @click="closeModal"
                        class="text-gray-400 hover:text-gray-600 transition-colors"
                    >
                        <X class="w-6 h-6" />
                    </button>
                </div>

                <!-- Tabs -->
                <div class="px-6 pt-4 border-b border-gray-200">
                    <div class="flex gap-4">
                        <button
                            v-for="tab in tabs"
                            :key="tab.id"
                            @click="activeTab = tab.id as any"
                            :class="[
                                'px-4 py-2 text-sm font-medium border-b-2 transition-colors',
                                activeTab === tab.id
                                    ? 'border-blue-500 text-blue-600'
                                    : 'border-transparent text-gray-600 hover:text-gray-900'
                            ]"
                        >
                            <span class="mr-2">{{ tab.icon }}</span>{{ tab.label }}
                        </button>
                    </div>
                </div>

                <!-- Content -->
                <div class="px-6 py-6">
                    <CreditCardForm
                        v-if="activeTab === 'credit_card'"
                        :loading="loading"
                        @submit="handleSubmit"
                    />
                    <AchForm
                        v-else-if="activeTab === 'ach'"
                        :loading="loading"
                        @submit="handleSubmit"
                    />
                    <InvoiceForm
                        v-else-if="activeTab === 'invoice'"
                        :loading="loading"
                        @submit="handleSubmit"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

