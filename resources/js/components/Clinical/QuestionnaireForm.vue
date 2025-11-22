<script setup lang="ts">
import { ref, computed } from 'vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Checkbox } from '@/components/ui/checkbox'

interface Question {
    id: string
    text: string
    type: 'text' | 'checkbox' | 'radio' | 'textarea'
    options?: string[]
    required?: boolean
}

interface Props {
    title: string
    description?: string
    questions: Question[]
    loading?: boolean
}

const props = withDefaults(defineProps<Props>(), {
    loading: false,
})

const emit = defineEmits<{
    submit: [responses: Record<string, any>]
    cancel: []
}>()

const responses = ref<Record<string, any>>({})
const currentStep = ref(0)

const currentQuestion = computed(() => props.questions[currentStep.value])
const isLastQuestion = computed(() => currentStep.value === props.questions.length - 1)
const isFirstQuestion = computed(() => currentStep.value === 0)

const handleNext = () => {
    if (!isLastQuestion.value) {
        currentStep.value++
    } else {
        emit('submit', responses.value)
    }
}

const handlePrevious = () => {
    if (!isFirstQuestion.value) {
        currentStep.value--
    }
}

const handleCancel = () => {
    emit('cancel')
}
</script>

<template>
    <Card class="w-full max-w-2xl">
        <CardHeader>
            <CardTitle>{{ title }}</CardTitle>
            <CardDescription v-if="description">{{ description }}</CardDescription>
            <div class="mt-4 text-sm text-muted-foreground">
                Question {{ currentStep + 1 }} of {{ questions.length }}
            </div>
        </CardHeader>

        <CardContent class="space-y-6">
            <!-- Progress Bar -->
            <div class="w-full bg-secondary rounded-full h-2">
                <div
                    class="bg-primary h-2 rounded-full transition-all duration-300"
                    :style="{ width: `${((currentStep + 1) / questions.length) * 100}%` }"
                />
            </div>

            <!-- Current Question -->
            <div v-if="currentQuestion" class="space-y-4">
                <Label class="text-base font-semibold">
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
                    class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                    rows="4"
                    placeholder="Enter your answer"
                />

                <!-- Checkbox -->
                <div v-if="currentQuestion.type === 'checkbox'" class="space-y-2">
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
                <div v-if="currentQuestion.type === 'radio'" class="space-y-2">
                    <div v-for="option in currentQuestion.options" :key="option" class="flex items-center gap-2">
                        <input
                            :id="`radio-${option}`"
                            v-model="responses[currentQuestion.id]"
                            type="radio"
                            :value="option"
                            class="w-4 h-4"
                        />
                        <Label :for="`radio-${option}`" class="font-normal cursor-pointer">{{ option }}</Label>
                    </div>
                </div>
            </div>

            <!-- Navigation Buttons -->
            <div class="flex justify-between gap-4 pt-6">
                <Button
                    variant="outline"
                    @click="handlePrevious"
                    :disabled="isFirstQuestion || loading"
                >
                    Previous
                </Button>

                <div class="flex gap-2">
                    <Button
                        variant="outline"
                        @click="handleCancel"
                        :disabled="loading"
                    >
                        Cancel
                    </Button>
                    <Button
                        @click="handleNext"
                        :disabled="loading"
                    >
                        {{ isLastQuestion ? 'Submit' : 'Next' }}
                    </Button>
                </div>
            </div>
        </CardContent>
    </Card>
</template>

