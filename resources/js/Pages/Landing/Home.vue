<script setup>
import { computed, ref, watch } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import LandingLayout from '@/Layouts/LandingLayout.vue'
import { Button } from '@/Components/ui/button'
import { Card, CardContent } from '@/Components/ui/card'
import { Badge } from '@/Components/ui/badge'
import {
  MapPin,
  Navigation,
  Radio,
  ShieldCheck,
  Sparkles,
  Clock4,
  Activity,
  SatelliteDish,
} from 'lucide-vue-next'

const props = defineProps({
  stats: {
    type: Object,
    default: () => ({}),
  },
  featuredProviders: {
    type: Array,
    default: () => [],
  },
  coverage: {
    type: Array,
    default: () => [],
  },
  timeline: {
    type: Array,
    default: () => [],
  },
  mapProviders: {
    type: Array,
    default: () => [],
  },
  canLogin: {
    type: Boolean,
    default: true,
  },
  canRegister: {
    type: Boolean,
    default: false,
  },
  appName: {
    type: String,
    default: 'Portal Provider',
  },
})

const heroStats = computed(() => [
  { label: 'Data tercatat', value: props.stats?.totalRecords ?? 0, icon: Radio },
  { label: 'Provider unik', value: props.stats?.uniqueProviders ?? 0, icon: ShieldCheck },
  { label: 'Kota terjangkau', value: props.stats?.coverageCities ?? 0, icon: MapPin },
  { label: 'Provinsi', value: props.stats?.coverageProvinces ?? 0, icon: Navigation },
])

// Map pagination
const mapPageSize = 6
const mapPage = ref(1)
const mapTotalPages = computed(() =>
  Math.max(1, Math.ceil((props.mapProviders.length || 0) / mapPageSize)),
)
const mapPaginatedProviders = computed(() => {
  const start = (mapPage.value - 1) * mapPageSize
  return props.mapProviders.slice(start, start + mapPageSize)
})

watch(
  () => props.mapProviders.length,
  () => {
    mapPage.value = 1
  },
)

const formatNumber = (value) => new Intl.NumberFormat('id-ID').format(value ?? 0)
const formatDate = (value) =>
  value ? new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium' }).format(new Date(value)) : 'Belum ada'
</script>

