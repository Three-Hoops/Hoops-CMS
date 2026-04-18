import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";
import { defineStore } from "pinia";
import type { SharedProps, UserRole } from "@/types/models";

export const useAuthStore = defineStore("auth", () => {
    const page = usePage<SharedProps>();

    const user = computed(() => page.props.auth);

    function hasRole(roles: UserRole[]): boolean {
        return !!user.value && roles.includes(user.value.role);
    }

    return { user, hasRole };
});
