import { mount } from '@vue/test-utils'
import { describe, it, expect } from 'vitest'
import StatusBadge from '@/components/Admin/StatusBadge.vue'

const globalConfig = {
    global: {
        stubs: {
            Badge: { template: '<span :data-variant="variant"><slot /></span>', props: ['variant'] },
        },
    },
}

describe('StatusBadge', () => {
    it('renders "Published" for published status', () => {
        // Arrange + Act
        const wrapper = mount(StatusBadge, { props: { status: 'published' }, ...globalConfig })

        // Assert
        expect(wrapper.text()).toBe('Published')
        expect(wrapper.find('[data-variant="default"]').exists()).toBe(true)
    })

    it('renders "Draft" for draft status', () => {
        // Arrange + Act
        const wrapper = mount(StatusBadge, { props: { status: 'draft' }, ...globalConfig })

        // Assert
        expect(wrapper.text()).toBe('Draft')
        expect(wrapper.find('[data-variant="secondary"]').exists()).toBe(true)
    })
})
