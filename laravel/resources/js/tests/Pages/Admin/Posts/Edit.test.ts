import { mount } from '@vue/test-utils'
import { describe, it, expect, vi } from 'vitest'
import PostsEdit from '@/Pages/Admin/Posts/Edit.vue'
import type { Category, Post, Tag } from '@/types/models'

const { mockPut } = vi.hoisted(() => ({ mockPut: vi.fn() }))

const author = { id: 1, name: 'Alice', email: 'a@example.com', role: 'super_admin' as const, locale: 'en', last_login_at: null, theme_mode: 'system' as const, timezone: 'UTC' }

const post: Post = {
    id: 3, user_id: 1, title: 'My Post', slug: 'my-post', content: '<p>Body</p>', content_json: {},
    excerpt: null, status: 'draft', meta_title: null, meta_description: null, meta_keywords: null,
    published_at: null, created_at: '2025-01-01 10:00:00', updated_at: '2025-01-01 10:00:00',
    featured_image: 'https://example.com/img.jpg', category_id: 2, category: null,
    tags: [{ id: 1, name: 'Laravel', slug: 'laravel' }], author,
}

const categories: Category[] = [
    { id: 1, name: 'Tech', slug: 'tech', description: null, parent_id: null, parent: null },
    { id: 2, name: 'News', slug: 'news', description: null, parent_id: null, parent: null },
]

const tags: Tag[] = [
    { id: 1, name: 'Laravel', slug: 'laravel' },
    { id: 2, name: 'Vue', slug: 'vue' },
]

vi.mock('@inertiajs/vue3', () => ({
    usePage: () => ({
        url: '/admin/posts/3/edit',
        props: { app: { name: 'Hoops CMS' }, auth: null, flash: { success: null, error: null }, errors: {}, ziggy: { location: 'http://localhost', routes: {} } },
    }),
    useForm: vi.fn((initial: Record<string, unknown>) => ({
        ...initial,
        processing: false, errors: {}, put: mockPut,
    })),
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

const globalConfig = {
    global: {
        stubs: {
            AdminLayout: { template: '<div><slot name="title" /><slot /></div>' },
            Button: { template: '<button type="submit" :disabled="disabled"><slot /></button>', props: ['disabled', 'variant', 'size', 'asChild'] },
            Input: { template: '<input :id="id" :value="modelValue" />', props: ['id', 'modelValue', 'type', 'placeholder', 'class'] },
            Label: { template: '<label><slot /></label>', props: ['for'] },
            Textarea: { template: '<textarea />', props: ['id', 'modelValue', 'rows'] },
            Select: { template: '<div><slot /></div>', props: ['modelValue'] },
            SelectTrigger: { template: '<div><slot /></div>' },
            SelectContent: { template: '<div><slot /></div>' },
            SelectItem: { template: '<div :data-value="value"><slot /></div>', props: ['value'] },
            SelectValue: { template: '<span />', props: ['placeholder'] },
            TipTapEditor: { template: '<div class="tiptap" :data-value="modelValue" />', props: ['modelValue'] },
            SlugInput: { template: '<input :value="modelValue" />', props: ['modelValue'] },
        },
    },
}

describe('Posts/Edit', () => {
    it('renders the page title "Edit Post"', () => {
        const wrapper = mount(PostsEdit, { props: { post, categories, tags }, ...globalConfig })
        expect(wrapper.text()).toContain('Edit Post')
    })

    it('pre-populates title from post prop', () => {
        const wrapper = mount(PostsEdit, { props: { post, categories, tags }, ...globalConfig })
        expect(wrapper.findAll('input').some(i => i.attributes('value') === 'My Post')).toBe(true)
    })

    it('pre-populates slug from post prop', () => {
        const wrapper = mount(PostsEdit, { props: { post, categories, tags }, ...globalConfig })
        expect(wrapper.findAll('input').some(i => i.attributes('value') === 'my-post')).toBe(true)
    })

    it('pre-populates content in TipTap editor', () => {
        const wrapper = mount(PostsEdit, { props: { post, categories, tags }, ...globalConfig })
        expect(wrapper.find('.tiptap').attributes('data-value')).toBe('<p>Body</p>')
    })

    it('renders tag checkboxes', () => {
        const wrapper = mount(PostsEdit, { props: { post, categories, tags }, ...globalConfig })
        expect(wrapper.text()).toContain('Laravel')
        expect(wrapper.text()).toContain('Vue')
    })

    it('renders category options', () => {
        const wrapper = mount(PostsEdit, { props: { post, categories, tags }, ...globalConfig })
        expect(wrapper.text()).toContain('Tech')
        expect(wrapper.text()).toContain('News')
    })

    it('calls form.put on submit', async () => {
        const wrapper = mount(PostsEdit, { props: { post, categories, tags }, ...globalConfig })
        await wrapper.find('form').trigger('submit')
        expect(mockPut).toHaveBeenCalledWith('/admin.posts.update/3')
    })
})
