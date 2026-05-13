import { mount } from '@vue/test-utils'
import { describe, it, expect, vi } from 'vitest'
import { nextTick } from 'vue'
import SlugInput from '@/components/Admin/SlugInput.vue'

vi.mock('@/components/ui/input', () => ({
    Input: { template: '<input :value="value" v-bind="$attrs" />', props: ['value'] },
}))

describe('SlugInput', () => {
    it('renders the current modelValue', () => {
        // Arrange + Act
        const wrapper = mount(SlugInput, { props: { modelValue: 'my-slug' } })

        // Assert
        expect(wrapper.find('input').attributes('value')).toBe('my-slug')
    })

    it('emits slugified value when source prop changes and modelValue is empty', async () => {
        // Arrange
        const wrapper = mount(SlugInput, { props: { modelValue: '', source: '' } })

        // Act
        await wrapper.setProps({ source: 'Hello World' })
        await nextTick()

        // Assert
        const emitted = wrapper.emitted('update:modelValue') as string[][]
        expect(emitted).toBeTruthy()
        expect(emitted[emitted.length - 1][0]).toBe('hello-world')
    })

    it('does not override modelValue when already set', async () => {
        // Arrange
        const wrapper = mount(SlugInput, { props: { modelValue: 'custom-slug', source: '' } })

        // Act
        await wrapper.setProps({ source: 'Something Else' })
        await nextTick()

        // Assert
        expect(wrapper.emitted('update:modelValue')).toBeFalsy()
    })

    it('emits slugified value on user input', async () => {
        // Arrange
        const wrapper = mount(SlugInput, { props: { modelValue: '' } })
        const input = wrapper.find('input')

        // Act — setValue fires an input event after setting the DOM value
        await input.setValue('My Tag')

        // Assert
        const emitted = wrapper.emitted('update:modelValue') as string[][]
        expect(emitted[0][0]).toBe('my-tag')
    })
})
