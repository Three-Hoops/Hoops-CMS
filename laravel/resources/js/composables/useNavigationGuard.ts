import { onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'

export function useNavigationGuard(isDirty: () => boolean): void {
    const unsubscribe = router.on('before', (event) => {
        if (event.detail.visit.method !== 'get') return
        if (!isDirty()) return
        const confirmed = window.confirm(
            'You have unsaved changes. If you leave, your changes will be lost.',
        )
        if (!confirmed) return false
    })

    function handleBeforeUnload(event: BeforeUnloadEvent) {
        if (isDirty()) {
            event.preventDefault()
            event.returnValue = ''
        }
    }

    window.addEventListener('beforeunload', handleBeforeUnload)

    onUnmounted(() => {
        unsubscribe()
        window.removeEventListener('beforeunload', handleBeforeUnload)
    })
}
