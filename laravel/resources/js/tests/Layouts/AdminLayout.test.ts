import { mount, flushPromises } from '@vue/test-utils'
import { describe, it, expect, vi, beforeEach } from 'vitest'
import { ref, nextTick } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const mockThemeMode = ref<'light' | 'dark' | 'system'>('system')
const mockSetTheme = vi.fn()

const currentUrl = ref('/admin')

vi.mock('@inertiajs/vue3', () => ({
    usePage: () => ({
        get url() { return currentUrl.value },
        props: {
            app: { name: 'Hoops CMS' },
            auth: { name: 'Admin', role: 'super_admin', last_login_at: null },
            flash: { success: null, error: null },
            ziggy: { location: 'http://localhost', routes: {} },
        },
    }),
    useForm: () => ({ post: vi.fn(), processing: false }),
    Head: { template: '<head><slot /></head>' },
    Link: { template: '<a><slot /></a>' },
}))

vi.mock('ziggy-js', () => ({
    route: (name: string) => `/${name}`,
}))

vi.mock('@/components/Admin/FlashBanner.vue', () => ({
    default: { template: '<div />' },
}))

vi.mock('@/stores/useAuthStore', () => ({
    useAuthStore: () => ({
        user: { name: 'Admin', role: 'super_admin', last_login_at: null },
    }),
}))

vi.mock('@/composables/useThemeMode', () => ({
    useThemeMode: () => ({
        themeMode: mockThemeMode,
        setTheme: mockSetTheme,
    }),
}))

const globalConfig = {
    global: {
        plugins: [],
        mocks: { $page: { url: '/admin' } },
        stubs: { Link: { template: '<a><slot /></a>' } },
    },
    slots: { default: '<div />' },
}

describe('AdminLayout', () => {
    beforeEach(() => {
        currentUrl.value = '/admin'
        mockThemeMode.value = 'system'
        mockSetTheme.mockReset()
    })

    it('hides the sidebar by default', () => {
        // Arrange
        const wrapper = mount(AdminLayout, globalConfig)

        // Assert
        expect(wrapper.find('aside').classes()).toContain('-translate-x-full')
    })

    it('shows the sidebar after clicking the hamburger button', async () => {
        // Arrange
        const wrapper = mount(AdminLayout, globalConfig)

        // Act
        await wrapper.find('button[class*="lg:hidden"]').trigger('click')

        // Assert
        expect(wrapper.find('aside').classes()).toContain('translate-x-0')
        expect(wrapper.find('aside').classes()).not.toContain('-translate-x-full')
    })

    it('shows the backdrop when sidebar is open', async () => {
        // Arrange
        const wrapper = mount(AdminLayout, globalConfig)

        // Act
        await wrapper.find('button[class*="lg:hidden"]').trigger('click')

        // Assert
        expect(wrapper.find('[class*="bg-black"]').exists()).toBe(true)
    })

    it('closes the sidebar when the backdrop is clicked', async () => {
        // Arrange
        const wrapper = mount(AdminLayout, globalConfig)
        await wrapper.find('button[class*="lg:hidden"]').trigger('click')

        // Act
        await wrapper.find('[class*="bg-black"]').trigger('click')

        // Assert
        expect(wrapper.find('aside').classes()).toContain('-translate-x-full')
    })

    it('closes the sidebar when the page URL changes', async () => {
        // Arrange
        const wrapper = mount(AdminLayout, globalConfig)
        await wrapper.find('button[class*="lg:hidden"]').trigger('click')

        // Act
        currentUrl.value = '/admin/posts'
        await flushPromises()

        // Assert
        expect(wrapper.find('aside').classes()).toContain('-translate-x-full')
    })

    describe('theme toggle', () => {
        it('renders the sun icon when theme is light', async () => {
            // Arrange
            mockThemeMode.value = 'light'
            const wrapper = mount(AdminLayout, globalConfig)
            await nextTick()

            // Assert
            const btn = wrapper.find('button[title="Theme: light"]')
            expect(btn.exists()).toBe(true)
            expect(btn.find('circle').exists()).toBe(true)
        })

        it('renders the moon icon when theme is dark', async () => {
            // Arrange
            mockThemeMode.value = 'dark'
            const wrapper = mount(AdminLayout, globalConfig)
            await nextTick()

            // Assert
            const btn = wrapper.find('button[title="Theme: dark"]')
            expect(btn.exists()).toBe(true)
            expect(btn.find('circle').exists()).toBe(false)
            expect(btn.find('rect').exists()).toBe(false)
        })

        it('renders the monitor icon when theme is system', async () => {
            // Arrange - system is the beforeEach default
            const wrapper = mount(AdminLayout, globalConfig)

            // Assert
            const btn = wrapper.find('button[title="Theme: system"]')
            expect(btn.exists()).toBe(true)
            expect(btn.find('rect').exists()).toBe(true)
        })

        it('cycles from light to dark when clicked', async () => {
            // Arrange
            mockThemeMode.value = 'light'
            const wrapper = mount(AdminLayout, globalConfig)
            await nextTick()

            // Act
            await wrapper.find('button[title="Theme: light"]').trigger('click')

            // Assert
            expect(mockSetTheme).toHaveBeenCalledWith('dark')
        })

        it('cycles from dark to system when clicked', async () => {
            // Arrange
            mockThemeMode.value = 'dark'
            const wrapper = mount(AdminLayout, globalConfig)
            await nextTick()

            // Act
            await wrapper.find('button[title="Theme: dark"]').trigger('click')

            // Assert
            expect(mockSetTheme).toHaveBeenCalledWith('system')
        })

        it('cycles from system to light when clicked', async () => {
            // Arrange - system is the beforeEach default
            const wrapper = mount(AdminLayout, globalConfig)

            // Act
            await wrapper.find('button[title="Theme: system"]').trigger('click')

            // Assert
            expect(mockSetTheme).toHaveBeenCalledWith('light')
        })
    })
})
