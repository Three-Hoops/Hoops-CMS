<script setup lang="ts">
import { ref, watch } from 'vue'
import { Input } from '@/components/ui/input'

const props = defineProps<{
    modelValue: string
    source?: string
}>()

const emit = defineEmits<{
    'update:modelValue': [value: string]
}>()

function toSlug(str: string): string {
    return str
        .toLowerCase()
        .normalize('NFD')
        .replace(/[̀-ͯ]/g, '')
        .replace(/[^a-z0-9\s-]/g, '')
        .trim()
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
}

// Track the last value we auto-generated so we know if the user has manually edited the slug
const lastAutoSlug = ref(toSlug(props.source ?? ''))

watch(() => props.source, (val) => {
    if (val === undefined) return
    const newSlug = toSlug(val)
    // Only auto-update while the slug still matches what we last generated (not manually edited)
    if (props.modelValue === '' || props.modelValue === lastAutoSlug.value) {
        lastAutoSlug.value = newSlug
        emit('update:modelValue', newSlug)
    }
})

function onInput(e: Event) {
    const newVal = toSlug((e.target as HTMLInputElement).value)
    // User manually edited — stop tracking auto-slug so source watcher backs off
    lastAutoSlug.value = ''
    emit('update:modelValue', newVal)
}
</script>

<template>
  <Input
    :value="modelValue"
    placeholder="auto-generated-from-title"
    @input="onInput"
  />
</template>
