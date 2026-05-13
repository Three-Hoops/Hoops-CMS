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
import type { Category, Post, Tag } from '@/types/models'

const props = defineProps<{
    post: Post
    categories: Category[]
    tags: Tag[]
}>()

const form = useForm({
    title: props.post.title,
    slug: props.post.slug,
    content: props.post.content,
    status: props.post.status,
    featured_image: props.post.featured_image ?? '',
    category_id: props.post.category_id ? String(props.post.category_id) : '' as string,
    tag_ids: props.post.tags.map((t) => t.id),
    meta_title: props.post.meta_title ?? '',
    meta_description: props.post.meta_description ?? '',
    published_at: props.post.published_at
        ? props.post.published_at.replace(' ', 'T').slice(0, 16)
        : '',
})

function toggleTag(id: number) {
    const idx = form.tag_ids.indexOf(id)
    if (idx === -1) form.tag_ids.push(id)
    else form.tag_ids.splice(idx, 1)
}

function submit() {
    form.put(route('admin.posts.update', props.post.id))
}
</script>

<template>
  <AdminLayout>
    <template #title>
      Edit Post
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

      <div class="grid grid-cols-2 gap-4">
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
          <Label>Category</Label>
          <Select v-model="form.category_id">
            <SelectTrigger>
              <SelectValue placeholder="None" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="">
                None
              </SelectItem>
              <SelectItem
                v-for="cat in categories"
                :key="cat.id"
                :value="String(cat.id)"
              >
                {{ cat.name }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>
      </div>

      <div
        v-if="tags.length > 0"
        class="space-y-2"
      >
        <Label>Tags</Label>
        <div class="flex flex-wrap gap-2">
          <label
            v-for="tag in tags"
            :key="tag.id"
            class="flex cursor-pointer items-center gap-1.5 rounded-md border px-2.5 py-1 text-sm transition-colors"
            :class="form.tag_ids.includes(tag.id) ? 'border-primary bg-primary/10' : 'hover:bg-accent'"
          >
            <input
              type="checkbox"
              class="sr-only"
              :checked="form.tag_ids.includes(tag.id)"
              @change="toggleTag(tag.id)"
            >
            {{ tag.name }}
          </label>
        </div>
      </div>

      <div class="space-y-2">
        <Label for="featured_image">Featured Image URL</Label>
        <Input
          id="featured_image"
          v-model="form.featured_image"
          placeholder="https://..."
        />
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
          :href="route('admin.posts.index')"
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
