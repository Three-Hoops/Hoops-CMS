<script setup lang="ts">
import { ref } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import Pagination from '@/components/Admin/Pagination.vue'
import SlugInput from '@/components/Admin/SlugInput.vue'
import ConfirmModal from '@/components/Admin/ConfirmModal.vue'
import { useAuthStore } from '@/stores/useAuthStore'
import type { Tag, Paginated } from '@/types/models'

const authStore = useAuthStore()

defineProps<{
    tags: Paginated<Tag>
}>()

// Add form
const addForm = useForm({ name: '', slug: '' })

function submitAdd() {
    addForm.post(route('admin.tags.store'), {
        onSuccess: () => addForm.reset(),
    })
}

// Edit state
const editingId = ref<number | null>(null)
const editForm = useForm({ name: '', slug: '' })

function startEdit(tag: Tag) {
    editingId.value = tag.id
    editForm.name = tag.name
    editForm.slug = tag.slug
}

function submitEdit() {
    if (editingId.value === null) return
    editForm.put(route('admin.tags.update', editingId.value), {
        onSuccess: () => { editingId.value = null },
    })
}

// Delete state
const deletingId = ref<number | null>(null)

function doDelete() {
    if (deletingId.value === null) return
    router.delete(route('admin.tags.destroy', deletingId.value), {
        onFinish: () => { deletingId.value = null },
    })
}
</script>

<template>
  <AdminLayout>
    <template #title>
      Tags
    </template>

    <div class="space-y-6">
      <!-- Add tag form -->
      <div
        v-if="!authStore.hasRole(['viewer'])"
        class="rounded-md border p-4"
      >
        <h2 class="mb-4 text-sm font-semibold">
          Add Tag
        </h2>
        <form
          class="flex items-end gap-3"
          @submit.prevent="submitAdd"
        >
          <div class="flex-1 space-y-1">
            <Label for="add-name">Name</Label>
            <Input
              id="add-name"
              v-model="addForm.name"
              :class="{ 'border-destructive': addForm.errors.name }"
            />
            <p
              v-if="addForm.errors.name"
              class="text-xs text-destructive"
            >
              {{ addForm.errors.name }}
            </p>
          </div>
          <div class="flex-1 space-y-1">
            <Label>Slug</Label>
            <SlugInput
              v-model="addForm.slug"
              :source="addForm.name"
            />
            <p
              v-if="addForm.errors.slug"
              class="text-xs text-destructive"
            >
              {{ addForm.errors.slug }}
            </p>
          </div>
          <Button
            type="submit"
            :disabled="addForm.processing"
          >
            Add
          </Button>
        </form>
      </div>

      <!-- Tags table -->
      <div class="rounded-md border">
        <table class="w-full text-sm">
          <thead class="border-b bg-muted/50">
            <tr class="text-left text-xs text-muted-foreground">
              <th class="px-4 py-3 font-medium">
                Name
              </th>
              <th class="px-4 py-3 font-medium">
                Slug
              </th>
              <th class="px-4 py-3 font-medium">
                Actions
              </th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="tags.data.length === 0">
              <td
                colspan="3"
                class="px-4 py-8 text-center text-muted-foreground"
              >
                No tags yet.
              </td>
            </tr>
            <tr
              v-for="tag in tags.data"
              :key="tag.id"
              class="border-b last:border-0"
            >
              <template v-if="editingId === tag.id">
                <td
                  colspan="2"
                  class="px-4 py-2"
                >
                  <form
                    class="flex items-center gap-2"
                    @submit.prevent="submitEdit"
                  >
                    <Input
                      v-model="editForm.name"
                      class="h-8 flex-1"
                    />
                    <SlugInput
                      v-model="editForm.slug"
                      class="h-8 flex-1"
                    />
                    <Button
                      type="submit"
                      size="sm"
                      :disabled="editForm.processing"
                    >
                      Save
                    </Button>
                    <Button
                      type="button"
                      variant="ghost"
                      size="sm"
                      @click="editingId = null"
                    >
                      Cancel
                    </Button>
                  </form>
                </td>
                <td class="px-4 py-2" />
              </template>
              <template v-else>
                <td class="px-4 py-3 font-medium">
                  {{ tag.name }}
                </td>
                <td class="px-4 py-3 text-muted-foreground">
                  {{ tag.slug }}
                </td>
                <td class="px-4 py-3">
                  <div
                    v-if="!authStore.hasRole(['viewer'])"
                    class="flex items-center gap-2"
                  >
                    <Button
                      variant="outline"
                      size="sm"
                      @click="startEdit(tag)"
                    >
                      Edit
                    </Button>
                    <Button
                      variant="destructive"
                      size="sm"
                      @click="deletingId = tag.id"
                    >
                      Delete
                    </Button>
                  </div>
                </td>
              </template>
            </tr>
          </tbody>
        </table>
      </div>

      <Pagination :links="tags.links" />
    </div>

    <ConfirmModal
      :open="deletingId !== null"
      title="Delete tag?"
      description="Posts using this tag will have it removed."
      @confirm="doDelete"
      @cancel="deletingId = null"
    />
  </AdminLayout>
</template>
