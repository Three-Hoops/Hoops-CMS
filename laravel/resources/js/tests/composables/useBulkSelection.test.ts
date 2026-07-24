import { describe, it, expect, beforeEach } from 'vitest'
import { useBulkSelection } from '@/composables/useBulkSelection'

describe('useBulkSelection', () => {
    let selection: ReturnType<typeof useBulkSelection>

    beforeEach(() => {
        selection = useBulkSelection()
    })

    it('starts with no ids selected', () => {
        // Assert
        expect(selection.selectedIds.value).toEqual([])
    })

    it('toggle adds an id when not selected', () => {
        // Act
        selection.toggle(1)

        // Assert
        expect(selection.selectedIds.value).toContain(1)
    })

    it('toggle removes an id when already selected', () => {
        // Arrange
        selection.toggle(1)

        // Act
        selection.toggle(1)

        // Assert
        expect(selection.selectedIds.value).not.toContain(1)
    })

    it('toggleAll selects all page ids when none selected', () => {
        // Act
        selection.toggleAll([1, 2, 3])

        // Assert
        expect(selection.selectedIds.value).toEqual([1, 2, 3])
    })

    it('toggleAll deselects all page ids when all are selected', () => {
        // Arrange
        selection.toggleAll([1, 2, 3])

        // Act
        selection.toggleAll([1, 2, 3])

        // Assert
        expect(selection.selectedIds.value).toEqual([])
    })

    it('toggleAll selects only the missing ids when partially selected', () => {
        // Arrange
        selection.toggle(1)

        // Act
        selection.toggleAll([1, 2, 3])

        // Assert
        expect(selection.selectedIds.value).toContain(1)
        expect(selection.selectedIds.value).toContain(2)
        expect(selection.selectedIds.value).toContain(3)
    })

    it('isAllSelected returns false when no ids selected', () => {
        // Assert
        expect(selection.isAllSelected([1, 2, 3])).toBe(false)
    })

    it('isAllSelected returns true when all page ids selected', () => {
        // Arrange
        selection.toggleAll([1, 2, 3])

        // Assert
        expect(selection.isAllSelected([1, 2, 3])).toBe(true)
    })

    it('isAllSelected returns false when only some page ids selected', () => {
        // Arrange
        selection.toggle(1)

        // Assert
        expect(selection.isAllSelected([1, 2, 3])).toBe(false)
    })

    it('isAllSelected returns false for empty page ids array', () => {
        // Assert
        expect(selection.isAllSelected([])).toBe(false)
    })

    it('clearSelection resets to empty', () => {
        // Arrange
        selection.toggleAll([1, 2, 3])

        // Act
        selection.clearSelection()

        // Assert
        expect(selection.selectedIds.value).toEqual([])
    })
})
