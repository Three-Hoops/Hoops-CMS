<script setup lang="ts">
import { computed, ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Button } from '@/components/ui/button'
import StatusBadge from '@/components/Admin/StatusBadge.vue'
import Pagination from '@/components/Admin/Pagination.vue'
import ConfirmModal from '@/components/Admin/ConfirmModal.vue'
import { useAuthStore } from '@/stores/useAuthStore'
import { useBulkSelection } from '@/composables/useBulkSelection'
import type { Page, Paginated } from '@/types/models'

const authStore = useAuthStore()

const props = defineProps<{
    pages: Paginated<Page>
    trash: boolean
}>()

// Per-row actions
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

// Bulk selection
const { selectedIds, isAllSelected, toggleAll, toggle, clearSelection } = useBulkSelection()
const pageIds = computed(() => props.pages.data.map(p => p.id))
const allSelected = computed(() => isAllSelected(pageIds.value))

const pendingAction = ref<string | null>(null)
const confirmingBulk = ref(false)

const bulkActionLabel = computed(() => {
    const n = selectedIds.value.length
    const labels: Record<string, string> = {
        publish: `Publish ${n} page(s)?`,
        draft: `Set ${n} page(s) to draft?`,
        delete: `Move ${n} page(s) to trash?`,
        restore: `Restore ${n} page(s) from trash?`,
    }
    return labels[pendingAction.value ?? ''] ?? ''
})

function triggerBulk(action: string) {
    pendingAction.value = action
    confirmingBulk.value = true
}

function submitBulkAction() {
    router.post(route('admin.pages.bulkAction'), {
        ids: selectedIds.value,
        action: pendingAction.value,
    }, {
        only: ['pages'],
        onSuccess: () => clearSelection(),
        onFinish: () => {
            confirmingBulk.value = false
            pendingAction.value = null
        },
    })
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

      <div
        v-if="selectedIds.length > 0 && !authStore.hasRole(['viewer'])"
        class="flex items-center gap-3 rounded-md border bg-background px-4 py-2 shadow-sm"
      >
        <span class="text-sm font-medium">{{ selectedIds.length }} selected</span>
        <div class="flex items-center gap-2">
          <Button
            v-if="!trash"
            size="sm"
            variant="outline"
            @click="triggerBulk('publish')"
          >
            Publish
          </Button>
          <Button
            v-if="!trash"
            size="sm"
            variant="outline"
            @click="triggerBulk('draft')"
          >
            Draft
          </Button>
          <Button
            v-if="!trash"
            size="sm"
            variant="destructive"
            @click="triggerBulk('delete')"
          >
            Delete
          </Button>
          <Button
            v-if="trash"
            size="sm"
            variant="outline"
            @click="triggerBulk('restore')"
          >
            Restore
          </Button>
        </div>
        <button
          class="ml-auto text-sm text-muted-foreground hover:text-foreground"
          @click="clearSelection"
        >
          Clear
        </button>
      </div>

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
                :colspan="authStore.hasRole(['viewer']) ? 6 : 7"
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
              <td
                v-if="!authStore.hasRole(['viewer'])"
                class="w-10 px-4 py-3"
              >
                <input
                  type="checkbox"
                  :checked="selectedIds.includes(page.id)"
                  @change="toggle(page.id)"
                >
              </td>
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

    <ConfirmModal
      :open="confirmingBulk"
      :title="bulkActionLabel"
      description="This action will be applied to all selected items."
      @confirm="submitBulkAction"
      @cancel="confirmingBulk = false; pendingAction = null"
    />
  </AdminLayout>
</template>
