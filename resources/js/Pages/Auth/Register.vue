<script setup>
import InputError from '@/components/InputError.vue';
import InputLabel from '@/components/InputLabel.vue';
import PrimaryButton from '@/components/PrimaryButton.vue';
import SsoButton from '@/components/SsoButton.vue';
import TextInput from '@/components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    nik: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Register" />

    <!-- Modern Register Container -->
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 via-white to-purple-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <!-- Header Section -->
            <div class="text-center">
                <div class="mx-auto h-16 w-16 bg-gradient-to-r from-green-500 to-blue-600 dark:from-green-400 dark:to-blue-500 rounded-full flex items-center justify-center mb-6 shadow-lg">
                    <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                </div>
                <h2 class="text-3xl font-bold text-foreground mb-2">Create Account</h2>
                <p class="text-muted-foreground">Join our alumni community</p>
            </div>

            <!-- Register Form Card -->
            <div class="bg-card shadow-xl rounded-2xl p-8 border border-border">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Name Field -->
                    <div class="space-y-2">
                        <InputLabel for="name" value="Full Name" class="text-foreground font-medium"/>
                        <div class="relative">
                            <TextInput
                                id="name"
                                type="text"
                                class="mt-1 block w-full px-4 py-3 border border-input rounded-xl focus:ring-2 focus:ring-ring focus:border-ring transition-all duration-200 bg-background text-foreground placeholder:text-muted-foreground"
                                v-model="form.name"
                                required
                                autofocus
                                autocomplete="name"
                                placeholder="Enter your full name"
                            />
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <svg class="h-5 w-5 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                        </div>
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>

                    <!-- Email Field -->
                    <div class="space-y-2">
                        <InputLabel for="email" value="Email Address" class="text-foreground font-medium"/>
                        <div class="relative">
                            <TextInput
                                id="email"
                                type="email"
                                class="mt-1 block w-full px-4 py-3 border border-input rounded-xl focus:ring-2 focus:ring-ring focus:border-ring transition-all duration-200 bg-background text-foreground placeholder:text-muted-foreground"
                                v-model="form.email"
                                required
                                autocomplete="username"
                                placeholder="Enter your email"
                            />
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <svg class="h-5 w-5 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                                </svg>
                            </div>
                        </div>
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <!-- NIK Field -->
                    <div class="space-y-2">
                        <InputLabel for="nik" value="NIK (Nomor Induk Kependudukan)" class="text-foreground font-medium"/>
                        <div class="relative">
                            <TextInput
                                id="nik"
                                type="text"
                                class="mt-1 block w-full px-4 py-3 border border-input rounded-xl focus:ring-2 focus:ring-ring focus:border-ring transition-all duration-200 bg-background text-foreground placeholder:text-muted-foreground"
                                v-model="form.nik"
                                required
                                autocomplete="off"
                                placeholder="Enter your NIK (16 digits)"
                                maxlength="16"
                                pattern="[0-9]{16}"
                            />
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <svg class="h-5 w-5 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/>
                                </svg>
                            </div>
                        </div>
                        <InputError class="mt-2" :message="form.errors.nik" />
                        <p class="text-xs text-muted-foreground">Enter your 16-digit National Identity Number</p>
                    </div>

                    <!-- Password Field -->
                    <div class="space-y-2">
                        <InputLabel for="password" value="Password" class="text-foreground font-medium"/>
                        <div class="relative">
                            <TextInput
                                id="password"
                                type="password"
                                class="mt-1 block w-full px-4 py-3 border border-input rounded-xl focus:ring-2 focus:ring-ring focus:border-ring transition-all duration-200 bg-background text-foreground placeholder:text-muted-foreground"
                                v-model="form.password"
                                required
                                autocomplete="new-password"
                                placeholder="Create a password"
                            />
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <svg class="h-5 w-5 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                        </div>
                        <InputError class="mt-2" :message="form.errors.password" />
                    </div>

                    <!-- Confirm Password Field -->
                    <div class="space-y-2">
                        <InputLabel for="password_confirmation" value="Confirm Password" class="text-foreground font-medium"/>
                        <div class="relative">
                            <TextInput
                                id="password_confirmation"
                                type="password"
                                class="mt-1 block w-full px-4 py-3 border border-input rounded-xl focus:ring-2 focus:ring-ring focus:border-ring transition-all duration-200 bg-background text-foreground placeholder:text-muted-foreground"
                                v-model="form.password_confirmation"
                                required
                                autocomplete="new-password"
                                placeholder="Confirm your password"
                            />
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <svg class="h-5 w-5 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                        <InputError class="mt-2" :message="form.errors.password_confirmation" />
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-4">
                        <PrimaryButton
                            class="w-full justify-center bg-primary hover:bg-primary/90 text-primary-foreground font-semibold py-3 px-4 rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200 focus:ring-4 focus:ring-ring/20"
                            :class="{ 'opacity-75 cursor-not-allowed': form.processing }"
                            :disabled="form.processing"
                        >
                            <div v-if="!form.processing" class="flex items-center justify-center">
                                Create Account
                            </div>
                            <div v-else class="flex items-center justify-center">Creating account...</div>
                        </PrimaryButton>
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
                        <SsoButton text="Register with Sakti SSO" />
                    </div>
                </form>
            </div>

            <!-- Footer -->
            <div class="text-center">
                <p class="text-sm text-muted-foreground">
                    Already have an account?
                    <Link
                        :href="route('login')"
                        class="font-medium text-primary hover:text-primary/80 hover:underline transition-colors duration-200"
                    >
                        Sign in here
                    </Link>
                </p>
            </div>
        </div>
    </div>
</template>
