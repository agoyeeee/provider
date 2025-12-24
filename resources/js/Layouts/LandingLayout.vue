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
  <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 overflow-x-hidden">
    <!-- Navigation Header -->
    <header class="fixed top-0 left-0 right-0 z-50 w-full bg-white/85 backdrop-blur-md dark:bg-gray-900/85 supports-[backdrop-filter]:backdrop-blur">
      <nav class="flex items-center justify-between px-6 py-4 lg:px-8 max-w-full shadow-sm">
        <!-- Logo -->
        <button
          type="button"
          @click="scrollToSection('hero')"
          class="flex items-center gap-3 flex-shrink-0 rounded-full bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 px-3 py-2 shadow-md ring-1 ring-slate-900/20 backdrop-blur focus:outline-none dark:bg-gray-900/80 dark:ring-gray-700/80"
        >
          <img
            src="/images/DISPERUMKIMTAN.png"
            alt="DISPERUMKIMTAN Logo"
            class="h-12 w-auto drop-shadow-[0_2px_6px_rgba(255,255,255,0.25)]"
          />
        </button>

        <!-- Navigation Menu -->
        <div class="hidden md:flex items-center space-x-8 flex-shrink-0">
          <button
            type="button"
            @click="scrollToSection('hero')"
            class="text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 font-medium transition-colors whitespace-nowrap"
          >
            Beranda
          </button>
          <button
            type="button"
            @click="scrollToSection('data')"
            class="text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 font-medium transition-colors whitespace-nowrap"
          >
            Data Provider
          </button>
          <Link
            :href="route('landing.map')"
            class="text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 font-medium transition-colors whitespace-nowrap"
          >
            Peta
          </Link>
          <button
            type="button"
            @click="scrollToSection('permohonan')"
            class="text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 font-medium transition-colors whitespace-nowrap"
          >
            Permohonan
          </button>
        </div>

        <!-- Right Side: Theme Toggle + Auth Links -->
        <div class="flex items-center space-x-4 flex-shrink-0">
          <!-- Theme Toggle Button -->
          <button
            @click="toggleTheme"
            class="p-2 rounded-lg bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 transition-all duration-300 ease-in-out transform hover:scale-105"
            :title="isDark ? 'Switch to Light Mode' : 'Switch to Dark Mode'"
          >
            <!-- Sun Icon (Light Mode) -->
            <svg
              v-if="isDark"
              class="w-5 h-5 text-yellow-500 transition-all duration-300 ease-in-out transform"
              :class="{ 'rotate-180 scale-110': !isDark }"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"
              />
            </svg>

            <!-- Moon Icon (Dark Mode) -->
            <svg
              v-else
              class="w-5 h-5 text-gray-700 transition-all duration-300 ease-in-out transform"
              :class="{ 'rotate-12 scale-110': isDark }"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"
              />
            </svg>
          </button>

          <!-- Auth Links -->
          <div v-if="canLogin" class="flex items-center space-x-4">
            <Button
              v-if="$page.props.auth?.user"
              as-child
              class="whitespace-nowrap"
            >
              <Link :href="$page.props.auth.user.role === 'admin' ? route('admin.dashboard') : route('profile.edit')">
                Dashboard
              </Link>
            </Button>

            <template v-else>
              <Button
                as-child
                variant="outline"
                class="whitespace-nowrap"
              >
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
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
          <div class="md:col-span-2">
            <div class="mb-4">
              <div class="h-12 w-12 bg-green-600 rounded-lg flex items-center justify-center">
                <span class="text-white font-bold text-lg">P</span>
              </div>
            </div>
            <p class="text-gray-400 mb-4">
              Portal publikasi data provider utilitas untuk pemetaan konektivitas yang lebih transparan.
            </p>
            <div class="flex space-x-4">
              <a href="#" class="text-gray-400 hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/>
                </svg>
              </a>
              <a href="#" class="text-gray-400 hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M22.46 6c-.77.35-1.6.58-2.46.69.88-.53 1.56-1.37 1.88-2.38-.83.5-1.75.85-2.72 1.05C18.37 4.5 17.26 4 16 4c-2.35 0-4.27 1.92-4.27 4.29 0 .34.04.67.11.98C8.28 9.09 5.11 7.38 3 4.79c-.37.63-.58 1.37-.58 2.15 0 1.49.75 2.81 1.91 3.56-.71 0-1.37-.20-1.95-.5v.03c0 2.08 1.48 3.82 3.44 4.21a4.22 4.22 0 0 1-1.93.07 4.28 4.28 0 0 0 4 2.98 8.521 8.521 0 0 1-5.33 1.84c-.34 0-.68-.02-1.02-.06C3.44 20.29 5.7 21 8.12 21 16 21 20.33 14.46 20.33 8.79c0-.19 0-.37-.01-.56.84-.60 1.56-1.36 2.14-2.23z"/>
                </svg>
              </a>
              <a href="#" class="text-gray-400 hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                </svg>
              </a>
            </div>
          </div>

          <div>
            <h4 class="font-semibold mb-4">Menu</h4>
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

          <div>
            <h4 class="font-semibold mb-4">Kontak</h4>
            <ul class="space-y-2 text-gray-400">
              <li>Jl. Telekomunikasi No.10</li>
              <li>Komplek Data Center</li>
              <li>DKI Jakarta, Indonesia</li>
              <li>(021) 555-1234</li>
            </ul>
          </div>
        </div>

        <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
          <p>&copy; 2025 UMKM Karanganyar. All rights reserved.</p>
        </div>
      </div>
    </footer>
  </div>
</template>

<style scoped>
/* Tambahkan CSS untuk memastikan tidak ada horizontal scroll */
html, body {
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
.shadow-lg, .shadow-xl {
  transition: box-shadow 0.3s ease-in-out;
}

/* Text color transitions */
.text-gray-700, .text-gray-300, .text-gray-900, .text-white {
  transition: color 0.2s ease-in-out;
}

/* Background color transitions */
.bg-white, .bg-gray-800, .bg-gray-900, .bg-black {
  transition: background-color 0.2s ease-in-out;
}
</style>
