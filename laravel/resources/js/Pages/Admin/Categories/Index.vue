<script setup lang="ts">
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Button } from '@/components/ui/button'
import Pagination from '@/components/Admin/Pagination.vue'
import ConfirmModal from '@/components/Admin/ConfirmModal.vue'
import { useAuthStore } from '@/stores/useAuthStore'
import type { Category, Paginated } from '@/types/models'

const authStore = useAuthStore()

defineProps<{
    categories: Paginated<Category>
    trash: boolean
}>()

const confirmingId = ref<number | null>(null)
const confirmingForceDelete = ref(false)

function confirmDelete(id: number) {
    confirmingId.value = id
    confirmingForceDelete.value = false
}

function confirmForceDelete(id: number) {
    confirmingId.value = id
    confirmingForceDelete.value = true
}

function doDelete() {
    if (confirmingId.value === null) return
    router.delete(route('admin.categories.destroy', confirmingId.value), {
        onFinish: () => { confirmingId.value = null },
    })
}

function doForceDelete() {
    if (confirmingId.value === null) return
    router.delete(route('admin.categories.forceDelete', confirmingId.value), {
        onFinish: () => { confirmingId.value = null },
    })
}

function restore(id: number) {
    router.post(route('admin.categories.restore', id))
}

function switchView(toTrash: boolean) {
    router.get(route('admin.categories.index'), toTrash ? { trash: 1 } : {}, { preserveState: false })
}
</script>

<template>
  <AdminLayout>
    <template #title>
      Categories
    </template>

    <div class="space-y-4">
      <div class="flex items-center justify-between">
        <div class="flex rounded-md border text-sm">
          <button
            class="px-4 py-1.5 transition-colors"
            :class="!trash ? 'bg-primary text-primary-foreground' : 'hover:bg-muted'"
            @click="switchView(false)"
          >
            All
          </button>
          <button
            class="px-4 py-1.5 transition-colors"
            :class="trash ? 'bg-primary text-primary-foreground' : 'hover:bg-muted'"
            @click="switchView(true)"
          >
            Trash
          </button>
        </div>

        <Button
          v-if="!trash && !authStore.hasRole(['viewer'])"
          as-child
        >
          <Link :href="route('admin.categories.create')">
            New Category
          </Link>
        </Button>
      </div>

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
                Parent
              </th>
              <th class="px-4 py-3 font-medium">
                Actions
              </th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="categories.data.length === 0">
              <td
                colspan="4"
                class="px-4 py-8 text-center text-muted-foreground"
              >
                {{ trash ? 'Trash is empty.' : 'No categories yet.' }}
              </td>
            </tr>
            <tr
              v-for="category in categories.data"
              :key="category.id"
              class="border-b last:border-0 hover:bg-muted/25"
            >
              <td class="px-4 py-3 font-medium">
                {{ category.name }}
              </td>
              <td class="px-4 py-3 text-muted-foreground">
                {{ category.slug }}
              </td>
              <td class="px-4 py-3 text-muted-foreground">
                {{ category.parent?.name ?? '—' }}
              </td>
              <td class="px-4 py-3">
                <div
                  v-if="!trash && !authStore.hasRole(['viewer'])"
                  class="flex items-center gap-2"
                >
                  <Button
                    variant="outline"
                    size="sm"
                    as-child
                  >
                    <Link :href="route('admin.categories.edit', category.id)">
                      Edit
                    </Link>
                  </Button>
                  <Button
                    variant="destructive"
                    size="sm"
                    @click="confirmDelete(category.id)"
                  >
                    Delete
                  </Button>
                </div>
                <div
                  v-else-if="trash && !authStore.hasRole(['viewer'])"
                  class="flex items-center gap-2"
                >
                  <Button
                    variant="outline"
                    size="sm"
                    @click="restore(category.id)"
                  >
                    Restore
                  </Button>
                  <Button
                    variant="destructive"
                    size="sm"
                    @click="confirmForceDelete(category.id)"
                  >
                    Delete permanently
                  </Button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <Pagination :links="categories.links" />
    </div>

    <ConfirmModal
      :open="confirmingId !== null && !confirmingForceDelete"
      title="Delete category?"
      description="Posts in this category will have their category cleared."
      @confirm="doDelete"
      @cancel="confirmingId = null"
    />

    <ConfirmModal
      :open="confirmingId !== null && confirmingForceDelete"
      title="Permanently delete category?"
      description="This cannot be undone. The category will be gone forever."
      @confirm="doForceDelete"
      @cancel="confirmingId = null"
    />
  </AdminLayout>
</template>
