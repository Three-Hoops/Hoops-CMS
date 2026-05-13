import { mount } from '@vue/test-utils'
import { describe, it, expect, vi } from 'vitest'
import PostsCreate from '@/Pages/Admin/Posts/Create.vue'
import type { Category, Tag } from '@/types/models'

const { mockPost } = vi.hoisted(() => ({ mockPost: vi.fn() }))

vi.mock('@inertiajs/vue3', () => ({
    usePage: () => ({
        url: '/admin/posts/create',
        props: { app: { name: 'Hoops CMS' }, auth: null, flash: { success: null, error: null }, errors: {}, ziggy: { location: 'http://localhost', routes: {} } },
    }),
    useForm: vi.fn(() => ({
        title: '', slug: '', content: '', status: 'draft', featured_image: '', category_id: '',
        tag_ids: [], meta_title: '', meta_description: '', published_at: '',
        processing: false, errors: {}, post: mockPost,
    })),
    Head: { template: '<div />' },
    Link: { template: '<a :href="href"><slot /></a>', props: ['href'] },
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

vi.mock('@/components/Admin/FlashBanner.vue', () => ({ default: { template: '<div />' } }))

const categories: Category[] = [
    { id: 1, name: 'Tech', slug: 'tech', description: null, parent_id: null, parent: null },
    { id: 2, name: 'News', slug: 'news', description: null, parent_id: null, parent: null },
]

const tags: Tag[] = [
    { id: 1, name: 'Laravel', slug: 'laravel' },
    { id: 2, name: 'Vue', slug: 'vue' },
]

const globalConfig = {
    global: {
        stubs: {
            AdminLayout: { template: '<div><slot name="title" /><slot /></div>' },
            Button: { template: '<button type="submit" :disabled="disabled"><slot /></button>', props: ['disabled', 'variant', 'size', 'asChild', 'as', 'href'] },
            Input: { template: '<input :id="id" />', props: ['id', 'modelValue', 'type', 'placeholder', 'class'] },
            Label: { template: '<label><slot /></label>', props: ['for'] },
            Textarea: { template: '<textarea />', props: ['id', 'modelValue', 'rows'] },
            Select: { template: '<div><slot /></div>', props: ['modelValue'] },
            SelectTrigger: { template: '<div><slot /></div>' },
            SelectContent: { template: '<div><slot /></div>' },
            SelectItem: { template: '<div :data-value="value"><slot /></div>', props: ['value'] },
            SelectValue: { template: '<span />', props: ['placeholder'] },
            TipTapEditor: { template: '<div class="tiptap" />', props: ['modelValue'] },
            SlugInput: { template: '<input />', props: ['modelValue'] },
        },
    },
}

describe('Posts/Create', () => {
    it('renders the page title "New Post"', () => {
        const wrapper = mount(PostsCreate, { props: { categories, tags }, ...globalConfig })
        expect(wrapper.text()).toContain('New Post')
    })

    it('renders form fields', () => {
        const wrapper = mount(PostsCreate, { props: { categories, tags }, ...globalConfig })
        expect(wrapper.text()).toContain('Title')
        expect(wrapper.text()).toContain('Status')
        expect(wrapper.text()).toContain('Category')
    })

    it('renders the TipTap editor', () => {
        const wrapper = mount(PostsCreate, { props: { categories, tags }, ...globalConfig })
        expect(wrapper.find('.tiptap').exists()).toBe(true)
    })

    it('renders tag checkboxes for each tag', () => {
        const wrapper = mount(PostsCreate, { props: { categories, tags }, ...globalConfig })
        expect(wrapper.text()).toContain('Laravel')
        expect(wrapper.text()).toContain('Vue')
    })

    it('does not render tags section when tags list is empty', () => {
        const wrapper = mount(PostsCreate, { props: { categories, tags: [] }, ...globalConfig })
        const tagLabels = wrapper.findAll('label').filter(l => l.text() === 'Laravel' || l.text() === 'Vue')
        expect(tagLabels.length).toBe(0)
    })

    it('calls form.post on submit', async () => {
        const wrapper = mount(PostsCreate, { props: { categories, tags }, ...globalConfig })
        await wrapper.find('form').trigger('submit')
        expect(mockPost).toHaveBeenCalledWith('/admin.posts.store')
    })

    it('renders category options from props', () => {
        const wrapper = mount(PostsCreate, { props: { categories, tags }, ...globalConfig })
        expect(wrapper.text()).toContain('Tech')
        expect(wrapper.text()).toContain('News')
    })
})
