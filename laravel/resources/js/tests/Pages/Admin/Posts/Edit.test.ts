import { mount } from '@vue/test-utils'
import { ref } from 'vue'
import { describe, it, expect, vi, beforeEach } from 'vitest'
import PostsEdit from '@/Pages/Admin/Posts/Edit.vue'
import type { Category, Post, Tag } from '@/types/models'

const { mockPut, mockClearDraft, mockDismissDraft } = vi.hoisted(() => ({
    mockPut: vi.fn(),
    mockClearDraft: vi.fn(),
    mockDismissDraft: vi.fn(),
}))

const autosaveState = {
    hasDraft: false,
    draftContent: null as string | null,
    lastSavedAt: null as Date | null,
}

const author = { id: 1, name: 'Alice', email: 'a@example.com', role: 'super_admin' as const, locale: 'en', last_login_at: null, theme_mode: 'system' as const, timezone: 'UTC' }

const post: Post = {
    id: 3, user_id: 1, title: 'My Post', slug: 'my-post', content: '<p>Body</p>', content_json: {},
    excerpt: null, status: 'draft', meta_title: null, meta_description: null, meta_keywords: null,
    published_at: null, created_at: '2025-01-01 10:00:00', updated_at: '2025-01-01 10:00:00',
    deleted_at: null, featured_image: 'https://example.com/img.jpg', category_id: 2, category: null,
    tags: [{ id: 1, name: 'Laravel', slug: 'laravel' }], author, parent_id: null, parent: null,
}

const categories: Category[] = [
    { id: 1, name: 'Tech', slug: 'tech', description: null, parent_id: null, parent: null, deleted_at: null },
    { id: 2, name: 'News', slug: 'news', description: null, parent_id: null, parent: null, deleted_at: null },
]

const tags: Tag[] = [
    { id: 1, name: 'Laravel', slug: 'laravel' },
    { id: 2, name: 'Vue', slug: 'vue' },
]

vi.mock('@inertiajs/vue3', () => ({
    usePage: () => ({
        url: '/admin/posts/3/edit',
        props: { app: { name: 'Hoops CMS' }, auth: null, flash: { success: null, error: null }, errors: {}, ziggy: { location: 'http://localhost', routes: {} } },
    }),
    useForm: vi.fn((initial: Record<string, unknown>) => ({
        ...initial,
        processing: false, errors: {}, put: mockPut,
    })),
    Head: { template: '<div />' },
    Link: { template: '<a :href="href"><slot /></a>', props: ['href'] },
}))

vi.mock('ziggy-js', () => ({
    route: (name: string, id?: number) => id ? `/${name}/${id}` : `/${name}`,
}))

vi.mock('@/stores/useAuthStore', () => ({
    useAuthStore: () => ({ user: { name: 'Admin', role: 'super_admin', last_login_at: null } }),
}))

vi.mock('@/composables/useThemeMode', () => ({
    useThemeMode: () => ({ themeMode: { value: 'system' }, setTheme: vi.fn() }),
}))

vi.mock('@/composables/useAutosave', () => ({
    useAutosave: () => ({
        lastSavedAt: ref(autosaveState.lastSavedAt),
        hasDraft: ref(autosaveState.hasDraft),
        draftContent: ref(autosaveState.draftContent),
        clearDraft: mockClearDraft,
        dismissDraft: mockDismissDraft,
    }),
}))

vi.mock('@/components/Admin/FlashBanner.vue', () => ({ default: { template: '<div />' } }))

const globalConfig = {
    global: {
        stubs: {
            AdminLayout: { template: '<div><slot name="title" /><slot /></div>' },
            Button: { template: '<button type="submit" :disabled="disabled"><slot /></button>', props: ['disabled', 'variant', 'size', 'asChild', 'as', 'href'] },
            Input: { template: '<input :id="id" :value="modelValue" />', props: ['id', 'modelValue', 'type', 'placeholder', 'class'] },
            Label: { template: '<label><slot /></label>', props: ['for'] },
            Textarea: { template: '<textarea />', props: ['id', 'modelValue', 'rows'] },
            Select: { template: '<div><slot /></div>', props: ['modelValue'] },
            SelectTrigger: { template: '<div><slot /></div>' },
            SelectContent: { template: '<div><slot /></div>' },
            SelectItem: { template: '<div :data-value="value"><slot /></div>', props: ['value'] },
            SelectValue: { template: '<span />', props: ['placeholder'] },
            TipTapEditor: { template: '<div class="tiptap" :data-value="modelValue" />', props: ['modelValue'] },
            SlugInput: { template: '<input :value="modelValue" />', props: ['modelValue'] },
        },
    },
}

