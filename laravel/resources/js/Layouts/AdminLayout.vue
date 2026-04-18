<script setup lang="ts">
import { computed, ref, watch } from "vue";
import { Link, usePage, useForm } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { useAuthStore } from "@/stores/useAuthStore";
import FlashBanner from "@/components/Admin/FlashBanner.vue";
import type { SharedProps } from "@/types/models";

const authStore = useAuthStore();
const page = usePage<SharedProps>();
const appName = computed(() => page.props.app.name);

const logoutForm = useForm({});
const sidebarOpen = ref(false)

watch(() => page.url, () => { sidebarOpen.value = false })

function logout() {
    logoutForm.post(route("admin.logout"));
}
</script>

<template>
  <div class="flex h-screen bg-background">
    <!-- Mobile sidebar backdrop -->
    <div
      v-if="sidebarOpen"
      class="fixed inset-0 z-20 bg-black/50 lg:hidden"
      @click="sidebarOpen = false"
    />

    <!-- Sidebar -->
    <aside
      :class="[
        'fixed inset-y-0 left-0 z-30 flex w-64 flex-col border-r bg-card transition-transform duration-200 lg:static lg:translate-x-0',
        sidebarOpen ? 'translate-x-0' : '-translate-x-full',
      ]"
    >
      <!-- App name -->
      <div class="flex h-16 items-center border-b px-6">
        <span class="text-lg font-semibold">{{ appName }}</span>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 space-y-1 p-4">
        <Link
          :href="route('admin.dashboard')"
          class="flex items-center rounded-md px-3 py-2 text-sm font-medium transition-colors hover:bg-accent hover:text-accent-foreground"
          :class="{
            'bg-accent text-accent-foreground':
              $page.url.startsWith('/admin'),
          }"
        >
          Dashboard
        </Link>
      </nav>

      <!-- Current user -->
      <div class="border-t p-4">
        <p class="text-sm font-medium">
          {{ authStore.user?.name }}
        </p>
        <p class="text-xs text-muted-foreground">
          {{ authStore.user?.role }}
        </p>
        <p
          v-if="authStore.user?.last_login_at"
          class="mt-1 text-xs text-muted-foreground"
        >
          Last login: {{ new Date(authStore.user.last_login_at).toLocaleString() }}
        </p>
      </div>
    </aside>

    <!-- Main area -->
    <div class="flex flex-1 flex-col overflow-hidden">
      <!-- Top bar -->
      <header
        class="flex h-16 items-center justify-between border-b bg-card px-4 lg:px-6"
      >
        <div class="flex items-center gap-3">
          <!-- Hamburger (mobile only) -->
          <button
            class="rounded-md p-1.5 text-muted-foreground hover:bg-accent hover:text-foreground lg:hidden"
            @click="sidebarOpen = true"
          >
            <svg
              class="h-5 w-5"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M4 6h16M4 12h16M4 18h16"
              />
            </svg>
          </button>
          <h1 class="text-lg font-semibold">
            <slot name="title" />
          </h1>
        </div>
        <button
          class="text-sm text-muted-foreground hover:text-foreground"
          @click="logout"
        >
          Log out
        </button>
      </header>

      <!-- Flash banner -->
      <FlashBanner />

      <!-- Page content -->
      <main class="flex-1 overflow-auto p-6">
        <slot />
      </main>
    </div>
  </div>
</template>
