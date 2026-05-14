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

function mountGuard(isDirtyValue: boolean) {
    const isDirty = ref(isDirtyValue)
    const wrapper = mount(
        defineComponent({
            setup() {
                useNavigationGuard(() => isDirty.value)
            },
            template: '<div />',
        }),
    )
    const beforeCallback = mockRouterOn.mock.calls[0]?.[1] as (e: CustomEvent) => boolean | void
    return { wrapper, isDirty, beforeCallback }
}

describe('useNavigationGuard', () => {
    beforeEach(() => {
        mockRouterOn.mockReset()
        mockRouterUnsubscribe.mockReset()
        mockRouterOn.mockReturnValue(mockRouterUnsubscribe)
        vi.spyOn(window, 'confirm').mockReturnValue(true)
        vi.spyOn(window, 'addEventListener')
        vi.spyOn(window, 'removeEventListener')
    })

    afterEach(() => {
        vi.restoreAllMocks()
    })

    it('allows GET navigation when form is not dirty', () => {
        // Arrange
        const { beforeCallback } = mountGuard(false)

        // Act
        const result = beforeCallback(makeBeforeEvent('get'))

        // Assert
        expect(window.confirm).not.toHaveBeenCalled()
        expect(result).toBeUndefined()
    })

    it('shows confirm for GET visits when dirty and allows navigation if user confirms', () => {
        // Arrange
        vi.spyOn(window, 'confirm').mockReturnValue(true)
        const { beforeCallback } = mountGuard(true)

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
        vi.spyOn(window, 'confirm').mockReturnValue(false)
        const { beforeCallback } = mountGuard(true)

        // Act
        const result = beforeCallback(makeBeforeEvent('get'))

        // Assert
        expect(window.confirm).toHaveBeenCalledOnce()
        expect(result).toBe(false)
    })

    it('does not block PUT visits even when dirty', () => {
        // Arrange
        const { beforeCallback } = mountGuard(true)

        // Act
        const result = beforeCallback(makeBeforeEvent('put'))

        // Assert
        expect(window.confirm).not.toHaveBeenCalled()
        expect(result).toBeUndefined()
    })

    it('calls event.preventDefault on beforeunload when dirty', () => {
        // Arrange
        mountGuard(true)
        const event = new Event('beforeunload') as BeforeUnloadEvent
        vi.spyOn(event, 'preventDefault')
        const [, handler] = vi.mocked(window.addEventListener).mock.calls.find(
            ([type]) => type === 'beforeunload',
        )! as [string, EventListener]

        // Act
        handler(event)

        // Assert
        expect(event.preventDefault).toHaveBeenCalled()
    })

    it('does not call event.preventDefault on beforeunload when not dirty', () => {
        // Arrange
        mountGuard(false)
        const event = new Event('beforeunload') as BeforeUnloadEvent
        vi.spyOn(event, 'preventDefault')
        const [, handler] = vi.mocked(window.addEventListener).mock.calls.find(
            ([type]) => type === 'beforeunload',
        )! as [string, EventListener]

        // Act
        handler(event)

        // Assert
        expect(event.preventDefault).not.toHaveBeenCalled()
    })

    it('unsubscribes from router and removes beforeunload listener on unmount', () => {
        // Arrange
        const { wrapper } = mountGuard(true)

        // Act
        wrapper.unmount()

        // Assert
        expect(mockRouterUnsubscribe).toHaveBeenCalledOnce()
        expect(window.removeEventListener).toHaveBeenCalledWith('beforeunload', expect.any(Function))
    })

    it('covers all before-callback branches within a single composable instance', () => {
        // Arrange — one instance, isDirty toggled mid-test so V8 sees all branches in scope
        const isDirty = ref(false)
        const wrapper = mount(
            defineComponent({
                setup() {
                    useNavigationGuard(() => isDirty.value)
                },
                template: '<div />',
            }),
        )
        const cb = mockRouterOn.mock.calls[0]![1] as (e: CustomEvent) => boolean | void

        // Not dirty + GET: allowed, no confirm
        expect(cb(makeBeforeEvent('get'))).toBeUndefined()
        expect(window.confirm).not.toHaveBeenCalled()

        // Dirty + PUT: never blocked
        isDirty.value = true
        expect(cb(makeBeforeEvent('put'))).toBeUndefined()
        expect(window.confirm).not.toHaveBeenCalled()

        // Dirty + GET + user confirms: allowed
        vi.mocked(window.confirm).mockReturnValue(true)
        expect(cb(makeBeforeEvent('get'))).toBeUndefined()

        // Dirty + GET + user cancels: blocked
        vi.mocked(window.confirm).mockReturnValue(false)
        expect(cb(makeBeforeEvent('get'))).toBe(false)

        wrapper.unmount()
    })

    it('covers both beforeunload branches within a single composable instance', () => {
        // Arrange — same instance, isDirty toggled so both branches execute in one scope
        const isDirty = ref(false)
        mount(
            defineComponent({
                setup() {
                    useNavigationGuard(() => isDirty.value)
                },
                template: '<div />',
            }),
        )
        const [, handler] = vi.mocked(window.addEventListener).mock.calls.find(
            ([type]) => type === 'beforeunload',
        )! as [string, EventListener]

        // Not dirty: no prevention
        const cleanEvent = new Event('beforeunload') as BeforeUnloadEvent
        vi.spyOn(cleanEvent, 'preventDefault')
        handler(cleanEvent)
        expect(cleanEvent.preventDefault).not.toHaveBeenCalled()

        // Now dirty: prevents unload
        isDirty.value = true
        const dirtyEvent = new Event('beforeunload') as BeforeUnloadEvent
        vi.spyOn(dirtyEvent, 'preventDefault')
        handler(dirtyEvent)
        expect(dirtyEvent.preventDefault).toHaveBeenCalled()
    })
})
