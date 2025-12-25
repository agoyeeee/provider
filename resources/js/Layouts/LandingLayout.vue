<script setup>
import { Link } from "@inertiajs/vue3";
import { Button } from "@/Components/ui/button";
import { ref, onMounted } from "vue";

defineProps({
  canLogin: {
    type: Boolean,
    default: true,
  },
  canRegister: {
    type: Boolean,
    default: false, // Disabled registration
  },
  showFooter: {
    type: Boolean,
    default: true,
  },
});

// Theme management
const isDark = ref(false);

// Initialize theme on mount
onMounted(() => {
  // Check for saved theme preference or default to system preference
  const savedTheme = localStorage.getItem('theme');
  const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

  if (savedTheme === 'dark' || (!savedTheme && systemPrefersDark)) {
    isDark.value = true;
    document.documentElement.classList.add('dark');
  } else {
    isDark.value = false;
    document.documentElement.classList.remove('dark');
  }
});

// Toggle theme function with smooth transition
const toggleTheme = () => {
  document.documentElement.classList.add('theme-transition');
  isDark.value = !isDark.value;

  if (isDark.value) {
    document.documentElement.classList.add('dark');
    localStorage.setItem('theme', 'dark');
  } else {
    document.documentElement.classList.remove('dark');
    localStorage.setItem('theme', 'light');
  }

  setTimeout(() => {
    document.documentElement.classList.remove('theme-transition');
  }, 300);
};

const scrollToSection = (sectionId) => {
  const section = document.getElementById(sectionId);

  if (section) {
    section.scrollIntoView({
      behavior: 'smooth',
      block: 'start',
    });
  } else {
    window.location.href = `/#${sectionId}`;
  }
};
</script>

<template>
  <div
    class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 overflow-x-hidden">
    <!-- Navigation Header -->
    <header
      class="fixed top-0 left-0 right-0 z-50 w-full bg-white/85 backdrop-blur-md dark:bg-gray-900/85 supports-[backdrop-filter]:backdrop-blur">
      <nav class="flex items-center justify-between px-6 py-4 lg:px-8 max-w-full shadow-sm">
        <!-- Logo -->
        <button type="button" @click="scrollToSection('hero')"
          class="flex items-center gap-3 flex-shrink-0 rounded-full bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 px-3 py-2 shadow-md ring-1 ring-slate-900/20 backdrop-blur focus:outline-none dark:bg-gray-900/80 dark:ring-gray-700/80">
          <img src="/images/DISPERUMKIMTAN.png" alt="DISPERUMKIMTAN Logo"
            class="h-12 w-auto drop-shadow-[0_2px_6px_rgba(255,255,255,0.25)]" />
        </button>

        <!-- Navigation Menu -->
        <div class="hidden md:flex items-center space-x-8 flex-shrink-0">
          <button type="button" @click="scrollToSection('hero')"
            class="text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 font-medium transition-colors whitespace-nowrap">
            Beranda
          </button>
          <button type="button" @click="scrollToSection('data')"
            class="text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 font-medium transition-colors whitespace-nowrap">
            Data Provider
          </button>
          <Link :href="route('landing.map')"
            class="text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 font-medium transition-colors whitespace-nowrap">
            Peta
          </Link>
          <button type="button" @click="scrollToSection('permohonan')"
            class="text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 font-medium transition-colors whitespace-nowrap">
            Permohonan
          </button>
        </div>

        <!-- Right Side: Theme Toggle + Auth Links -->
        <div class="flex items-center space-x-4 flex-shrink-0">
          <!-- Theme Toggle Button -->
          <button @click="toggleTheme"
            class="p-2 rounded-lg bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 transition-all duration-300 ease-in-out transform hover:scale-105"
            :title="isDark ? 'Switch to Light Mode' : 'Switch to Dark Mode'">
            <!-- Sun Icon (Light Mode) -->
            <svg v-if="isDark" class="w-5 h-5 text-yellow-500 transition-all duration-300 ease-in-out transform"
              :class="{ 'rotate-180 scale-110': !isDark }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>

            <!-- Moon Icon (Dark Mode) -->
            <svg v-else class="w-5 h-5 text-gray-700 transition-all duration-300 ease-in-out transform"
              :class="{ 'rotate-12 scale-110': isDark }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
            </svg>
          </button>

          <!-- Auth Links -->
          <div v-if="canLogin" class="flex items-center space-x-4">
            <Button v-if="$page.props.auth?.user" as-child class="whitespace-nowrap">
              <Link :href="$page.props.auth.user.role === 'admin' ? route('admin.dashboard') : route('profile.edit')">
                Dashboard
              </Link>
            </Button>

            <template v-else>
              <Button as-child variant="outline" class="whitespace-nowrap">
                <Link :href="route('login')">
                  Log in
                </Link>
              </Button>
            </template>
          </div>
        </div>
      </nav>
    </header>

    <!-- Main Content -->
    <main class="relative w-full overflow-x-hidden pt-20 lg:pt-24">
      <slot />
    </main>

    <footer v-if="showFooter" class="bg-gray-900 dark:bg-black text-white py-12 w-full overflow-x-hidden">
      <div class="container mx-auto px-6 max-w-full">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <!-- Column 1: Logo, Description, and Social Media -->
          <div class="text-center md:text-left">
            <div class="mb-4">
              <img src="/images/DISPERUMKIMTAN.png" alt="DISPERUMKIMTAN Logo" class="h-16 w-auto mx-auto md:mx-0" />
            </div>
            <p class="text-gray-400 mb-4">
              Dinas Perumahan, dan Kawasan Permukiman serta Pertanahan<br />Kota Surakarta © 2025.
            </p>
            <div class="flex justify-center md:justify-start space-x-4 mt-4">
              <!-- Instagram -->
              <a href="https://www.instagram.com/perkimsolo/" target="_blank" rel="noopener noreferrer"
                class="text-gray-400 hover:text-pink-500 transition-colors">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                  <path
                    d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" />
                </svg>
              </a>
              <!-- YouTube -->
              <a href="https://www.youtube.com/@disperumkimtansurakarta" target="_blank" rel="noopener noreferrer"
                class="text-gray-400 hover:text-red-500 transition-colors">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                  <path
                    d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z" />
                </svg>
              </a>
            </div>
          </div>

          <!-- Column 2: Menu -->
          <div class="text-center md:text-left">
            <h4 class="font-semibold mb-4 text-lg">Menu</h4>
            <ul class="space-y-2 text-gray-400">
              <li>
                <button @click="scrollToSection('hero')" class="hover:text-white transition-colors text-left">
                  Beranda
                </button>
              </li>
              <li>
                <button @click="scrollToSection('data')" class="hover:text-white transition-colors text-left">
                  Data Provider
                </button>
              </li>
              <li>
                <Link :href="route('landing.map')" class="hover:text-white transition-colors text-left">
                  Peta
                </Link>
              </li>
              <li>
                <button @click="scrollToSection('permohonan')" class="hover:text-white transition-colors text-left">
                  Permohonan
                </button>
              </li>
              <li>
                <button @click="scrollToSection('timeline')" class="hover:text-white transition-colors text-left">
                  Pembaruan
                </button>
              </li>
            </ul>
          </div>

          <!-- Column 3: Contact Information -->
          <div class="text-center md:text-left">
            <h4 class="font-semibold mb-4 text-lg">Kontak</h4>
            <ul class="space-y-3 text-gray-400">
              <li class="flex items-center justify-center md:justify-start gap-3">
                <svg class="w-5 h-5 flex-shrink-0 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                  <path
                    d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z" />
                </svg>
                <a href="tel:(0271)644320" class="hover:text-white transition-colors">(0271)644320</a>
              </li>
              <li class="flex items-center justify-center md:justify-start gap-3">
                <svg class="w-5 h-5 flex-shrink-0 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                  <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                </svg>
                <a href="mailto:disperumkimtan@surakarta.go.id"
                  class="hover:text-white transition-colors">disperumkimtan@surakarta.go.id</a>
              </li>
              <li class="flex items-start justify-center md:justify-start gap-3">
                <svg class="w-5 h-5 flex-shrink-0 text-blue-400 mt-1" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd"
                    d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"
                    clip-rule="evenodd" />
                </svg>
                <span class="text-left">
                  Jalan Yosodipuro No. 164, Mangkubumen, Banjarsari<br />Kota Surakarta, Jawa Tengah
                </span>
              </li>
            </ul>
          </div>
        </div>

        <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
          <p>&copy; 2025 Dinas Perumahan, dan Kawasan Permukiman serta Pertanahan Kota Surakarta. All rights reserved.
          </p>
        </div>
      </div>
    </footer>
  </div>
