<script setup lang="ts">
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import StatusBadge from '@/components/Admin/StatusBadge.vue'
import Pagination from '@/components/Admin/Pagination.vue'
import ConfirmModal from '@/components/Admin/ConfirmModal.vue'
import type { Post, Paginated } from '@/types/models'

defineProps<{
    posts: Paginated<Post>
}>()

const deletingId = ref<number | null>(null)

function confirmDelete(id: number) {
    deletingId.value = id
}

function doDelete() {
    if (deletingId.value === null) return
    router.delete(route('admin.posts.destroy', deletingId.value), {
        onFinish: () => { deletingId.value = null },
    })
}
</script>

<template>
  <AdminLayout>
    <template #title>
      Posts
    </template>

    <div class="space-y-4">
      <div class="flex justify-end">
        <Button as-child>
          <Link :href="route('admin.posts.create')">
            New Post
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
                Category
              </th>
              <th class="px-4 py-3 font-medium">
                Tags
              </th>
              <th class="px-4 py-3 font-medium">
                Status
              </th>
              <th class="px-4 py-3 font-medium">
                Author
              </th>
              <th class="px-4 py-3 font-medium">
                Actions
              </th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="posts.data.length === 0">
              <td
                colspan="6"
                class="px-4 py-8 text-center text-muted-foreground"
              >
                No posts yet.
              </td>
            </tr>
            <tr
              v-for="post in posts.data"
              :key="post.id"
              class="border-b last:border-0 hover:bg-muted/25"
            >
              <td class="px-4 py-3 font-medium">
                {{ post.title }}
              </td>
              <td class="px-4 py-3 text-muted-foreground">
                {{ post.category?.name ?? '—' }}
              </td>
              <td class="px-4 py-3">
                <div class="flex flex-wrap gap-1">
                  <Badge
                    v-for="tag in post.tags"
                    :key="tag.id"
                    variant="outline"
                    class="text-xs"
                  >
                    {{ tag.name }}
                  </Badge>
                  <span
                    v-if="post.tags.length === 0"
                    class="text-muted-foreground"
                  >—</span>
                </div>
              </td>
              <td class="px-4 py-3">
                <StatusBadge :status="post.status" />
              </td>
              <td class="px-4 py-3 text-muted-foreground">
                {{ post.author.name }}
              </td>
              <td class="px-4 py-3">
                <div class="flex items-center gap-2">
                  <Button
                    variant="outline"
                    size="sm"
                    as-child
                  >
                    <Link :href="route('admin.posts.edit', post.id)">
                      Edit
                    </Link>
                  </Button>
                  <Button
                    variant="destructive"
                    size="sm"
                    @click="confirmDelete(post.id)"
                  >
                    Delete
                  </Button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <Pagination :links="posts.links" />
    </div>

    <ConfirmModal
      :open="deletingId !== null"
      title="Delete post?"
      description="This action cannot be undone."
      @confirm="doDelete"
      @cancel="deletingId = null"
    />
  </AdminLayout>
</template>
