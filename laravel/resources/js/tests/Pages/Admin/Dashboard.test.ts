import { mount } from '@vue/test-utils'
import { describe, it, expect, vi } from 'vitest'
import Dashboard from '@/Pages/Admin/Dashboard.vue'
import type { DashboardStats } from '@/types/models'

vi.mock('@inertiajs/vue3', () => ({
    usePage: () => ({
        url: '/admin',
        props: {
            app: { name: 'Hoops CMS' },
            auth: { name: 'Admin', role: 'super_admin', last_login_at: null },
            flash: { success: null, error: null },
            errors: {},
            ziggy: { location: 'http://localhost', routes: {} },
        },
    }),
    useForm: () => ({ post: vi.fn(), processing: false }),
    Head: { template: '<div />' },
    Link: { template: '<a><slot /></a>' },
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

vi.mock('@/components/Admin/FlashBanner.vue', () => ({
    default: { template: '<div />' },
}))

vi.mock('@/components/Admin/StatusBadge.vue', () => ({
    default: { template: '<span :data-status="status" />', props: ['status'] },
}))

const stats: DashboardStats = {
    posts: { total: 10, published: 7, draft: 3 },
    pages: { total: 4, published: 2, draft: 2 },
    categories: 5,
    tags: 12,
    recent_posts: [
        {
            id: 1,
            title: 'Hello World',
            status: 'published',
            published_at: '2025-01-01 10:00:00',
            created_at: '2025-01-01 10:00:00',
            user_id: 1,
            author: { id: 1, name: 'Alice', email: 'alice@example.com', role: 'super_admin', locale: 'en', last_login_at: null, theme_mode: 'system', timezone: 'UTC' },
        },
    ],
}

const globalConfig = {
    global: {
        stubs: {
            AdminLayout: { template: '<div><slot name="title" /><slot /></div>' },
            Card: { template: '<div><slot /></div>' },
            CardHeader: { template: '<div><slot /></div>' },
            CardTitle: { template: '<div><slot /></div>' },
            CardContent: { template: '<div><slot /></div>' },
        },
    },
}

describe('Dashboard', () => {
    it('displays the total post count', () => {
        // Arrange + Act
        const wrapper = mount(Dashboard, { props: { stats }, ...globalConfig })

        // Assert
        expect(wrapper.text()).toContain('10')
    })

    it('shows published and draft breakdown for posts', () => {
        // Arrange + Act
        const wrapper = mount(Dashboard, { props: { stats }, ...globalConfig })

        // Assert
        expect(wrapper.text()).toContain('7 published')
        expect(wrapper.text()).toContain('3 draft')
    })

    it('displays categories and tags counts', () => {
        // Arrange + Act
        const wrapper = mount(Dashboard, { props: { stats }, ...globalConfig })

        // Assert
        expect(wrapper.text()).toContain('5')
        expect(wrapper.text()).toContain('12')
    })

    it('renders recent posts table with post title', () => {
        // Arrange + Act
        const wrapper = mount(Dashboard, { props: { stats }, ...globalConfig })

        // Assert
        expect(wrapper.text()).toContain('Hello World')
    })

    it('shows "No posts yet" when recent_posts is empty', () => {
        // Arrange + Act
        const wrapper = mount(Dashboard, {
            props: { stats: { ...stats, recent_posts: [] } },
            ...globalConfig,
        })

        // Assert
        expect(wrapper.text()).toContain('No posts yet')
    })
})
