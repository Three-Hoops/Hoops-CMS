import { mount } from '@vue/test-utils'
import { describe, it, expect, vi } from 'vitest'
import CategoriesEdit from '@/Pages/Admin/Categories/Edit.vue'
import type { Category } from '@/types/models'

const { mockPut } = vi.hoisted(() => ({ mockPut: vi.fn() }))

const category: Category = { id: 3, name: 'Technology', slug: 'technology', description: 'Tech stuff', parent_id: 1, parent: null }

const parents: Category[] = [
    { id: 1, name: 'Root', slug: 'root', description: null, parent_id: null, parent: null },
]

vi.mock('@inertiajs/vue3', () => ({
    usePage: () => ({
        url: '/admin/categories/3/edit',
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

vi.mock('@/components/Admin/FlashBanner.vue', () => ({ default: { template: '<div />' } }))

const globalConfig = {
    global: {
        stubs: {
            AdminLayout: { template: '<div><slot name="title" /><slot /></div>' },
            Button: { template: '<button type="submit" :disabled="disabled"><slot /></button>', props: ['disabled', 'variant', 'size', 'asChild', 'as', 'href'] },
            Input: { template: '<input :id="id" :value="modelValue" />', props: ['id', 'modelValue', 'class'] },
            Label: { template: '<label><slot /></label>', props: ['for'] },
            Textarea: { template: '<textarea :value="modelValue" />', props: ['id', 'modelValue', 'rows'] },
            Select: { template: '<div><slot /></div>', props: ['modelValue'] },
            SelectTrigger: { template: '<div><slot /></div>' },
            SelectContent: { template: '<div><slot /></div>' },
            SelectItem: { template: '<div :data-value="value"><slot /></div>', props: ['value'] },
            SelectValue: { template: '<span />', props: ['placeholder'] },
            SlugInput: { template: '<input :value="modelValue" />', props: ['modelValue'] },
        },
    },
}

describe('Categories/Edit', () => {
    it('renders the title "Edit Category"', () => {
        const wrapper = mount(CategoriesEdit, { props: { category, parents }, ...globalConfig })
        expect(wrapper.text()).toContain('Edit Category')
    })

    it('pre-populates the name field', () => {
        const wrapper = mount(CategoriesEdit, { props: { category, parents }, ...globalConfig })
        expect(wrapper.findAll('input').some(i => i.attributes('value') === 'Technology')).toBe(true)
    })

    it('pre-populates the slug field', () => {
        const wrapper = mount(CategoriesEdit, { props: { category, parents }, ...globalConfig })
        expect(wrapper.findAll('input').some(i => i.attributes('value') === 'technology')).toBe(true)
    })

    it('renders parent options', () => {
        const wrapper = mount(CategoriesEdit, { props: { category, parents }, ...globalConfig })
        expect(wrapper.text()).toContain('Root')
    })

    it('calls form.put on submit', async () => {
        const wrapper = mount(CategoriesEdit, { props: { category, parents }, ...globalConfig })
        await wrapper.find('form').trigger('submit')
        expect(mockPut).toHaveBeenCalledWith('/admin.categories.update/3')
    })
})
