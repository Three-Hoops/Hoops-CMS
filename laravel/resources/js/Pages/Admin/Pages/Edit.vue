<script setup lang="ts">
import { computed } from "vue";
import { useForm, Link } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select";
import { Textarea } from "@/components/ui/textarea";
import TipTapEditor from "@/components/Admin/TipTapEditor.vue";
import SlugInput from "@/components/Admin/SlugInput.vue";
import { useAutosave } from "@/composables/useAutosave";
import { useNavigationGuard } from "@/composables/useNavigationGuard";
import type { Page } from "@/types/models";

const props = defineProps<{
    page: Page;
    pages: Array<{ id: number; title: string; slug: string }>;
    autosaveDraft: string | null;
}>();

const form = useForm({
    title: props.page.title,
    slug: props.page.slug,
    content: props.page.content,
    status: props.page.status,
    meta_title: props.page.meta_title ?? "",
    meta_description: props.page.meta_description ?? "",
    published_at: props.page.published_at
        ? props.page.published_at.replace(" ", "T").slice(0, 16)
        : "",
    parent_id: props.page.parent_id,
});

useNavigationGuard(() => form.isDirty)

const { lastSavedAt, hasDraft, draftContent, clearDraft, dismissDraft } = useAutosave({
    resource: "page",
    resourceId: props.page.id,
    updatedAt: props.page.updated_at,
    content: computed(() => form.content),
    serverDraft: props.autosaveDraft,
});

function restoreDraft() {
    if (draftContent.value !== null) {
        form.content = draftContent.value;
    }
    dismissDraft();
}

function submit() {
    form.put(route("admin.pages.update", props.page.id), {
        onSuccess: () => {
            clearDraft()
            form.setDefaults()
        },
    });
}
</script>

<template>
  <AdminLayout>
    <template #title>
      Edit Page
    </template>

    <div
      v-if="hasDraft"
      class="mx-auto mb-4 max-w-3xl flex items-center justify-between rounded-md border border-yellow-300 bg-yellow-50 px-4 py-2 text-sm text-yellow-800 dark:border-yellow-700 dark:bg-yellow-950 dark:text-yellow-200"
    >
      <span>Unsaved draft found. Restore it?</span>
      <div class="flex gap-2">
        <button
          type="button"
          class="font-medium underline"
          @click="restoreDraft"
        >
          Restore
        </button>
        <button
          type="button"
          class="opacity-60 hover:opacity-100"
          @click="dismissDraft"
        >
          Dismiss
        </button>
      </div>
    </div>

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
        <Label>Parent Page</Label>
        <Select
          :model-value="form.parent_id != null ? form.parent_id.toString() : 'none'"
          @update:model-value="
            (val) => (form.parent_id = val === 'none' ? null : Number(val))
          "
        >
          <SelectTrigger>
            <SelectValue placeholder="None (top-level)" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="none">
              None (top-level)
            </SelectItem>
            <SelectItem
              v-for="p in pages"
              :key="p.id"
              :value="p.id.toString()"
            >
              {{ p.title }}
            </SelectItem>
          </SelectContent>
        </Select>
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
        <span
          v-if="lastSavedAt"
          class="text-xs text-muted-foreground"
        >
          Draft saved {{ lastSavedAt.toLocaleTimeString() }}
        </span>
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
