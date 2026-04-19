import { mount } from '@vue/test-utils'
import { describe, it, expect, vi, beforeEach } from 'vitest'
import ForgotPassword from '@/Pages/Admin/Auth/ForgotPassword.vue'

const mockErrors: Record<string, string> = {}
const mockFlash = { success: null as string | null, error: null as string | null }
const mockPost = vi.fn()

vi.mock('@inertiajs/vue3', () => ({
    usePage: () => ({
        props: {
            flash: mockFlash,
            errors: mockErrors,
        },
    }),
    useForm: () => ({
        email: '',
        processing: false,
        errors: mockErrors,
        post: mockPost,
    }),
    Link: { template: '<a :href="href"><slot /></a>', props: ['href'] },
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

describe('ForgotPassword', () => {
    beforeEach(() => {
        Object.keys(mockErrors).forEach(key => delete mockErrors[key])
        mockFlash.success = null
        mockFlash.error = null
        mockPost.mockReset()
    })

    it('renders the email input', () => {
        // Arrange
        const wrapper = mount(ForgotPassword, globalConfig)

        // Assert
        expect(wrapper.find('input').exists()).toBe(true)
    })

    it('shows success flash message after submission', () => {
        // Arrange
        mockFlash.success = 'If that email is registered, a reset link has been sent.'
        const wrapper = mount(ForgotPassword, globalConfig)

        // Assert
        expect(wrapper.find('.bg-green-50').exists()).toBe(true)
        expect(wrapper.find('.bg-green-50').text()).toContain('reset link')
    })

    it('shows throttle error when rate limited', () => {
        // Arrange
        mockErrors.throttle = 'Too many requests.'
        const wrapper = mount(ForgotPassword, globalConfig)

        // Assert
        expect(wrapper.find('.bg-red-50').exists()).toBe(true)
    })

    it('submits to the correct route', async () => {
        // Arrange
        const wrapper = mount(ForgotPassword, globalConfig)

        // Act
        await wrapper.find('form').trigger('submit')

        // Assert
        expect(mockPost).toHaveBeenCalledWith('/admin.password.email', expect.anything())
    })

    it('has a back to login link', () => {
        // Arrange
        const wrapper = mount(ForgotPassword, globalConfig)

        // Assert
        expect(wrapper.text()).toContain('Back to login')
    })
})
