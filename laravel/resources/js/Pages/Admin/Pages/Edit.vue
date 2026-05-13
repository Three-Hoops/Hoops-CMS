<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Textarea } from '@/components/ui/textarea'
import TipTapEditor from '@/components/Admin/TipTapEditor.vue'
import SlugInput from '@/components/Admin/SlugInput.vue'
import type { Page } from '@/types/models'

const props = defineProps<{
    page: Page
}>()

const form = useForm({
    title: props.page.title,
    slug: props.page.slug,
    content: props.page.content,
    status: props.page.status,
    meta_title: props.page.meta_title ?? '',
    meta_description: props.page.meta_description ?? '',
    published_at: props.page.published_at
        ? props.page.published_at.replace(' ', 'T').slice(0, 16)
        : '',
})

function submit() {
    form.put(route('admin.pages.update', props.page.id))
}
</script>

<template>
  <AdminLayout>
    <template #title>
      Edit Page
    </template>

    <form
      class="mx-auto max-w-3xl space-y-6"
      @submit.prevent="submit"
    >
      <div class="space-y-2">
        <Label for="title">Title</Label>
        <Input
          id="title"
          v-model="form.title"
          :class="{ 'border-destructive': form.errors.title }"
        />
        <p
          v-if="form.errors.title"
          class="text-xs text-destructive"
        >
          {{ form.errors.title }}
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
        <Label>Content</Label>
        <TipTapEditor v-model="form.content" />
        <p
          v-if="form.errors.content"
          class="text-xs text-destructive"
        >
          {{ form.errors.content }}
        </p>
      </div>

      <div class="space-y-2">
        <Label>Status</Label>
        <Select v-model="form.status">
          <SelectTrigger>
            <SelectValue />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="draft">
              Draft
            </SelectItem>
            <SelectItem value="published">
              Published
            </SelectItem>
          </SelectContent>
        </Select>
      </div>

      <div class="space-y-2">
        <Label for="published_at">Publish Date (optional)</Label>
        <Input
          id="published_at"
          v-model="form.published_at"
          type="datetime-local"
        />
      </div>

      <div class="space-y-2">
        <Label for="meta_title">Meta Title</Label>
        <Input
          id="meta_title"
          v-model="form.meta_title"
        />
      </div>

      <div class="space-y-2">
        <Label for="meta_description">Meta Description</Label>
        <Textarea
          id="meta_description"
          v-model="form.meta_description"
          rows="3"
        />
      </div>

      <div class="flex items-center justify-end gap-3">
        <Button
          variant="outline"
          :as="Link"
          :href="route('admin.pages.index')"
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
