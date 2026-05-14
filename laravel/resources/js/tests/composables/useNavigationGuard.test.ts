import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { defineComponent, ref } from 'vue'
import { useNavigationGuard } from '@/composables/useNavigationGuard'

const { mockRouterOn, mockRouterUnsubscribe } = vi.hoisted(() => ({
    mockRouterOn: vi.fn(),
    mockRouterUnsubscribe: vi.fn(),
}))

vi.mock('@inertiajs/vue3', () => ({
    router: { on: mockRouterOn },
}))

function makeBeforeEvent(method: string) {
    return { detail: { visit: { method } } } as unknown as CustomEvent
}

describe('useNavigationGuard', () => {
    let isDirty: ReturnType<typeof ref<boolean>>
    let beforeCallback: (e: CustomEvent) => boolean | void
    let beforeunloadHandler: EventListener
    let wrapper: ReturnType<typeof mount>

    beforeEach(() => {
        mockRouterOn.mockReset()
        mockRouterUnsubscribe.mockReset()
        mockRouterOn.mockReturnValue(mockRouterUnsubscribe)
        vi.spyOn(window, 'confirm').mockReturnValue(true)
        vi.spyOn(window, 'addEventListener')
        vi.spyOn(window, 'removeEventListener')

        isDirty = ref(false)
        wrapper = mount(
            defineComponent({
                setup() {
                    useNavigationGuard(() => isDirty.value)
                },
                template: '<div />',
            }),
        )

        beforeCallback = mockRouterOn.mock.calls[0]![1] as (e: CustomEvent) => boolean | void
        const listenerCall = vi.mocked(window.addEventListener).mock.calls.find(
            ([type]) => type === 'beforeunload',
        )!
        beforeunloadHandler = listenerCall[1] as EventListener
    })

    afterEach(() => {
        vi.restoreAllMocks()
    })

    it('allows GET navigation when form is not dirty', () => {
        // Arrange — isDirty is false by default

        // Act
        const result = beforeCallback(makeBeforeEvent('get'))

        // Assert
        expect(window.confirm).not.toHaveBeenCalled()
        expect(result).toBeUndefined()
    })

    it('shows confirm for GET visits when dirty and allows navigation if user confirms', () => {
        // Arrange
        isDirty.value = true
        vi.mocked(window.confirm).mockReturnValue(true)

        // Act
        const result = beforeCallback(makeBeforeEvent('get'))

        // Assert
        expect(window.confirm).toHaveBeenCalledWith(
            'You have unsaved changes. If you leave, your changes will be lost.',
        )
        expect(result).toBeUndefined()
    })

    it('cancels GET navigation when dirty and user declines', () => {
        // Arrange
        isDirty.value = true
        vi.mocked(window.confirm).mockReturnValue(false)

        // Act
        const result = beforeCallback(makeBeforeEvent('get'))

        // Assert
        expect(window.confirm).toHaveBeenCalledOnce()
        expect(result).toBe(false)
    })

    it('does not block PUT visits even when dirty', () => {
        // Arrange
        isDirty.value = true

        // Act
        const result = beforeCallback(makeBeforeEvent('put'))

        // Assert
        expect(window.confirm).not.toHaveBeenCalled()
        expect(result).toBeUndefined()
    })

    it('calls event.preventDefault on beforeunload when dirty', () => {
        // Arrange
        isDirty.value = true
        const event = new Event('beforeunload') as BeforeUnloadEvent
        vi.spyOn(event, 'preventDefault')

        // Act
        beforeunloadHandler(event)

        // Assert
        expect(event.preventDefault).toHaveBeenCalled()
    })

    it('does not call event.preventDefault on beforeunload when not dirty', () => {
        // Arrange — isDirty is false by default
        const event = new Event('beforeunload') as BeforeUnloadEvent
        vi.spyOn(event, 'preventDefault')

        // Act
        beforeunloadHandler(event)

        // Assert
        expect(event.preventDefault).not.toHaveBeenCalled()
    })

    it('unsubscribes from router and removes beforeunload listener on unmount', () => {
        // Act
        wrapper.unmount()

        // Assert
        expect(mockRouterUnsubscribe).toHaveBeenCalledOnce()
        expect(window.removeEventListener).toHaveBeenCalledWith('beforeunload', expect.any(Function))
    })
})
