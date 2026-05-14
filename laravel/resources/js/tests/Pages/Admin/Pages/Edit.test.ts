import { mount } from '@vue/test-utils'
import { ref } from 'vue'
import { describe, it, expect, vi, beforeEach } from 'vitest'
import PagesEdit from '@/Pages/Admin/Pages/Edit.vue'
import type { Page } from '@/types/models'

const { mockPut, mockUseForm, mockClearDraft, mockDismissDraft } = vi.hoisted(() => {
    const mockPut = vi.fn()
    const mockUseForm = vi.fn((initial: Record<string, unknown>) => ({
        ...initial,
        processing: false,
        errors: {},
        put: mockPut,
    }))
    const mockClearDraft = vi.fn()
    const mockDismissDraft = vi.fn()
    return { mockPut, mockUseForm, mockClearDraft, mockDismissDraft }
})

const autosaveState = {
    hasDraft: false,
    draftContent: null as string | null,
    lastSavedAt: null as Date | null,
}

const page: Page = {
    id: 5, user_id: 1, title: 'My Page', slug: 'my-page', content: '<p>Content</p>', content_json: {},
    excerpt: null, status: 'published', meta_title: 'SEO Title', meta_description: 'SEO desc',
    meta_keywords: null, published_at: '2025-03-01 09:00:00', created_at: '2025-01-01 10:00:00', updated_at: '2025-01-01 10:00:00',
    deleted_at: null,
    author: { id: 1, name: 'Alice', email: 'a@example.com', role: 'super_admin', locale: 'en', last_login_at: null, theme_mode: 'system', timezone: 'UTC' },
    parent_id: null, parent: null,
}

const pages = [{ id: 1, title: 'Home', slug: 'home' }, { id: 2, title: 'About', slug: 'about' }]

