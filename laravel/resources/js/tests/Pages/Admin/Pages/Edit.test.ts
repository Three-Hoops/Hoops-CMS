import { mount } from '@vue/test-utils'
import { describe, it, expect, vi } from 'vitest'
import PagesEdit from '@/Pages/Admin/Pages/Edit.vue'
import type { Page } from '@/types/models'

const { mockPut } = vi.hoisted(() => ({ mockPut: vi.fn() }))

const page: Page = {
    id: 5, user_id: 1, title: 'My Page', slug: 'my-page', content: '<p>Content</p>', content_json: {},
    excerpt: null, status: 'published', meta_title: 'SEO Title', meta_description: 'SEO desc',
    meta_keywords: null, published_at: '2025-03-01 09:00:00', created_at: '2025-01-01 10:00:00', updated_at: '2025-01-01 10:00:00',
    author: { id: 1, name: 'Alice', email: 'a@example.com', role: 'super_admin', locale: 'en', last_login_at: null, theme_mode: 'system', timezone: 'UTC' },
}

vi.mock('@inertiajs/vue3', () => ({
    usePage: () => ({
        url: '/admin/pages/5/edit',
        props: { app: { name: 'Hoops CMS' }, auth: null, flash: { success: null, error: null }, errors: {}, ziggy: { location: 'http://localhost', routes: {} } },
    }),
    useForm: vi.fn((initial: Record<string, unknown>) => ({
        ...initial,
        processing: false,
        errors: {},
        put: mockPut,
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

vi.mock('@/components/Admin/FlashBanner.vue', () => ({ default: { template: '<div />' } }))

const globalConfig = {
    global: {
        stubs: {
            AdminLayout: { template: '<div><slot name="title" /><slot /></div>' },
            Button: { template: '<button type="submit" :disabled="disabled"><slot /></button>', props: ['disabled', 'variant', 'size', 'asChild', 'as', 'href'] },
            Input: { template: '<input :id="id" :value="modelValue" />', props: ['id', 'modelValue', 'type', 'class'] },
            Label: { template: '<label><slot /></label>', props: ['for'] },
            Textarea: { template: '<textarea :value="modelValue" />', props: ['id', 'modelValue', 'rows'] },
            Select: { template: '<div><slot /></div>', props: ['modelValue'] },
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
    it('renders the page title "Edit Page"', () => {
        const wrapper = mount(PagesEdit, { props: { page }, ...globalConfig })
        expect(wrapper.text()).toContain('Edit Page')
    })

    it('pre-populates the title field', () => {
        const wrapper = mount(PagesEdit, { props: { page }, ...globalConfig })
        const inputs = wrapper.findAll('input')
        const titleInput = inputs.find(i => i.attributes('value') === 'My Page')
        expect(titleInput).toBeTruthy()
    })

    it('pre-populates the slug field', () => {
        const wrapper = mount(PagesEdit, { props: { page }, ...globalConfig })
        const inputs = wrapper.findAll('input')
        expect(inputs.some(i => i.attributes('value') === 'my-page')).toBe(true)
    })

    it('pre-populates the content in TipTap editor', () => {
        const wrapper = mount(PagesEdit, { props: { page }, ...globalConfig })
        expect(wrapper.find('.tiptap').attributes('data-value')).toBe('<p>Content</p>')
    })

    it('calls form.put on submit', async () => {
        const wrapper = mount(PagesEdit, { props: { page }, ...globalConfig })
        await wrapper.find('form').trigger('submit')
        expect(mockPut).toHaveBeenCalledWith('/admin.pages.update/5')
    })
})
