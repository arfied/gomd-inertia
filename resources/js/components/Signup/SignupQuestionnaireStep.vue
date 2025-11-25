<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useSignupStore } from '@/stores/signupStore'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Checkbox } from '@/components/ui/checkbox'
import axios from 'axios'

const emit = defineEmits<{
    completed: []
}>()

interface Question {
    id: string
    text: string
    type: 'text' | 'checkbox' | 'radio' | 'textarea' | 'select' | 'date' | 'number'
    options?: Array<{ value: string; label: string }> | string[]
    required?: boolean
    section?: string
    order?: number
    parent_question_id?: string | null
    parent_answer_value?: string | null
}

const signupStore = useSignupStore()
const questions = ref<Question[]>([])
const responses = ref<Record<string, any>>({})
const currentQuestionIndex = ref(0)
const loadingQuestions = ref(false)
const questionnaireUuid = ref<string | null>(null)
const validationErrors = ref<Record<string, string>>({})
const submitting = ref(false)

const currentQuestion = computed(() => questions.value[currentQuestionIndex.value])
const isFirstQuestion = computed(() => currentQuestionIndex.value === 0)
const isLastQuestion = computed(() => currentQuestionIndex.value === questions.value.length - 1)
const progressPercentage = computed(() => {
    if (questions.value.length === 0) return 0
    return ((currentQuestionIndex.value + 1) / questions.value.length) * 100
})

// Check if current question should be displayed based on parent question conditions
const shouldShowCurrentQuestion = computed(() => {
    const question = currentQuestion.value
    if (!question) return false

    // If no parent question, always show
    if (!question.parent_question_id) return true

    // Find parent question
    const parentQuestion = questions.value.find(q => q.id === question.parent_question_id)
    if (!parentQuestion) return true

    // Check if parent answer matches the required value
    const parentAnswer = responses.value[parentQuestion.id]
    return parentAnswer === question.parent_answer_value
})

// Helper to get option value (handles both string and object formats)
function getOptionValue(option: any): string {
    return typeof option === 'string' ? option : option.value
}

// Helper to get option label (handles both string and object formats)
function getOptionLabel(option: any): string {
    return typeof option === 'string' ? option : option.label
}

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
        questionnaireUuid.value = response.data.questionnaire_uuid || null

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
    validationErrors.value = {}
    submitting.value = true
    try {
        await signupStore.completeQuestionnaire(responses.value, questionnaireUuid.value)
        // Emit completed event to parent component
        emit('completed')
    } catch (error: any) {
        console.error('Failed to submit questionnaire:', error)
        // Handle validation errors from the API
        if (error.response?.status === 422 && error.response?.data?.errors) {
            validationErrors.value = error.response.data.errors
        }
    } finally {
        submitting.value = false
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

            <!-- Error Messages -->
            <div v-if="Object.keys(validationErrors).length > 0" class="mb-6 p-4 bg-red-50 border border-red-200 rounded-md">
                <p class="text-sm font-medium text-red-800 mb-2">Please fix the following errors:</p>
                <ul class="space-y-1">
                    <li v-for="(error, field) in validationErrors" :key="field" class="text-sm text-red-700">
                        <strong>{{ field }}:</strong> {{ error }}
                    </li>
                </ul>
            </div>

            <!-- Current Question -->
            <div v-if="currentQuestion && shouldShowCurrentQuestion" class="space-y-4">
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

                <!-- Number Input -->
                <Input
                    v-if="currentQuestion.type === 'number'"
                    v-model.number="responses[currentQuestion.id]"
                    type="number"
                    placeholder="Enter a number"
                />

                <!-- Date Input -->
                <Input
                    v-if="currentQuestion.type === 'date'"
                    v-model="responses[currentQuestion.id]"
                    type="date"
                />

                <!-- Textarea -->
                <textarea
                    v-if="currentQuestion.type === 'textarea'"
                    v-model="responses[currentQuestion.id]"
                    class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-600"
                    rows="4"
                    placeholder="Enter your answer"
                />

                <!-- Select -->
                <select
                    v-if="currentQuestion.type === 'select'"
                    v-model="responses[currentQuestion.id]"
                    class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-600"
                >
                    <option value="">Select an option</option>
                    <option v-for="option in currentQuestion.options" :key="getOptionValue(option)" :value="getOptionValue(option)">
                        {{ getOptionLabel(option) }}
                    </option>
                </select>

                <!-- Checkbox -->
                <div v-if="currentQuestion.type === 'checkbox'" class="space-y-3">
                    <div v-for="option in currentQuestion.options" :key="getOptionValue(option)" class="flex items-center gap-2">
                        <Checkbox
                            :id="`option-${getOptionValue(option)}`"
                            :checked="responses[currentQuestion.id]?.includes(getOptionValue(option)) ?? false"
                            @update:checked="(checked: boolean) => {
                                if (!responses[currentQuestion.id]) responses[currentQuestion.id] = []
                                const val = getOptionValue(option)
                                if (checked) {
                                    if (!responses[currentQuestion.id].includes(val)) {
                                        responses[currentQuestion.id].push(val)
                                    }
                                } else {
                                    responses[currentQuestion.id] = responses[currentQuestion.id].filter((o: string) => o !== val)
                                }
                            }"
                        />
                        <Label :for="`option-${getOptionValue(option)}`" class="font-normal cursor-pointer">{{ getOptionLabel(option) }}</Label>
                    </div>
                </div>

                <!-- Radio -->
                <div v-if="currentQuestion.type === 'radio'" class="space-y-3">
                    <div v-for="option in currentQuestion.options" :key="getOptionValue(option)" class="flex items-center gap-2">
                        <input
                            :id="`radio-${getOptionValue(option)}`"
                            v-model="responses[currentQuestion.id]"
                            type="radio"
                            :value="getOptionValue(option)"
                            class="w-4 h-4 text-indigo-600"
                        />
                        <Label :for="`radio-${getOptionValue(option)}`" class="font-normal cursor-pointer">{{ getOptionLabel(option) }}</Label>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <div class="flex justify-between gap-4 pt-6">
                <button
                    @click="previousQuestion"
                    :disabled="isFirstQuestion || signupStore.loading || submitting"
                    class="px-4 py-2 border rounded-md hover:bg-gray-50 disabled:opacity-50"
                >
                    Previous
                </button>
                <button
                    @click="nextQuestion"
                    :disabled="signupStore.loading || submitting"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50"
                >
                    <span v-if="submitting && isLastQuestion" class="inline-block animate-spin mr-2">⏳</span>
                    {{ submitting && isLastQuestion ? 'Submitting...' : (isLastQuestion ? 'Submit' : 'Next') }}
                </button>
            </div>
        </div>
    </div>
</template>

