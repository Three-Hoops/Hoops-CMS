<script setup lang="ts">
import { computed } from "vue";
import { Link, usePage, useForm } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { useAuthStore } from "@/stores/useAuthStore";
import FlashBanner from "@/Components/Admin/FlashBanner.vue";
import type { SharedProps } from "@/types/models";

const authStore = useAuthStore();
const page = usePage<SharedProps>();
const appName = computed(() => page.props.app.name);

const logoutForm = useForm({});

function logout() {
    logoutForm.post(route("admin.logout"));
}
</script>

<template>
  <div class="flex h-screen bg-background">
    <!-- Sidebar -->
    <aside class="flex w-64 flex-col border-r bg-card">
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
      </div>
    </aside>

    <!-- Main area -->
    <div class="flex flex-1 flex-col overflow-hidden">
      <!-- Top bar -->
      <header
        class="flex h-16 items-center justify-between border-b bg-card px-6"
      >
        <h1 class="text-lg font-semibold">
          <slot name="title" />
        </h1>
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
