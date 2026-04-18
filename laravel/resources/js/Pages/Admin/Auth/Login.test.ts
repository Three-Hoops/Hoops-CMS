import { mount } from '@vue/test-utils'
import { describe, it, expect, vi, beforeEach } from 'vitest'
import Login from './Login.vue'

const mockPageProps = {
    flash: { success: null as string | null, error: null as string | null },
    errors: {} as Record<string, string>,
}

vi.mock('@inertiajs/vue3', () => ({
    usePage: () => ({ props: mockPageProps }),
    useForm: () => ({
        email: '',
        password: '',
        remember: false,
        processing: false,
        errors: {},
        post: vi.fn(),
        reset: vi.fn(),
    }),
}))

vi.mock('ziggy-js', () => ({
    route: (name: string) => `/${name}`,
}))

const globalConfig = {
    global: {
        stubs: {
            Button: { template: '<button><slot /></button>' },
            Card: { template: '<div><slot /></div>' },
            CardContent: { template: '<div><slot /></div>' },
            CardHeader: { template: '<div><slot /></div>' },
            CardTitle: { template: '<div><slot /></div>' },
            Input: { template: '<input />' },
            Label: { template: '<label><slot /></label>' },
        },
    },
}

describe('Login', () => {
    beforeEach(() => {
        mockPageProps.flash = { success: null, error: null }
        mockPageProps.errors = {}
    })

    it('shows throttle error banner when throttle error is present', () => {
        // Arrange
        mockPageProps.errors = { throttle: 'Too many login attempts. Please try again in 60 seconds.' }
        const wrapper = mount(Login, globalConfig)

        // Assert
        expect(wrapper.find('.bg-red-50').exists()).toBe(true)
        expect(wrapper.find('.bg-red-50').text()).toContain('Too many login attempts')
    })

    it('does not show throttle error banner when no throttle error', () => {
        // Arrange
        const wrapper = mount(Login, globalConfig)

        // Assert
        expect(wrapper.find('.bg-red-50').exists()).toBe(false)
    })

    it('shows success flash message on logout', () => {
        // Arrange
        mockPageProps.flash = { success: 'You have been logged out.', error: null }
        const wrapper = mount(Login, globalConfig)

        // Assert
        expect(wrapper.find('.bg-green-50').exists()).toBe(true)
        expect(wrapper.find('.bg-green-50').text()).toContain('You have been logged out.')
    })
})
