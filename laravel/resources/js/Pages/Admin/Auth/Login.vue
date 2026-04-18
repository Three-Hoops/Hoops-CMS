<script setup lang="ts">
import { usePage, useForm } from "@inertiajs/vue3";
import { computed } from "vue";
import { route } from "ziggy-js";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import type { SharedProps } from "@/types/models";

const page = usePage<SharedProps>();

const form = useForm({
    email: "",
    password: "",
    remember: false,
});

const throttleError = computed(() => (form.errors as Record<string, string | undefined>).throttle)

function submit() {
    form.post(route("admin.post.login"), {
        onFinish: () => form.reset("password"),
    });
}
</script>

<template>
  <div class="flex min-h-screen items-center justify-center bg-background">
    <div class="w-full max-w-sm space-y-4">
      <div
        v-if="page.props.flash.success"
        class="rounded-md bg-green-50 px-4 py-3 text-sm font-medium text-green-800"
      >
        {{ page.props.flash.success }}
      </div>
      <div
        v-if="throttleError"
        class="rounded-md bg-red-50 px-4 py-3 text-sm font-medium text-red-800"
      >
        {{ throttleError }}
      </div>
      <Card class="w-full">
        <CardHeader>
          <CardTitle class="text-2xl">
            Sign in
          </CardTitle>
        </CardHeader>
        <CardContent>
          <form
            class="space-y-4"
            @submit.prevent="submit"
          >
            <div class="space-y-2">
              <Label for="email">Email</Label>
              <Input
                id="email"
                v-model="form.email"
                type="email"
                autocomplete="email"
                autofocus
              />
              <p
                v-if="form.errors.email"
                class="text-sm text-destructive"
              >
                {{ form.errors.email }}
              </p>
            </div>

            <div class="space-y-2">
              <Label for="password">Password</Label>
              <Input
                id="password"
                v-model="form.password"
                type="password"
                autocomplete="current-password"
              />
              <p
                v-if="form.errors.password"
                class="text-sm text-destructive"
              >
                {{ form.errors.password }}
              </p>
            </div>

            <div class="flex items-center gap-2">
              <input
                id="remember"
                v-model="form.remember"
                type="checkbox"
                class="rounded border-input"
              >
              <Label for="remember">Remember me</Label>
            </div>

            <Button
              type="submit"
              class="w-full"
              :disabled="form.processing"
            >
              {{ form.processing ? "Signing in…" : "Sign in" }}
            </Button>
          </form>
        </CardContent>
      </Card>
    </div>
  </div>
</template>
