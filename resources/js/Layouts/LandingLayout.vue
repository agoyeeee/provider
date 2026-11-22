<script setup>
import { Link, usePage, router } from "@inertiajs/vue3";
import { buttonVariants } from "@/Components/ui/button";
import { ref, computed, onMounted, onUnmounted } from "vue";
import {
  Menu,
  X,
  ArrowRight,
  MapPin,
  Mail,
  Phone,
  Globe,
  Instagram,
  Youtube,
} from "lucide-vue-next";

defineProps({
  canLogin: {
    type: Boolean,
    default: true,
  },
  canRegister: {
    type: Boolean,
    default: false,
  },
  showFooter: {
    type: Boolean,
    default: true,
  },
});

const page = usePage();
const currentUrl = computed(() => page.url || "/");
const isMapPage = computed(() => currentUrl.value.startsWith("/peta"));

const isScrolled = ref(false);
const scrollProgress = ref(0);
const isSidebarOpen = ref(false);
const activeSection = ref("hero");

const sectionIds = ["hero", "statistik", "data", "permohonan"];

const updateActiveSection = () => {
  if (isMapPage.value) {
    activeSection.value = "peta";
    return;
  }

  const scrollPos = window.scrollY + 140;
  let current = "hero";

  for (const id of sectionIds) {
    const el = document.getElementById(id);
    if (el && el.offsetTop <= scrollPos) {
      current = id;
    }
  }

  activeSection.value = current;
};

const handleScroll = () => {
  const scrollTop = window.scrollY || document.documentElement.scrollTop;
  isScrolled.value = scrollTop > 40;

  const docHeight =
    document.documentElement.scrollHeight -
    document.documentElement.clientHeight;
  scrollProgress.value =
    docHeight > 0 ? Math.min(100, Math.round((scrollTop / docHeight) * 100)) : 0;

  updateActiveSection();
};

const handleEscape = (e) => {
  if (e.key === "Escape") {
    isSidebarOpen.value = false;
  }
};

onMounted(() => {
  document.documentElement.classList.remove("dark");

  handleScroll();
  window.addEventListener("scroll", handleScroll, { passive: true });
  document.addEventListener("keydown", handleEscape);
});

onUnmounted(() => {
  window.removeEventListener("scroll", handleScroll);
  document.removeEventListener("keydown", handleEscape);
});

const closeSidebar = () => {
  isSidebarOpen.value = false;
};

const scrollToSection = (sectionId) => {
  closeSidebar();
  const section = document.getElementById(sectionId);

  if (section) {
    activeSection.value = sectionId;
    section.scrollIntoView({
      behavior: "smooth",
      block: "start",
    });
  } else {
    router.visit(`/#${sectionId}`);
  }
};

const isNavActive = (key) => {
  if (key === "peta") return isMapPage.value;
  if (isMapPage.value) return false;
  return activeSection.value === key;
};

const socialLinks = [
  {
    label: "Instagram",
    url: "https://www.instagram.com/perkimsolo/",
    icon: Instagram,
  },
  {
    label: "YouTube",
    url: "https://www.youtube.com/@disperumkimtansurakarta",
    icon: Youtube,
  },
  {
    label: "Website Resmi",
    url: "https://disperumkimtan.surakarta.go.id/",
    icon: Globe,
  },
];
</script>