describe('Posts/Edit', () => {
    beforeEach(() => {
        autosaveState.hasDraft = false
        autosaveState.draftContent = null
        autosaveState.lastSavedAt = null
        mockClearDraft.mockReset()
        mockDismissDraft.mockReset()
    })

    it('renders the page title "Edit Post"', () => {
        const wrapper = mount(PostsEdit, { props: { post, categories, tags, autosaveDraft: null }, ...globalConfig })
        expect(wrapper.text()).toContain('Edit Post')
    })

    it('pre-populates title from post prop', () => {
        const wrapper = mount(PostsEdit, { props: { post, categories, tags, autosaveDraft: null }, ...globalConfig })
        expect(wrapper.findAll('input').some(i => i.attributes('value') === 'My Post')).toBe(true)
    })

    it('pre-populates slug from post prop', () => {
        const wrapper = mount(PostsEdit, { props: { post, categories, tags, autosaveDraft: null }, ...globalConfig })
        expect(wrapper.findAll('input').some(i => i.attributes('value') === 'my-post')).toBe(true)
    })

    it('pre-populates content in TipTap editor', () => {
        const wrapper = mount(PostsEdit, { props: { post, categories, tags, autosaveDraft: null }, ...globalConfig })
        expect(wrapper.find('.tiptap').attributes('data-value')).toBe('<p>Body</p>')
    })

    it('renders tag checkboxes', () => {
        const wrapper = mount(PostsEdit, { props: { post, categories, tags, autosaveDraft: null }, ...globalConfig })
        expect(wrapper.text()).toContain('Laravel')
        expect(wrapper.text()).toContain('Vue')
    })

    it('renders category options', () => {
        const wrapper = mount(PostsEdit, { props: { post, categories, tags, autosaveDraft: null }, ...globalConfig })
        expect(wrapper.text()).toContain('Tech')
        expect(wrapper.text()).toContain('News')
    })

    it('calls form.put on submit', async () => {
        const wrapper = mount(PostsEdit, { props: { post, categories, tags, autosaveDraft: null }, ...globalConfig })
        await wrapper.find('form').trigger('submit')
        expect(mockPut).toHaveBeenCalledWith('/admin.posts.update/3', expect.objectContaining({ onSuccess: expect.any(Function) }))
    })

    it('shows draft banner when hasDraft is true', () => {
        // Arrange
        autosaveState.hasDraft = true

        // Act
        const wrapper = mount(PostsEdit, { props: { post, categories, tags, autosaveDraft: null }, ...globalConfig })

        // Assert
        expect(wrapper.text()).toContain('Unsaved draft found. Restore it?')
    })

    it('calls dismissDraft when Restore button is clicked', async () => {
        // Arrange
        autosaveState.hasDraft = true
        autosaveState.draftContent = '<p>Draft content</p>'

        // Act
        const wrapper = mount(PostsEdit, { props: { post, categories, tags, autosaveDraft: null }, ...globalConfig })
        const restoreButton = wrapper.findAll('button[type="button"]').find(b => b.text() === 'Restore')
        await restoreButton!.trigger('click')

        // Assert
        expect(mockDismissDraft).toHaveBeenCalledOnce()
    })

    it('calls dismissDraft when Dismiss button is clicked', async () => {
        // Arrange
        autosaveState.hasDraft = true

        // Act
        const wrapper = mount(PostsEdit, { props: { post, categories, tags, autosaveDraft: null }, ...globalConfig })
        const dismissButton = wrapper.findAll('button[type="button"]').find(b => b.text() === 'Dismiss')
        await dismissButton!.trigger('click')

        // Assert
        expect(mockDismissDraft).toHaveBeenCalledOnce()
    })

    it('shows the last saved timestamp when lastSavedAt is set', () => {
        // Arrange
        autosaveState.lastSavedAt = new Date('2025-06-01T12:30:00.000Z')

        // Act
        const wrapper = mount(PostsEdit, { props: { post, categories, tags, autosaveDraft: null }, ...globalConfig })

        // Assert
        expect(wrapper.find('span.text-xs.text-muted-foreground').exists()).toBe(true)
        expect(wrapper.find('span.text-xs.text-muted-foreground').text()).toContain('Draft saved')
    })

    it('calls clearDraft when form is submitted successfully', async () => {
        // Arrange — make mockPut invoke its onSuccess callback synchronously
        mockPut.mockImplementationOnce((_url: string, options: { onSuccess: () => void }) => {
            options.onSuccess()
        })

        // Act
        const wrapper = mount(PostsEdit, { props: { post, categories, tags, autosaveDraft: null }, ...globalConfig })
        await wrapper.find('form').trigger('submit')

        // Assert
        expect(mockClearDraft).toHaveBeenCalledOnce()
    })

    it('restoreDraft does nothing to form when draftContent is null', async () => {
        // Arrange — hasDraft true but no actual draft content (defensive branch)
        autosaveState.hasDraft = true
        autosaveState.draftContent = null

        // Act
        const wrapper = mount(PostsEdit, { props: { post, categories, tags, autosaveDraft: null }, ...globalConfig })
        const restoreButton = wrapper.findAll('button[type="button"]').find(b => b.text() === 'Restore')
        await restoreButton!.trigger('click')

        // Assert — dismissDraft still called even when no content to restore
        expect(mockDismissDraft).toHaveBeenCalledOnce()
    })
})
