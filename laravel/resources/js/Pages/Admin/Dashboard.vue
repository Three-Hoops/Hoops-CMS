<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'

defineProps<{
    stats: Record<string, never>
}>()

const statCards = [
    { label: 'Total Posts', value: 0 },
    { label: 'Total Pages', value: 0 },
    { label: 'Total Users', value: 0 },
    { label: 'Media Files', value: 0 },
]
</script>

<template>
  <AdminLayout>
    <template #title>
      Dashboard
    </template>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
      <Card
        v-for="card in statCards"
        :key="card.label"
      >
        <CardHeader class="pb-2">
          <CardTitle class="text-sm font-medium text-muted-foreground">
            {{ card.label }}
          </CardTitle>
        </CardHeader>
        <CardContent>
          <p class="text-2xl font-bold">
            {{ card.value }}
          </p>
        </CardContent>
      </Card>
    </div>
  </AdminLayout>
</template>
