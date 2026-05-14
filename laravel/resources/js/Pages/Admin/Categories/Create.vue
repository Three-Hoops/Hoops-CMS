<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { useNavigationGuard } from '@/composables/useNavigationGuard'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Textarea } from '@/components/ui/textarea'
import SlugInput from '@/components/Admin/SlugInput.vue'
import type { Category } from '@/types/models'

defineProps<{
    parents: Category[]
}>()

const form = useForm({
    name: '',
    slug: '',
    description: '',
    parent_id: '' as string | number,
})

useNavigationGuard(() => form.isDirty)

function submit() {
    form.post(route('admin.categories.store'))
}
</script>

<template>
  <AdminLayout>
    <template #title>
      New Category
    </template>

    <form
      class="mx-auto max-w-xl space-y-6"
      @submit.prevent="submit"
    >
      <div class="space-y-2">
        <Label for="name">Name</Label>
        <Input
          id="name"
          v-model="form.name"
          :class="{ 'border-destructive': form.errors.name }"
        />
        <p
          v-if="form.errors.name"
          class="text-xs text-destructive"
        >
          {{ form.errors.name }}
        </p>
      </div>

      <div class="space-y-2">
        <Label>Slug</Label>
        <SlugInput
          v-model="form.slug"
          :source="form.name"
        />
        <p
          v-if="form.errors.slug"
          class="text-xs text-destructive"
        >
          {{ form.errors.slug }}
        </p>
      </div>

      <div class="space-y-2">
        <Label for="description">Description</Label>
        <Textarea
          id="description"
          v-model="form.description"
          rows="3"
        />
      </div>

      <div class="space-y-2">
        <Label>Parent Category</Label>
        <Select v-model="form.parent_id">
          <SelectTrigger>
            <SelectValue placeholder="None (top-level)" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="">
              None (top-level)
            </SelectItem>
            <SelectItem
              v-for="parent in parents"
              :key="parent.id"
              :value="String(parent.id)"
            >
              {{ parent.name }}
            </SelectItem>
          </SelectContent>
        </Select>
        <p
          v-if="form.errors.parent_id"
          class="text-xs text-destructive"
        >
          {{ form.errors.parent_id }}
        </p>
      </div>

      <div class="flex items-center justify-end gap-3">
        <Button
          variant="outline"
          :as="Link"
          :href="route('admin.categories.index')"
        >
          Cancel
        </Button>
        <Button
          type="submit"
          :disabled="form.processing"
        >
          Create Category
        </Button>
      </div>
    </form>
  </AdminLayout>
</template>
