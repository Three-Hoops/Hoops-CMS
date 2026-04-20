import { mount } from '@vue/test-utils'
import { describe, it, expect } from 'vitest'
import Error from '@/Pages/Error.vue'

describe('Error page', () => {
    it('renders the status code', () => {
        // Arrange + Act
        const wrapper = mount(Error, { props: { status: 404 } })

        // Assert
        expect(wrapper.text()).toContain('404')
    })

    it('renders the correct title and message for 404', () => {
        // Arrange + Act
        const wrapper = mount(Error, { props: { status: 404 } })

        // Assert
        expect(wrapper.text()).toContain('Page Not Found')
        expect(wrapper.text()).toContain("The page you're looking for doesn't exist.")
    })

    it('renders the correct title and message for 419', () => {
        // Arrange + Act
        const wrapper = mount(Error, { props: { status: 419 } })

        // Assert
        expect(wrapper.text()).toContain('Page Expired')
        expect(wrapper.text()).toContain('Your session has expired.')
    })

    it('renders the correct title and message for 500', () => {
        // Arrange + Act
        const wrapper = mount(Error, { props: { status: 500 } })

        // Assert
        expect(wrapper.text()).toContain('Server Error')
        expect(wrapper.text()).toContain('Something went wrong on our end.')
    })

    it('renders the correct title and message for 503', () => {
        // Arrange + Act
        const wrapper = mount(Error, { props: { status: 503 } })

        // Assert
        expect(wrapper.text()).toContain('Service Unavailable')
        expect(wrapper.text()).toContain("We're down for maintenance.")
    })

    it('renders the correct title and message for 403', () => {
        // Arrange + Act
        const wrapper = mount(Error, { props: { status: 403 } })

        // Assert
        expect(wrapper.text()).toContain('Forbidden')
        expect(wrapper.text()).toContain("You don't have permission")
    })

    it('renders a fallback message for unknown status codes', () => {
        // Arrange + Act
        const wrapper = mount(Error, { props: { status: 418 } })

        // Assert
        expect(wrapper.text()).toContain('An Error Occurred')
        expect(wrapper.text()).toContain('An unexpected error occurred.')
    })

    it('renders a link to the dashboard', () => {
        // Arrange + Act
        const wrapper = mount(Error, { props: { status: 404 } })

        // Assert
        expect(wrapper.find('a').text()).toBe('Go to dashboard')
    })
})
