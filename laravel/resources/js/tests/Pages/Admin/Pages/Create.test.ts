import { mount } from '@vue/test-utils'
import { describe, it, expect, vi } from 'vitest'
import PagesCreate from '@/Pages/Admin/Pages/Create.vue'

const { mockPost, mockUseForm } = vi.hoisted(() => {
    const post = vi.fn()
    const useForm = vi.fn(() => ({
        title: '', slug: '', content: '', status: 'draft', meta_title: '', meta_description: '', published_at: '', parent_id: null,
        processing: false, errors: {} as Record<string, string>, post,
    }))
    return { mockPost: post, mockUseForm: useForm }
})

vi.mock('@inertiajs/vue3', () => ({
    usePage: () => ({
        url: '/admin/pages/create',
        props: { app: { name: 'Hoops CMS' }, auth: null, flash: { success: null, error: null }, errors: {}, ziggy: { location: 'http://localhost', routes: {} } },
    }),
    useForm: mockUseForm,
    Head: { template: '<div />' },
    Link: { template: '<a :href="href"><slot /></a>', props: ['href'] },
}))

vi.mock('ziggy-js', () => ({
    route: (name: string) => `/${name}`,
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
            Input: { template: '<input :id="id" />', props: ['id', 'modelValue', 'type', 'class'] },
            Label: { template: '<label><slot /></label>', props: ['for'] },
            Textarea: { template: '<textarea />', props: ['id', 'modelValue', 'rows'] },
            Select: { template: '<div><slot /></div>', props: ['modelValue'] },
            SelectTrigger: { template: '<div><slot /></div>' },
            SelectContent: { template: '<div><slot /></div>' },
            SelectItem: { template: '<div><slot /></div>', props: ['value'] },
            SelectValue: { template: '<span />' },
            TipTapEditor: { template: '<div class="tiptap" />', props: ['modelValue'] },
            SlugInput: { template: '<input />', props: ['modelValue'] },
        },
    },
}

describe('Pages/Create', () => {
const pages = [{ id: 1, title: 'Home', slug: 'home' }]

    it('renders the page title "New Page"', () => {
        const wrapper = mount(PagesCreate, { props: { pages }, ...globalConfig })
        expect(wrapper.text()).toContain('New Page')
    })

    it('renders title, slug, status, meta fields', () => {
        const wrapper = mount(PagesCreate, { props: { pages }, ...globalConfig })
        expect(wrapper.text()).toContain('Title')
        expect(wrapper.text()).toContain('Slug')
        expect(wrapper.text()).toContain('Status')
        expect(wrapper.text()).toContain('Meta Title')
        expect(wrapper.text()).toContain('Meta Description')
    })

    it('renders the TipTap editor', () => {
        const wrapper = mount(PagesCreate, { props: { pages }, ...globalConfig })
        expect(wrapper.find('.tiptap').exists()).toBe(true)
    })

    it('calls form.post on submit', async () => {
        const wrapper = mount(PagesCreate, { props: { pages }, ...globalConfig })
        await wrapper.find('form').trigger('submit')
        expect(mockPost).toHaveBeenCalledWith('/admin.pages.store')
    })

    it('shows validation error when title error is set', () => {
        mockUseForm.mockReturnValueOnce({
            title: '', slug: '', content: '', status: 'draft', meta_title: '', meta_description: '', published_at: '', parent_id: null,
            processing: false, errors: { title: 'The title field is required.' }, post: mockPost,
        })
        const wrapper = mount(PagesCreate, { props: { pages }, ...globalConfig })
        expect(wrapper.text()).toContain('The title field is required.')
    })
})
