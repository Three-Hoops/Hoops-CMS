import { mount } from '@vue/test-utils'
import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest'
import TagsIndex from '@/Pages/Admin/Tags/Index.vue'
import type { Paginated, Tag } from '@/types/models'

const { authRole } = vi.hoisted(() => ({ authRole: { current: 'super_admin' as string } }))

vi.mock('@inertiajs/vue3', () => ({
    usePage: () => ({
        url: '/admin/tags',
        props: {
            app: { name: 'Hoops CMS' },
            auth: null,
            flash: { success: null, error: null },
            errors: {},
            ziggy: { location: 'http://localhost', routes: {} },
        },
    }),
    useForm: () => ({
        name: '',
        slug: '',
        processing: false,
        errors: {},
        post: vi.fn(),
        put: vi.fn(),
        reset: vi.fn(),
    }),
    router: { delete: vi.fn() },
    Head: { template: '<div />' },
    Link: { template: '<a><slot /></a>' },
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

vi.mock('@/components/Admin/FlashBanner.vue', () => ({
    default: { template: '<div />' },
}))

vi.mock('@/components/Admin/SlugInput.vue', () => ({
    default: { template: '<input />' },
}))

vi.mock('@/components/Admin/Pagination.vue', () => ({
    default: { template: '<div />' },
}))

vi.mock('@/components/Admin/ConfirmModal.vue', () => ({
    default: { template: '<div />', props: ['open', 'title', 'description'] },
}))

const tags: Paginated<Tag> = {
    data: [
        { id: 1, name: 'Laravel', slug: 'laravel' },
        { id: 2, name: 'Vue', slug: 'vue' },
    ],
    links: [],
    meta: { current_page: 1, last_page: 1, per_page: 15, total: 2 },
}

const globalConfig = {
    global: {
        stubs: {
            AdminLayout: { template: '<div><slot name="title" /><slot /></div>' },
            Button: { template: '<button :disabled="disabled" @click="$emit(\'click\')"><slot /></button>', props: ['disabled', 'variant', 'size'] },
            Input: { template: '<input v-model="modelValue" />', props: ['modelValue'] },
            Label: { template: '<label><slot /></label>' },
        },
    },
}

describe('Tags/Index', () => {
    it('renders all tags from props', () => {
        // Arrange + Act
        const wrapper = mount(TagsIndex, { props: { tags }, ...globalConfig })

        // Assert
        expect(wrapper.text()).toContain('Laravel')
        expect(wrapper.text()).toContain('Vue')
    })

    it('shows the Add Tag form', () => {
        // Arrange + Act
        const wrapper = mount(TagsIndex, { props: { tags }, ...globalConfig })

        // Assert
        expect(wrapper.text()).toContain('Add Tag')
    })

    it('shows "No tags yet" when data is empty', () => {
        // Arrange + Act
        const emptyTags: Paginated<Tag> = { ...tags, data: [] }
        const wrapper = mount(TagsIndex, { props: { tags: emptyTags }, ...globalConfig })

        // Assert
        expect(wrapper.text()).toContain('No tags yet')
    })

    it('shows edit form when Edit button is clicked', async () => {
        // Arrange
        const wrapper = mount(TagsIndex, { props: { tags }, ...globalConfig })

        // Act
        await wrapper.findAll('button').find(b => b.text() === 'Edit')?.trigger('click')

        // Assert — Save button appears in inline edit row
        const saveButtons = wrapper.findAll('button').filter(b => b.text() === 'Save')
        expect(saveButtons.length).toBeGreaterThan(0)
    })
})

describe('Tags/Index — viewer role', () => {
    beforeEach(() => { authRole.current = 'viewer' })
    afterEach(() => { authRole.current = 'super_admin' })

    it('hides Add Tag form for viewers', () => {
        // Arrange + Act
        const wrapper = mount(TagsIndex, { props: { tags }, ...globalConfig })

        // Assert
        expect(wrapper.text()).not.toContain('Add Tag')
    })

    it('hides Edit button for viewers', () => {
        // Arrange + Act
        const wrapper = mount(TagsIndex, { props: { tags }, ...globalConfig })

        // Assert
        expect(wrapper.findAll('button').filter(b => b.text() === 'Edit')).toHaveLength(0)
    })

    it('hides Delete button for viewers', () => {
        // Arrange + Act
        const wrapper = mount(TagsIndex, { props: { tags }, ...globalConfig })

        // Assert
        expect(wrapper.findAll('button').filter(b => b.text() === 'Delete')).toHaveLength(0)
    })
})
