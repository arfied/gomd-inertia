<script setup lang="ts">
import { ref } from 'vue'
import { QuestionnaireForm } from '@/components/Clinical'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import AppLayout from '@/layouts/AppLayout.vue'
import type { BreadcrumbItemType } from '@/types'

interface Questionnaire {
    id: string
    title: string
    description: string
    questions: any[]
    status: string
    createdAt: string
}

interface Props {
    questionnaires: {
        data: Questionnaire[]
        current_page: number
        last_page: number
        per_page: number
        total: number
    }
    selectedQuestionnaire?: Questionnaire | null
}

const props = withDefaults(defineProps<Props>(), {
    selectedQuestionnaire: null,
})

const showForm = ref(false)
const selectedQuestionnaire = ref<Questionnaire | null>(props.selectedQuestionnaire)

const handleSubmit = async (responses: Record<string, any>) => {
    if (!selectedQuestionnaire.value) return

    try {
        const form = new FormData()
        form.append('responses', JSON.stringify(responses))
        form.append('_method', 'POST')

        const response = await fetch(`/clinical/questionnaires/${selectedQuestionnaire.value.id}/responses`, {
            method: 'POST',
            body: form,
            headers: {
                'Accept': 'application/json',
            }
        })

        if (response.ok) {
            showForm.value = false
            selectedQuestionnaire.value = null
            window.location.reload()
        }
    } catch (error) {
        console.error('Failed to submit questionnaire:', error)
    }
}

const handleCancel = () => {
    showForm.value = false
    selectedQuestionnaire.value = null
}

const startQuestionnaire = (questionnaire: Questionnaire) => {
    selectedQuestionnaire.value = questionnaire
    showForm.value = true
}

const breadcrumbs: BreadcrumbItemType[] = [
  { title: 'Clinical', href: '/clinical' },
  { title: 'Questionnaires', href: '/clinical/questionnaires' }
]
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <div>
                <h1 class="text-3xl font-bold">Questionnaires</h1>
                <p class="text-muted-foreground">Complete patient health questionnaires</p>
            </div>

        <!-- Form View -->
        <div v-if="showForm && selectedQuestionnaire" class="flex justify-center">
            <QuestionnaireForm
                :title="selectedQuestionnaire.title"
                :description="selectedQuestionnaire.description"
                :questions="selectedQuestionnaire.questions"
                @submit="handleSubmit"
                @cancel="handleCancel"
            />
        </div>

        <!-- List View -->
        <div v-else class="grid gap-4">
            <div v-for="questionnaire in props.questionnaires.data" :key="questionnaire.id">
                <Card>
                    <CardHeader>
                        <CardTitle>{{ questionnaire.title }}</CardTitle>
                        <CardDescription>{{ questionnaire.description }}</CardDescription>
                    </CardHeader>
                    <CardContent class="flex justify-between items-center">
                        <div class="text-sm text-muted-foreground">
                            {{ questionnaire.questions.length }} questions
                        </div>
                        <Button @click="startQuestionnaire(questionnaire)">
                            Start Questionnaire
                        </Button>
                    </CardContent>
                </Card>
            </div>

            <div v-if="props.questionnaires.data.length === 0" class="text-center py-12 text-muted-foreground">
                <p>No questionnaires available</p>
            </div>
        </div>
        </div>
    </AppLayout>
</template>

