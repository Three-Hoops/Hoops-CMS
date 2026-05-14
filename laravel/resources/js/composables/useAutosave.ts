import { ref, watch, onMounted, onUnmounted, type Ref } from 'vue'
import axios from 'axios'

export interface AutosaveOptions {
    resource: 'post' | 'page'
    resourceId: number
    updatedAt: string
    content: Readonly<Ref<string>>
    serverDraft: string | null
}

export function useAutosave(options: AutosaveOptions) {
    const lastSavedAt = ref<Date | null>(null)
    const hasDraft = ref(false)
    const draftContent = ref<string | null>(null)

    const storageKey = `autosave:${options.resource}:${options.resourceId}`

    function saveToLocalStorage() {
        localStorage.setItem(
            storageKey,
            JSON.stringify({ content: options.content.value, savedAt: new Date().toISOString() }),
        )
        lastSavedAt.value = new Date()
    }

    let debounceTimer: ReturnType<typeof setTimeout> | null = null

    function scheduleSave() {
        if (debounceTimer) clearTimeout(debounceTimer)
        debounceTimer = setTimeout(saveToLocalStorage, 2000)
    }

    const stopWatch = watch(options.content, scheduleSave)

    async function serverSave() {
        if (document.visibilityState !== 'visible') return
        try {
            const url = `/admin/${options.resource}s/${options.resourceId}/autosave`
            await axios.post(url, { content: options.content.value })
        } catch {
            // Silent fail — localStorage is the safety net
        }
    }

    const serverInterval = setInterval(serverSave, 30_000)

    onMounted(() => {
        // Prefer localStorage draft if it's newer than the last server save
        const stored = localStorage.getItem(storageKey)
        if (stored) {
            try {
                const parsed = JSON.parse(stored) as { content: string; savedAt: string }
                if (new Date(parsed.savedAt) > new Date(options.updatedAt)) {
                    draftContent.value = parsed.content
                    hasDraft.value = true
                    return
                }
            } catch {
                localStorage.removeItem(storageKey)
            }
        }

        // Fall back to server draft if one exists
        if (options.serverDraft !== null) {
            draftContent.value = options.serverDraft
            hasDraft.value = true
        }
    })

    onUnmounted(() => {
        stopWatch()
        if (debounceTimer) clearTimeout(debounceTimer)
        clearInterval(serverInterval)
    })

    function clearDraft() {
        localStorage.removeItem(storageKey)
        hasDraft.value = false
        draftContent.value = null
    }

    function dismissDraft() {
        hasDraft.value = false
        draftContent.value = null
    }

    return { lastSavedAt, hasDraft, draftContent, clearDraft, dismissDraft }
}
