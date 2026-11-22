<script setup>
import { computed } from 'vue'
import { Head, Link, usePage } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { Button } from '@/Components/ui/button'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/Components/ui/table'
import { Badge } from '@/Components/ui/badge'
import {
  Users,
  Wifi,
  MapPin,
  Building2,
  ArrowRight,
  ArrowUpRight,
  Calendar,
  Database,
  Map as MapIcon,
  Layers,
  Plus,
  Sparkles,
} from 'lucide-vue-next'

const props = defineProps({
  stats: {
    type: Object,
    default: () => ({
      total_users: 0,
      total_provider: 0,
      total_provider_unique: 0,
      total_kecamatan: 0,
      total_kelurahan: 0,
    }),
  },
  recent_providers: {
    type: Array,
    default: () => [],
  },
  provider_by_kecamatan: {
    type: Array,
    default: () => [],
  },
  provider_by_kelurahan: {
    type: Array,
    default: () => [],
  },
})

const page = usePage()
const user = computed(() => page.props.auth?.user)

const distKecamatan = computed(() => {
  const list = props.provider_by_kecamatan?.length
    ? props.provider_by_kecamatan
    : props.provider_by_provinsi || []
  return [...list].sort((a, b) => (b.value ?? 0) - (a.value ?? 0))
})

const distKelurahan = computed(() => {
  const list = props.provider_by_kelurahan?.length
    ? props.provider_by_kelurahan
    : props.provider_by_name || []
  return [...list].sort((a, b) => (b.value ?? 0) - (a.value ?? 0)).slice(0, 8)
})

const formatNumber = (val) => new Intl.NumberFormat('id-ID').format(val ?? 0)

const formatDate = (dateStr) => {
  if (!dateStr) return '-'
  const date = new Date(dateStr)
  return date.toLocaleDateString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  })
}

const getPercentage = (value) => {
  const total = Number(props.stats?.total_provider || 0)
  if (!total || !value) return 0
  return Math.min(100, Math.round((Number(value) / total) * 100))
}

const statsCards = computed(() => [
  {
    title: 'Total Titik Data',
    subtitle: 'Titik survei provider tercatat',
    value: formatNumber(props.stats.total_provider),
    icon: Wifi,
    iconBg: 'bg-emerald-500/10 text-emerald-600 ring-1 ring-emerald-500/20',
    glow: 'from-emerald-500/15 to-teal-500/5',
  },
  {
    title: 'Jumlah Provider',
    subtitle: 'Operator jaringan terdata',
    value: formatNumber(props.stats.total_provider_unique),
    icon: Building2,
    iconBg: 'bg-sky-500/10 text-sky-600 ring-1 ring-sky-500/20',
    glow: 'from-sky-500/15 to-cyan-500/5',
  },
  {
    title: 'Cakupan Wilayah',
    subtitle: `${formatNumber(props.stats.total_kelurahan)} kelurahan terpetakan`,
    value: `${formatNumber(props.stats.total_kecamatan)} Kec`,
    icon: MapPin,
    iconBg: 'bg-violet-500/10 text-violet-600 ring-1 ring-violet-500/20',
    glow: 'from-violet-500/15 to-purple-500/5',
  },
  {
    title: 'Total Pengguna',
    subtitle: 'Akun pengelola terdaftar',
    value: formatNumber(props.stats.total_users),
    icon: Users,
    iconBg: 'bg-amber-500/10 text-amber-600 ring-1 ring-amber-500/20',
    glow: 'from-amber-500/15 to-orange-500/5',
  },
])
</script>

