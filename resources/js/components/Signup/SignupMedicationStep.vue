<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useSignupStore } from '@/stores/signupStore'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import axios from 'axios'

interface Medication {
    id: string
    name: string
    generic_name: string
    description?: string
}

function truncateDescription(description: string | undefined, maxLength: number = 50): string {
    if (!description) return ''
    return description.length > maxLength ? description.substring(0, maxLength) + '...' : description
}

const signupStore = useSignupStore()
const medications = ref<Medication[]>([])
const filteredMedications = ref<Medication[]>([])
const searchQuery = ref('')
const loadingMeds = ref(false)

const selectedMedications = computed(() => signupStore.state.medicationNames)

onMounted(async () => {
    await loadMedications()
})

async function loadMedications() {
    loadingMeds.value = true
    try {
        const response = await axios.get('/api/medications')
        medications.value = response.data.data || []
        filteredMedications.value = medications.value
    } catch (error) {
        console.error('Failed to load medications:', error)
        signupStore.error = 'Failed to load medications'
    } finally {
        loadingMeds.value = false
    }
}

function filterMedications() {
    if (!searchQuery.value) {
        filteredMedications.value = medications.value
        return
    }

    const query = searchQuery.value.toLowerCase()
    filteredMedications.value = medications.value.filter(med =>
        med.name.toLowerCase().includes(query) ||
        med.description?.toLowerCase().includes(query)
    )
}

async function selectMedication(medicationName: string) {
    try {
        await signupStore.selectMedication(medicationName)
    } catch (error) {
        console.error('Failed to select medication:', error)
    }
}
</script>

<template>
    <div class="space-y-4">
        <!-- Selected Medications Display -->
        <div v-if="selectedMedications.length > 0" class="mb-6 p-4 bg-indigo-50 rounded-lg border border-indigo-200">
            <p class="text-sm font-medium text-indigo-900 mb-2">Selected Medications:</p>
            <div class="flex flex-wrap gap-2">
                <Badge
                    v-for="med in selectedMedications"
                    :key="med"
                    class="bg-indigo-600 text-white"
                >
                    {{ med }}
                </Badge>
            </div>
        </div>

        <div class="mb-6">
            <Input
                v-model="searchQuery"
                @input="filterMedications"
                type="text"
                placeholder="Search medications..."
                class="w-full"
            />
        </div>

        <div v-if="loadingMeds" class="text-center py-8">
            <div class="inline-block animate-spin">
                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
            </div>
        </div>

        <div v-else-if="filteredMedications.length === 0" class="text-center py-8 text-gray-500">
            <p>No medications found</p>
        </div>

        <div v-else class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            <button
                v-for="medication in filteredMedications"
                :key="medication.id"
                @click="selectMedication(medication.name)"
                :disabled="signupStore.loading"
                class="text-left"
            >
                <Card
                    :class="[
                        'cursor-pointer transition-all hover:shadow-md',
                        selectedMedications.includes(medication.name)
                            ? 'ring-2 ring-indigo-600 bg-indigo-50'
                            : 'hover:border-indigo-300',
                    ]"
                >
                    <CardContent>
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-900">{{ medication.generic_name }}</h3>
                                <p
                                    v-if="medication.description"
                                    class="text-sm text-gray-600 mt-1 cursor-help"
                                    :title="medication.description"
                                >
                                    {{ truncateDescription(medication.description) }}
                                </p>
                            </div>
                            <div
                                v-if="selectedMedications.includes(medication.name)"
                                class="w-6 h-6 rounded-full bg-indigo-600 flex items-center justify-center flex-shrink-0"
                            >
                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </button>
        </div>
    </div>
</template>

