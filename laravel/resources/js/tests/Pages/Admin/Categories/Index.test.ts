import { mount } from '@vue/test-utils'
import { describe, it, expect, vi } from 'vitest'
import CategoriesIndex from '@/Pages/Admin/Categories/Index.vue'
import type { Category, Paginated } from '@/types/models'

const { mockDelete } = vi.hoisted(() => ({ mockDelete: vi.fn() }))

vi.mock('@inertiajs/vue3', () => ({
    usePage: () => ({
        url: '/admin/categories',
        props: { app: { name: 'Hoops CMS' }, auth: null, flash: { success: null, error: null }, errors: {}, ziggy: { location: 'http://localhost', routes: {} } },
    }),
    useForm: vi.fn(() => ({ processing: false, errors: {}, post: vi.fn() })),
    router: { delete: mockDelete },
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
vi.mock('@/components/Admin/Pagination.vue', () => ({ default: { template: '<div />' } }))
vi.mock('@/components/Admin/ConfirmModal.vue', () => ({
    default: { name: 'ConfirmModal', template: '<div :data-open="open" />', props: ['open', 'title', 'description'], emits: ['confirm', 'cancel'] },
}))

const parent: Category = { id: 1, name: 'Root', slug: 'root', description: null, parent_id: null, parent: null }
const categories: Paginated<Category> = {
    data: [
        { id: 1, name: 'Root', slug: 'root', description: null, parent_id: null, parent: null },
        { id: 2, name: 'Child', slug: 'child', description: null, parent_id: 1, parent },
    ],
    links: [],
    meta: { current_page: 1, last_page: 1, per_page: 15, total: 2 },
}

const globalConfig = {
    global: {
        stubs: {
            AdminLayout: { template: '<div><slot name="title" /><slot /></div>' },
            Button: { template: '<button @click="$emit(\'click\')"><slot /></button>', props: ['variant', 'size', 'asChild'] },
        },
    },
}

describe('Categories/Index', () => {
    it('renders category names', () => {
        const wrapper = mount(CategoriesIndex, { props: { categories }, ...globalConfig })
        expect(wrapper.text()).toContain('Root')
        expect(wrapper.text()).toContain('Child')
    })

    it('shows "No categories yet" when empty', () => {
        const empty: Paginated<Category> = { ...categories, data: [] }
        const wrapper = mount(CategoriesIndex, { props: { categories: empty }, ...globalConfig })
        expect(wrapper.text()).toContain('No categories yet')
    })

    it('shows parent name for child categories', () => {
        const wrapper = mount(CategoriesIndex, { props: { categories }, ...globalConfig })
        expect(wrapper.text()).toContain('Root')
    })

    it('shows em-dash for top-level categories', () => {
        const wrapper = mount(CategoriesIndex, { props: { categories }, ...globalConfig })
        expect(wrapper.text()).toContain('—')
    })

    it('opens confirm modal when delete is clicked', async () => {
        const wrapper = mount(CategoriesIndex, { props: { categories }, ...globalConfig })
        await wrapper.findAll('button').filter(b => b.text() === 'Delete')[0].trigger('click')
        expect(wrapper.find('[data-open="true"]').exists()).toBe(true)
    })

    it('calls router.delete on confirm', async () => {
        const wrapper = mount(CategoriesIndex, { props: { categories }, ...globalConfig })
        await wrapper.findAll('button').filter(b => b.text() === 'Delete')[0].trigger('click')
        await wrapper.findComponent({ name: 'ConfirmModal' }).vm.$emit('confirm')
        expect(mockDelete).toHaveBeenCalledWith(expect.stringContaining('1'), expect.any(Object))
    })

    it('closes modal on cancel', async () => {
        const wrapper = mount(CategoriesIndex, { props: { categories }, ...globalConfig })
        await wrapper.findAll('button').filter(b => b.text() === 'Delete')[0].trigger('click')
        await wrapper.findComponent({ name: 'ConfirmModal' }).vm.$emit('cancel')
        expect(wrapper.find('[data-open="true"]').exists()).toBe(false)
    })
})
