import { describe, it, expect } from 'vitest'

describe('app bootstrap', () => {
    it('runs in a js module environment', () => {
        expect(typeof window).toBe('object')
    })
})
