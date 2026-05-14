import { mount } from '@vue/test-utils'
import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest'
import CategoriesIndex from '@/Pages/Admin/Categories/Index.vue'
import type { Category, Paginated } from '@/types/models'

const { mockDelete, authRole } = vi.hoisted(() => ({
    mockDelete: vi.fn(),
    authRole: { current: 'super_admin' as string },
}))

vi.mock('@inertiajs/vue3', () => ({
    usePage: () => ({
        url: '/admin/categories',
        props: { app: { name: 'Hoops CMS' }, auth: null, flash: { success: null, error: null }, errors: {}, ziggy: { location: 'http://localhost', routes: {} } },
    }),
    useForm: vi.fn(() => ({ processing: false, errors: {}, post: vi.fn() })),
    router: { delete: mockDelete, post: vi.fn(), get: vi.fn() },
    Head: { template: '<div />' },
    Link: { template: '<a :href="href"><slot /></a>', props: ['href'] },
}))

vi.mock('ziggy-js', () => ({
    route: (name: string, id?: number) => id ? `/${name}/${id}` : `/${name}`,
}))

vi.mock('@/stores/useAuthStore', () => ({
    useAuthStore: () => ({
        user: { name: 'Admin', role: authRole.current, last_login_at: null },
        hasRole: (roles: string[]) => roles.includes(authRole.current),
    }),
}))

vi.mock('@/composables/useThemeMode', () => ({
    useThemeMode: () => ({ themeMode: { value: 'system' }, setTheme: vi.fn() }),
}))

vi.mock('@/components/Admin/FlashBanner.vue', () => ({ default: { template: '<div />' } }))
vi.mock('@/components/Admin/Pagination.vue', () => ({ default: { template: '<div />' } }))
vi.mock('@/components/Admin/ConfirmModal.vue', () => ({
    default: { name: 'ConfirmModal', template: '<div :data-open="open" />', props: ['open', 'title', 'description'], emits: ['confirm', 'cancel'] },
}))

const parent: Category = { id: 1, name: 'Root', slug: 'root', description: null, parent_id: null, parent: null, deleted_at: null }
const categories: Paginated<Category> = {
    data: [
        { id: 1, name: 'Root', slug: 'root', description: null, parent_id: null, parent: null, deleted_at: null },
        { id: 2, name: 'Child', slug: 'child', description: null, parent_id: 1, parent, deleted_at: null },
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
        const wrapper = mount(CategoriesIndex, { props: { categories, trash: false }, ...globalConfig })
        expect(wrapper.text()).toContain('Root')
        expect(wrapper.text()).toContain('Child')
    })

    it('shows "No categories yet" when empty', () => {
        const empty: Paginated<Category> = { ...categories, data: [] }
        const wrapper = mount(CategoriesIndex, { props: { categories: empty, trash: false }, ...globalConfig })
        expect(wrapper.text()).toContain('No categories yet')
    })

    it('shows parent name for child categories', () => {
        const wrapper = mount(CategoriesIndex, { props: { categories, trash: false }, ...globalConfig })
        expect(wrapper.text()).toContain('Root')
    })

    it('shows em-dash for top-level categories', () => {
        const wrapper = mount(CategoriesIndex, { props: { categories, trash: false }, ...globalConfig })
        expect(wrapper.text()).toContain('—')
    })

    it('opens confirm modal when delete is clicked', async () => {
        const wrapper = mount(CategoriesIndex, { props: { categories, trash: false }, ...globalConfig })
        await wrapper.findAll('button').filter(b => b.text() === 'Delete')[0].trigger('click')
        expect(wrapper.find('[data-open="true"]').exists()).toBe(true)
    })

    it('calls router.delete on confirm', async () => {
        const wrapper = mount(CategoriesIndex, { props: { categories, trash: false }, ...globalConfig })
        await wrapper.findAll('button').filter(b => b.text() === 'Delete')[0].trigger('click')
        await wrapper.findComponent({ name: 'ConfirmModal' }).vm.$emit('confirm')
        expect(mockDelete).toHaveBeenCalledWith(expect.stringContaining('1'), expect.any(Object))
    })

    it('closes modal on cancel', async () => {
        const wrapper = mount(CategoriesIndex, { props: { categories, trash: false }, ...globalConfig })
        await wrapper.findAll('button').filter(b => b.text() === 'Delete')[0].trigger('click')
        await wrapper.findComponent({ name: 'ConfirmModal' }).vm.$emit('cancel')
        expect(wrapper.find('[data-open="true"]').exists()).toBe(false)
    })
})

describe('Categories/Index — viewer role', () => {
    beforeEach(() => { authRole.current = 'viewer' })
    afterEach(() => { authRole.current = 'super_admin' })

    it('hides New Category button for viewers', () => {
        // Arrange + Act
        const wrapper = mount(CategoriesIndex, { props: { categories, trash: false }, ...globalConfig })

        // Assert
        expect(wrapper.text()).not.toContain('New Category')
    })

    it('hides Edit button for viewers', () => {
        // Arrange + Act
        const wrapper = mount(CategoriesIndex, { props: { categories, trash: false }, ...globalConfig })

        // Assert
        expect(wrapper.findAll('button').filter(b => b.text() === 'Edit')).toHaveLength(0)
    })

    it('hides Delete button for viewers', () => {
        // Arrange + Act
        const wrapper = mount(CategoriesIndex, { props: { categories, trash: false }, ...globalConfig })

        // Assert
        expect(wrapper.findAll('button').filter(b => b.text() === 'Delete')).toHaveLength(0)
    })
})
