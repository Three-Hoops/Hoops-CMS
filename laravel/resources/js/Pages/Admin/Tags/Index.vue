<script setup lang="ts">
import { computed, ref } from 'vue'
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
import { useBulkSelection } from '@/composables/useBulkSelection'
import type { Tag, Paginated } from '@/types/models'

const authStore = useAuthStore()

const props = defineProps<{
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

// Bulk selection
const { selectedIds, isAllSelected, toggleAll, toggle, clearSelection } = useBulkSelection()
const pageIds = computed(() => props.tags.data.map(t => t.id))
const allSelected = computed(() => isAllSelected(pageIds.value))
const confirmingBulk = ref(false)

function submitBulkDelete() {
    router.post(route('admin.tags.bulkAction'), {
        ids: selectedIds.value,
        action: 'delete',
    }, {
        onSuccess: () => clearSelection(),
        onFinish: () => { confirmingBulk.value = false },
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

      <div
        v-if="selectedIds.length > 0 && !authStore.hasRole(['viewer'])"
        class="flex items-center gap-3 rounded-md border bg-background px-4 py-2 shadow-sm"
      >
        <span class="text-sm font-medium">{{ selectedIds.length }} selected</span>
        <Button
          size="sm"
          variant="destructive"
          @click="confirmingBulk = true"
        >
          Delete
        </Button>
        <button
          class="ml-auto text-sm text-muted-foreground hover:text-foreground"
          @click="clearSelection"
        >
          Clear
        </button>
      </div>

      <!-- Tags table -->
      <div class="rounded-md border">
        <table class="w-full text-sm">
          <thead class="border-b bg-muted/50">
            <tr class="text-left text-xs text-muted-foreground">
              <th
                v-if="!authStore.hasRole(['viewer'])"
                class="w-10 px-4 py-3"
              >
                <input
                  type="checkbox"
                  :checked="allSelected"
                  :indeterminate="selectedIds.length > 0 && !allSelected"
                  @change="toggleAll(pageIds)"
                >
              </th>
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
                :colspan="authStore.hasRole(['viewer']) ? 3 : 4"
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
                  v-if="!authStore.hasRole(['viewer'])"
                  class="w-10 px-4 py-3"
                />
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
                <td
                  v-if="!authStore.hasRole(['viewer'])"
                  class="w-10 px-4 py-3"
                >
                  <input
                    type="checkbox"
                    :checked="selectedIds.includes(tag.id)"
                    @change="toggle(tag.id)"
                  >
                </td>
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

    <ConfirmModal
      :open="confirmingBulk"
      :title="`Delete ${selectedIds.length} tag(s)?`"
      description="Posts using these tags will have them removed."
      @confirm="submitBulkDelete"
      @cancel="confirmingBulk = false"
    />
  </AdminLayout>
</template>
