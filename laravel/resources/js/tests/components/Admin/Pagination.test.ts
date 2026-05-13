import { mount } from '@vue/test-utils'
import { describe, it, expect, vi } from 'vitest'
import Pagination from '@/components/Admin/Pagination.vue'

vi.mock('@inertiajs/vue3', () => ({
    router: { visit: vi.fn() },
}))

const links = [
    { url: null, label: '&laquo; Previous', active: false },
    { url: 'http://example.com/admin/tags?page=1', label: '1', active: true },
    { url: 'http://example.com/admin/tags?page=2', label: '2', active: false },
    { url: 'http://example.com/admin/tags?page=2', label: 'Next &raquo;', active: false },
]

describe('Pagination', () => {
    it('renders a button for each link', () => {
        // Arrange + Act
        const wrapper = mount(Pagination, { props: { links } })

        // Assert
        expect(wrapper.findAll('button')).toHaveLength(4)
    })

    it('disables the previous button when url is null', () => {
        // Arrange + Act
        const wrapper = mount(Pagination, { props: { links } })

        // Assert
        expect(wrapper.findAll('button')[0].attributes('disabled')).toBeDefined()
    })

    it('marks the active page button with primary styling', () => {
        // Arrange + Act
        const wrapper = mount(Pagination, { props: { links } })

        // Assert — active button has bg-primary class
        expect(wrapper.findAll('button')[1].classes()).toContain('bg-primary')
    })

    it('calls router.visit when a link with url is clicked', async () => {
        // Arrange
        const { router } = await import('@inertiajs/vue3')
        const wrapper = mount(Pagination, { props: { links } })

        // Act
        await wrapper.findAll('button')[1].trigger('click')

        // Assert
        expect(router.visit).toHaveBeenCalledWith(links[1].url, { preserveScroll: true })
    })
})
