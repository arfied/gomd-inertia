<script setup lang="ts">
import { ref, computed } from 'vue'
import { QuestionnaireForm } from '@/components/Clinical'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Spinner } from '@/components/ui/spinner'
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
const questionnaires = ref(props.questionnaires)
const meta = ref(props.questionnaires)
const loadingList = ref(false)
const listError = ref<string | null>(null)

const search = ref('')
const perPage = ref(15)
const filterByStatus = ref('')

const hasNextPage = computed(() => !!meta.value?.last_page && meta.value.current_page < meta.value.last_page)
const hasPrevPage = computed(() => meta.value?.current_page > 1)

function buildQuery(base: string): string {
    const params = new URLSearchParams()
    if (search.value.trim() !== '') params.set('search', search.value.trim())
    params.set('per_page', String(perPage.value))
    if (filterByStatus.value) params.set('status', filterByStatus.value)
    const q = params.toString()
    return q ? `${base}?${q}` : base
}

async function loadQuestionnaires(url?: string) {
    loadingList.value = true
    listError.value = null

    try {
        const response = await fetch(url ?? buildQuery('/clinical/questionnaires'), {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        })

        if (!response.ok) {
            listError.value = `Failed to load questionnaires (${response.status})`
            questionnaires.value = { data: [], current_page: 1, last_page: 1, per_page: 15, total: 0 }
            meta.value = { data: [], current_page: 1, last_page: 1, per_page: 15, total: 0 }
            return
        }

        const data = (await response.json()) as any
        questionnaires.value = data.questionnaires
        meta.value = data.questionnaires
    } catch (error) {
        console.error('Error loading questionnaires:', error)
        listError.value = 'A network error occurred while loading questionnaires.'
    } finally {
        loadingList.value = false
    }
}

function applyFilters() {
    void loadQuestionnaires()
}

function goToNextPage() {
    if (meta.value?.last_page && meta.value.current_page < meta.value.last_page) {
        void loadQuestionnaires(`${buildQuery('/clinical/questionnaires')}&page=${meta.value.current_page + 1}`)
    }
}

function goToPrevPage() {
    if (meta.value?.current_page > 1) {
        void loadQuestionnaires(`${buildQuery('/clinical/questionnaires')}&page=${meta.value.current_page - 1}`)
    }
}

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
        <div class="space-y-6 p-4">
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
            <div v-else class="space-y-4">
                <Card>
                    <CardHeader>
                        <CardTitle>Questionnaires</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                            <div class="flex flex-1 flex-col gap-2">
                                <div class="flex gap-2">
                                    <Input v-model="search" type="search" placeholder="Search by title" class="max-w-xs" />
                                    <Input v-model.number="perPage" type="number" min="1" max="100" class="w-24" />
                                    <Button type="button" size="sm" @click="applyFilters">Apply</Button>
                                </div>
                                <div class="flex flex-wrap items-center gap-2 text-xs text-muted-foreground">
                                    <label class="inline-flex items-center gap-1">
                                        <select v-model="filterByStatus" class="h-6 rounded border border-input bg-background px-2 text-xs">
                                            <option value="">All statuses</option>
                                            <option value="active">Active</option>
                                            <option value="inactive">Inactive</option>
                                        </select>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="border rounded-md">
                            <div v-if="loadingList" class="flex items-center gap-2 p-4 text-sm text-muted-foreground">
                                <Spinner class="h-4 w-4" />
                                <span>Loading questionnaires…</span>
                            </div>
                            <p v-else-if="listError" class="p-4 text-sm text-destructive">{{ listError }}</p>
                            <p v-else-if="!questionnaires.data.length" class="p-4 text-sm text-muted-foreground">No questionnaires found.</p>
                            <div v-else class="grid gap-4 p-4">
                                <div v-for="questionnaire in questionnaires.data" :key="questionnaire.id">
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
                            </div>
                        </div>

                        <div class="flex items-center justify-between text-xs text-muted-foreground">
                            <div>
                                <span v-if="meta">Page {{ meta.current_page }}</span>
                            </div>
                            <div class="flex gap-2">
                                <Button type="button" size="sm" variant="outline" :disabled="!hasPrevPage" @click="goToPrevPage">Previous</Button>
                                <Button type="button" size="sm" variant="outline" :disabled="!hasNextPage" @click="goToNextPage">Next</Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>

