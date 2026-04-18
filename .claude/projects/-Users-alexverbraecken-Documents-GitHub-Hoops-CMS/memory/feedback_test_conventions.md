---
name: Test file naming and AAA pattern
description: Vue/TS test files must use .test.ts extension and follow Arrange/Act/Assert pattern
type: feedback
---

Always name Vue/TypeScript test files `*.test.ts` (never `*.spec.ts`).

All tests must follow the **Arrange / Act / Assert** pattern with inline comments:

```ts
it('does something', () => {
    // Arrange
    const wrapper = mount(MyComponent)

    // Act
    await wrapper.find('button').trigger('click')

    // Assert
    expect(wrapper.find('.result').text()).toBe('done')
})
```

**Why:** Agreed convention with the user — consistent naming and structure across all tests.

**How to apply:** Every new `it()` block gets the three comment sections. If there is no Act step (e.g. pure environment checks), note it inline.