<template>
  <Head title="Dashboard Admin" />

  <DashboardLayout>
    <template #header>
      <div class="flex items-center justify-between w-full">
        <div>
          <h1 class="text-lg font-bold tracking-tight text-slate-900">Dashboard</h1>
        </div>
        <div class="flex items-center gap-2.5">
          <Link
            :href="route('landing.map')"
            class="hidden sm:inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-sm transition hover:border-emerald-300 hover:bg-emerald-50/50 hover:text-emerald-700"
          >
            <MapIcon class="h-3.5 w-3.5 text-emerald-600" />
            Peta Sebaran
          </Link>
          <Link :href="route('admin.provider.index')">
            <Button
              size="sm"
              class="rounded-xl bg-slate-900 px-4 text-xs font-semibold text-white shadow-sm hover:bg-emerald-600 transition-colors"
            >
              <Database class="w-3.5 h-3.5 mr-1.5" />
              Kelola Provider
            </Button>
          </Link>
        </div>
      </div>
    </template>

    <div class="py-6 px-4 sm:px-6 lg:px-8">
      <div class="max-w-7xl mx-auto space-y-6">
        <!-- Welcome Banner -->
        <div
          class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-slate-900 via-slate-900 to-emerald-950 p-6 sm:p-8 text-white shadow-lg"
        >
          <!-- Ambient decorative blobs -->
          <div
            class="pointer-events-none absolute -top-24 -right-20 h-72 w-72 rounded-full bg-emerald-500/25 blur-3xl"
          ></div>
          <div
            class="pointer-events-none absolute -bottom-24 left-1/3 h-64 w-64 rounded-full bg-sky-500/20 blur-3xl"
          ></div>

          <div
            class="relative z-10 flex flex-col justify-between gap-6 md:flex-row md:items-center"
          >
            <div class="space-y-2 max-w-xl">
              <div
                class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-wider text-emerald-300 ring-1 ring-white/15 backdrop-blur"
              >
                <Sparkles class="h-3 w-3" />
                Panel Kendali SIPJALIN
              </div>
              <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                Selamat Datang, {{ user?.name || 'Administrator' }}!
              </h2>
              <p class="text-sm text-slate-300 leading-relaxed">
                Pantau ringkasan distribusi jaringan utilitas, kelola direktori titik ODP provider, atau import data survei terbaru di Kota Surakarta.
              </p>
            </div>

            <div class="flex flex-wrap items-center gap-3 shrink-0">
              <Link
                :href="route('admin.provider.index')"
                class="inline-flex items-center gap-2 rounded-xl bg-emerald-500 px-4 py-2.5 text-xs sm:text-sm font-semibold text-slate-950 shadow-lg shadow-emerald-500/20 transition hover:-translate-y-0.5 hover:bg-emerald-400"
              >
                <Plus class="h-4 w-4" />
                Tambah / Import Data
              </Link>
              <Link
                :href="route('landing')"
                class="inline-flex items-center gap-1.5 rounded-xl bg-white/10 px-4 py-2.5 text-xs sm:text-sm font-semibold text-white ring-1 ring-white/15 backdrop-blur transition hover:bg-white/20"
              >
                Lihat Beranda
                <ArrowUpRight class="h-4 w-4" />
              </Link>
            </div>
          </div>
        </div>

        <!-- 4 Metric Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <div
            v-for="stat in statsCards"
            :key="stat.title"
            class="group relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:border-emerald-300/80 hover:shadow-md"
          >
            <div
              class="pointer-events-none absolute -right-6 -top-6 h-24 w-24 rounded-full bg-gradient-to-br opacity-80 blur-2xl transition-opacity group-hover:opacity-100"
              :class="stat.glow"
            ></div>

            <div class="relative flex items-start justify-between gap-3">
              <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                  {{ stat.title }}
                </p>
                <p
                  class="mt-2.5 text-3xl font-extrabold tracking-tight text-slate-900 tabular-nums"
                >
                  {{ stat.value }}
                </p>
                <p class="mt-1 text-xs text-slate-500">
                  {{ stat.subtitle }}
                </p>
              </div>
              <div
                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl transition-transform duration-300 group-hover:scale-110"
                :class="stat.iconBg"
              >
                <component :is="stat.icon" class="w-5 h-5" />
              </div>
            </div>
          </div>
        </div>

        <!-- Distribution Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Provider by Kecamatan -->
          <div
            class="flex flex-col justify-between rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm"
          >
            <div>
              <div class="flex items-center justify-between gap-3 mb-1">
                <div class="flex items-center gap-2.5">
                  <div
                    class="flex h-9 w-9 items-center justify-center rounded-xl bg-violet-500/10 text-violet-600 ring-1 ring-violet-500/20"
                  >
                    <MapPin class="w-4 h-4" />
                  </div>
                  <div>
                    <h3 class="text-base font-bold text-slate-900">
                      Distribusi per Kecamatan
                    </h3>
                    <p class="text-xs text-slate-500">
                      Sebaran titik data provider berdasarkan kecamatan
                    </p>
                  </div>
                </div>
                <span
                  v-if="distKecamatan.length"
                  class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600"
                >
                  {{ distKecamatan.length }} wilayah
                </span>
              </div>

              <div v-if="distKecamatan.length > 0" class="mt-5 space-y-3.5">
                <div
                  v-for="(item, idx) in distKecamatan"
                  :key="item.name"
                  class="space-y-1.5"
                >
                  <div class="flex items-center justify-between text-xs">
                    <span class="font-semibold text-slate-700 flex items-center gap-2">
                      <span
                        class="inline-flex h-5 w-5 items-center justify-center rounded-md bg-slate-100 text-[11px] font-bold text-slate-500"
                      >
                        {{ idx + 1 }}
                      </span>
                      {{ item.name }}
                    </span>
                    <span class="font-bold text-slate-900 tabular-nums">
                      {{ formatNumber(item.value) }}
                      <span class="font-normal text-slate-400">
                        ({{ getPercentage(item.value) }}%)
                      </span>
                    </span>
                  </div>
                  <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                    <div
                      class="h-full rounded-full transition-all duration-500"
                      :style="{
                        width: `${Math.max(4, getPercentage(item.value))}%`,
                        backgroundColor: item.color || '#10b981',
                      }"
                    ></div>
                  </div>
                </div>
              </div>

              <!-- Empty State -->
              <div
                v-else
                class="mt-5 flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-200 bg-slate-50/50 py-10 px-4 text-center"
              >
                <div
                  class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white text-slate-400 shadow-sm ring-1 ring-slate-200/80 mb-3"
                >
                  <Layers class="h-5 w-5" />
                </div>
                <p class="text-sm font-semibold text-slate-700">
                  Belum Ada Data Kecamatan
                </p>
                <p class="mt-1 max-w-xs text-xs text-slate-500">
                  Statistik distribusi wilayah akan otomatis terisi setelah data provider ditambahkan.
                </p>
              </div>
            </div>
          </div>

          <!-- Provider by Kelurahan -->
          <div
            class="flex flex-col justify-between rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm"
          >
            <div>
              <div class="flex items-center justify-between gap-3 mb-1">
                <div class="flex items-center gap-2.5">
                  <div
                    class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 ring-1 ring-emerald-500/20"
                  >
                    <Building2 class="w-4 h-4" />
                  </div>
                  <div>
                    <h3 class="text-base font-bold text-slate-900">
                      Distribusi per Kelurahan
                    </h3>
                    <p class="text-xs text-slate-500">
                      Kelurahan dengan jumlah titik provider terbanyak
                    </p>
                  </div>
                </div>
                <span
                  v-if="distKelurahan.length"
                  class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600"
                >
                  Top {{ distKelurahan.length }}
                </span>
              </div>

              <div v-if="distKelurahan.length > 0" class="mt-5 space-y-3.5">
                <div
                  v-for="(item, idx) in distKelurahan"
                  :key="item.name"
                  class="space-y-1.5"
                >
                  <div class="flex items-center justify-between text-xs">
                    <span class="font-semibold text-slate-700 flex items-center gap-2">
                      <span
                        class="inline-flex h-5 w-5 items-center justify-center rounded-md bg-slate-100 text-[11px] font-bold text-slate-500"
                      >
                        {{ idx + 1 }}
                      </span>
                      {{ item.name }}
                    </span>
                    <span class="font-bold text-slate-900 tabular-nums">
                      {{ formatNumber(item.value) }}
                      <span class="font-normal text-slate-400">
                        ({{ getPercentage(item.value) }}%)
                      </span>
                    </span>
                  </div>
                  <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                    <div
                      class="h-full rounded-full transition-all duration-500"
                      :style="{
                        width: `${Math.max(4, getPercentage(item.value))}%`,
                        backgroundColor: item.color || '#0ea5e9',
                      }"
                    ></div>
                  </div>
                </div>
              </div>

              <!-- Empty State -->
              <div
                v-else
                class="mt-5 flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-200 bg-slate-50/50 py-10 px-4 text-center"
              >
                <div
                  class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white text-slate-400 shadow-sm ring-1 ring-slate-200/80 mb-3"
                >
                  <Building2 class="h-5 w-5" />
                </div>
                <p class="text-sm font-semibold text-slate-700">
                  Belum Ada Data Kelurahan
                </p>
                <p class="mt-1 max-w-xs text-xs text-slate-500">
                  Tambahkan atau import file Excel data provider untuk melihat sebaran kelurahan.
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Providers Table -->
        <div
          class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm"
        >
          <div
            class="flex flex-col gap-3 border-b border-slate-100 p-6 sm:flex-row sm:items-center sm:justify-between"
          >
            <div class="flex items-center gap-3">
              <div
                class="flex h-10 w-10 items-center justify-center rounded-xl bg-sky-500/10 text-sky-600 ring-1 ring-sky-500/20"
              >
                <Calendar class="w-5 h-5" />
              </div>
              <div>
                <h3 class="text-base font-bold text-slate-900">
                  Data Provider Terbaru
                </h3>
                <p class="text-xs text-slate-500">
                  5 data titik provider yang terakhir ditambahkan ke sistem
                </p>
              </div>
            </div>
            <Link :href="route('admin.provider.index')">
              <Button
                variant="outline"
                size="sm"
                class="rounded-xl border-slate-200 text-xs font-semibold text-slate-700 hover:border-emerald-300 hover:bg-emerald-50/60 hover:text-emerald-700"
              >
                Lihat Semua
                <ArrowRight class="w-3.5 h-3.5 ml-1.5" />
              </Button>
            </Link>
          </div>

          <div v-if="recent_providers.length > 0">
            <Table>
              <TableHeader>
                <TableRow class="bg-slate-50/80 hover:bg-slate-50/80">
                  <TableHead class="text-xs font-bold uppercase tracking-wider text-slate-500 pl-6">
                    Provider
                  </TableHead>
                  <TableHead class="text-xs font-bold uppercase tracking-wider text-slate-500">
                    Kota
                  </TableHead>
                  <TableHead class="text-xs font-bold uppercase tracking-wider text-slate-500">
                    Kecamatan
                  </TableHead>
                  <TableHead class="text-xs font-bold uppercase tracking-wider text-slate-500">
                    ODP
                  </TableHead>
                  <TableHead class="text-xs font-bold uppercase tracking-wider text-slate-500 pr-6">
                    Tgl Survey
                  </TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow
                  v-for="provider in recent_providers"
                  :key="provider.id"
                  class="transition-colors hover:bg-slate-50/80"
                >
                  <TableCell class="pl-6 py-3.5">
                    <Badge
                      variant="secondary"
                      class="rounded-lg bg-emerald-500/10 text-emerald-700 border-0 font-semibold"
                    >
                      {{ provider.n_provider || '-' }}
                    </Badge>
                  </TableCell>
                  <TableCell class="text-sm font-medium text-slate-700">
                    {{ provider.kota || '-' }}
                  </TableCell>
                  <TableCell class="text-sm text-slate-600">
                    {{ provider.kecamatan || '-' }}
                  </TableCell>
                  <TableCell class="font-mono text-xs text-slate-600 max-w-xs truncate">
                    {{ provider.odp || '-' }}
                  </TableCell>
                  <TableCell class="pr-6 text-xs font-medium text-slate-500">
                    {{ formatDate(provider.tgl_survey) }}
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>

          <!-- Empty State for Table -->
          <div
            v-else
            class="flex flex-col items-center justify-center py-12 px-4 text-center"
          >
            <div
              class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 mb-3"
            >
              <Database class="h-5 w-5" />
            </div>
            <p class="text-sm font-semibold text-slate-700">
              Belum Ada Data Provider
            </p>
            <p class="mt-1 max-w-sm text-xs text-slate-500">
              Mulai tambahkan data provider secara manual atau import file Excel survei di halaman Data Provider.
            </p>
            <Link :href="route('admin.provider.index')" class="mt-4">
              <Button
                size="sm"
                class="rounded-xl bg-emerald-600 text-xs font-semibold text-white hover:bg-emerald-700"
              >
                <Plus class="h-3.5 w-3.5 mr-1.5" />
                Kelola Data Provider
              </Button>
            </Link>
          </div>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>
