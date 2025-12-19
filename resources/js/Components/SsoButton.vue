<template>
  <button
    @click="handleSsoRedirect"
    :class="[
      'w-full flex justify-center items-center px-4 py-3 border border-border rounded-xl shadow-sm',
      'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300',
      'hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary',
      'transition-all duration-200 font-medium',
      buttonClass
    ]"
  >
    <!-- Sakti Logo/Icon -->
    <svg class="w-5 h-5 mr-3" viewBox="0 0 24 24" fill="currentColor">
      <path d="M12 2L2 7v10c0 5.55 3.84 9.74 9 9 5.16.74 9-3.45 9-9V7l-10-5z"/>
    </svg>
    {{ text }}
  </button>
</template>

<script setup>
import { router } from '@inertiajs/vue3';

defineProps({
  text: {
    type: String,
    default: 'Continue with Sakti SSO'
  },
  buttonClass: {
    type: String,
    default: ''
  }
});

const handleSsoRedirect = () => {
  // Force full page redirect using window.location to avoid CORS issues
  window.location.href = route('sso.redirect');
};
</script>
