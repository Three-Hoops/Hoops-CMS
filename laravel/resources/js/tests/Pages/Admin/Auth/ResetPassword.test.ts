import { mount } from '@vue/test-utils'
import { describe, it, expect, vi, beforeEach } from 'vitest'
import ResetPassword from '@/Pages/Admin/Auth/ResetPassword.vue'

const mockErrors: Record<string, string> = {}
const mockPost = vi.fn()

vi.mock('@inertiajs/vue3', () => ({
    useForm: (initial: Record<string, string>) => ({
        ...initial,
        processing: false,
        errors: mockErrors,
        post: mockPost,
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
            Input: { template: '<input v-bind="$attrs" />', inheritAttrs: false },
            Label: { template: '<label><slot /></label>' },
        },
    },
}

const defaultProps = {
    token: 'test-token-abc',
    email: 'admin@example.com',
}

describe('ResetPassword', () => {
    beforeEach(() => {
        Object.keys(mockErrors).forEach(key => delete mockErrors[key])
        mockPost.mockReset()
    })

    it('renders three input fields', () => {
        // Arrange
        const wrapper = mount(ResetPassword, { ...globalConfig, props: defaultProps })

        // Assert
        expect(wrapper.findAll('input').length).toBe(3)
    })

    it('submits to the correct route', async () => {
        // Arrange
        const wrapper = mount(ResetPassword, { ...globalConfig, props: defaultProps })

        // Act
        await wrapper.find('form').trigger('submit')

        // Assert
        expect(mockPost).toHaveBeenCalledWith('/admin.password.update')
    })

    it('shows email error when token is invalid', () => {
        // Arrange
        mockErrors.email = 'This password reset token is invalid.'
        const wrapper = mount(ResetPassword, { ...globalConfig, props: defaultProps })

        // Assert
        expect(wrapper.text()).toContain('This password reset token is invalid.')
    })

    it('shows password error when password is too short', () => {
        // Arrange
        mockErrors.password = 'The password field must be at least 8 characters.'
        const wrapper = mount(ResetPassword, { ...globalConfig, props: defaultProps })

        // Assert
        expect(wrapper.text()).toContain('The password field must be at least 8 characters.')
    })
})
