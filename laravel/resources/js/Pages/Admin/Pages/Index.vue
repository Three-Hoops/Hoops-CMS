<script setup lang="ts">
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Button } from '@/components/ui/button'
import StatusBadge from '@/components/Admin/StatusBadge.vue'
import Pagination from '@/components/Admin/Pagination.vue'
import ConfirmModal from '@/components/Admin/ConfirmModal.vue'
import { useAuthStore } from '@/stores/useAuthStore'
import type { Page, Paginated } from '@/types/models'

const authStore = useAuthStore()

defineProps<{
    pages: Paginated<Page>
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
    router.delete(route('admin.pages.destroy', confirmingId.value), {
        onFinish: () => { confirmingId.value = null },
    })
}

function doForceDelete() {
    if (confirmingId.value === null) return
    router.delete(route('admin.pages.forceDelete', confirmingId.value), {
        onFinish: () => { confirmingId.value = null },
    })
}

function restore(id: number) {
    router.post(route('admin.pages.restore', id))
}

function duplicate(id: number) {
    router.post(route('admin.pages.duplicate', id))
}

function switchView(toTrash: boolean) {
    router.get(route('admin.pages.index'), toTrash ? { trash: 1 } : {}, { preserveState: false })
}
</script>

<template>
  <AdminLayout>
    <template #title>
      Pages
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
          <Link :href="route('admin.pages.create')">
            New Page
          </Link>
        </Button>
      </div>

      <div class="rounded-md border">
        <table class="w-full text-sm">
          <thead class="border-b bg-muted/50">
            <tr class="text-left text-xs text-muted-foreground">
              <th class="px-4 py-3 font-medium">
                Title
              </th>
              <th class="px-4 py-3 font-medium">
                Slug
              </th>
              <th class="px-4 py-3 font-medium">
                Parent
              </th>
              <th class="px-4 py-3 font-medium">
                Status
              </th>
              <th class="px-4 py-3 font-medium">
                Published
              </th>
              <th class="px-4 py-3 font-medium">
                Actions
              </th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="pages.data.length === 0">
              <td
                colspan="6"
                class="px-4 py-8 text-center text-muted-foreground"
              >
                {{ trash ? 'Trash is empty.' : 'No pages yet.' }}
              </td>
            </tr>
            <tr
              v-for="page in pages.data"
              :key="page.id"
              class="border-b last:border-0 hover:bg-muted/25"
            >
              <td class="px-4 py-3 font-medium">
                {{ page.title }}
              </td>
              <td class="px-4 py-3 text-muted-foreground">
                {{ page.slug }}
              </td>
              <td class="px-4 py-3 text-muted-foreground">
                {{ page.parent ? page.parent.title : '—' }}
              </td>
              <td class="px-4 py-3">
                <StatusBadge :status="page.status" />
              </td>
              <td class="px-4 py-3 text-muted-foreground">
                {{ page.published_at ? new Date(page.published_at).toLocaleDateString() : '—' }}
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
                    <Link :href="route('admin.pages.edit', page.id)">
                      Edit
                    </Link>
                  </Button>
                  <Button
                    variant="outline"
                    size="sm"
                    @click="duplicate(page.id)"
                  >
                    Duplicate
                  </Button>
                  <Button
                    variant="destructive"
                    size="sm"
                    @click="confirmDelete(page.id)"
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
                    @click="restore(page.id)"
                  >
                    Restore
                  </Button>
                  <Button
                    variant="destructive"
                    size="sm"
                    @click="confirmForceDelete(page.id)"
                  >
                    Delete permanently
                  </Button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <Pagination :links="pages.links" />
    </div>

    <ConfirmModal
      :open="confirmingId !== null && !confirmingForceDelete"
      title="Delete page?"
      description="This will move the page to trash."
      @confirm="doDelete"
      @cancel="confirmingId = null"
    />

    <ConfirmModal
      :open="confirmingId !== null && confirmingForceDelete"
      title="Permanently delete page?"
      description="This cannot be undone. The page will be gone forever."
      @confirm="doForceDelete"
      @cancel="confirmingId = null"
    />
  </AdminLayout>
</template>