</template>

<style scoped>
/* Tambahkan CSS untuk memastikan tidak ada horizontal scroll */
html,
body {
  overflow-x: hidden;
  max-width: 100%;
}

* {
  box-sizing: border-box;
}

/* Global theme transition styles */
:global(.theme-transition),
:global(.theme-transition *),
:global(.theme-transition *:before),
:global(.theme-transition *:after) {
  transition: background-color 0.3s ease-in-out,
    border-color 0.3s ease-in-out,
    color 0.3s ease-in-out,
    fill 0.3s ease-in-out,
    stroke 0.3s ease-in-out,
    opacity 0.3s ease-in-out,
    box-shadow 0.3s ease-in-out,
    transform 0.3s ease-in-out !important;
}

/* Smooth transitions for theme toggle button */
.theme-toggle-button {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.theme-toggle-button:hover {
  transform: scale(1.05);
}

/* Icon rotation animations */
.theme-icon {
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.theme-icon.rotate-in {
  transform: rotate(180deg) scale(1.1);
}

.theme-icon.rotate-out {
  transform: rotate(-180deg) scale(0.9);
}

/* Enhanced CSS for better transitions */
* {
  transition: background-color 0.2s ease-in-out,
    border-color 0.2s ease-in-out,
    color 0.2s ease-in-out;
}

/* Ensure smooth background transitions */
.bg-gradient-to-br {
  transition: background 0.3s ease-in-out;
}

/* Smooth shadow transitions */
.shadow-lg,
.shadow-xl {
  transition: box-shadow 0.3s ease-in-out;
}

/* Text color transitions */
.text-gray-700,
.text-gray-300,
.text-gray-900,
.text-white {
  transition: color 0.2s ease-in-out;
}

/* Background color transitions */
.bg-white,
.bg-gray-800,
.bg-gray-900,
.bg-black {
  transition: background-color 0.2s ease-in-out;
}
</style>
