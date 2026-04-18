import { mount } from '@vue/test-utils'
import { describe, it, expect, vi, beforeEach } from 'vitest'
import Login from './Login.vue'

const mockFormErrors: Record<string, string> = {}
const mockFlash = { success: null as string | null, error: null as string | null }
const mockPost = vi.fn()
const mockReset = vi.fn()

vi.mock('@inertiajs/vue3', () => ({
    usePage: () => ({
        props: {
            flash: mockFlash,
            errors: {},
        },
    }),
    useForm: () => ({
        email: '',
        password: '',
        remember: false,
        processing: false,
        errors: mockFormErrors,
        post: mockPost,
        reset: mockReset,
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
        Object.keys(mockFormErrors).forEach(key => delete mockFormErrors[key])
        mockFlash.success = null
        mockFlash.error = null
        mockPost.mockReset()
        mockReset.mockReset()
    })

    it('shows throttle error banner when throttle error is present', () => {
        // Arrange
        mockFormErrors.throttle = 'Too many login attempts. Please try again in 60 seconds.'
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
        mockFlash.success = 'You have been logged out.'
        const wrapper = mount(Login, globalConfig)

        // Assert
        expect(wrapper.find('.bg-green-50').exists()).toBe(true)
        expect(wrapper.find('.bg-green-50').text()).toContain('You have been logged out.')
    })

    it('submits the form when the sign in button is clicked', async () => {
        // Arrange
        const wrapper = mount(Login, globalConfig)

        // Act
        await wrapper.find('form').trigger('submit')

        // Assert
        expect(mockPost).toHaveBeenCalledWith('/admin.post.login', expect.objectContaining({ onFinish: expect.any(Function) }))
    })
})
