<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useSignupStore } from '@/stores/signupStore'
import { Input } from '@/components/ui/input'
import { Card, CardContent } from '@/components/ui/card'
import axios from 'axios'

interface Condition {
    id: string
    name: string
    description?: string
    icdCode?: string
}

const signupStore = useSignupStore()
const conditions = ref<Condition[]>([])
const filteredConditions = ref<Condition[]>([])
const searchQuery = ref('')
const loadingConds = ref(false)

onMounted(async () => {
    await loadConditions()
})

async function loadConditions() {
    loadingConds.value = true
    try {
        const response = await axios.get('/api/conditions')
        conditions.value = response.data.data || []
        filteredConditions.value = conditions.value
    } catch (error) {
        console.error('Failed to load conditions:', error)
        signupStore.error = 'Failed to load conditions'
    } finally {
        loadingConds.value = false
    }
}

function filterConditions() {
    if (!searchQuery.value) {
        filteredConditions.value = conditions.value
        return
    }
    
    const query = searchQuery.value.toLowerCase()
    filteredConditions.value = conditions.value.filter(cond =>
        cond.name.toLowerCase().includes(query) ||
        cond.description?.toLowerCase().includes(query) ||
        cond.icdCode?.toLowerCase().includes(query)
    )
}

async function selectCondition(conditionId: string) {
    try {
        await signupStore.selectCondition(conditionId)
    } catch (error) {
        console.error('Failed to select condition:', error)
    }
}
</script>

<template>
    <div class="space-y-4">
        <div class="mb-6">
            <Input
                v-model="searchQuery"
                @input="filterConditions"
                type="text"
                placeholder="Search conditions..."
                class="w-full"
            />
        </div>

        <div v-if="loadingConds" class="text-center py-8">
            <div class="inline-block animate-spin">
                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
            </div>
        </div>

        <div v-else-if="filteredConditions.length === 0" class="text-center py-8 text-gray-500">
            <p>No conditions found</p>
        </div>

        <div v-else class="grid gap-3">
            <button
                v-for="condition in filteredConditions"
                :key="condition.id"
                @click="selectCondition(condition.id)"
                :disabled="signupStore.loading"
                class="text-left"
            >
                <Card
                    :class="[
                        'cursor-pointer transition-all hover:shadow-md',
                        signupStore.state.conditionId === condition.id
                            ? 'ring-2 ring-indigo-600 bg-indigo-50'
                            : 'hover:border-indigo-300',
                    ]"
                >
                    <CardContent class="pt-6">
                        <div class="flex items-start justify-between">
                            <div>
                                <h3 class="font-semibold text-gray-900">{{ condition.name }}</h3>
                                <p v-if="condition.description" class="text-sm text-gray-600 mt-1">
                                    {{ condition.description }}
                                </p>
                                <p v-if="condition.icdCode" class="text-sm text-gray-500 mt-1">
                                    ICD Code: {{ condition.icdCode }}
                                </p>
                            </div>
                            <div
                                v-if="signupStore.state.conditionId === condition.id"
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

