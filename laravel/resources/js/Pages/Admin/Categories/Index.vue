<script setup lang="ts">
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Button } from '@/components/ui/button'
import Pagination from '@/components/Admin/Pagination.vue'
import ConfirmModal from '@/components/Admin/ConfirmModal.vue'
import type { Category, Paginated } from '@/types/models'

defineProps<{
    categories: Paginated<Category>
}>()

const deletingId = ref<number | null>(null)

function doDelete() {
    if (deletingId.value === null) return
    router.delete(route('admin.categories.destroy', deletingId.value), {
        onFinish: () => { deletingId.value = null },
    })
}
</script>

<template>
  <AdminLayout>
    <template #title>
      Categories
    </template>

    <div class="space-y-4">
      <div class="flex justify-end">
        <Button as-child>
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
                No categories yet.
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
                <div class="flex items-center gap-2">
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
                    @click="deletingId = category.id"
                  >
                    Delete
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
      :open="deletingId !== null"
      title="Delete category?"
      description="Posts in this category will have their category cleared."
      @confirm="doDelete"
      @cancel="deletingId = null"
    />
  </AdminLayout>
</template>
