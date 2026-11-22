<script setup>
import InputError from "@/Components/InputError.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref, onMounted } from "vue";
import {
  ArrowLeft,
  ArrowRight,
  Eye,
  EyeOff,
  Loader2,
  Lock,
  Mail,
  MapPin,
  Radio,
  ShieldCheck,
} from "lucide-vue-next";

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

const showPassword = ref(false);
const entered = ref(false);

onMounted(() => {
  document.documentElement.classList.remove("dark");
  requestAnimationFrame(() => {
    entered.value = true;
  });
});

const submit = () => {
  form.post(route("login"), {
    onFinish: () => form.reset("password"),
  });
};
</script>

<template>
  <Head title="Log in" />

  <div class="relative min-h-screen overflow-hidden bg-white text-slate-900">
    <div class="grid min-h-screen lg:grid-cols-2">
      <!-- ============ LEFT: Brand Panel ============ -->
      <aside
        class="relative hidden overflow-hidden bg-gradient-to-br from-emerald-600 via-teal-600 to-sky-700 lg:flex lg:flex-col lg:justify-between lg:p-12"
      >
        <!-- Decorative blurred blobs & grid -->
        <div class="pointer-events-none absolute inset-0">
          <div
            class="absolute -top-24 -left-24 h-96 w-96 rounded-full bg-emerald-300/30 blur-3xl"
          ></div>
          <div
            class="absolute -bottom-32 -right-24 h-[28rem] w-[28rem] rounded-full bg-sky-300/30 blur-3xl"
          ></div>
          <div
            class="absolute top-1/3 left-1/2 h-72 w-72 -translate-x-1/2 rounded-full bg-violet-300/20 blur-3xl"
          ></div>
          <div class="login-grid absolute inset-0 opacity-30"></div>
        </div>

        <!-- Top: logo -->
        <div class="relative z-10 flex items-center gap-3">
          <span
            class="flex items-center justify-center rounded-xl bg-slate-900/90 px-3 py-2 shadow-lg ring-1 ring-white/10"
          >
            <img
              src="/images/DISPERUMKIMTAN.png"
              alt="DISPERUMKIMTAN"
              class="h-9 w-auto"
            />
          </span>
          <div class="leading-tight text-white">
            <p class="text-sm font-extrabold tracking-[0.14em]">SIPJALIN</p>
            <p class="text-[10px] font-semibold uppercase tracking-widest text-white/70">
              Kota Surakarta
            </p>
          </div>
        </div>

        <!-- Middle: giant SIPJALIN wordmark -->
        <div class="relative z-10 flex flex-1 items-center justify-center">
          <div class="relative w-full text-center">
            <!-- Soft glow behind the wordmark -->
            <div
              class="pointer-events-none absolute left-1/2 top-1/2 h-48 w-3/4 -translate-x-1/2 -translate-y-1/2 rounded-full bg-white/25 blur-3xl"
              aria-hidden="true"
            ></div>
            <h2
              class="relative select-none font-sans text-[clamp(4rem,9.5vw,8.5rem)] font-black uppercase leading-none tracking-[-0.02em] text-white drop-shadow-[0_10px_30px_rgba(2,6,23,0.25)]"
            >
              SIPJALIN
            </h2>
          </div>
        </div>

        <!-- Bottom: description + chips -->
        <div class="relative z-10 space-y-5 text-white">
          <div class="space-y-2">
            <h3 class="text-2xl font-bold leading-snug">
              Sistem Informasi Provider<br />Jalan Lingkungan
            </h3>
            <p class="max-w-md text-sm leading-relaxed text-white/80">
              Kelola data jaringan dan infrastruktur provider di Kota Surakarta dalam satu
              dashboard terpadu.
            </p>
          </div>
          <div class="flex flex-wrap gap-2">
            <span
              class="inline-flex items-center gap-1.5 rounded-full bg-white/15 px-3 py-1.5 text-xs font-medium ring-1 ring-white/20 backdrop-blur"
            >
              <Radio class="h-3.5 w-3.5" /> Data Terpusat
            </span>
            <span
              class="inline-flex items-center gap-1.5 rounded-full bg-white/15 px-3 py-1.5 text-xs font-medium ring-1 ring-white/20 backdrop-blur"
            >
              <MapPin class="h-3.5 w-3.5" /> Peta Sebaran
            </span>
            <span
              class="inline-flex items-center gap-1.5 rounded-full bg-white/15 px-3 py-1.5 text-xs font-medium ring-1 ring-white/20 backdrop-blur"
            >
              <ShieldCheck class="h-3.5 w-3.5" /> Akses Aman
            </span>
          </div>
        </div>
      </aside>

      <!-- ============ RIGHT: Form Panel ============ -->
      <main
        class="relative flex items-center justify-center bg-gradient-to-br from-emerald-50/70 via-white to-sky-50/80 px-5 py-10 sm:px-8"
      >
        <!-- Soft ambient blobs (visible mostly on mobile) -->
        <div class="pointer-events-none absolute inset-0 overflow-hidden lg:hidden">
          <div
            class="absolute -top-20 -left-16 h-64 w-64 rounded-full bg-emerald-300/30 blur-3xl"
          ></div>
          <div
            class="absolute -bottom-24 -right-16 h-72 w-72 rounded-full bg-sky-300/30 blur-3xl"
          ></div>
        </div>

        <div
          class="login-card relative w-full max-w-md"
          :class="{ 'is-entered': entered }"
        >
          <!-- Back to home -->
          <Link
            :href="route('landing')"
            class="group mb-6 inline-flex items-center gap-2 text-sm font-medium text-slate-500 transition-colors hover:text-emerald-700"
          >
            <ArrowLeft
              class="h-4 w-4 transition-transform duration-200 group-hover:-translate-x-1"
            />
            Kembali ke Beranda
          </Link>

          <!-- Mobile brand -->
          <div class="mb-6 flex items-center gap-3 lg:hidden">
            <span class="flex items-center rounded-xl bg-slate-900 px-2.5 py-1.5 shadow">
              <img src="/images/DISPERUMKIMTAN.png" alt="Logo" class="h-8 w-auto" />
            </span>
            <p class="text-sm font-extrabold tracking-[0.14em] text-slate-900">SIPJALIN</p>
          </div>

          <!-- Heading -->
          <div class="mb-8 space-y-2">
            <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">
              Selamat Datang
            </h1>
            <p class="text-sm text-slate-500 sm:text-base">
              Masuk ke dashboard admin SIPJALIN untuk mengelola data provider.
            </p>
          </div>

          <!-- Status -->
          <div
            v-if="status"
            class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700"
          >
            {{ status }}
          </div>

          <!-- Form Card -->
          <div
            class="rounded-3xl border border-slate-200/80 bg-white/90 p-6 shadow-xl shadow-slate-900/[0.06] backdrop-blur sm:p-8"
          >
            <form @submit.prevent="submit" class="space-y-5">
              <!-- Email -->
              <div class="space-y-2">
                <label for="email" class="block text-sm font-semibold text-slate-700">
                  Email
                </label>
                <div class="relative">
                  <Mail
                    class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                  />
                  <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="nama@email.com"
                    class="block w-full rounded-xl border bg-slate-50/70 py-3 pl-10 pr-4 text-sm text-slate-900 placeholder:text-slate-400 transition focus:bg-white focus:outline-none focus:ring-4"
                    :class="
                      form.errors.email
                        ? 'border-red-300 focus:border-red-400 focus:ring-red-100'
                        : 'border-slate-200 focus:border-emerald-500 focus:ring-emerald-100'
                    "
                  />
                </div>
                <InputError :message="form.errors.email" />
              </div>

              <!-- Password -->
              <div class="space-y-2">
                <div class="flex items-center justify-between">
                  <label for="password" class="block text-sm font-semibold text-slate-700">
                    Password
                  </label>
                  <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="text-xs font-semibold text-emerald-600 transition-colors hover:text-emerald-700 hover:underline"
                  >
                    Lupa password?
                  </Link>
                </div>
                <div class="relative">
                  <Lock
                    class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                  />
                  <input
                    id="password"
                    v-model="form.password"
                    :type="showPassword ? 'text' : 'password'"
                    required
                    autocomplete="current-password"
                    placeholder="Masukkan password"
                    class="block w-full rounded-xl border bg-slate-50/70 py-3 pl-10 pr-11 text-sm text-slate-900 placeholder:text-slate-400 transition focus:bg-white focus:outline-none focus:ring-4"
                    :class="
                      form.errors.password
                        ? 'border-red-300 focus:border-red-400 focus:ring-red-100'
                        : 'border-slate-200 focus:border-emerald-500 focus:ring-emerald-100'
                    "
                  />
                  <button
                    type="button"
                    @click="showPassword = !showPassword"
                    class="absolute right-2.5 top-1/2 flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                    :aria-label="showPassword ? 'Sembunyikan password' : 'Tampilkan password'"
                  >
                    <EyeOff v-if="showPassword" class="h-4 w-4" />
                    <Eye v-else class="h-4 w-4" />
                  </button>
                </div>
                <InputError :message="form.errors.password" />
              </div>

              <!-- Remember -->
              <label class="flex cursor-pointer select-none items-center gap-2.5">
                <input
                  v-model="form.remember"
                  type="checkbox"
                  name="remember"
                  class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                />
                <span class="text-sm text-slate-600">Ingat saya di perangkat ini</span>
              </label>

              <!-- Submit -->
              <button
                type="submit"
                :disabled="form.processing"
                class="group relative flex w-full items-center justify-center gap-2 overflow-hidden rounded-xl bg-slate-900 px-4 py-3.5 text-sm font-semibold text-white shadow-lg shadow-slate-900/20 transition-all duration-300 hover:-translate-y-0.5 hover:bg-emerald-600 hover:shadow-emerald-600/25 focus:outline-none focus:ring-4 focus:ring-emerald-200 disabled:cursor-not-allowed disabled:opacity-70 disabled:hover:translate-y-0 disabled:hover:bg-slate-900"
              >
                <template v-if="!form.processing">
                  Masuk
                  <ArrowRight
                    class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-1"
                  />
                </template>
                <template v-else>
                  <Loader2 class="h-4 w-4 animate-spin" />
                  Memproses...
                </template>
              </button>
            </form>
          </div>

          <p class="mt-6 text-center text-xs text-slate-400">
            &copy; {{ new Date().getFullYear() }} DISPERUMKIMTAN Kota Surakarta
          </p>
        </div>
      </main>
    </div>
  </div>
</template>

<style scoped>
.login-grid {
  background-image: linear-gradient(to right, rgba(255, 255, 255, 0.18) 1px, transparent 1px),
    linear-gradient(to bottom, rgba(255, 255, 255, 0.18) 1px, transparent 1px);
  background-size: 44px 44px;
  mask-image: radial-gradient(ellipse 80% 70% at 50% 40%, #000 30%, transparent 100%);
}

.login-card {
  opacity: 0;
  transform: translateY(18px);
  transition: opacity 0.7s cubic-bezier(0.16, 1, 0.3, 1),
    transform 0.7s cubic-bezier(0.16, 1, 0.3, 1);
}

.login-card.is-entered {
  opacity: 1;
  transform: translateY(0);
}

@media (prefers-reduced-motion: reduce) {
  .login-card {
    opacity: 1;
    transform: none;
    transition: none;
  }
}
</style>
