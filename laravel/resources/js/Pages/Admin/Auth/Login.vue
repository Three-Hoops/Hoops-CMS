<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'

const form = useForm({
    email: '',
    password: '',
    remember: false,
})

function submit() {
    form.post(route('admin.post.login'), {
        onFinish: () => form.reset('password'),
    })
}
</script>

<template>
  <div class="flex min-h-screen items-center justify-center bg-background">
    <Card class="w-full max-w-sm">
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
            {{ form.processing ? 'Signing in…' : 'Sign in' }}
          </Button>
        </form>
      </CardContent>
    </Card>
  </div>
</template>
