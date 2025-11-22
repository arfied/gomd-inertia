<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import Layout from '@/layouts/AppLayout.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Spinner } from '@/components/ui/spinner'
import { ClinicalNoteEditor } from '@/components/Clinical'

interface ClinicalNote {
    id: string
    patient_id: string
    doctor_id: string
    note_type: string
    content: string
    recorded_at: string
}

interface Props {
    notes: {
        data: ClinicalNote[]
        current_page: number
        last_page: number
        per_page: number
        total: number
    }
    selectedNote?: ClinicalNote | null
}

const props = withDefaults(defineProps<Props>(), {
    selectedNote: null,
})

const showCreateModal = ref(false)
const notes = ref(props.notes)
const meta = ref(props.notes)
const loadingList = ref(false)
const listError = ref<string | null>(null)

const search = ref('')
const perPage = ref(15)
const filterByType = ref('')

const hasNextPage = computed(() => !!meta.value?.last_page && meta.value.current_page < meta.value.last_page)
const hasPrevPage = computed(() => meta.value?.current_page > 1)

function buildQuery(base: string): string {
    const params = new URLSearchParams()
    if (search.value.trim() !== '') params.set('search', search.value.trim())
    params.set('per_page', String(perPage.value))
    if (filterByType.value) params.set('note_type', filterByType.value)
    const q = params.toString()
    return q ? `${base}?${q}` : base
}

async function loadNotes(url?: string) {
    loadingList.value = true
    listError.value = null

    try {
        const response = await fetch(url ?? buildQuery('/clinical/notes'), {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        })

        if (!response.ok) {
            listError.value = `Failed to load notes (${response.status})`
            notes.value = { data: [], current_page: 1, last_page: 1, per_page: 15, total: 0 }
            meta.value = { data: [], current_page: 1, last_page: 1, per_page: 15, total: 0 }
            return
        }

        const data = (await response.json()) as any
        notes.value = data.notes
        meta.value = data.notes
    } catch {
        listError.value = 'A network error occurred while loading notes.'
    } finally {
        loadingList.value = false
    }
}

function applyFilters() {
    void loadNotes()
}

function goToNextPage() {
    if (meta.value?.last_page && meta.value.current_page < meta.value.last_page) {
        void loadNotes(`${buildQuery('/clinical/notes')}&page=${meta.value.current_page + 1}`)
    }
}

function goToPrevPage() {
    if (meta.value?.current_page > 1) {
        void loadNotes(`${buildQuery('/clinical/notes')}&page=${meta.value.current_page - 1}`)
    }
}

const handleCreateNote = () => {
    showCreateModal.value = true
}

const handleNoteCreated = () => {
    showCreateModal.value = false
    void loadNotes()
}
</script>

<template>
    <Layout>
        <Head title="Clinical Notes" />

        <div class="space-y-6 p-4">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold">Clinical Notes</h1>
                    <p class="text-muted-foreground">Manage clinical notes and documentation</p>
                </div>
                <Button @click="handleCreateNote">
                    Create Note
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Clinical Notes</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                        <div class="flex flex-1 flex-col gap-2">
                            <div class="flex gap-2">
                                <Input v-model="search" type="search" placeholder="Search by content" class="max-w-xs" />
                                <Input v-model.number="perPage" type="number" min="1" max="100" class="w-24" />
                                <Button type="button" size="sm" @click="applyFilters">Apply</Button>
                            </div>
                            <div class="flex flex-wrap items-center gap-2 text-xs text-muted-foreground">
                                <label class="inline-flex items-center gap-1">
                                    <select v-model="filterByType" class="h-6 rounded border border-input bg-background px-2 text-xs">
                                        <option value="">All types</option>
                                        <option value="assessment">Assessment</option>
                                        <option value="progress">Progress</option>
                                        <option value="consultation">Consultation</option>
                                    </select>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="border rounded-md">
                        <div v-if="loadingList" class="flex items-center gap-2 p-4 text-sm text-muted-foreground">
                            <Spinner class="h-4 w-4" />
                            <span>Loading notes…</span>
                        </div>
                        <p v-else-if="listError" class="p-4 text-sm text-destructive">{{ listError }}</p>
                        <p v-else-if="!notes.data.length" class="p-4 text-sm text-muted-foreground">No clinical notes found.</p>
                        <div v-else class="space-y-4 p-4">
                            <div v-for="note in notes.data" :key="note.id" class="border-b pb-4 last:border-b-0">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="font-semibold">{{ note.note_type }}</p>
                                        <p class="text-sm text-muted-foreground">{{ note.content }}</p>
                                        <p class="text-xs text-muted-foreground mt-2">{{ note.recorded_at }}</p>
                                    </div>
                                </div>
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

        <!-- Create Note Modal would go here -->
    </Layout>
</template>