vi.mock('@inertiajs/vue3', () => ({
    usePage: () => ({
        url: '/admin/pages/5/edit',
        props: { app: { name: 'Hoops CMS' }, auth: null, flash: { success: null, error: null }, errors: {}, ziggy: { location: 'http://localhost', routes: {} } },
    }),
    useForm: mockUseForm,
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

const SelectStub = {
    template: '<div :data-model="modelValue"><slot /></div>',
    props: ['modelValue'],
    emits: ['update:modelValue'],
}

const globalConfig = {
    global: {
        stubs: {
            AdminLayout: { template: '<div><slot name="title" /><slot /></div>' },
            Button: { template: '<button type="submit" :disabled="disabled"><slot /></button>', props: ['disabled', 'variant', 'size', 'asChild', 'as', 'href'] },
            Input: { template: '<input :id="id" :value="modelValue" />', props: ['id', 'modelValue', 'type', 'class'] },
            Label: { template: '<label><slot /></label>', props: ['for'] },
            Textarea: { template: '<textarea :value="modelValue" />', props: ['id', 'modelValue', 'rows'] },
            Select: SelectStub,
            SelectTrigger: { template: '<div><slot /></div>' },
            SelectContent: { template: '<div><slot /></div>' },
            SelectItem: { template: '<div><slot /></div>', props: ['value'] },
            SelectValue: { template: '<span />' },
            TipTapEditor: { template: '<div class="tiptap" :data-value="modelValue" />', props: ['modelValue'] },
            SlugInput: { template: '<input :value="modelValue" />', props: ['modelValue'] },
        },
    },
}

describe('Pages/Edit', () => {
    beforeEach(() => {
        autosaveState.hasDraft = false
        autosaveState.draftContent = null
        autosaveState.lastSavedAt = null
        mockClearDraft.mockReset()
        mockDismissDraft.mockReset()
    })

    it('renders the page title "Edit Page"', () => {
        const wrapper = mount(PagesEdit, { props: { page, pages, autosaveDraft: null }, ...globalConfig })
        expect(wrapper.text()).toContain('Edit Page')
    })

    it('pre-populates the title field', () => {
        const wrapper = mount(PagesEdit, { props: { page, pages, autosaveDraft: null }, ...globalConfig })
        const inputs = wrapper.findAll('input')
        const titleInput = inputs.find(i => i.attributes('value') === 'My Page')
        expect(titleInput).toBeTruthy()
    })

    it('pre-populates the slug field', () => {
        const wrapper = mount(PagesEdit, { props: { page, pages, autosaveDraft: null }, ...globalConfig })
        const inputs = wrapper.findAll('input')
        expect(inputs.some(i => i.attributes('value') === 'my-page')).toBe(true)
    })

    it('pre-populates the content in TipTap editor', () => {
        const wrapper = mount(PagesEdit, { props: { page, pages, autosaveDraft: null }, ...globalConfig })
        expect(wrapper.find('.tiptap').attributes('data-value')).toBe('<p>Content</p>')
    })

    it('calls form.put on submit', async () => {
        const wrapper = mount(PagesEdit, { props: { page, pages, autosaveDraft: null }, ...globalConfig })
        await wrapper.find('form').trigger('submit')
        expect(mockPut).toHaveBeenCalledWith('/admin.pages.update/5', expect.objectContaining({ onSuccess: expect.any(Function) }))
    })

    it('passes parent_id as string modelValue to parent selector when set', () => {
        // Arrange
        const pageWithParent = { ...page, parent_id: 2, parent: { id: 2, title: 'About', slug: 'about' } }

        // Act
        const wrapper = mount(PagesEdit, { props: { page: pageWithParent, pages, autosaveDraft: null }, ...globalConfig })

        // Assert — first Select stub is the parent page selector
        const parentSelect = wrapper.findAllComponents(SelectStub)[0]
        expect(parentSelect.attributes('data-model')).toBe('2')
    })

    it('sets parent_id to null when "none" is selected in parent dropdown', async () => {
        // Arrange
        const pageWithParent = { ...page, parent_id: 2, parent: { id: 2, title: 'About', slug: 'about' } }
        const form = { ...pageWithParent, processing: false, errors: {}, put: mockPut }
        mockUseForm.mockReturnValueOnce(form)
        const wrapper = mount(PagesEdit, { props: { page: pageWithParent, pages, autosaveDraft: null }, ...globalConfig })

        // Act
        await wrapper.findAllComponents(SelectStub)[0].vm.$emit('update:modelValue', 'none')

        // Assert
        expect(form.parent_id).toBeNull()
    })

    it('sets parent_id to a number when a page is selected in parent dropdown', async () => {
        // Arrange
        const form = { ...page, processing: false, errors: {}, put: mockPut }
        mockUseForm.mockReturnValueOnce(form)
        const wrapper = mount(PagesEdit, { props: { page, pages, autosaveDraft: null }, ...globalConfig })

        // Act
        await wrapper.findAllComponents(SelectStub)[0].vm.$emit('update:modelValue', '1')

        // Assert
        expect(form.parent_id).toBe(1)
    })

    it('shows draft banner when hasDraft is true', () => {
        // Arrange
        autosaveState.hasDraft = true

        // Act
        const wrapper = mount(PagesEdit, { props: { page, pages, autosaveDraft: null }, ...globalConfig })

        // Assert
        expect(wrapper.text()).toContain('Unsaved draft found. Restore it?')
    })

    it('calls dismissDraft when Restore button is clicked', async () => {
        // Arrange
        autosaveState.hasDraft = true
        autosaveState.draftContent = '<p>Restored draft</p>'

        // Act
        const wrapper = mount(PagesEdit, { props: { page, pages, autosaveDraft: null }, ...globalConfig })
        const restoreButton = wrapper.findAll('button[type="button"]').find(b => b.text() === 'Restore')
        await restoreButton!.trigger('click')

        // Assert
        expect(mockDismissDraft).toHaveBeenCalledOnce()
    })

    it('calls dismissDraft when Dismiss button is clicked', async () => {
        // Arrange
        autosaveState.hasDraft = true

        // Act
        const wrapper = mount(PagesEdit, { props: { page, pages, autosaveDraft: null }, ...globalConfig })
        const dismissButton = wrapper.findAll('button[type="button"]').find(b => b.text() === 'Dismiss')
        await dismissButton!.trigger('click')

        // Assert
        expect(mockDismissDraft).toHaveBeenCalledOnce()
    })

    it('shows the last saved timestamp when lastSavedAt is set', () => {
        // Arrange
        autosaveState.lastSavedAt = new Date('2025-06-01T12:30:00.000Z')

        // Act
        const wrapper = mount(PagesEdit, { props: { page, pages, autosaveDraft: null }, ...globalConfig })

        // Assert
        expect(wrapper.find('span.text-xs.text-muted-foreground').exists()).toBe(true)
        expect(wrapper.find('span.text-xs.text-muted-foreground').text()).toContain('Draft saved')
    })
})
