import { mount, flushPromises } from '@vue/test-utils'
import { describe, it, expect, vi, beforeEach } from 'vitest'
import { ref, nextTick } from 'vue'
import TipTapEditor from '@/components/Admin/TipTapEditor.vue'

const mockRun = vi.fn()
const mockGetHTML = vi.fn(() => '<p>Hello</p>')
const mockSetContent = vi.fn()
const mockFocus = vi.fn()
const mockIsActive = vi.fn(() => false)

const mockEditor = {
    getHTML: mockGetHTML,
    commands: { setContent: mockSetContent, focus: mockFocus },
    chain: () => ({
        focus: () => ({
            toggleBold: () => ({ run: mockRun }),
            toggleItalic: () => ({ run: mockRun }),
            toggleUnderline: () => ({ run: mockRun }),
            toggleBulletList: () => ({ run: mockRun }),
            toggleHeading: () => ({ run: mockRun }),
        }),
    }),
    isActive: mockIsActive,
}

let capturedOnUpdate: ((args: { editor: typeof mockEditor }) => void) | null = null

vi.mock('@tiptap/vue-3', () => ({
    useEditor: vi.fn((options: { onUpdate?: (args: unknown) => void }) => {
        capturedOnUpdate = options?.onUpdate ?? null
        return ref(mockEditor)
    }),
    EditorContent: { template: '<div class="editor-content" />', props: ['editor'] },
}))

vi.mock('@tiptap/starter-kit', () => ({ default: {} }))
vi.mock('@tiptap/extension-link', () => ({ default: { configure: vi.fn(() => ({})) } }))
vi.mock('@tiptap/extension-placeholder', () => ({ default: { configure: vi.fn(() => ({})) } }))
vi.mock('@tiptap/extension-underline', () => ({ default: {} }))

describe('TipTapEditor', () => {
    beforeEach(() => {
        mockRun.mockReset()
        mockGetHTML.mockReturnValue('<p>Hello</p>')
        mockIsActive.mockReturnValue(false)
        mockFocus.mockReset()
        capturedOnUpdate = null
    })

    it('renders the EditorContent component', () => {
        const wrapper = mount(TipTapEditor, { props: { modelValue: '' } })
        expect(wrapper.find('.editor-content').exists()).toBe(true)
    })

    it('renders the toolbar when editor is available', () => {
        const wrapper = mount(TipTapEditor, { props: { modelValue: '<p>Test</p>' } })
        const buttons = wrapper.findAll('button[type="button"]')
        expect(buttons.length).toBeGreaterThan(0)
    })

    it('renders Bold, Italic, Underline, List, H2 buttons', () => {
        const wrapper = mount(TipTapEditor, { props: { modelValue: '' } })
        const text = wrapper.text()
        expect(text).toContain('B')
        expect(text).toContain('I')
        expect(text).toContain('U')
        expect(text).toContain('• List')
        expect(text).toContain('H2')
    })

    it('calls chain().focus().toggleBold().run() when Bold is clicked', async () => {
        const wrapper = mount(TipTapEditor, { props: { modelValue: '' } })
        const boldBtn = wrapper.findAll('button[type="button"]').find(b => b.text() === 'B')
        await boldBtn?.trigger('click')
        expect(mockRun).toHaveBeenCalled()
    })

    it('emits update:modelValue when onUpdate fires', async () => {
        const wrapper = mount(TipTapEditor, { props: { modelValue: '' } })
        capturedOnUpdate?.({ editor: mockEditor })
        await nextTick()
        expect(wrapper.emitted('update:modelValue')?.[0]?.[0]).toBe('<p>Hello</p>')
    })

    it('calls setContent when modelValue prop changes to a different value', async () => {
        const wrapper = mount(TipTapEditor, { props: { modelValue: '<p>Hello</p>' } })
        mockGetHTML.mockReturnValue('<p>Hello</p>')
        await wrapper.setProps({ modelValue: '<p>New content</p>' })
        await flushPromises()
        expect(mockSetContent).toHaveBeenCalledWith('<p>New content</p>')
    })

    it('calls editor.commands.focus() when the wrapper div is clicked', async () => {
        const wrapper = mount(TipTapEditor, { props: { modelValue: '' } })
        await wrapper.find('div').trigger('click')
        expect(mockFocus).toHaveBeenCalled()
    })
})
