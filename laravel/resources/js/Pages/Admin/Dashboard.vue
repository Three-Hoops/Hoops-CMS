<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import StatusBadge from '@/components/Admin/StatusBadge.vue'
import type { DashboardStats } from '@/types/models'

defineProps<{
    stats: DashboardStats
}>()
</script>

<template>
  <AdminLayout>
    <template #title>
      Dashboard
    </template>

    <div class="space-y-6">
      <!-- Stat cards -->
      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <Card>
          <CardHeader class="pb-2">
            <CardTitle class="text-sm font-medium text-muted-foreground">
              Posts
            </CardTitle>
          </CardHeader>
          <CardContent>
            <p class="text-2xl font-bold">
              {{ stats.posts.total }}
            </p>
            <p class="mt-1 text-xs text-muted-foreground">
              {{ stats.posts.published }} published · {{ stats.posts.draft }} draft
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="pb-2">
            <CardTitle class="text-sm font-medium text-muted-foreground">
              Pages
            </CardTitle>
          </CardHeader>
          <CardContent>
            <p class="text-2xl font-bold">
              {{ stats.pages.total }}
            </p>
            <p class="mt-1 text-xs text-muted-foreground">
              {{ stats.pages.published }} published · {{ stats.pages.draft }} draft
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="pb-2">
            <CardTitle class="text-sm font-medium text-muted-foreground">
              Categories
            </CardTitle>
          </CardHeader>
          <CardContent>
            <p class="text-2xl font-bold">
              {{ stats.categories }}
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="pb-2">
            <CardTitle class="text-sm font-medium text-muted-foreground">
              Tags
            </CardTitle>
          </CardHeader>
          <CardContent>
            <p class="text-2xl font-bold">
              {{ stats.tags }}
            </p>
          </CardContent>
        </Card>
      </div>

      <!-- Recent posts -->
      <Card>
        <CardHeader>
          <CardTitle class="text-base">
            Recent Posts
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div
            v-if="stats.recent_posts.length === 0"
            class="py-4 text-center text-sm text-muted-foreground"
          >
            No posts yet.
          </div>
          <table
            v-else
            class="w-full text-sm"
          >
            <thead>
              <tr class="border-b text-left text-xs text-muted-foreground">
                <th class="pb-2 font-medium">
                  Title
                </th>
                <th class="pb-2 font-medium">
                  Status
                </th>
                <th class="pb-2 font-medium">
                  Author
                </th>
                <th class="pb-2 font-medium">
                  Date
                </th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="post in stats.recent_posts"
                :key="post.id"
                class="border-b last:border-0"
              >
                <td class="py-2 font-medium">
                  {{ post.title }}
                </td>
                <td class="py-2">
                  <StatusBadge :status="post.status" />
                </td>
                <td class="py-2 text-muted-foreground">
                  {{ post.author.name }}
                </td>
                <td class="py-2 text-muted-foreground">
                  {{ new Date(post.created_at).toLocaleDateString() }}
                </td>
              </tr>
            </tbody>
          </table>
        </CardContent>
      </Card>
    </div>
  </AdminLayout>
</template>
