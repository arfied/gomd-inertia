<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useSignupStore } from '@/stores/signupStore'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Checkbox } from '@/components/ui/checkbox'
import axios from 'axios'

interface Question {
    id: string
    text: string
    type: 'text' | 'checkbox' | 'radio' | 'textarea'
    options?: string[]
    required?: boolean
}

const signupStore = useSignupStore()
const questions = ref<Question[]>([])
const responses = ref<Record<string, any>>({})
const currentQuestionIndex = ref(0)
const loadingQuestions = ref(false)

const currentQuestion = computed(() => questions.value[currentQuestionIndex.value])
const isFirstQuestion = computed(() => currentQuestionIndex.value === 0)
const isLastQuestion = computed(() => currentQuestionIndex.value === questions.value.length - 1)
const progressPercentage = computed(() => {
    if (questions.value.length === 0) return 0
    return ((currentQuestionIndex.value + 1) / questions.value.length) * 100
})

onMounted(async () => {
    await loadQuestionnaire()
})

async function loadQuestionnaire() {
    loadingQuestions.value = true
    try {
        const params = new URLSearchParams()
        // Use medicationNames (first medication if available) and conditionId
        if (signupStore.state.medicationNames.length > 0) {
            params.set('medication_name', signupStore.state.medicationNames[0])
        }
        if (signupStore.state.conditionId) {
            params.set('condition_id', signupStore.state.conditionId)
        }

        const response = await axios.get(`/api/questionnaires?${params}`)
        questions.value = response.data.data || []

        if (questions.value.length === 0) {
            signupStore.error = 'No questionnaire available'
        }
    } catch (error) {
        console.error('Failed to load questionnaire:', error)
        signupStore.error = 'Failed to load questionnaire'
    } finally {
        loadingQuestions.value = false
    }
}

function nextQuestion() {
    if (!isLastQuestion.value) {
        currentQuestionIndex.value++
    } else {
        submitQuestionnaire()
    }
}

function previousQuestion() {
    if (!isFirstQuestion.value) {
        currentQuestionIndex.value--
    }
}

async function submitQuestionnaire() {
    try {
        await signupStore.completeQuestionnaire(responses.value)
    } catch (error) {
        console.error('Failed to submit questionnaire:', error)
    }
}
</script>

<template>
    <div class="space-y-6">
        <div v-if="loadingQuestions" class="text-center py-8">
            <div class="inline-block animate-spin">
                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
            </div>
        </div>

        <div v-else-if="questions.length === 0" class="text-center py-8 text-gray-500">
            <p>No questionnaire available</p>
        </div>

        <div v-else>
            <!-- Progress Bar -->
            <div class="mb-6">
                <div class="flex justify-between mb-2">
                    <span class="text-sm font-medium text-gray-700">
                        Question {{ currentQuestionIndex + 1 }} of {{ questions.length }}
                    </span>
                    <span class="text-sm font-medium text-gray-700">{{ Math.round(progressPercentage) }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div
                        class="bg-indigo-600 h-2 rounded-full transition-all duration-300"
                        :style="{ width: `${progressPercentage}%` }"
                    />
                </div>
            </div>

            <!-- Current Question -->
            <div v-if="currentQuestion" class="space-y-4">
                <Label class="text-lg font-semibold">
                    {{ currentQuestion.text }}
                    <span v-if="currentQuestion.required" class="text-red-500">*</span>
                </Label>

                <!-- Text Input -->
                <Input
                    v-if="currentQuestion.type === 'text'"
                    v-model="responses[currentQuestion.id]"
                    type="text"
                    placeholder="Enter your answer"
                />

                <!-- Textarea -->
                <textarea
                    v-if="currentQuestion.type === 'textarea'"
                    v-model="responses[currentQuestion.id]"
                    class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-600"
                    rows="4"
                    placeholder="Enter your answer"
                />

                <!-- Checkbox -->
                <div v-if="currentQuestion.type === 'checkbox'" class="space-y-3">
                    <div v-for="option in currentQuestion.options" :key="option" class="flex items-center gap-2">
                        <Checkbox
                            :id="`option-${option}`"
                            :checked="responses[currentQuestion.id]?.includes(option)"
                            @update:checked="(checked) => {
                                if (!responses[currentQuestion.id]) responses[currentQuestion.id] = []
                                if (checked) {
                                    responses[currentQuestion.id].push(option)
                                } else {
                                    responses[currentQuestion.id] = responses[currentQuestion.id].filter((o: string) => o !== option)
                                }
                            }"
                        />
                        <Label :for="`option-${option}`" class="font-normal cursor-pointer">{{ option }}</Label>
                    </div>
                </div>

                <!-- Radio -->
                <div v-if="currentQuestion.type === 'radio'" class="space-y-3">
                    <div v-for="option in currentQuestion.options" :key="option" class="flex items-center gap-2">
                        <input
                            :id="`radio-${option}`"
                            v-model="responses[currentQuestion.id]"
                            type="radio"
                            :value="option"
                            class="w-4 h-4 text-indigo-600"
                        />
                        <Label :for="`radio-${option}`" class="font-normal cursor-pointer">{{ option }}</Label>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <div class="flex justify-between gap-4 pt-6">
                <button
                    @click="previousQuestion"
                    :disabled="isFirstQuestion || signupStore.loading"
                    class="px-4 py-2 border rounded-md hover:bg-gray-50 disabled:opacity-50"
                >
                    Previous
                </button>
                <button
                    @click="nextQuestion"
                    :disabled="signupStore.loading"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50"
                >
                    {{ isLastQuestion ? 'Submit' : 'Next' }}
                </button>
            </div>
        </div>
    </div>
</template>

