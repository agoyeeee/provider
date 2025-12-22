<script setup>
import Checkbox from "@/Components/Checkbox.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import SsoButton from "@/Components/SsoButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { Button } from "@/Components/ui/button";
import { Head, Link, useForm } from "@inertiajs/vue3";

defineProps({
  canResetPassword: {
    type: Boolean,
  },
  status: {
    type: String,
  },
});

const form = useForm({
  email: "",
  password: "",
  remember: false,
});

const submit = () => {
  form.post(route("login"), {
    onFinish: () => form.reset("password"),
  });
};
</script>

<template>
  <Head title="Log in" />

  <!-- Modern Login Container -->
  <div
    class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 via-white to-purple-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 py-12 px-4 sm:px-6 lg:px-8"
  >
    <div class="max-w-md w-full space-y-8">
      <!-- Header Section -->
      <div class="text-center">
        <div
          class="mx-auto h-16 w-16 bg-gradient-to-r from-blue-500 to-purple-600 dark:from-blue-400 dark:to-purple-500 rounded-full flex items-center justify-center mb-6 shadow-lg"
        >
          <svg
            class="h-8 w-8 text-white"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
            />
          </svg>
        </div>
        <h2 class="text-3xl font-bold text-foreground mb-2">Welcome Back</h2>
        <p class="text-muted-foreground">Sign in to your account</p>
      </div>

      <!-- Status Message -->
      <div
        v-if="status"
        class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-300 px-4 py-3 rounded-lg text-sm font-medium"
      >
        {{ status }}
      </div>

      <!-- Login Form Card -->
      <div class="bg-card shadow-xl rounded-2xl p-8 border border-border">
        <form @submit.prevent="submit" class="space-y-6">
          <!-- Email Field -->
          <div class="space-y-2">
            <InputLabel
              for="email"
              value="Email Address"
              class="text-foreground font-medium"
            />
            <div class="relative">
              <TextInput
                id="email"
                type="email"
                class="mt-1 block w-full px-4 py-3 border border-input rounded-xl focus:ring-2 focus:ring-ring focus:border-ring transition-all duration-200 bg-background text-foreground placeholder:text-muted-foreground"
                v-model="form.email"
                required
                autofocus
                autocomplete="username"
                placeholder="Enter your email"
              />
              <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                <svg
                  class="h-5 w-5 text-muted-foreground"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"
                  />
                </svg>
              </div>
            </div>
            <InputError class="mt-2" :message="form.errors.email" />
          </div>

          <!-- Password Field -->
          <div class="space-y-2">
            <InputLabel
              for="password"
              value="Password"
              class="text-foreground font-medium"
            />
            <div class="relative">
              <TextInput
                id="password"
                type="password"
                class="mt-1 block w-full px-4 py-3 border border-input rounded-xl focus:ring-2 focus:ring-ring focus:border-ring transition-all duration-200 bg-background text-foreground placeholder:text-muted-foreground"
                v-model="form.password"
                required
                autocomplete="current-password"
                placeholder="Enter your password"
              />
              <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                <svg
                  class="h-5 w-5 text-muted-foreground"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                  />
                </svg>
              </div>
            </div>
            <InputError class="mt-2" :message="form.errors.password" />
          </div>

          <!-- Remember Me & Forgot Password -->
          <div class="flex items-center justify-between">
            <label class="flex items-center group cursor-pointer">
              <Checkbox
                name="remember"
                v-model:checked="form.remember"
                class="rounded border-input text-primary focus:ring-ring"
              />
              <span
                class="ml-2 text-sm text-muted-foreground group-hover:text-foreground transition-colors duration-200"
                >Remember me</span
              >
            </label>

            <Link
              v-if="canResetPassword"
              :href="route('password.request')"
              class="text-sm text-primary hover:text-primary/80 font-medium hover:underline transition-colors duration-200"
            >
              Forgot password?
            </Link>
          </div>

          <!-- Submit Button -->
          <div class="pt-4">
            <Button
              type="submit"
              class="w-full justify-center font-semibold py-3 px-4 rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200 focus:ring-4 focus:ring-ring/20"
              :class="{ 'opacity-75 cursor-not-allowed': form.processing }"
              :disabled="form.processing"
            >
              <div v-if="!form.processing" class="flex items-center justify-center">
                Sign In
              </div>
              <div v-else class="flex items-center justify-center">Signing in...</div>
            </Button>
          </div>

          <!-- Divider -->
          <div class="relative my-6">
            <div class="absolute inset-0 flex items-center">
              <div class="w-full border-t border-border"></div>
            </div>
            <div class="relative flex justify-center text-sm">
              <span class="px-4 bg-card text-muted-foreground">Or continue with</span>
            </div>
          </div>

          <!-- SSO Button -->
          <div>
            <SsoButton text="Login with Sakti SSO" />
          </div>
        </form>
      </div>

      <!-- Footer - Register link -->
      <div class="text-center space-y-3">
        <p class="text-sm text-muted-foreground">
          Don't have an account?
          <Link
            :href="route('register')"
            class="text-primary hover:text-primary/80 font-medium hover:underline transition-colors duration-200 ml-1"
          >
            Sign up here
          </Link>
        </p>
        <p class="text-sm text-muted-foreground">
          Sistem terbatas untuk admin dan alumni terdaftar
        </p>
      </div>
    </div>
  </div>
</template>
