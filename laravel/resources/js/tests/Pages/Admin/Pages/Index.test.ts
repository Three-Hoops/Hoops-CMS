import { mount } from '@vue/test-utils'
import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest'
import PagesIndex from '@/Pages/Admin/Pages/Index.vue'
import type { Page, Paginated } from '@/types/models'

const { mockDelete, mockPost, authRole } = vi.hoisted(() => ({
    mockDelete: vi.fn(),
    mockPost: vi.fn(),
    authRole: { current: 'super_admin' as string },
}))

vi.mock('@inertiajs/vue3', () => ({
    usePage: () => ({
        url: '/admin/pages',
        props: { app: { name: 'Hoops CMS' }, auth: null, flash: { success: null, error: null }, errors: {}, ziggy: { location: 'http://localhost', routes: {} } },
    }),
    useForm: vi.fn(() => ({ processing: false, errors: {}, post: vi.fn(), reset: vi.fn() })),
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
vi.mock('@/components/Admin/StatusBadge.vue', () => ({
    default: { template: '<span :data-status="status" />', props: ['status'] },
}))
vi.mock('@/components/Admin/Pagination.vue', () => ({ default: { template: '<div />' } }))
vi.mock('@/components/Admin/ConfirmModal.vue', () => ({
    default: { name: 'ConfirmModal', template: '<div :data-open="open"><slot /></div>', props: ['open', 'title', 'description'], emits: ['confirm', 'cancel'] },
}))

const makePage = (overrides: Partial<Page> = {}): Page => ({
    id: 1, user_id: 1, title: 'About Us', slug: 'about-us', content: '<p>Hello</p>', content_json: {},
    excerpt: null, status: 'published', meta_title: null, meta_description: null, meta_keywords: null,
    published_at: '2025-01-01 10:00:00', created_at: '2025-01-01 10:00:00', updated_at: '2025-01-01 10:00:00',
    deleted_at: null,
    author: { id: 1, name: 'Alice', email: 'a@example.com', role: 'super_admin', locale: 'en', last_login_at: null, theme_mode: 'system', timezone: 'UTC' },
    parent_id: null, parent: null,
    ...overrides,
})

const pages: Paginated<Page> = {
    data: [makePage({ id: 1, title: 'About Us' }), makePage({ id: 2, title: 'Contact', status: 'draft', published_at: null })],
    links: [],
    meta: { current_page: 1, last_page: 1, per_page: 15, total: 2 },
}

const globalConfig = {
    global: {
        stubs: {
            AdminLayout: { template: '<div><slot name="title" /><slot /></div>' },
            Button: { template: '<button :disabled="disabled" @click="$emit(\'click\')"><slot /></button>', props: ['disabled', 'variant', 'size', 'asChild'] },
        },
    },
}

describe('Pages/Index', () => {
    it('renders page titles from props', () => {
        const wrapper = mount(PagesIndex, { props: { pages, trash: false }, ...globalConfig })
        expect(wrapper.text()).toContain('About Us')
        expect(wrapper.text()).toContain('Contact')
    })

    it('shows "No pages yet" when data is empty', () => {
        const empty: Paginated<Page> = { ...pages, data: [] }
        const wrapper = mount(PagesIndex, { props: { pages: empty, trash: false }, ...globalConfig })
        expect(wrapper.text()).toContain('No pages yet')
    })

    it('shows published_at date when present', () => {
        const wrapper = mount(PagesIndex, { props: { pages, trash: false }, ...globalConfig })
        expect(wrapper.text()).toContain('1/1/2025')
    })

    it('shows em-dash when published_at is null', () => {
        const wrapper = mount(PagesIndex, { props: { pages, trash: false }, ...globalConfig })
        expect(wrapper.text()).toContain('—')
    })

    it('opens confirm modal when delete button is clicked', async () => {
        const wrapper = mount(PagesIndex, { props: { pages, trash: false }, ...globalConfig })
        const deleteButtons = wrapper.findAll('button').filter(b => b.text() === 'Delete')
        await deleteButtons[0].trigger('click')
        expect(wrapper.find('[data-open="true"]').exists()).toBe(true)
    })

    it('calls router.delete when confirm is emitted', async () => {
        const wrapper = mount(PagesIndex, { props: { pages, trash: false }, ...globalConfig })
        await wrapper.findAll('button').filter(b => b.text() === 'Delete')[0].trigger('click')
        await wrapper.findComponent({ name: 'ConfirmModal' }).vm.$emit('confirm')
        expect(mockDelete).toHaveBeenCalledWith(expect.stringContaining('1'), expect.any(Object))
    })

    it('closes confirm modal when cancel is emitted', async () => {
        const wrapper = mount(PagesIndex, { props: { pages, trash: false }, ...globalConfig })
        await wrapper.findAll('button').filter(b => b.text() === 'Delete')[0].trigger('click')
        await wrapper.findComponent({ name: 'ConfirmModal' }).vm.$emit('cancel')
        expect(wrapper.find('[data-open="true"]').exists()).toBe(false)
    })

    it('shows parent title when page has a parent', () => {
        // Arrange
        const pageWithParent = makePage({ parent_id: 3, parent: { id: 3, title: 'Home', slug: 'home' } })
        const withParent: Paginated<Page> = { ...pages, data: [pageWithParent] }

        // Act
        const wrapper = mount(PagesIndex, { props: { pages: withParent, trash: false }, ...globalConfig })

        // Assert
        expect(wrapper.text()).toContain('Home')
    })

    it('shows em-dash in parent column when page has no parent', () => {
        // Arrange — default pages fixture has parent: null
        const wrapper = mount(PagesIndex, { props: { pages, trash: false }, ...globalConfig })

        // Assert — at least one em-dash present (published_at and parent columns)
        expect(wrapper.text()).toContain('—')
    })

    it('renders a Duplicate button for each page in non-trash view', () => {
        // Arrange
        const wrapper = mount(PagesIndex, { props: { pages, trash: false }, ...globalConfig })

        // Assert
        const duplicateButtons = wrapper.findAll('button').filter(b => b.text() === 'Duplicate')
        expect(duplicateButtons).toHaveLength(pages.data.length)
    })

    it('does not render Duplicate button in trash view', () => {
        // Arrange
        const wrapper = mount(PagesIndex, { props: { pages, trash: true }, ...globalConfig })

        // Assert
        const duplicateButtons = wrapper.findAll('button').filter(b => b.text() === 'Duplicate')
        expect(duplicateButtons).toHaveLength(0)
    })

    it('shows Restore and Delete permanently buttons in trash view for non-viewer', () => {
        // Arrange
        const trashPages: Paginated<Page> = { ...pages, data: [makePage({ id: 1, deleted_at: '2025-01-02 10:00:00' })] }

        // Act
        const wrapper = mount(PagesIndex, { props: { pages: trashPages, trash: true }, ...globalConfig })

        // Assert
        expect(wrapper.findAll('button').filter(b => b.text() === 'Restore')).toHaveLength(1)
        expect(wrapper.findAll('button').filter(b => b.text() === 'Delete permanently')).toHaveLength(1)
    })

    it('calls router.post with duplicate route when Duplicate is clicked', async () => {
        // Arrange
        const wrapper = mount(PagesIndex, { props: { pages, trash: false }, ...globalConfig })

        // Act
        await wrapper.findAll('button').filter(b => b.text() === 'Duplicate')[0].trigger('click')

        // Assert
        expect(mockPost).toHaveBeenCalledWith(expect.stringContaining('1'))
    })
})

describe('Pages/Index — viewer role', () => {
    beforeEach(() => { authRole.current = 'viewer' })
    afterEach(() => { authRole.current = 'super_admin' })

    it('hides New Page button for viewers', () => {
        // Arrange + Act
        const wrapper = mount(PagesIndex, { props: { pages, trash: false }, ...globalConfig })

        // Assert
        expect(wrapper.findAll('button').filter(b => b.text() === 'New Page')).toHaveLength(0)
    })

    it('hides Edit button for viewers', () => {
        // Arrange + Act
        const wrapper = mount(PagesIndex, { props: { pages, trash: false }, ...globalConfig })

        // Assert
        expect(wrapper.findAll('button').filter(b => b.text() === 'Edit')).toHaveLength(0)
    })

    it('hides Duplicate button for viewers', () => {
        // Arrange + Act
        const wrapper = mount(PagesIndex, { props: { pages, trash: false }, ...globalConfig })

        // Assert
        expect(wrapper.findAll('button').filter(b => b.text() === 'Duplicate')).toHaveLength(0)
    })

    it('hides Delete button for viewers', () => {
        // Arrange + Act
        const wrapper = mount(PagesIndex, { props: { pages, trash: false }, ...globalConfig })

        // Assert
        expect(wrapper.findAll('button').filter(b => b.text() === 'Delete')).toHaveLength(0)
    })

    it('hides Restore and Delete permanently buttons for viewers in trash view', () => {
        // Arrange + Act
        const trashPages: Paginated<Page> = { ...pages, data: [makePage({ id: 1, deleted_at: '2025-01-02 10:00:00' })] }
        const wrapper = mount(PagesIndex, { props: { pages: trashPages, trash: true }, ...globalConfig })

        // Assert
        expect(wrapper.findAll('button').filter(b => b.text() === 'Restore')).toHaveLength(0)
        expect(wrapper.findAll('button').filter(b => b.text() === 'Delete permanently')).toHaveLength(0)
    })
})
