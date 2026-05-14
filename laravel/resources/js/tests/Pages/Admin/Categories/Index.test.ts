import { mount } from '@vue/test-utils'
import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest'
import CategoriesIndex from '@/Pages/Admin/Categories/Index.vue'
import type { Category, Paginated } from '@/types/models'

const { mockDelete, mockPost, authRole } = vi.hoisted(() => ({
    mockDelete: vi.fn(),
    mockPost: vi.fn(),
    authRole: { current: 'super_admin' as string },
}))

vi.mock('@inertiajs/vue3', () => ({
    usePage: () => ({
        url: '/admin/categories',
        props: { app: { name: 'Hoops CMS' }, auth: null, flash: { success: null, error: null }, errors: {}, ziggy: { location: 'http://localhost', routes: {} } },
    }),
    useForm: vi.fn(() => ({ processing: false, errors: {}, post: vi.fn() })),
    router: { delete: mockDelete, post: mockPost, get: vi.fn() },
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
    default: { name: 'ConfirmModal', template: '<div :data-open="open" />', props: ['open', 'title', 'description'], emits: ['confirm', 'cancel'], },
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

    it('shows Restore and Delete permanently buttons in trash view for non-viewer', () => {
        // Arrange
        const trashCategories: Paginated<Category> = {
            ...categories,
            data: [{ id: 1, name: 'Root', slug: 'root', description: null, parent_id: null, parent: null, deleted_at: '2025-01-02 10:00:00' }],
        }

        // Act
        const wrapper = mount(CategoriesIndex, { props: { categories: trashCategories, trash: true }, ...globalConfig })

        // Assert
        expect(wrapper.findAll('button').filter(b => b.text() === 'Restore')).toHaveLength(1)
        expect(wrapper.findAll('button').filter(b => b.text() === 'Delete permanently')).toHaveLength(1)
    })
})

describe('Categories/Index — bulk selection', () => {
    it('renders a checkbox in each row for non-viewers', () => {
        // Arrange + Act
        const wrapper = mount(CategoriesIndex, { props: { categories, trash: false }, ...globalConfig })

        // Assert — one per row + one in header
        expect(wrapper.findAll('input[type="checkbox"]').length).toBe(categories.data.length + 1)
    })

    it('does not render checkboxes for viewers', () => {
        // Arrange
        authRole.current = 'viewer'
        const wrapper = mount(CategoriesIndex, { props: { categories, trash: false }, ...globalConfig })
        authRole.current = 'super_admin'

        // Assert
        expect(wrapper.findAll('input[type="checkbox"]')).toHaveLength(0)
    })

    it('bulk toolbar is hidden when nothing selected', () => {
        // Arrange + Act
        const wrapper = mount(CategoriesIndex, { props: { categories, trash: false }, ...globalConfig })

        // Assert — Delete button only appears in rows normally, not in a floating toolbar
        const allText = wrapper.text()
        expect(allText).not.toContain('selected')
    })

    it('bulk toolbar appears after selecting a row', async () => {
        // Arrange
        const wrapper = mount(CategoriesIndex, { props: { categories, trash: false }, ...globalConfig })

        // Act
        await wrapper.findAll('input[type="checkbox"]')[1].trigger('change')

        // Assert — selection count label appears
        expect(wrapper.text()).toContain('1 selected')
    })

    it('confirming bulk delete calls router.post with correct payload', async () => {
        // Arrange
        const wrapper = mount(CategoriesIndex, { props: { categories, trash: false }, ...globalConfig })
        await wrapper.findAll('input[type="checkbox"]')[1].trigger('change')

        // find the Delete button inside the bulk toolbar (not a row action)
        const deleteButtons = wrapper.findAll('button').filter(b => b.text() === 'Delete')
        await deleteButtons[0].trigger('click')

        // Act
        const modals = wrapper.findAllComponents({ name: 'ConfirmModal' })
        const openModal = modals.find(m => m.props('open') === true)
        await openModal!.vm.$emit('confirm')

        // Assert
        expect(mockPost).toHaveBeenCalledWith(
            expect.stringContaining('bulk'),
            expect.objectContaining({ action: 'delete', ids: expect.arrayContaining([expect.any(Number)]) }),
            expect.any(Object),
        )
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

    it('hides Restore and Delete permanently buttons for viewers in trash view', () => {
        // Arrange + Act
        const trashCategories: Paginated<Category> = {
            ...categories,
            data: [{ id: 1, name: 'Root', slug: 'root', description: null, parent_id: null, parent: null, deleted_at: '2025-01-02 10:00:00' }],
        }
        const wrapper = mount(CategoriesIndex, { props: { categories: trashCategories, trash: true }, ...globalConfig })

        // Assert
        expect(wrapper.findAll('button').filter(b => b.text() === 'Restore')).toHaveLength(0)
        expect(wrapper.findAll('button').filter(b => b.text() === 'Delete permanently')).toHaveLength(0)
    })
})
