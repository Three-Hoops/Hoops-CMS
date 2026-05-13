<script setup lang="ts">
import { ref } from "vue";
import { Link, router } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Button } from "@/components/ui/button";
import StatusBadge from "@/components/Admin/StatusBadge.vue";
import Pagination from "@/components/Admin/Pagination.vue";
import ConfirmModal from "@/components/Admin/ConfirmModal.vue";
import type { Page, Paginated } from "@/types/models";

defineProps<{
    pages: Paginated<Page>;
}>();

const deletingId = ref<number | null>(null);

function confirmDelete(id: number) {
    deletingId.value = id;
}

function doDelete() {
    if (deletingId.value === null) return;
    router.delete(route("admin.pages.destroy", deletingId.value), {
        onFinish: () => {
            deletingId.value = null;
        },
    });
}
</script>

<template>
  <AdminLayout>
    <template #title>
      Pages
    </template>

    <div class="space-y-4">
      <div class="flex justify-end">
        <Button as-child>
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
                No pages yet.
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
                {{ page.parent ? page.parent.title : "—" }}
              </td>
              <td class="px-4 py-3">
                <StatusBadge :status="page.status" />
              </td>
              <td class="px-4 py-3 text-muted-foreground">
                {{
                  page.published_at
                    ? new Date(
                      page.published_at
                    ).toLocaleDateString()
                    : "—"
                }}
              </td>
              <td class="px-4 py-3">
                <div class="flex items-center gap-2">
                  <Button
                    variant="outline"
                    size="sm"
                    as-child
                  >
                    <Link
                      :href="
                        route(
                          'admin.pages.edit',
                          page.id
                        )
                      "
                    >
                      Edit
                    </Link>
                  </Button>
                  <Button
                    variant="destructive"
                    size="sm"
                    @click="confirmDelete(page.id)"
                  >
                    Delete
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
      :open="deletingId !== null"
      title="Delete page?"
      description="This action cannot be undone."
      @confirm="doDelete"
      @cancel="deletingId = null"
    />
  </AdminLayout>
</template>
