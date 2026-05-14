import { ref } from 'vue'

export function useBulkSelection() {
    const selectedIds = ref<number[]>([])

    function isAllSelected(pageIds: number[]): boolean {
        return pageIds.length > 0 && pageIds.every(id => selectedIds.value.includes(id))
    }

    function toggleAll(pageIds: number[]) {
        if (isAllSelected(pageIds)) {
            selectedIds.value = selectedIds.value.filter(id => !pageIds.includes(id))
        } else {
            const toAdd = pageIds.filter(id => !selectedIds.value.includes(id))
            selectedIds.value = [...selectedIds.value, ...toAdd]
        }
    }

    function toggle(id: number) {
        if (selectedIds.value.includes(id)) {
            selectedIds.value = selectedIds.value.filter(i => i !== id)
        } else {
            selectedIds.value = [...selectedIds.value, id]
        }
    }

    function clearSelection() {
        selectedIds.value = []
    }

    return { selectedIds, isAllSelected, toggleAll, toggle, clearSelection }
}
