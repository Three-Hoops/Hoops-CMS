<script setup lang="ts">
import { route } from "ziggy-js";
import { useForm } from "@inertiajs/vue3";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";

const props = defineProps<{ token: string; email: string }>();
const form = useForm({
    token: props.token,
    email: props.email,
    password: "",
    password_confirmation: "",
});
</script>

<template>
  <div class="flex min-h-screen items-center justify-center bg-background">
    <div class="w-full max-w-sm space-y-4">
      <Card class="w-full">
        <CardHeader>
          <CardTitle class="text-2xl">
            Reset your password
          </CardTitle>
        </CardHeader>
        <CardContent>
          <form
            class="space-y-4"
            @submit.prevent="
              form.post(route('admin.password.update'))
            "
          >
            <div class="space-y-2">
              <Label for="email">Email</Label>
              <Input
                id="email"
                v-model="form.email"
                type="email"
                readonly
              />
              <p
                v-if="form.errors.email"
                class="text-sm text-destructive"
              >
                {{ form.errors.email }}
              </p>
            </div>
            <div class="space-y-2">
              <Label for="password">New password</Label>
              <Input
                id="password"
                v-model="form.password"
                type="password"
                autocomplete="new-password"
                autofocus
              />
              <p
                v-if="form.errors.password"
                class="text-sm text-destructive"
              >
                {{ form.errors.password }}
              </p>
            </div>
            <div class="space-y-2">
              <Label for="password_confirmation">Confirm password</Label>
              <Input
                id="password_confirmation"
                v-model="form.password_confirmation"
                type="password"
                autocomplete="new-password"
              />
              <p
                v-if="form.errors.password_confirmation"
                class="text-sm text-destructive"
              >
                {{ form.errors.password_confirmation }}
              </p>
            </div>
            <Button
              type="submit"
              class="w-full"
              :disabled="form.processing"
            >
              {{
                form.processing
                  ? "Resetting…"
                  : "Reset password"
              }}
            </Button>
          </form>
        </CardContent>
      </Card>
    </div>
  </div>
</template>
