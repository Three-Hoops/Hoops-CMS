import { mount } from '@vue/test-utils'
import { describe, it, expect, vi } from 'vitest'
import CategoriesCreate from '@/Pages/Admin/Categories/Create.vue'
import type { Category } from '@/types/models'

const { mockPost, mockUseForm } = vi.hoisted(() => {
    const post = vi.fn()
    const useForm = vi.fn(() => ({
        name: '', slug: '', description: '', parent_id: '',
        processing: false, errors: {} as Record<string, string>, post,
    }))
    return { mockPost: post, mockUseForm: useForm }
})

vi.mock('@inertiajs/vue3', () => ({
    usePage: () => ({
        url: '/admin/categories/create',
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

const parents: Category[] = [
    { id: 1, name: 'Root', slug: 'root', description: null, parent_id: null, parent: null, deleted_at: null },
    { id: 2, name: 'Other', slug: 'other', description: null, parent_id: null, parent: null, deleted_at: null },
]

const globalConfig = {
    global: {
        stubs: {
            AdminLayout: { template: '<div><slot name="title" /><slot /></div>' },
            Button: { template: '<button type="submit" :disabled="disabled"><slot /></button>', props: ['disabled', 'variant', 'size', 'asChild', 'as', 'href'] },
            Input: { template: '<input :id="id" />', props: ['id', 'modelValue', 'class'] },
            Label: { template: '<label><slot /></label>', props: ['for'] },
            Textarea: { template: '<textarea />', props: ['id', 'modelValue', 'rows'] },
            Select: { template: '<div><slot /></div>', props: ['modelValue'] },
            SelectTrigger: { template: '<div><slot /></div>' },
            SelectContent: { template: '<div><slot /></div>' },
            SelectItem: { template: '<div :data-value="value"><slot /></div>', props: ['value'] },
            SelectValue: { template: '<span />', props: ['placeholder'] },
            SlugInput: { template: '<input />', props: ['modelValue'] },
        },
    },
}

describe('Categories/Create', () => {
    it('renders the title "New Category"', () => {
        const wrapper = mount(CategoriesCreate, { props: { parents }, ...globalConfig })
        expect(wrapper.text()).toContain('New Category')
    })

    it('renders Name, Slug, Description fields', () => {
        const wrapper = mount(CategoriesCreate, { props: { parents }, ...globalConfig })
        expect(wrapper.text()).toContain('Name')
        expect(wrapper.text()).toContain('Slug')
        expect(wrapper.text()).toContain('Description')
    })

    it('renders parent category options', () => {
        const wrapper = mount(CategoriesCreate, { props: { parents }, ...globalConfig })
        expect(wrapper.text()).toContain('Root')
        expect(wrapper.text()).toContain('Other')
    })

    it('calls form.post on submit', async () => {
        const wrapper = mount(CategoriesCreate, { props: { parents }, ...globalConfig })
        await wrapper.find('form').trigger('submit')
        expect(mockPost).toHaveBeenCalledWith('/admin.categories.store')
    })

    it('shows validation error when name error is set', () => {
        mockUseForm.mockReturnValueOnce({
            name: '', slug: '', description: '', parent_id: '',
            processing: false, errors: { name: 'The name field is required.' }, post: mockPost,
        })
        const wrapper = mount(CategoriesCreate, { props: { parents }, ...globalConfig })
        expect(wrapper.text()).toContain('The name field is required.')
    })
})
