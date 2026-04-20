import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest'
import { ref } from 'vue'

Object.defineProperty(window, 'matchMedia', {
    writable: true,
    value: vi.fn().mockImplementation((query: string) => ({
        matches: query.includes('dark') ? false : false,
        media: query,
        addEventListener: vi.fn(),
        removeEventListener: vi.fn(),
    })),
})

const mockThemeMode = ref<string>('system')
const mockRouterPut = vi.fn()

vi.mock('@inertiajs/vue3', () => ({
    usePage: () => ({
        props: {
            get auth() {
                return { theme_mode: mockThemeMode.value }
            },
        },
    }),
    router: { put: mockRouterPut },
}))

vi.mock('ziggy-js', () => ({
    route: (name: string) => `/${name}`,
}))

// Import after mocks are registered
const { useThemeMode } = await import('@/composables/useThemeMode')

describe('useThemeMode', () => {
    let classListToggle: ReturnType<typeof vi.fn>

    beforeEach(() => {
        classListToggle = vi.fn()
        Object.defineProperty(document.documentElement, 'classList', {
            value: { toggle: classListToggle },
            configurable: true,
        })
        mockRouterPut.mockReset()
    })

    afterEach(() => {
        mockThemeMode.value = 'system'
    })

    it('returns the current theme mode from page props', () => {
        // Arrange
        mockThemeMode.value = 'dark'

        // Act
        const { themeMode } = useThemeMode()

        // Assert
        expect(themeMode.value).toBe('dark')
    })

    it('applies dark class when theme is dark', async () => {
        // Arrange
        mockThemeMode.value = 'dark'

        // Act
        useThemeMode()
        await Promise.resolve()

        // Assert
        expect(classListToggle).toHaveBeenCalledWith('dark', true)
    })

    it('removes dark class when theme is light', async () => {
        // Arrange
        mockThemeMode.value = 'light'

        // Act
        useThemeMode()
        await Promise.resolve()

        // Assert
        expect(classListToggle).toHaveBeenCalledWith('dark', false)
    })

    it('calls router.put with the new theme on setTheme', () => {
        // Arrange
        const { setTheme } = useThemeMode()

        // Act
        setTheme('dark')

        // Assert
        expect(mockRouterPut).toHaveBeenCalledWith(
            '/admin.preferences.theme',
            { theme_mode: 'dark' },
            { preserveScroll: true },
        )
    })
})
