<script setup lang="ts">
import { router } from '@inertiajs/vue3'

defineProps<{
    links: Array<{
        url: string | null
        label: string
        active: boolean
    }>
}>()

function navigate(url: string | null) {
    if (url) router.visit(url, { preserveScroll: true })
}

function decodeLabel(label: string): string {
    return label
        .replace(/&laquo;/g, '«')
        .replace(/&raquo;/g, '»')
        .replace(/&lt;/g, '<')
        .replace(/&gt;/g, '>')
        .replace(/&amp;/g, '&')
}
</script>

<template>
  <div class="flex items-center gap-1">
    <button
      v-for="link in links"
      :key="link.label"
      type="button"
      class="inline-flex h-8 min-w-8 items-center justify-center rounded-md border px-2 text-sm transition-colors disabled:pointer-events-none disabled:opacity-50"
      :class="link.active ? 'border-primary bg-primary text-primary-foreground' : 'border-input bg-background hover:bg-accent'"
      :disabled="!link.url"
      @click="navigate(link.url)"
    >
      {{ decodeLabel(link.label) }}
    </button>
  </div>
</template>
