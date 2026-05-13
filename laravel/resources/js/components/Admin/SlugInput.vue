<script setup lang="ts">
import { watch } from 'vue'
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

watch(() => props.source, (val) => {
    if (val !== undefined && !props.modelValue) {
        emit('update:modelValue', toSlug(val))
    }
})

function onInput(e: Event) {
    emit('update:modelValue', toSlug((e.target as HTMLInputElement).value))
}
</script>

<template>
  <Input
    :value="modelValue"
    placeholder="auto-generated-from-title"
    @input="onInput"
  />
</template>
