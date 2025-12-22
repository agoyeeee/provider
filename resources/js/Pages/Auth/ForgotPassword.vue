<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <Head title="Forgot Password" />

    <!-- Modern Forgot Password Container -->
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 via-white to-purple-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <!-- Header Section -->
            <div class="text-center">
                <div class="mx-auto h-16 w-16 bg-gradient-to-r from-orange-500 to-red-600 dark:from-orange-400 dark:to-red-500 rounded-full flex items-center justify-center mb-6 shadow-lg">
                    <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                </div>
                <h2 class="text-3xl font-bold text-foreground mb-2">Forgot Password?</h2>
                <p class="text-muted-foreground">No problem, we'll send you a reset link</p>
            </div>

            <!-- Status Message -->
            <div v-if="status" class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-300 px-4 py-3 rounded-lg text-sm font-medium">
                {{ status }}
            </div>

            <!-- Forgot Password Form Card -->
            <div class="bg-card shadow-xl rounded-2xl p-8 border border-border">
                <div class="mb-6 text-sm text-muted-foreground text-center">
                    Enter your email address and we'll send you a password reset link that will allow you to choose a new one.
                </div>

                <form @submit.prevent="submit" class="space-y-6">
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
                                autofocus
                                autocomplete="username"
                                placeholder="Enter your email address"
                            />
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <svg class="h-5 w-5 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                                </svg>
                            </div>
                        </div>
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-4">
                        <PrimaryButton
                            class="w-full justify-center bg-primary hover:bg-primary/90 text-primary-foreground font-semibold py-3 px-4 rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200 focus:ring-4 focus:ring-ring/20"
                            :class="{ 'opacity-75 cursor-not-allowed': form.processing }"
                            :disabled="form.processing"
                        >
                            <div v-if="!form.processing" class="flex items-center justify-center">
                                Send Reset Link
                            </div>
                            <div v-else class="flex items-center justify-center">Sending...</div>
                        </PrimaryButton>
                    </div>
                </form>
            </div>

            <!-- Footer - Register link removed -->
            <div class="text-center">
                <p class="text-sm text-muted-foreground">
                    Remember your password?
                    <Link
                        :href="route('login')"
                        class="font-medium text-primary hover:text-primary/80 hover:underline transition-colors duration-200"
                    >
                        Back to sign in
                    </Link>
                </p>
            </div>
        </div>
    </div>
</template>
