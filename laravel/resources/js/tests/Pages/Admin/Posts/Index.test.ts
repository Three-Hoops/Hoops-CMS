import { mount } from '@vue/test-utils'
import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest'
import PostsIndex from '@/Pages/Admin/Posts/Index.vue'
import type { Post, Paginated } from '@/types/models'

const { mockDelete, mockPost, authRole } = vi.hoisted(() => ({
    mockDelete: vi.fn(),
    mockPost: vi.fn(),
    authRole: { current: 'super_admin' as string },
}))

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
    default: { name: 'ConfirmModal', template: '<div :data-open="open" />', props: ['open', 'title', 'description'], emits: ['confirm', 'cancel'] },
}))

const author = { id: 1, name: 'Alice', email: 'a@example.com', role: 'super_admin' as const, locale: 'en', last_login_at: null, theme_mode: 'system' as const, timezone: 'UTC' }

const makePost = (overrides: Partial<Post> = {}): Post => ({
    id: 1, user_id: 1, title: 'Hello World', slug: 'hello-world', content: '<p>Hi</p>', content_json: {},
    excerpt: null, status: 'published', meta_title: null, meta_description: null, meta_keywords: null, og_title: null, og_description: null,
    published_at: '2025-01-01 10:00:00', created_at: '2025-01-01 10:00:00', updated_at: '2025-01-01 10:00:00',
    deleted_at: null, featured_image: null, is_featured: false, category_id: null, category: null, tags: [], author, parent_id: null, parent: null,
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

    it('shows Restore and Delete permanently buttons in trash view for non-viewer', () => {
        // Arrange
        const trashPosts: Paginated<Post> = { ...posts, data: [makePost({ id: 1, deleted_at: '2025-01-02 10:00:00' })] }

        // Act
        const wrapper = mount(PostsIndex, { props: { posts: trashPosts, trash: true }, ...globalConfig })

        // Assert
        expect(wrapper.findAll('button').filter(b => b.text() === 'Restore')).toHaveLength(1)
        expect(wrapper.findAll('button').filter(b => b.text() === 'Delete permanently')).toHaveLength(1)
    })

    it('calls router.post with duplicate route when Duplicate is clicked', async () => {
        // Arrange
        const wrapper = mount(PostsIndex, { props: { posts, trash: false }, ...globalConfig })

        // Act
        await wrapper.findAll('button').filter(b => b.text() === 'Duplicate')[0].trigger('click')

        // Assert
        expect(mockPost).toHaveBeenCalledWith(expect.stringContaining('1'))
    })

    it('shows a star icon when post is featured', () => {
        // Arrange
        const featuredPosts: Paginated<Post> = {
            ...posts,
            data: [makePost({ id: 1, title: 'Featured Post', is_featured: true })],
        }

        // Act
        const wrapper = mount(PostsIndex, { props: { posts: featuredPosts, trash: false }, ...globalConfig })

        // Assert
        expect(wrapper.text()).toContain('★')
    })

    it('does not show a star icon when post is not featured', () => {
        // Arrange
        const nonFeaturedPosts: Paginated<Post> = {
            ...posts,
            data: [makePost({ id: 1, title: 'Normal Post', is_featured: false })],
        }

        // Act
        const wrapper = mount(PostsIndex, { props: { posts: nonFeaturedPosts, trash: false }, ...globalConfig })

        // Assert
        expect(wrapper.text()).not.toContain('★')
    })
})

describe('Posts/Index — viewer role', () => {
    beforeEach(() => { authRole.current = 'viewer' })
    afterEach(() => { authRole.current = 'super_admin' })

    it('hides New Post button for viewers', () => {
        // Arrange + Act
        const wrapper = mount(PostsIndex, { props: { posts, trash: false }, ...globalConfig })

        // Assert
        expect(wrapper.findAll('button').filter(b => b.text() === 'New Post')).toHaveLength(0)
    })

    it('hides Edit button for viewers', () => {
        // Arrange + Act
        const wrapper = mount(PostsIndex, { props: { posts, trash: false }, ...globalConfig })

        // Assert
        expect(wrapper.findAll('button').filter(b => b.text() === 'Edit')).toHaveLength(0)
    })

    it('hides Duplicate button for viewers', () => {
        // Arrange + Act
        const wrapper = mount(PostsIndex, { props: { posts, trash: false }, ...globalConfig })

        // Assert
        expect(wrapper.findAll('button').filter(b => b.text() === 'Duplicate')).toHaveLength(0)
    })

    it('hides Delete button for viewers', () => {
        // Arrange + Act
        const wrapper = mount(PostsIndex, { props: { posts, trash: false }, ...globalConfig })

        // Assert
        expect(wrapper.findAll('button').filter(b => b.text() === 'Delete')).toHaveLength(0)
    })

    it('hides Restore and Delete permanently buttons for viewers in trash view', () => {
        // Arrange + Act
        const trashPosts: Paginated<Post> = { ...posts, data: [makePost({ id: 1, deleted_at: '2025-01-02 10:00:00' })] }
        const wrapper = mount(PostsIndex, { props: { posts: trashPosts, trash: true }, ...globalConfig })

        // Assert
        expect(wrapper.findAll('button').filter(b => b.text() === 'Restore')).toHaveLength(0)
        expect(wrapper.findAll('button').filter(b => b.text() === 'Delete permanently')).toHaveLength(0)
    })
})
