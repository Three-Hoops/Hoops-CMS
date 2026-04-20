<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{ status: number }>()

const titles: Record<number, string> = {
    403: 'Forbidden',
    404: 'Page Not Found',
    419: 'Page Expired',
    500: 'Server Error',
    503: 'Service Unavailable',
}

const messages: Record<number, string> = {
    403: "You don't have permission to access this page.",
    404: "The page you're looking for doesn't exist.",
    419: 'Your session has expired. Please go back and try again.',
    500: 'Something went wrong on our end. Please try again later.',
    503: "We're down for maintenance. Please check back soon.",
}

const title = computed(() => titles[props.status] ?? 'An Error Occurred')
const message = computed(() => messages[props.status] ?? 'An unexpected error occurred.')
</script>

<template>
  <div class="flex min-h-screen flex-col items-center justify-center bg-background">
    <div class="space-y-4 text-center">
      <p class="text-6xl font-bold text-muted-foreground">
        {{ status }}
      </p>
      <h1 class="text-2xl font-semibold">
        {{ title }}
      </h1>
      <p class="text-muted-foreground">
        {{ message }}
      </p>
      <a
        href="/admin"
        class="inline-block text-sm underline-offset-4 hover:underline"
      >
        Go to dashboard
      </a>
    </div>
  </div>
</template>