<template>
  <div
    class="min-h-screen flex flex-col bg-white text-slate-900 overflow-x-hidden selection:bg-emerald-500/20 selection:text-emerald-900"
  >
    <!-- Scroll Progress Bar -->
    <div
      class="fixed top-0 left-0 right-0 z-[60] h-[2.5px] bg-transparent pointer-events-none"
    >
      <div
        class="h-full bg-gradient-to-r from-emerald-500 via-teal-400 to-sky-500 transition-[width] duration-150 ease-out"
        :style="{ width: `${scrollProgress}%` }"
      ></div>
    </div>

    <!-- Navigation Header (Full-width at top -> Floating rounded bar on scroll) -->
    <header
      :class="[
        'fixed top-0 left-0 right-0 z-50 flex justify-center transition-all duration-500 pointer-events-none',
        isScrolled ? 'pt-3 px-3 sm:pt-4 sm:px-6' : 'pt-0 px-0',
      ]"
    >
      <nav
        :class="[
          'pointer-events-auto flex items-center justify-between w-full mx-auto backdrop-blur-xl transition-all duration-500',
          isScrolled
            ? 'max-w-6xl px-4 sm:px-6 py-2.5 rounded-full bg-white/90 dark:bg-slate-900/90 border border-slate-200/80 dark:border-slate-700/70 shadow-[0_12px_40px_rgba(15,23,42,0.12)] dark:shadow-[0_12px_40px_rgba(0,0,0,0.45)]'
            : 'max-w-full px-4 sm:px-8 lg:px-12 py-3 rounded-none bg-white/80 dark:bg-slate-950/75 border-b border-slate-200/60 dark:border-slate-800/70 shadow-sm',
        ]"
      >
        <!-- Left: Logo + Wordmark -->
        <button
          type="button"
          @click="scrollToSection('hero')"
          class="group flex items-center gap-2.5 flex-shrink-0 focus:outline-none text-left"
        >
          <span
            class="flex items-center justify-center rounded-xl bg-slate-900 px-2.5 py-1.5 shadow-sm ring-1 ring-slate-900/10 transition-transform duration-300 group-hover:scale-105 dark:bg-slate-800 dark:ring-white/10"
          >
            <img
              src="/images/DISPERUMKIMTAN.png"
              alt="DISPERUMKIMTAN Logo"
              class="h-7 sm:h-8 w-auto"
            />
          </span>
          <div class="hidden sm:block leading-tight">
            <p
              class="text-sm font-extrabold tracking-wide text-slate-900 dark:text-white uppercase"
            >
              SIPJALIN
            </p>
            <p
              class="text-[10px] font-semibold tracking-wider text-slate-500 dark:text-slate-400 uppercase"
            >
              Surakarta
            </p>
          </div>
        </button>

        <!-- Center Navigation Links (Active Pill style adapted from ASKARA) -->
        <div
          class="hidden md:flex items-center justify-center gap-1 rounded-full p-1 transition-colors"
          :class="
            isScrolled
              ? 'bg-slate-100/80 dark:bg-slate-800/70 border border-slate-200/60 dark:border-slate-700/60'
              : 'bg-slate-100/70 dark:bg-slate-900/70 border border-slate-200/60 dark:border-slate-800'
          "
        >
          <button
            type="button"
            @click="scrollToSection('hero')"
            :class="[
              'px-4 py-1.5 text-sm font-semibold rounded-full transition-all duration-300 whitespace-nowrap',
              isNavActive('hero')
                ? 'bg-slate-900 text-white shadow-sm dark:bg-emerald-500 dark:text-slate-950'
                : 'text-slate-600 hover:text-emerald-700 hover:bg-emerald-500/10 dark:text-slate-300 dark:hover:text-emerald-300',
            ]"
          >
            Beranda
          </button>
          <button
            type="button"
            @click="scrollToSection('statistik')"
            :class="[
              'px-4 py-1.5 text-sm font-semibold rounded-full transition-all duration-300 whitespace-nowrap',
              isNavActive('statistik')
                ? 'bg-slate-900 text-white shadow-sm dark:bg-emerald-500 dark:text-slate-950'
                : 'text-slate-600 hover:text-emerald-700 hover:bg-emerald-500/10 dark:text-slate-300 dark:hover:text-emerald-300',
            ]"
          >
            Statistik
          </button>
          <button
            type="button"
            @click="scrollToSection('data')"
            :class="[
              'px-4 py-1.5 text-sm font-semibold rounded-full transition-all duration-300 whitespace-nowrap',
              isNavActive('data')
                ? 'bg-slate-900 text-white shadow-sm dark:bg-emerald-500 dark:text-slate-950'
                : 'text-slate-600 hover:text-emerald-700 hover:bg-emerald-500/10 dark:text-slate-300 dark:hover:text-emerald-300',
            ]"
          >
            Data Provider
          </button>
          <Link
            :href="route('landing.map')"
            :class="[
              'px-4 py-1.5 text-sm font-semibold rounded-full transition-all duration-300 whitespace-nowrap',
              isNavActive('peta')
                ? 'bg-slate-900 text-white shadow-sm dark:bg-emerald-500 dark:text-slate-950'
                : 'text-slate-600 hover:text-emerald-700 hover:bg-emerald-500/10 dark:text-slate-300 dark:hover:text-emerald-300',
            ]"
          >
            Peta
          </Link>
          <button
            type="button"
            @click="scrollToSection('permohonan')"
            :class="[
              'px-4 py-1.5 text-sm font-semibold rounded-full transition-all duration-300 whitespace-nowrap',
              isNavActive('permohonan')
                ? 'bg-slate-900 text-white shadow-sm dark:bg-emerald-500 dark:text-slate-950'
                : 'text-slate-600 hover:text-emerald-700 hover:bg-emerald-500/10 dark:text-slate-300 dark:hover:text-emerald-300',
            ]"
          >
            Permohonan
          </button>
        </div>

        <!-- Right: Auth + Mobile Hamburger -->
        <div class="flex items-center justify-end gap-2 flex-shrink-0">
          <!-- Auth Button -->
          <div v-if="canLogin" class="hidden sm:flex items-center">
            <Link
              v-if="$page.props.auth?.user"
              :href="
                $page.props.auth?.user?.role === 'admin'
                  ? route('admin.dashboard')
                  : route('profile.edit')
              "
              :class="[
                buttonVariants({ size: 'sm' }),
                'rounded-full bg-emerald-600 px-4 text-white shadow-sm hover:bg-emerald-700 dark:bg-emerald-500 dark:text-slate-950 dark:hover:bg-emerald-400 whitespace-nowrap',
              ]"
            >
              Dashboard
            </Link>

            <Link
              v-else
              :href="route('login')"
              :class="[
                buttonVariants({ variant: 'outline', size: 'sm' }),
                'rounded-full border-slate-200 bg-white px-4 font-semibold text-slate-800 shadow-sm hover:border-emerald-300 hover:bg-emerald-50 hover:text-emerald-800 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:hover:bg-slate-700 whitespace-nowrap',
              ]"
            >
              Log in
            </Link>
          </div>

          <!-- Mobile Hamburger -->
          <button
            type="button"
            @click="isSidebarOpen = !isSidebarOpen"
            :class="[
              'flex md:hidden h-9 w-9 items-center justify-center rounded-full transition-all duration-300 active:scale-90',
              isScrolled
                ? 'bg-slate-900 text-white shadow-md dark:bg-emerald-500 dark:text-slate-950'
                : 'bg-slate-900/10 text-slate-800 dark:bg-white/10 dark:text-slate-100',
              { 'rotate-90': isSidebarOpen },
            ]"
            aria-label="Toggle menu"
          >
            <Menu class="h-5 w-5" />
          </button>
        </div>
      </nav>
    </header>

    <!-- Mobile Overlay -->
    <div
      v-if="isSidebarOpen"
      @click="closeSidebar"
      class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-sm transition-opacity duration-300 md:hidden"
    ></div>

    <!-- Mobile Slide-In Drawer (from left, matching PKKMB-SV-2026) -->
    <aside
      :class="[
        'fixed top-0 left-0 z-[55] h-full w-[280px] sm:w-[320px] bg-white dark:bg-slate-950 shadow-2xl md:hidden transform transition-transform duration-300 ease-out flex flex-col border-r border-slate-200/80 dark:border-slate-800',
        isSidebarOpen ? 'translate-x-0' : '-translate-x-full',
      ]"
    >
      <div
        class="flex items-center justify-between p-4 border-b border-slate-200/80 dark:border-slate-800"
      >
        <div class="flex items-center gap-2.5">
          <span class="rounded-lg bg-slate-900 px-2 py-1 dark:bg-slate-800">
            <img
              src="/images/DISPERUMKIMTAN.png"
              alt="Logo"
              class="h-7 w-auto"
            />
          </span>
          <span class="font-extrabold text-sm tracking-wide text-slate-900 dark:text-white">
            SIPJALIN
          </span>
        </div>
        <button
          type="button"
          @click="closeSidebar"
          class="p-2 rounded-full text-slate-500 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800"
          aria-label="Close menu"
        >
          <X class="h-5 w-5" />
        </button>
      </div>

      <nav class="flex-1 overflow-y-auto p-4">
        <ul class="space-y-1.5">
          <li>
            <button
              type="button"
              @click="scrollToSection('hero')"
              :class="[
                'w-full px-4 py-3 text-sm font-semibold text-left rounded-xl transition-colors',
                isNavActive('hero')
                  ? 'bg-slate-900 text-white dark:bg-emerald-500 dark:text-slate-950'
                  : 'text-slate-700 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-900',
              ]"
            >
              Beranda
            </button>
          </li>
          <li>
            <button
              type="button"
              @click="scrollToSection('statistik')"
              :class="[
                'w-full px-4 py-3 text-sm font-semibold text-left rounded-xl transition-colors',
                isNavActive('statistik')
                  ? 'bg-slate-900 text-white dark:bg-emerald-500 dark:text-slate-950'
                  : 'text-slate-700 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-900',
              ]"
            >
              Statistik
            </button>
          </li>
          <li>
            <button
              type="button"
              @click="scrollToSection('data')"
              :class="[
                'w-full px-4 py-3 text-sm font-semibold text-left rounded-xl transition-colors',
                isNavActive('data')
                  ? 'bg-slate-900 text-white dark:bg-emerald-500 dark:text-slate-950'
                  : 'text-slate-700 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-900',
              ]"
            >
              Data Provider
            </button>
          </li>
          <li>
            <Link
              :href="route('landing.map')"
              @click="closeSidebar"
              :class="[
                'block w-full px-4 py-3 text-sm font-semibold text-left rounded-xl transition-colors',
                isNavActive('peta')
                  ? 'bg-slate-900 text-white dark:bg-emerald-500 dark:text-slate-950'
                  : 'text-slate-700 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-900',
              ]"
            >
              Peta Interaktif
            </Link>
          </li>
          <li>
            <button
              type="button"
              @click="scrollToSection('permohonan')"
              :class="[
                'w-full px-4 py-3 text-sm font-semibold text-left rounded-xl transition-colors',
                isNavActive('permohonan')
                  ? 'bg-slate-900 text-white dark:bg-emerald-500 dark:text-slate-950'
                  : 'text-slate-700 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-900',
              ]"
            >
              Permohonan
            </button>
          </li>
        </ul>
      </nav>

      <div
        v-if="canLogin"
        class="p-4 border-t border-slate-200/80 dark:border-slate-800"
      >
        <Link
          v-if="$page.props.auth?.user"
          :href="
            $page.props.auth?.user?.role === 'admin'
              ? route('admin.dashboard')
              : route('profile.edit')
          "
          class="block w-full rounded-full bg-emerald-600 px-4 py-2.5 text-center text-sm font-semibold text-white hover:bg-emerald-700 dark:bg-emerald-500 dark:text-slate-950"
        >
          Dashboard
        </Link>
        <Link
          v-else
          :href="route('login')"
          class="block w-full rounded-full border border-slate-300 px-4 py-2.5 text-center text-sm font-semibold text-slate-800 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-100 dark:hover:bg-slate-900"
        >
          Log in
        </Link>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="relative flex-1 w-full overflow-x-hidden pt-16 lg:pt-[70px]">
      <slot />
    </main>

    <!-- Wave Divider + Footer (Adapted from PKKMB-SV-2026 with SIPJALIN colors) -->
    <div v-if="showFooter" class="flex flex-col w-full">
      <!-- Wave SVG Divider -->
      <div
        class="relative w-full overflow-hidden leading-none -mb-[1px]"
        aria-hidden="true"
      >
        <svg
          viewBox="0 0 1440 72"
          xmlns="http://www.w3.org/2000/svg"
          preserveAspectRatio="none"
          class="block w-full h-[56px] sm:h-[72px]"
        >
          <!-- Primary footer fill -->
          <path
            d="M0,72 L0,36 C240,72 480,4 720,28 C960,52 1200,0 1440,24 L1440,72 Z"
            fill="#0b1120"
          />
          <!-- Secondary translucent depth wave -->
          <path
            d="M0,72 L0,48 C200,80 440,12 720,40 C1000,68 1260,8 1440,36 L1440,72 Z"
            fill="#0b1120"
            opacity="0.65"
          />
        </svg>
      </div>

      <!-- Footer Body -->
      <footer class="bg-[#0b1120] text-slate-200">
        <div class="max-w-6xl px-6 pt-10 pb-6 mx-auto md:px-12 md:pt-14 md:pb-8">
          <div class="grid grid-cols-1 gap-10 md:grid-cols-3 md:gap-8 lg:gap-16">
            <!-- Col 1 — Brand + Description + Social Media -->
            <div class="space-y-5">
              <div class="flex items-center gap-3">
                <img
                  src="/images/DISPERUMKIMTAN.png"
                  alt="DISPERUMKIMTAN Kota Surakarta"
                  class="w-auto h-11"
                />
                <div class="leading-tight">
                  <p class="font-black tracking-[0.14em] text-white text-base uppercase">
                    SIPJALIN
                  </p>
                  <p class="text-[10px] tracking-widest text-emerald-400/80 font-semibold uppercase">
                    DISPERUMKIMTAN Surakarta
                  </p>
                </div>
              </div>

              <p class="text-sm text-slate-400 leading-relaxed max-w-[280px]">
                Portal resmi Sistem Informasi Provider Jalan Lingkungan Kota Surakarta. Pantau sebaran jaringan dan data perizinan utilitas secara terpadu.
              </p>

              <!-- Social Media Buttons -->
              <div class="flex flex-wrap gap-2.5">
                <a
                  v-for="social in socialLinks"
                  :key="social.label"
                  :href="social.url"
                  target="_blank"
                  rel="noopener noreferrer"
                  :title="social.label"
                  class="flex items-center justify-center w-9 h-9 rounded-xl bg-white/10 border border-white/10 text-slate-300 transition-all duration-300 hover:bg-emerald-500 hover:border-emerald-500 hover:text-slate-950 hover:scale-110 hover:-translate-y-0.5"
                >
                  <component :is="social.icon" class="w-4 h-4" />
                </a>
              </div>
            </div>

            <!-- Col 2 — JELAJAHI -->
            <div class="space-y-5">
              <div class="flex items-center gap-3">
                <span class="w-6 h-0.5 bg-emerald-400 rounded-full"></span>
                <h3 class="text-xs font-bold tracking-[0.2em] text-slate-400 uppercase">
                  Jelajahi
                </h3>
              </div>

              <ul class="space-y-3 text-sm">
                <li>
                  <button
                    type="button"
                    @click="scrollToSection('hero')"
                    class="group inline-flex items-center gap-2.5 text-slate-300/85 hover:text-white transition-colors"
                  >
                    <ArrowRight
                      class="w-3.5 h-3.5 text-emerald-400 transition-transform duration-200 group-hover:translate-x-1"
                    />
                    Beranda
                  </button>
                </li>
                <li>
                  <button
                    type="button"
                    @click="scrollToSection('statistik')"
                    class="group inline-flex items-center gap-2.5 text-slate-300/85 hover:text-white transition-colors"
                  >
                    <ArrowRight
                      class="w-3.5 h-3.5 text-emerald-400 transition-transform duration-200 group-hover:translate-x-1"
                    />
                    Statistik &amp; Grafik
                  </button>
                </li>
                <li>
                  <button
                    type="button"
                    @click="scrollToSection('data')"
                    class="group inline-flex items-center gap-2.5 text-slate-300/85 hover:text-white transition-colors"
                  >
                    <ArrowRight
                      class="w-3.5 h-3.5 text-emerald-400 transition-transform duration-200 group-hover:translate-x-1"
                    />
                    Data Provider
                  </button>
                </li>
                <li>
                  <Link
                    :href="route('landing.map')"
                    class="group inline-flex items-center gap-2.5 text-slate-300/85 hover:text-white transition-colors"
                  >
                    <ArrowRight
                      class="w-3.5 h-3.5 text-emerald-400 transition-transform duration-200 group-hover:translate-x-1"
                    />
                    Peta Interaktif
                  </Link>
                </li>
                <li>
                  <button
                    type="button"
                    @click="scrollToSection('permohonan')"
                    class="group inline-flex items-center gap-2.5 text-slate-300/85 hover:text-white transition-colors"
                  >
                    <ArrowRight
                      class="w-3.5 h-3.5 text-emerald-400 transition-transform duration-200 group-hover:translate-x-1"
                    />
                    Permohonan Izin
                  </button>
                </li>
              </ul>
            </div>

            <!-- Col 3 — KONTAK -->
            <div class="space-y-5">
              <div class="flex items-center gap-3">
                <span class="w-6 h-0.5 bg-emerald-400 rounded-full"></span>
                <h3 class="text-xs font-bold tracking-[0.2em] text-slate-400 uppercase">
                  Kontak
                </h3>
              </div>

              <div class="space-y-4 text-sm">
                <div class="flex items-start gap-3 group">
                  <div
                    class="flex-shrink-0 w-8 h-8 rounded-full bg-white/10 border border-white/10 flex items-center justify-center mt-0.5 transition-colors duration-300 group-hover:bg-emerald-500/20 group-hover:border-emerald-400/40"
                  >
                    <MapPin class="w-3.5 h-3.5 text-slate-300 group-hover:text-emerald-300" />
                  </div>
                  <p
                    class="text-slate-300/80 leading-relaxed group-hover:text-white transition-colors duration-300"
                  >
                    Jl. Yosodipuro No. 164, Mangkubumen,<br />
                    Banjarsari, Surakarta
                  </p>
                </div>

                <div class="flex items-center gap-3 group">
                  <div
                    class="flex-shrink-0 w-8 h-8 rounded-full bg-white/10 border border-white/10 flex items-center justify-center transition-colors duration-300 group-hover:bg-emerald-500/20 group-hover:border-emerald-400/40"
                  >
                    <Mail class="w-3.5 h-3.5 text-slate-300 group-hover:text-emerald-300" />
                  </div>
                  <a
                    href="mailto:disperumkimtan@surakarta.go.id"
                    class="text-slate-300/80 hover:text-white transition-colors duration-300 break-all"
                  >
                    disperumkimtan@surakarta.go.id
                  </a>
                </div>

                <div class="flex items-center gap-3 group">
                  <div
                    class="flex-shrink-0 w-8 h-8 rounded-full bg-white/10 border border-white/10 flex items-center justify-center transition-colors duration-300 group-hover:bg-emerald-500/20 group-hover:border-emerald-400/40"
                  >
                    <Phone class="w-3.5 h-3.5 text-slate-300 group-hover:text-emerald-300" />
                  </div>
                  <a
                    href="tel:+62271644320"
                    class="text-slate-300/80 hover:text-white transition-colors duration-300"
                  >
                    (0271) 644320
                  </a>
                </div>
              </div>
            </div>
          </div>

          <!-- Bottom Bar with Centered Accent Dot -->
          <div class="relative pt-5 mt-10 md:mt-12">
            <div class="absolute top-0 left-0 right-0 flex items-center gap-3">
              <div class="flex-1 h-px bg-white/10"></div>
              <div class="w-1.5 h-1.5 rounded-full bg-emerald-400 flex-shrink-0"></div>
              <div class="flex-1 h-px bg-white/10"></div>
            </div>

            <div class="flex flex-col items-center justify-between gap-3 sm:flex-row text-xs">
              <p class="text-slate-400/70 text-center sm:text-left">
                &copy; {{ new Date().getFullYear() }} DISPERUMKIMTAN Kota Surakarta.
              </p>
              <p class="text-slate-400/60">
                SIPJALIN · Sistem Informasi Provider Jalan Lingkungan
              </p>
            </div>
          </div>
        </div>
      </footer>
    </div>
  </div>
</template>

<style scoped>
:global(.theme-transition),
:global(.theme-transition *),
:global(.theme-transition *:before),
:global(.theme-transition *:after) {
  transition: background-color 0.3s ease-in-out,
    border-color 0.3s ease-in-out,
    color 0.3s ease-in-out,
    fill 0.3s ease-in-out,
    stroke 0.3s ease-in-out !important;
}
</style>
