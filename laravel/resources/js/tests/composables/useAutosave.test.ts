import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest'
import { defineComponent, ref, nextTick } from 'vue'
import { mount } from '@vue/test-utils'

vi.mock('axios', () => ({
    default: { post: vi.fn().mockResolvedValue({}) },
}))

const { useAutosave } = await import('@/composables/useAutosave')
const { default: axios } = await import('axios')

type AutosaveResult = ReturnType<typeof useAutosave>

function withSetup(composable: () => AutosaveResult): [AutosaveResult, () => void] {
    let result!: AutosaveResult
    const App = defineComponent({
        setup() {
            result = composable()
            return () => null
        },
        render() {
            return null
        },
    })
    const wrapper = mount(App)
    return [result, () => wrapper.unmount()]
}

describe('useAutosave', () => {
    beforeEach(() => {
        vi.useFakeTimers()
        localStorage.clear()
        vi.mocked(axios.post).mockClear()
    })

    afterEach(() => {
        vi.useRealTimers()
    })

    it('saves content to localStorage after the 2-second debounce', async () => {
        // Arrange
        const content = ref('<p>Hello</p>')
        const [, unmount] = withSetup(() =>
            useAutosave({
                resource: 'post',
                resourceId: 1,
                updatedAt: '2020-01-01T00:00:00.000Z',
                content,
                serverDraft: null,
            }),
        )

        // Act
        content.value = '<p>Updated</p>'
        await nextTick()
        vi.advanceTimersByTime(2000)

        // Assert
        const stored = localStorage.getItem('autosave:post:1')
        expect(stored).not.toBeNull()
        expect(JSON.parse(stored!).content).toBe('<p>Updated</p>')

        unmount()
    })

    it('does not save before the debounce elapses', async () => {
        // Arrange
        const content = ref('<p>Hello</p>')
        const [, unmount] = withSetup(() =>
            useAutosave({
                resource: 'post',
                resourceId: 1,
                updatedAt: '2020-01-01T00:00:00.000Z',
                content,
                serverDraft: null,
            }),
        )

        // Act
        content.value = '<p>Updated</p>'
        await nextTick()
        vi.advanceTimersByTime(1999)

        // Assert
        expect(localStorage.getItem('autosave:post:1')).toBeNull()

        unmount()
    })

    it('sets lastSavedAt after a successful localStorage save', async () => {
        // Arrange
        const content = ref('<p>Hello</p>')
        const [{ lastSavedAt }, unmount] = withSetup(() =>
            useAutosave({
                resource: 'post',
                resourceId: 1,
                updatedAt: '2020-01-01T00:00:00.000Z',
                content,
                serverDraft: null,
            }),
        )
        expect(lastSavedAt.value).toBeNull()

        // Act
        content.value = '<p>Changed</p>'
        await nextTick()
        vi.advanceTimersByTime(2000)

        // Assert
        expect(lastSavedAt.value).toBeInstanceOf(Date)

        unmount()
    })

    it('shows a draft when localStorage entry is newer than updatedAt', async () => {
        // Arrange
        localStorage.setItem(
            'autosave:post:5',
            JSON.stringify({ content: '<p>Draft</p>', savedAt: '2030-01-01T00:00:00.000Z' }),
        )
        const content = ref('<p>Saved</p>')
        const [{ hasDraft, draftContent }, unmount] = withSetup(() =>
            useAutosave({
                resource: 'post',
                resourceId: 5,
                updatedAt: '2020-01-01T00:00:00.000Z',
                content,
                serverDraft: null,
            }),
        )
        await nextTick()

        // Assert
        expect(hasDraft.value).toBe(true)
        expect(draftContent.value).toBe('<p>Draft</p>')

        unmount()
    })

    it('ignores localStorage draft if it is older than updatedAt', async () => {
        // Arrange
        localStorage.setItem(
            'autosave:post:5',
            JSON.stringify({ content: '<p>Old draft</p>', savedAt: '2010-01-01T00:00:00.000Z' }),
        )
        const content = ref('<p>Saved</p>')
        const [{ hasDraft }, unmount] = withSetup(() =>
            useAutosave({
                resource: 'post',
                resourceId: 5,
                updatedAt: '2020-01-01T00:00:00.000Z',
                content,
                serverDraft: null,
            }),
        )
        await nextTick()

        // Assert
        expect(hasDraft.value).toBe(false)

        unmount()
    })

    it('falls back to serverDraft when no localStorage draft exists', async () => {
        // Arrange
        const content = ref('<p>Saved</p>')
        const [{ hasDraft, draftContent }, unmount] = withSetup(() =>
            useAutosave({
                resource: 'page',
                resourceId: 10,
                updatedAt: '2020-01-01T00:00:00.000Z',
                content,
                serverDraft: '<p>Server draft</p>',
            }),
        )
        await nextTick()

        // Assert
        expect(hasDraft.value).toBe(true)
        expect(draftContent.value).toBe('<p>Server draft</p>')

        unmount()
    })

    it('clearDraft removes the localStorage entry and hides the banner', async () => {
        // Arrange
        localStorage.setItem(
            'autosave:post:5',
            JSON.stringify({ content: '<p>Draft</p>', savedAt: '2030-01-01T00:00:00.000Z' }),
        )
        const content = ref('<p>Saved</p>')
        const [{ hasDraft, clearDraft }, unmount] = withSetup(() =>
            useAutosave({
                resource: 'post',
                resourceId: 5,
                updatedAt: '2020-01-01T00:00:00.000Z',
                content,
                serverDraft: null,
            }),
        )
        await nextTick()

        // Act
        clearDraft()

        // Assert
        expect(hasDraft.value).toBe(false)
        expect(localStorage.getItem('autosave:post:5')).toBeNull()

        unmount()
    })

    it('dismissDraft hides the banner without removing localStorage', async () => {
        // Arrange
        localStorage.setItem(
            'autosave:post:5',
            JSON.stringify({ content: '<p>Draft</p>', savedAt: '2030-01-01T00:00:00.000Z' }),
        )
        const content = ref('<p>Saved</p>')
        const [{ hasDraft, dismissDraft }, unmount] = withSetup(() =>
            useAutosave({
                resource: 'post',
                resourceId: 5,
                updatedAt: '2020-01-01T00:00:00.000Z',
                content,
                serverDraft: null,
            }),
        )
        await nextTick()

        // Act
        dismissDraft()

        // Assert
        expect(hasDraft.value).toBe(false)
        expect(localStorage.getItem('autosave:post:5')).not.toBeNull()

        unmount()
    })

    it('POSTs to the server autosave endpoint every 30 seconds', async () => {
        // Arrange
        const content = ref('<p>Content</p>')
        const [, unmount] = withSetup(() =>
            useAutosave({
                resource: 'post',
                resourceId: 42,
                updatedAt: '2020-01-01T00:00:00.000Z',
                content,
                serverDraft: null,
            }),
        )

        // Act
        vi.advanceTimersByTime(30_000)
        await nextTick()

        // Assert
        expect(axios.post).toHaveBeenCalledWith('/admin/posts/42/autosave', {
            content: '<p>Content</p>',
        })

        unmount()
    })
})
