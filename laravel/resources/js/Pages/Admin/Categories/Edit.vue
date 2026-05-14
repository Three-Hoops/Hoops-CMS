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

const props = defineProps<{
    category: Category
    parents: Category[]
}>()

const form = useForm({
    name: props.category.name,
    slug: props.category.slug,
    description: props.category.description ?? '',
    parent_id: props.category.parent_id ? String(props.category.parent_id) : '' as string,
})

useNavigationGuard(() => form.isDirty)

function submit() {
    form.put(route('admin.categories.update', props.category.id), {
        onSuccess: () => form.defaults(),
    })
}
</script>

<template>
  <AdminLayout>
    <template #title>
      Edit Category
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
        <SlugInput v-model="form.slug" />
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
          Save Changes
        </Button>
      </div>
    </form>
  </AdminLayout>
</template>
