import { mount } from '@vue/test-utils'
import { describe, it, expect, vi } from 'vitest'
import PostsIndex from '@/Pages/Admin/Posts/Index.vue'
import type { Post, Paginated } from '@/types/models'

const { mockDelete, mockPost } = vi.hoisted(() => ({ mockDelete: vi.fn(), mockPost: vi.fn() }))

vi.mock('@inertiajs/vue3', () => ({
    usePage: () => ({
        url: '/admin/posts',
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
    useAuthStore: () => ({ user: { name: 'Admin', role: 'super_admin', last_login_at: null } }),
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
    default: { name: 'ConfirmModal', template: '<div :data-open="open" />', props: ['open', 'title', 'description'], emits: ['confirm', 'cancel'] },
}))

const author = { id: 1, name: 'Alice', email: 'a@example.com', role: 'super_admin' as const, locale: 'en', last_login_at: null, theme_mode: 'system' as const, timezone: 'UTC' }

const makePost = (overrides: Partial<Post> = {}): Post => ({
    id: 1, user_id: 1, title: 'Hello World', slug: 'hello-world', content: '<p>Hi</p>', content_json: {},
    excerpt: null, status: 'published', meta_title: null, meta_description: null, meta_keywords: null,
    published_at: '2025-01-01 10:00:00', created_at: '2025-01-01 10:00:00', updated_at: '2025-01-01 10:00:00',
    deleted_at: null, featured_image: null, category_id: null, category: null, tags: [], author, parent_id: null, parent: null,
    ...overrides,
})

const posts: Paginated<Post> = {
    data: [
        makePost({ id: 1, title: 'Hello World', category: { id: 1, name: 'Tech', slug: 'tech', description: null, parent_id: null, parent: null, deleted_at: null }, tags: [{ id: 1, name: 'Vue', slug: 'vue' }] }),
        makePost({ id: 2, title: 'Second Post', status: 'draft' }),
    ],
    links: [],
    meta: { current_page: 1, last_page: 1, per_page: 15, total: 2 },
}

const globalConfig = {
    global: {
        stubs: {
            AdminLayout: { template: '<div><slot name="title" /><slot /></div>' },
            Badge: { template: '<span><slot /></span>', props: ['variant'] },
            Button: { template: '<button @click="$emit(\'click\')"><slot /></button>', props: ['variant', 'size', 'asChild'] },
        },
    },
}

describe('Posts/Index', () => {
    it('renders post titles from props', () => {
        const wrapper = mount(PostsIndex, { props: { posts, trash: false }, ...globalConfig })
        expect(wrapper.text()).toContain('Hello World')
        expect(wrapper.text()).toContain('Second Post')
    })

    it('shows "No posts yet" when data is empty', () => {
        const empty: Paginated<Post> = { ...posts, data: [] }
        const wrapper = mount(PostsIndex, { props: { posts: empty, trash: false }, ...globalConfig })
        expect(wrapper.text()).toContain('No posts yet')
    })

    it('shows category name when post has a category', () => {
        const wrapper = mount(PostsIndex, { props: { posts, trash: false }, ...globalConfig })
        expect(wrapper.text()).toContain('Tech')
    })

    it('shows em-dash when post has no category', () => {
        const wrapper = mount(PostsIndex, { props: { posts, trash: false }, ...globalConfig })
        expect(wrapper.text()).toContain('—')
    })

    it('renders tag names as badges', () => {
        const wrapper = mount(PostsIndex, { props: { posts, trash: false }, ...globalConfig })
        expect(wrapper.text()).toContain('Vue')
    })

    it('shows author name', () => {
        const wrapper = mount(PostsIndex, { props: { posts, trash: false }, ...globalConfig })
        expect(wrapper.text()).toContain('Alice')
    })

    it('opens confirm modal when delete is clicked', async () => {
        const wrapper = mount(PostsIndex, { props: { posts, trash: false }, ...globalConfig })
        await wrapper.findAll('button').filter(b => b.text() === 'Delete')[0].trigger('click')
        expect(wrapper.find('[data-open="true"]').exists()).toBe(true)
    })

    it('calls router.delete on confirm', async () => {
        const wrapper = mount(PostsIndex, { props: { posts, trash: false }, ...globalConfig })
        await wrapper.findAll('button').filter(b => b.text() === 'Delete')[0].trigger('click')
        await wrapper.findComponent({ name: 'ConfirmModal' }).vm.$emit('confirm')
        expect(mockDelete).toHaveBeenCalledWith(expect.stringContaining('1'), expect.any(Object))
    })

    it('renders a Duplicate button for each post in non-trash view', () => {
        // Arrange
        const wrapper = mount(PostsIndex, { props: { posts, trash: false }, ...globalConfig })

        // Assert
        const duplicateButtons = wrapper.findAll('button').filter(b => b.text() === 'Duplicate')
        expect(duplicateButtons).toHaveLength(posts.data.length)
    })

    it('does not render Duplicate button in trash view', () => {
        // Arrange
        const wrapper = mount(PostsIndex, { props: { posts, trash: true }, ...globalConfig })

        // Assert
        const duplicateButtons = wrapper.findAll('button').filter(b => b.text() === 'Duplicate')
        expect(duplicateButtons).toHaveLength(0)
    })

    it('calls router.post with duplicate route when Duplicate is clicked', async () => {
        // Arrange
        const wrapper = mount(PostsIndex, { props: { posts, trash: false }, ...globalConfig })

        // Act
        await wrapper.findAll('button').filter(b => b.text() === 'Duplicate')[0].trigger('click')

        // Assert
        expect(mockPost).toHaveBeenCalledWith(expect.stringContaining('1'))
    })
})
