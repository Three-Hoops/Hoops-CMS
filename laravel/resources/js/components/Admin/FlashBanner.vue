<script setup lang="ts">
import { ref, watch } from 'vue'
import { usePage } from '@inertiajs/vue3'
import type { SharedProps } from '@/types/models'

const page = usePage<SharedProps>()

const message = ref<string | null>(null)
const type = ref<'success' | 'error'>('success')
let timer: ReturnType<typeof setTimeout> | null = null

watch(
    () => page.props.flash,
    (flash) => {
        if (flash.success) {
            message.value = flash.success
            type.value = 'success'
        } else if (flash.error) {
            message.value = flash.error
            type.value = 'error'
        } else {
            return
        }

        if (timer) clearTimeout(timer)
        timer = setTimeout(() => (message.value = null), 4000)
    },
    { immediate: true }
)

function dismiss() {
    message.value = null
    if (timer) clearTimeout(timer)
}
</script>

<template>
  <div
    v-if="message"
    :class="[
      'flex items-center justify-between px-6 py-3 text-sm font-medium',
      type === 'success' ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800',
    ]"
  >
    <span>{{ message }}</span>
    <button
      class="ml-4 opacity-60 hover:opacity-100"
      @click="dismiss"
    >
      ✕
    </button>
  </div>
</template>
