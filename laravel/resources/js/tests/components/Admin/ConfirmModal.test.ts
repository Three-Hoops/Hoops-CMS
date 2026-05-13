import { mount } from '@vue/test-utils'
import { describe, it, expect } from 'vitest'
import ConfirmModal from '@/components/Admin/ConfirmModal.vue'

const alertDialogStubs = {
    AlertDialog: { template: '<div v-if="open"><slot /></div>', props: ['open'] },
    AlertDialogContent: { template: '<div><slot /></div>' },
    AlertDialogHeader: { template: '<div><slot /></div>' },
    AlertDialogTitle: { template: '<h2><slot /></h2>' },
    AlertDialogDescription: { template: '<p><slot /></p>' },
    AlertDialogFooter: { template: '<div><slot /></div>' },
    AlertDialogCancel: { template: '<button @click="$emit(\'click\')"><slot /></button>' },
    AlertDialogAction: { template: '<button @click="$emit(\'click\')"><slot /></button>' },
}

describe('ConfirmModal', () => {
    it('does not render content when closed', () => {
        // Arrange + Act
        const wrapper = mount(ConfirmModal, {
            props: { open: false, title: 'Delete?' },
            global: { stubs: alertDialogStubs },
        })

        // Assert
        expect(wrapper.find('h2').exists()).toBe(false)
    })

    it('renders the title when open', () => {
        // Arrange + Act
        const wrapper = mount(ConfirmModal, {
            props: { open: true, title: 'Delete this item?' },
            global: { stubs: alertDialogStubs },
        })

        // Assert
        expect(wrapper.find('h2').text()).toBe('Delete this item?')
    })

    it('renders the description when provided', () => {
        // Arrange + Act
        const wrapper = mount(ConfirmModal, {
            props: { open: true, title: 'Delete?', description: 'This cannot be undone.' },
            global: { stubs: alertDialogStubs },
        })

        // Assert
        expect(wrapper.find('p').text()).toBe('This cannot be undone.')
    })

    it('emits confirm when action button is clicked', async () => {
        // Arrange
        const wrapper = mount(ConfirmModal, {
            props: { open: true, title: 'Delete?' },
            global: { stubs: alertDialogStubs },
        })

        // Act
        await wrapper.findAll('button')[1].trigger('click')

        // Assert
        expect(wrapper.emitted('confirm')).toBeTruthy()
    })

    it('emits cancel when cancel button is clicked', async () => {
        // Arrange
        const wrapper = mount(ConfirmModal, {
            props: { open: true, title: 'Delete?' },
            global: { stubs: alertDialogStubs },
        })

        // Act
        await wrapper.findAll('button')[0].trigger('click')

        // Assert
        expect(wrapper.emitted('cancel')).toBeTruthy()
    })
})
