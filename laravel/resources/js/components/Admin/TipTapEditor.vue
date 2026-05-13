<script setup lang="ts">
import { watch } from 'vue'
import { useEditor, EditorContent } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import Link from '@tiptap/extension-link'
import Placeholder from '@tiptap/extension-placeholder'
import Underline from '@tiptap/extension-underline'

const props = defineProps<{
    modelValue: string
    placeholder?: string
}>()

const emit = defineEmits<{
    'update:modelValue': [value: string]
}>()

const editor = useEditor({
    content: props.modelValue,
    extensions: [
        StarterKit,
        Underline,
        Link.configure({ openOnClick: false }),
        Placeholder.configure({ placeholder: props.placeholder ?? 'Write something…' }),
    ],
    onUpdate({ editor }) {
        emit('update:modelValue', editor.getHTML())
    },
})

watch(() => props.modelValue, (val) => {
    if (editor.value && editor.value.getHTML() !== val) {
        editor.value.commands.setContent(val)
    }
})
</script>

<template>
  <div
    class="min-h-[200px] cursor-text rounded-md border border-input bg-background px-3 py-2 text-sm focus-within:ring-1 focus-within:ring-ring"
    @click="editor?.commands.focus()"
  >
    <div
      v-if="editor"
      class="mb-2 flex flex-wrap gap-1 border-b pb-2"
    >
      <button
        type="button"
        class="rounded px-2 py-0.5 text-xs hover:bg-accent"
        :class="{ 'bg-accent': editor.isActive('bold') }"
        @click="editor.chain().focus().toggleBold().run()"
      >
        B
      </button>
      <button
        type="button"
        class="rounded px-2 py-0.5 text-xs italic hover:bg-accent"
        :class="{ 'bg-accent': editor.isActive('italic') }"
        @click="editor.chain().focus().toggleItalic().run()"
      >
        I
      </button>
      <button
        type="button"
        class="rounded px-2 py-0.5 text-xs underline hover:bg-accent"
        :class="{ 'bg-accent': editor.isActive('underline') }"
        @click="editor.chain().focus().toggleUnderline().run()"
      >
        U
      </button>
      <button
        type="button"
        class="rounded px-2 py-0.5 text-xs hover:bg-accent"
        :class="{ 'bg-accent': editor.isActive('bulletList') }"
        @click="editor.chain().focus().toggleBulletList().run()"
      >
        • List
      </button>
      <button
        type="button"
        class="rounded px-2 py-0.5 text-xs hover:bg-accent"
        :class="{ 'bg-accent': editor.isActive('heading', { level: 2 }) }"
        @click="editor.chain().focus().toggleHeading({ level: 2 }).run()"
      >
        H2
      </button>
    </div>
    <EditorContent :editor="editor" />
  </div>
</template>

<style scoped>
:deep(.ProseMirror) {
    min-height: 8rem;
    outline: none;
}
</style>
