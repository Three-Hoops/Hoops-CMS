import { computed, watchEffect } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import type { SharedProps, ThemeMode } from '@/types/models'

export function useThemeMode() {
    const page = usePage<SharedProps>()

    const themeMode = computed(() => page.props.auth?.theme_mode ?? 'system')

    watchEffect(() => {
        const mode = themeMode.value
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches
        const isDark = mode === 'dark' || (mode === 'system' && prefersDark)
        document.documentElement.classList.toggle('dark', isDark)
    })

    function setTheme(mode: ThemeMode) {
        router.put(route('admin.preferences.theme'), { theme_mode: mode }, { preserveScroll: true })
    }

    return { themeMode, setTheme }
}
