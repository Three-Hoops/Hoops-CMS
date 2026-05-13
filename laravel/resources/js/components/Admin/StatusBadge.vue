<script setup lang="ts">
import { Badge } from '@/components/ui/badge'

const props = defineProps<{
    status: 'draft' | 'published'
}>()
</script>

<template>
  <Badge :variant="props.status === 'published' ? 'default' : 'secondary'">
    {{ props.status === 'published' ? 'Published' : 'Draft' }}
  </Badge>
</template>
