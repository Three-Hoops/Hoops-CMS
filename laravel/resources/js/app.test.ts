import { describe, it, expect } from 'vitest'

describe('app bootstrap', () => {
    it('runs in a js module environment', () => {
        // Arrange + Assert (no act needed for environment checks)
        expect(typeof window).toBe('object')
    })
})
