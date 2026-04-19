<script setup lang="ts">
import { usePage, useForm, Link } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import type { SharedProps } from "@/types/models";

const page = usePage<SharedProps>();

const form = useForm({
    email: "",
});

function submit() {
    form.post(route("admin.password.email"), {});
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
        v-if="page.props.errors.throttle"
        class="rounded-md bg-red-50 px-4 py-3 text-sm font-medium text-red-800"
      >
        {{ page.props.errors.throttle }}
      </div>
      <Card class="w-full">
        <CardHeader>
          <CardTitle class="text-2xl">
            Forgot your password?
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

            <Button
              type="submit"
              class="w-full"
              :disabled="form.processing"
            >
              {{
                form.processing ? "Sending…" : "Send reset link"
              }}
            </Button>

            <div class="text-center text-sm">
              <Link
                :href="route('admin.login')"
                class="text-muted-foreground underline-offset-4 hover:underline"
              >
                Back to login
              </Link>
            </div>
          </form>
        </CardContent>
      </Card>
    </div>
  </div>
</template>
