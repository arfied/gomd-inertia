<script setup lang="ts">
import { ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import Layout from '@/layouts/AppLayout.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
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

const handleCreateNote = () => {
    showCreateModal.value = true
}

const handleNoteCreated = () => {
    showCreateModal.value = false
    window.location.reload()
}
</script>

<template>
    <Layout>
        <Head title="Clinical Notes" />

        <div class="space-y-6">
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
                    <CardTitle>Recent Notes</CardTitle>
                    <CardDescription>View and manage clinical notes</CardDescription>
                </CardHeader>
                <CardContent>
                    <div v-if="props.notes.data.length > 0" class="space-y-4">
                        <div v-for="note in props.notes.data" :key="note.id" class="border-b pb-4 last:border-b-0">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="font-semibold">{{ note.note_type }}</p>
                                    <p class="text-sm text-muted-foreground">{{ note.content }}</p>
                                    <p class="text-xs text-muted-foreground mt-2">{{ note.recorded_at }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p v-else class="text-muted-foreground">No clinical notes found. Create your first note to get started.</p>
                </CardContent>
            </Card>
        </div>

        <!-- Create Note Modal would go here -->
    </Layout>
</template>