<template>
  <LandingLayout :can-login="canLogin" :can-register="canRegister">
    <Head title="Beranda" />

    <!-- Hero -->
    <section id="hero" class="relative overflow-hidden pb-16 pt-10 lg:pt-16">
      <div class="absolute inset-0 -z-10">
        <div class="absolute inset-0 bg-gradient-to-br from-emerald-50 via-white to-blue-50 dark:from-gray-900 dark:via-gray-900 dark:to-gray-950"></div>
        <div class="absolute left-1/2 top-10 h-64 w-64 -translate-x-1/2 rounded-full bg-emerald-500/20 blur-3xl"></div>
        <div class="absolute right-10 bottom-0 h-40 w-40 rounded-full bg-blue-500/10 blur-3xl"></div>
      </div>

      <div class="mx-auto flex max-w-6xl flex-col gap-12 px-6 lg:flex-row lg:items-center">
        <div class="space-y-6 lg:w-1/2">
          <div class="inline-flex items-center gap-2 rounded-full bg-white/80 px-4 py-2 text-sm font-semibold text-emerald-700 shadow-sm ring-1 ring-emerald-100 backdrop-blur dark:bg-gray-800/60 dark:text-emerald-200 dark:ring-emerald-800">
            <Sparkles class="h-4 w-4" />
            Pusat Data Provider Utilitas
          </div>
          <div class="space-y-4">
            <h1 class="text-4xl font-bold leading-tight text-gray-900 dark:text-white sm:text-5xl">
              {{ appName }} memetakan konektivitas, kota demi kota.
            </h1>
            <p class="text-lg text-gray-600 dark:text-gray-300">
              Lihat sebaran provider, status survey, dan jejak utilitas di seluruh Indonesia dalam satu layar yang ringan dan informatif.
            </p>
          </div>
          <div class="flex flex-wrap items-center gap-3">
            <Button as-child size="lg" class="shadow-lg shadow-emerald-500/20">
              <a href="#data">Lihat data provider</a>
            </Button>
            <Button as-child size="lg" variant="outline" class="backdrop-blur">
              <Link :href="route('login')">Masuk dashboard</Link>
            </Button>
            <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
              <div class="h-2 w-2 rounded-full bg-emerald-500"></div>
              Pembaruan terakhir: {{ formatDate(stats?.latestSurvey) }}
            </div>
          </div>

          <Card class="border border-emerald-100/80 bg-white/70 shadow-lg shadow-emerald-200/40 backdrop-blur dark:border-emerald-900/50 dark:bg-gray-900/70">
            <CardContent class="flex items-center gap-6 p-5">
              <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-500 text-white shadow-lg">
                <SatelliteDish class="h-6 w-6" />
              </div>
              <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Ringkasan cepat</p>
                <p class="text-lg font-semibold text-gray-900 dark:text-white">
                  {{ formatNumber(stats?.totalRecords) }} titik utilitas tercatat, {{ formatNumber(stats?.coverageProvinces) }} provinsi terjangkau.
                </p>
              </div>
            </CardContent>
          </Card>
        </div>

        <div class="lg:w-1/2">
          <div class="relative overflow-hidden rounded-3xl border border-white/60 bg-white/80 p-6 shadow-2xl backdrop-blur dark:border-gray-800 dark:bg-gray-900/70">
            <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/10 via-transparent to-blue-500/10"></div>
            <div class="relative grid gap-4">
              <div class="flex items-center justify-between rounded-2xl bg-emerald-600 px-5 py-4 text-white shadow-lg">
                <div>
                  <p class="text-sm text-emerald-100">Total provider terdata</p>
                  <p class="text-4xl font-bold">{{ formatNumber(stats?.uniqueProviders) }}</p>
                </div>
                <div class="rounded-xl bg-white/15 p-3">
                  <ShieldCheck class="h-6 w-6" />
                </div>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div
                  v-for="item in heroStats"
                  :key="item.label"
                  class="rounded-2xl border border-emerald-100/60 bg-white/70 p-4 shadow-sm backdrop-blur dark:border-gray-800 dark:bg-gray-800/70"
                >
                  <div class="flex items-center justify-between">
                    <div class="rounded-lg bg-emerald-50 p-2 text-emerald-600 dark:bg-emerald-900/40 dark:text-emerald-200">
                      <component :is="item.icon" class="h-4 w-4" />
                    </div>
                    <span class="text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">{{ item.label }}</span>
                  </div>
                  <p class="mt-3 text-2xl font-semibold text-gray-900 dark:text-white">
                    {{ formatNumber(item.value) }}
                  </p>
                </div>
              </div>

              <div class="rounded-2xl border border-emerald-100/60 bg-gradient-to-r from-emerald-500 via-teal-500 to-blue-500 p-5 text-white shadow-lg backdrop-blur dark:border-emerald-900/50">
                <div class="flex items-center justify-between">
                  <div>
                    <p class="text-sm text-emerald-50">Kecepatan onboarding</p>
                    <p class="text-3xl font-bold">Realtime survey tracker</p>
                  </div>
                  <Activity class="h-8 w-8 text-emerald-50" />
                </div>
                <p class="mt-3 text-emerald-100">
                  Data survey terbaru tercatat pada {{ formatDate(stats?.latestSurvey) }} dengan sebaran kota yang terus bertambah.
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Statistik -->
    <section id="statistik" class="bg-white/80 py-16 dark:bg-gray-950/40">
      <div class="mx-auto max-w-6xl px-6">
        <div class="mb-10 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
          <div>
            <p class="text-sm font-semibold uppercase tracking-wide text-emerald-600 dark:text-emerald-300">
              Statistik cepat
            </p>
            <h2 class="text-3xl font-bold text-gray-900 dark:text-white">
              Pulse data provider
            </h2>
            <p class="mt-2 max-w-2xl text-gray-600 dark:text-gray-400">
              Angka agregat untuk melihat kesehatan data utilitas sebelum masuk ke dashboard analitik.
            </p>
          </div>
          <Badge variant="secondary" class="w-fit">
            <Clock4 class="mr-2 h-4 w-4" />
            Terakhir diperbarui {{ formatDate(stats?.latestSurvey) }}
          </Badge>
        </div>

        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-4">
          <Card v-for="item in heroStats" :key="item.label" class="group relative overflow-hidden border-emerald-100/60 bg-white/70 backdrop-blur dark:border-gray-800 dark:bg-gray-900/70">
            <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/[0.08] via-transparent to-blue-500/[0.06] opacity-0 transition-opacity duration-300 group-hover:opacity-100"></div>
            <CardContent class="relative p-5">
              <div class="flex items-center justify-between">
                <div class="rounded-lg bg-emerald-50 p-3 text-emerald-600 shadow-sm dark:bg-emerald-900/40 dark:text-emerald-200">
                  <component :is="item.icon" class="h-5 w-5" />
                </div>
                <span class="text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">{{ item.label }}</span>
              </div>
              <p class="mt-4 text-3xl font-bold text-gray-900 dark:text-white">
                {{ formatNumber(item.value) }}
              </p>
              <p class="text-sm text-gray-500 dark:text-gray-400">Data tersinkronisasi dengan dashboard admin.</p>
            </CardContent>
          </Card>
        </div>
      </div>
    </section>

    <!-- Sorotan data -->
    <section id="data" class="relative overflow-hidden bg-gradient-to-b from-emerald-50 via-white to-white py-16 dark:from-gray-950 dark:via-gray-900 dark:to-gray-900">
      <div class="absolute inset-0 -z-10 bg-[radial-gradient(circle_at_top,_#34d3991a,_transparent_40%)]"></div>
      <div class="mx-auto max-w-6xl px-6">
        <div class="mb-10 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <p class="text-sm font-semibold uppercase tracking-wide text-emerald-600 dark:text-emerald-300">
              Sorotan data lapangan
            </p>
            <h2 class="text-3xl font-bold text-gray-900 dark:text-white">Survey terbaru</h2>
            <p class="mt-2 max-w-2xl text-gray-600 dark:text-gray-400">
              Enam titik terakhir yang masuk ke sistem, lengkap dengan lokasi dan status utilitas.
            </p>
          </div>
          <Button as-child variant="outline">
            <Link :href="route('login')">Buka dashboard admin</Link>
          </Button>
        </div>

        <div v-if="featuredProviders.length" class="grid gap-6 lg:grid-cols-2">
          <div
            v-for="provider in featuredProviders"
            :key="provider.id"
            class="relative overflow-hidden rounded-2xl border border-emerald-100/70 bg-white/80 p-5 shadow-lg transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl backdrop-blur dark:border-gray-800 dark:bg-gray-900/70"
          >
            <div class="absolute inset-0 bg-gradient-to-r from-emerald-500/[0.08] via-transparent to-blue-500/[0.08]"></div>
            <div class="relative flex items-start justify-between gap-3">
              <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-600 text-white shadow-inner">
                  <SatelliteDish class="h-6 w-6" />
                </div>
                <div>
                  <p class="text-sm text-gray-500 dark:text-gray-400">FID {{ provider.fid || '-' }}</p>
                  <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ provider.name || 'Provider' }}</p>
                  <p class="text-sm text-emerald-700 dark:text-emerald-300">
                    {{ provider.utilitas || 'Telekomunikasi' }}
                  </p>
                </div>
              </div>
              <Badge variant="outline" class="border-emerald-200 text-emerald-700 dark:border-emerald-700/60 dark:text-emerald-200">
                {{ provider.sijali === 'Y' ? 'Sijali' : 'Survey' }}
              </Badge>
            </div>

            <div class="relative mt-4 grid grid-cols-2 gap-3 text-sm text-gray-600 dark:text-gray-400">
              <div class="flex items-center gap-2">
                <MapPin class="h-4 w-4 text-emerald-500" />
                <span>{{ provider.kelurahan || '-' }}, {{ provider.kecamatan || '-' }}</span>
              </div>
              <div class="flex items-center gap-2">
                <Navigation class="h-4 w-4 text-emerald-500" />
                <span>{{ provider.kota || '-' }} · {{ provider.provinsi || '-' }}</span>
              </div>
              <div class="flex items-center gap-2">
                <Radio class="h-4 w-4 text-emerald-500" />
                <span>ODP {{ provider.odp || '-' }}</span>
              </div>
              <div class="flex items-center gap-2">
                <Clock4 class="h-4 w-4 text-emerald-500" />
                <span>{{ formatDate(provider.surveyed_at) }}</span>
              </div>
            </div>
          </div>
        </div>
        <div
          v-else
          class="rounded-2xl border border-dashed border-emerald-200 bg-white/70 p-8 text-center text-gray-500 backdrop-blur dark:border-emerald-900/50 dark:bg-gray-900/60 dark:text-gray-400"
        >
          Belum ada data survey terbaru. Tambahkan data dari dashboard admin untuk mulai menampilkan sorotan.
        </div>
      </div>
    </section>

    <!-- Peta Provider -->
    <section id="map" class="bg-white/90 py-16 dark:bg-gray-950/40">
      <div class="mx-auto max-w-6xl px-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-6">
          <div>
            <p class="text-sm font-semibold uppercase tracking-wide text-emerald-600 dark:text-emerald-300">
              Peta sebaran
            </p>
            <h2 class="text-3xl font-bold text-gray-900 dark:text-white">Titik provider terbaru</h2>
            <p class="mt-2 max-w-2xl text-gray-600 dark:text-gray-400">
              Lihat posisi koordinat dan buka langsung di Google Maps untuk verifikasi lapangan.
            </p>
          </div>
          <Badge variant="secondary" class="w-fit">
            {{ mapProviders.length }} titik terpetakan
          </Badge>
        </div>

        <div v-if="mapProviders.length" class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
          <div
            v-for="point in mapPaginatedProviders"
            :key="point.id"
            class="rounded-2xl border border-emerald-100/70 bg-white/80 p-4 shadow-sm backdrop-blur hover:shadow-md transition dark:border-gray-800 dark:bg-gray-900/70"
          >
            <div class="flex items-start justify-between gap-3">
              <div>
                <p class="text-xs uppercase tracking-wide text-emerald-600 dark:text-emerald-300">Provider</p>
                <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ point.name || 'Provider' }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ point.utilitas || 'Telekomunikasi' }}</p>
              </div>
              <Badge variant="outline" class="text-xs">
                {{ point.odp || 'ODP' }}
              </Badge>
            </div>

            <div class="mt-3 space-y-2 text-sm text-gray-600 dark:text-gray-400">
              <div class="flex items-center gap-2">
                <MapPin class="h-4 w-4 text-emerald-500" />
                <span>{{ point.location || 'Lokasi tidak tersedia' }}</span>
              </div>
              <div class="flex items-center gap-2">
                <Navigation class="h-4 w-4 text-emerald-500" />
                <span>Lat {{ point.coordinates?.lat ?? '-' }}, Lng {{ point.coordinates?.lng ?? '-' }}</span>
              </div>
              <div class="flex items-center gap-2">
                <Clock4 class="h-4 w-4 text-emerald-500" />
                <span>{{ formatDate(point.surveyed_at) }}</span>
              </div>
            </div>

            <div class="mt-4 flex justify-between items-center">
              <span class="text-xs text-gray-500 dark:text-gray-400">ID: {{ point.id }}</span>
              <Button
                as-child
                variant="outline"
                size="sm"
                class="text-emerald-700 hover:text-emerald-800 dark:text-emerald-200 dark:hover:text-emerald-100"
                :disabled="!point.maps_url"
              >
                <a :href="point.maps_url || '#'" target="_blank" rel="noopener noreferrer">
                  Buka peta
                </a>
              </Button>
            </div>
          </div>
        </div>

        <div v-if="mapProviders.length" class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between text-sm text-gray-600 dark:text-gray-300">
          <div>
            Menampilkan
            <strong class="text-gray-900 dark:text-gray-100">
              {{ mapProviders.length ? (mapPage - 1) * mapPageSize + 1 : 0 }}
            </strong>
            -
            <strong class="text-gray-900 dark:text-gray-100">
              {{ Math.min(mapPage * mapPageSize, mapProviders.length) }}
            </strong>
            dari
            <strong class="text-gray-900 dark:text-gray-100">{{ mapProviders.length }}</strong>
            titik
          </div>
          <div class="flex items-center gap-2">
            <Button
              variant="outline"
              size="sm"
              :disabled="mapPage <= 1"
              @click="mapPage = Math.max(1, mapPage - 1)"
            >
              Sebelumnya
            </Button>
            <span class="text-xs text-gray-500 dark:text-gray-400">
              Halaman {{ mapPage }} / {{ mapTotalPages }}
            </span>
            <Button
              variant="outline"
              size="sm"
              :disabled="mapPage >= mapTotalPages"
              @click="mapPage = Math.min(mapTotalPages, mapPage + 1)"
            >
              Selanjutnya
            </Button>
          </div>
        </div>

        <div
          v-else
          class="mt-4 rounded-2xl border border-dashed border-emerald-200 bg-white/70 p-6 text-center text-gray-500 dark:border-emerald-900/50 dark:bg-gray-900/60 dark:text-gray-400"
        >
          Belum ada titik dengan koordinat. Tambahkan data provider beserta koordinat untuk melihat peta.
        </div>
      </div>
    </section>

    <!-- Cakupan wilayah -->
    <section id="coverage" class="bg-white/90 py-16 dark:bg-gray-950/50">
      <div class="mx-auto max-w-6xl px-6">
        <div class="mb-8 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <p class="text-sm font-semibold uppercase tracking-wide text-emerald-600 dark:text-emerald-300">
              Cakupan wilayah
            </p>
            <h2 class="text-3xl font-bold text-gray-900 dark:text-white">Provinsi dengan kontribusi data tertinggi</h2>
            <p class="mt-2 max-w-2xl text-gray-600 dark:text-gray-400">
              Gambaran cepat daerah yang paling aktif menyuplai data utilitas.
            </p>
          </div>
          <Badge variant="secondary" class="w-fit">
            {{ formatNumber(stats?.coverageCities) }} kota · {{ formatNumber(stats?.coverageProvinces) }} provinsi
          </Badge>
        </div>

        <div v-if="coverage.length" class="grid gap-4 md:grid-cols-2">
          <div
            v-for="area in coverage"
            :key="area.provinsi"
            class="flex items-center justify-between rounded-2xl border border-emerald-100/60 bg-gradient-to-r from-white via-emerald-50/60 to-white px-5 py-4 shadow-sm backdrop-blur dark:border-gray-800 dark:from-gray-900 dark:via-emerald-900/20 dark:to-gray-900"
          >
            <div>
              <p class="text-sm text-gray-500 dark:text-gray-400">Provinsi</p>
              <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ area.provinsi }}</p>
            </div>
            <div class="flex items-center gap-3">
              <div class="text-right">
                <p class="text-sm text-gray-500 dark:text-gray-400">Jumlah titik</p>
                <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-300">{{ formatNumber(area.total) }}</p>
              </div>
              <div class="h-12 w-12 rounded-xl bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200 flex items-center justify-center font-semibold">
                {{ area.provinsi?.charAt(0) || 'P' }}
              </div>
            </div>
          </div>
        </div>
        <div
          v-else
          class="rounded-2xl border border-dashed border-emerald-200 bg-white/70 p-8 text-center text-gray-500 backdrop-blur dark:border-emerald-900/50 dark:bg-gray-900/60 dark:text-gray-400"
        >
          Data cakupan provinsi belum tersedia. Tambahkan data provider untuk melihat distribusi wilayah.
        </div>
      </div>
    </section>

    <!-- Timeline -->
    <section id="timeline" class="bg-gradient-to-b from-white to-emerald-50 py-16 dark:from-gray-900 dark:to-gray-950">
      <div class="mx-auto max-w-6xl px-6">
        <div class="mb-8 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <p class="text-sm font-semibold uppercase tracking-wide text-emerald-600 dark:text-emerald-300">
              Kecepatan survey
            </p>
            <h2 class="text-3xl font-bold text-gray-900 dark:text-white">Ritme input data mingguan</h2>
            <p class="mt-2 max-w-2xl text-gray-600 dark:text-gray-400">
              Pantau seberapa sering data survey baru masuk untuk menjaga kualitas informasi.
            </p>
          </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-emerald-100/60 bg-white/80 p-6 shadow-md backdrop-blur dark:border-gray-800 dark:bg-gray-900/70">
          <div v-if="timeline.length" class="grid gap-4 sm:grid-cols-3 lg:grid-cols-6">
            <div
              v-for="item in timeline"
              :key="item.date"
              class="flex flex-col gap-2 rounded-xl bg-emerald-50/70 p-4 text-emerald-800 ring-1 ring-emerald-100 dark:bg-emerald-900/30 dark:text-emerald-100 dark:ring-emerald-900/50"
            >
              <p class="text-sm font-semibold">{{ formatDate(item.date) }}</p>
              <div class="h-20 w-full rounded-lg bg-white/70 p-2 shadow-inner dark:bg-gray-900/60">
                <div
                  class="rounded-md bg-gradient-to-t from-emerald-500 to-emerald-300 shadow-sm"
                  :style="{ height: Math.min(100, (item.total ?? 0) * 20) + '%' }"
                ></div>
              </div>
              <p class="text-xs text-emerald-700 dark:text-emerald-200">{{ formatNumber(item.total) }} titik</p>
            </div>
          </div>
          <div
            v-else
            class="rounded-xl border border-dashed border-emerald-200 bg-white/60 p-6 text-center text-gray-500 dark:border-emerald-900/60 dark:bg-gray-900/40 dark:text-gray-400"
          >
            Belum ada data survey terjadwal. Input survei berikutnya untuk memantau ritme pengumpulan data.
          </div>
        </div>
      </div>
    </section>

    <!-- CTA -->
    <section class="bg-emerald-600 py-14">
      <div class="mx-auto max-w-5xl px-6 text-center text-white">
        <div class="flex justify-center">
          <div class="rounded-full bg-white/15 px-4 py-2 text-sm font-semibold uppercase tracking-wide">
            Siap melanjutkan
          </div>
        </div>
        <h3 class="mt-4 text-3xl font-bold sm:text-4xl">Sinkronkan survei berikutnya</h3>
        <p class="mt-3 text-emerald-50">
          Login untuk mengelola data, menambah titik baru, dan ekspor laporan CSV dari dashboard admin.
        </p>
        <div class="mt-6 flex justify-center gap-4">
          <Button as-child size="lg" variant="secondary" class="text-emerald-700">
            <Link :href="route('login')">Masuk sekarang</Link>
          </Button>
          <Button as-child size="lg" variant="outline" class="border-white/50 text-white hover:bg-white/10">
            <a href="#hero">Kembali ke atas</a>
          </Button>
        </div>
      </div>
    </section>
  </LandingLayout>
</template>
