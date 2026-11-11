<script setup>
import { computed, ref, watch } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import LandingLayout from '@/Layouts/LandingLayout.vue'
import { Button, buttonVariants } from '@/Components/ui/button'
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/Components/ui/card'
import { Badge } from '@/Components/ui/badge'
import { BarChart } from '@/Components/ui/chart-bar'
import { DonutChart } from '@/Components/ui/chart-donut'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/Components/ui/table'
import { Input } from '@/Components/ui/input'
import {
  MapPin,
  Navigation,
  Radio,
  ShieldCheck,
  Sparkles,
  Clock4,
  SatelliteDish,
  Search,
  ExternalLink,
  PieChart as PieChartIcon,
  Table as TableIcon,
  RotateCcw,
  LayoutGrid,
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
    default: 'SIPJALIN',
  },
})

const heroStats = computed(() => [
  { label: 'Jumlah Tiang', value: props.stats?.totalRecords ?? 0, icon: Radio },
  { label: 'Jumlah provider', value: props.stats?.uniqueProviders ?? 0, icon: ShieldCheck },
  { label: 'Kecamatan terjangkau', value: props.stats?.coverageDistricts ?? 0, icon: MapPin },
  { label: 'Kelurahan', value: props.stats?.coverageVillages ?? 0, icon: Navigation },
])

// Map pagination
const mapPageSize = 6
const mapPage = ref(1)
const selectedKecamatan = ref('')
const selectedKelurahan = ref('')
const selectedJalan = ref('')

const normalizeArea = (value) => String(value ?? '').trim().toLowerCase()
const normalizeProviderKey = (value) =>
  String(value ?? '')
    .trim()
    .toLowerCase()
    .replace(/[^a-z0-9]/g, '')
const normalizeProviderName = (value) => {
  const raw = String(value ?? '').trim()
  if (!raw) return ''
  if (raw.includes('/')) {
    const parts = raw
      .split('/')
      .map((part) => part.trim())
      .filter(Boolean)
    const normalizedParts = parts.map(normalizeProviderKey).filter(Boolean)
    const uniqueParts = new Set(normalizedParts)
    if (uniqueParts.size === 1) {
      return normalizedParts[0] || ''
    }
  }
  return normalizeProviderKey(raw)
}
const getProviderLabel = (value) => {
  const raw = String(value ?? '').trim()
  if (!raw) return ''
  if (raw.includes('/')) {
    const parts = raw
      .split('/')
      .map((part) => part.trim())
      .filter(Boolean)
    return parts[0] || raw
  }
  return raw
}
const splitProviderNames = (value) =>
  String(value ?? '')
    .split(',')
    .map((name) => name.trim())
    .filter(Boolean)

const kecamatanOptions = computed(() => {
  const options = new Map()
  props.mapProviders.forEach((provider) => {
    const label = String(provider.kecamatan ?? '').trim()
    if (!label) return
    const key = normalizeArea(label)
    const current = options.get(key)
    if (current) {
      current.total += 1
    } else {
      options.set(key, { value: label, label, total: 1 })
    }
  })
  return Array.from(options.values()).sort((a, b) =>
    a.label.localeCompare(b.label, 'id', { sensitivity: 'base' }),
  )
})

const kelurahanOptions = computed(() => {
  const kecamatanKey = normalizeArea(selectedKecamatan.value)
  if (!kecamatanKey) return []
  const options = new Map()
  props.mapProviders
    .filter((provider) => {
      return normalizeArea(provider.kecamatan) === kecamatanKey
    })
    .forEach((provider) => {
      const label = String(provider.kelurahan ?? '').trim()
      if (!label) return
      const key = normalizeArea(label)
      const current = options.get(key)
      if (current) {
        current.total += 1
      } else {
        options.set(key, { value: label, label, total: 1 })
      }
    })
  return Array.from(options.values()).sort((a, b) =>
    a.label.localeCompare(b.label, 'id', { sensitivity: 'base' }),
  )
})

const jalanOptions = computed(() => {
  const kecamatanKey = normalizeArea(selectedKecamatan.value)
  const kelurahanKey = normalizeArea(selectedKelurahan.value)

  const options = new Map()
  props.mapProviders
    .filter((provider) => {
      if (kecamatanKey && normalizeArea(provider.kecamatan) !== kecamatanKey) return false
      if (kelurahanKey && normalizeArea(provider.kelurahan) !== kelurahanKey) return false
      return true
    })
    .forEach((provider) => {
      const label = String(provider.sijali ?? '').trim()
      if (!label) return
      const key = normalizeArea(label)
      const current = options.get(key)
      if (current) {
        current.total += 1
      } else {
        options.set(key, { value: label, label, total: 1 })
      }
    })
  return Array.from(options.values()).sort((a, b) =>
    a.label.localeCompare(b.label, 'id', { sensitivity: 'base' }),
  )
})

const filteredMapProviders = computed(() => {
  const kecamatan = normalizeArea(selectedKecamatan.value)
  const kelurahan = normalizeArea(selectedKelurahan.value)
  const jalan = normalizeArea(selectedJalan.value)

  return props.mapProviders.filter((provider) => {
    if (kecamatan) {
      if (normalizeArea(provider.kecamatan) !== kecamatan) return false
    }
    if (kelurahan) {
      if (normalizeArea(provider.kelurahan) !== kelurahan) return false
    }
    if (jalan) {
      if (normalizeArea(provider.sijali) !== jalan) return false
    }
    return true
  })
})

const filteredProviderGroups = computed(() => {
  const groups = new Map()

  filteredMapProviders.value.forEach((provider) => {
    splitProviderNames(provider.name).forEach((name) => {
      const key = normalizeProviderName(name)
      if (!key) return
      const existing = groups.get(key)
    if (existing) {
      existing.total += 1
      const sampleHasSijali = Boolean(existing.sample?.sijali)
      const candidateHasSijali = Boolean(provider.sijali)
      if (!sampleHasSijali && candidateHasSijali) {
        existing.sample = provider
        return
      }
      if (
        !existing.sample?.maps_url &&
        provider.maps_url &&
        (!sampleHasSijali || candidateHasSijali)
      ) {
        existing.sample = provider
      }
      return
    }
      groups.set(key, {
        key,
        label: getProviderLabel(name),
        total: 1,
        sample: provider,
      })
    })
  })

  return Array.from(groups.values()).sort((a, b) =>
    a.label.localeCompare(b.label, 'id', { sensitivity: 'base' }),
  )
})

const streetProviderCounts = computed(() => {
  const counts = new Map()

  filteredMapProviders.value.forEach((provider) => {
    const streetLabel = String(provider.sijali ?? '').trim()
    if (!streetLabel) return
    const key = normalizeArea(streetLabel)
    let entry = counts.get(key)
    if (!entry) {
      entry = { label: streetLabel, providerKeys: new Set() }
      counts.set(key, entry)
    }
    splitProviderNames(provider.name).forEach((name) => {
      const providerKey = normalizeProviderName(name)
      if (!providerKey) return
      entry.providerKeys.add(providerKey)
    })
  })

  const result = new Map()
  counts.forEach((entry, key) => {
    result.set(key, { label: entry.label, total: entry.providerKeys.size })
  })
  return result
})

const getStreetProviderCount = (streetName) => {
  if (!streetName) return 0
  const key = normalizeArea(streetName)
  return streetProviderCounts.value.get(key)?.total ?? 0
}

const collectUniqueProviders = (providers) => {
  const names = new Map()
  providers.forEach((provider) => {
    splitProviderNames(provider.name).forEach((name) => {
      const key = normalizeProviderName(name)
      if (!key) return
      if (!names.has(key)) names.set(key, name)
    })
  })
  return Array.from(names.values()).sort((a, b) => a.localeCompare(b, 'id', { sensitivity: 'base' }))
}

const filteredProviderNames = computed(() => collectUniqueProviders(filteredMapProviders.value))
const allProviderNames = computed(() => collectUniqueProviders(props.mapProviders))
const filteredProviderPreview = computed(() => {
  const names = filteredProviderNames.value
  if (!names.length) return '-'
  const limit = 6
  const preview = names.slice(0, limit).join(', ')
  const extra = names.length > limit ? ` +${names.length - limit} lainnya` : ''
  return `${preview}${extra}`
})

const mapTotalPages = computed(() =>
  Math.max(1, Math.ceil((filteredProviderGroups.value.length || 0) / mapPageSize)),
)
const mapPaginatedProviders = computed(() => {
  const start = (mapPage.value - 1) * mapPageSize
  return filteredProviderGroups.value.slice(start, start + mapPageSize)
})

// View & Table state
const activeViewMode = ref('table')
const tableSearchQuery = ref('')
const tablePageSize = ref(10)
const tablePageSizeOptions = [5, 10, 25, 50]
const tableCurrentPage = ref(1)

const tableFilteredProviders = computed(() => {
  const q = tableSearchQuery.value.trim().toLowerCase()
  if (!q) return filteredMapProviders.value

  return filteredMapProviders.value.filter((p) => {
    return (
      (p.name && String(p.name).toLowerCase().includes(q)) ||
      (p.kecamatan && String(p.kecamatan).toLowerCase().includes(q)) ||
      (p.kelurahan && String(p.kelurahan).toLowerCase().includes(q)) ||
      (p.sijali && String(p.sijali).toLowerCase().includes(q)) ||
      (p.odp && String(p.odp).toLowerCase().includes(q))
    )
  })
})

const tableTotalPages = computed(() =>
  Math.max(1, Math.ceil((tableFilteredProviders.value.length || 0) / tablePageSize.value)),
)

const tablePaginatedProviders = computed(() => {
  const start = (tableCurrentPage.value - 1) * tablePageSize.value
  return tableFilteredProviders.value.slice(start, start + tablePageSize.value)
})

watch(tableSearchQuery, () => {
  tableCurrentPage.value = 1
})

watch(
  () => props.mapProviders.length,
  () => {
    mapPage.value = 1
    tableCurrentPage.value = 1
  },
)
watch(
  () => filteredMapProviders.value.length,
  () => {
    mapPage.value = 1
    tableCurrentPage.value = 1
  },
)
watch(selectedKecamatan, () => {
  selectedKelurahan.value = ''
  selectedJalan.value = ''
  mapPage.value = 1
  tableCurrentPage.value = 1
})
watch(selectedKelurahan, () => {
  selectedJalan.value = ''
  mapPage.value = 1
  tableCurrentPage.value = 1
})
watch(selectedJalan, () => {
  mapPage.value = 1
  tableCurrentPage.value = 1
})

const resetLandingFilters = () => {
  selectedKecamatan.value = ''
  selectedKelurahan.value = ''
  selectedJalan.value = ''
  tableSearchQuery.value = ''
  mapPage.value = 1
  tableCurrentPage.value = 1
}

// Pie Chart (AKAMSI Style: up to Kelurahan level)
const piePalette = [
  '#10b981', '#3b82f6', '#f59e0b', '#8b5cf6', '#ec4899',
  '#06b6d4', '#14b8a6', '#f97316', '#6366f1', '#84cc16',
  '#a855f7', '#0ea5e9', '#e11d48', '#d97706', '#0284c7'
]

const pieChartInfo = computed(() => {
  if (selectedKelurahan.value) {
    // Kelurahan level: shows distribution by provider
    const counts = new Map()
    filteredMapProviders.value.forEach((provider) => {
      splitProviderNames(provider.name).forEach((name) => {
        const label = getProviderLabel(name)
        if (!label) return
        counts.set(label, (counts.get(label) || 0) + 1)
      })
    })
    const data = Array.from(counts.entries())
      .map(([name, value], idx) => ({
        name,
        value,
        color: piePalette[idx % piePalette.length],
      }))
      .sort((a, b) => b.value - a.value)

    const total = data.reduce((acc, c) => acc + c.value, 0)
    return {
      scope: 'kelurahan',
      title: `Sebaran Provider di Kel. ${selectedKelurahan.value}`,
      subtitle: `Komposisi provider utilitas di kelurahan ${selectedKelurahan.value}`,
      data,
      total,
      unit: 'tiang',
    }
  } else if (selectedKecamatan.value) {
    // Kecamatan level: shows distribution by kelurahan
    const counts = new Map()
    filteredMapProviders.value.forEach((provider) => {
      const label = String(provider.kelurahan ?? '').trim()
      if (!label) return
      counts.set(label, (counts.get(label) || 0) + 1)
    })
    const data = Array.from(counts.entries())
      .map(([name, value], idx) => ({
        name,
        value,
        color: piePalette[idx % piePalette.length],
      }))
      .sort((a, b) => b.value - a.value)

    const total = data.reduce((acc, c) => acc + c.value, 0)
    return {
      scope: 'kecamatan',
      title: `Sebaran Kelurahan di Kec. ${selectedKecamatan.value}`,
      subtitle: `Persebaran data per-kelurahan pada kecamatan ${selectedKecamatan.value}`,
      data,
      total,
      unit: 'titik',
    }
  } else {
    // Citywide: shows distribution by kecamatan
    const counts = new Map()
    props.mapProviders.forEach((provider) => {
      const label = String(provider.kecamatan ?? '').trim()
      if (!label) return
      counts.set(label, (counts.get(label) || 0) + 1)
    })
    const data = Array.from(counts.entries())
      .map(([name, value], idx) => ({
        name,
        value,
        color: piePalette[idx % piePalette.length],
      }))
      .sort((a, b) => b.value - a.value)

    const total = data.reduce((acc, c) => acc + c.value, 0)
    return {
      scope: 'kota',
      title: 'Persebaran Wilayah per-Kecamatan',
      subtitle: 'Proporsi sebaran data provider di seluruh kecamatan Kota Surakarta',
      data,
      total,
      unit: 'titik',
    }
  }
})

const formatNumber = (value) => new Intl.NumberFormat('id-ID').format(value ?? 0)
const formatDate = (value) =>
  value ? new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium' }).format(new Date(value)) : 'Belum ada'

const axisLabelStyle = {
  '--vis-axis-tick-label-font-size': '11px',
  '--vis-axis-tick-label-weight': '600',
}

const buildAreaDistribution = (providers, key) => {
  const counts = new Map()
  providers.forEach((provider) => {
    const label = String(provider?.[key] ?? '').trim()
    if (!label) return
    const normalized = normalizeArea(label)
    const existing = counts.get(normalized)
    if (existing) {
      existing.total += 1
    } else {
      counts.set(normalized, { name: label, total: 1 })
    }
  })
  return Array.from(counts.values()).sort(
    (a, b) => b.total - a.total || a.name.localeCompare(b.name, 'id', { sensitivity: 'base' }),
  )
}

const barCategoryKey = 'Jumlah'
const kecamatanDistribution = computed(() => buildAreaDistribution(props.mapProviders, 'kecamatan'))
const kelurahanDistribution = computed(() => buildAreaDistribution(props.mapProviders, 'kelurahan'))
const kecamatanBarData = computed(() =>
  kecamatanDistribution.value.map((item) => ({
    name: item.name,
    [barCategoryKey]: item.total,
  })),
)
const kelurahanBarData = computed(() =>
  kelurahanDistribution.value.map((item) => ({
    name: item.name,
    [barCategoryKey]: item.total,
  })),
)
const formatBarLabel = (data) => (value) => {
  const label = data?.[value]?.name ?? ''
  if (!label) return ''
  return label.length > 12 ? `${label.slice(0, 12)}...` : label
}

const permitRequests = [
  {
    id: 1,
    provider: 'Link Net',
    kecamatan: 'Banjarsari',
    kelurahan: 'Keprabon',
    status: 'legal',
    submitted_at: '2025-01-10',
  },
  {
    id: 2,
    provider: 'Biznet',
    kecamatan: 'Banjarsari',
    kelurahan: 'Keprabon',
    status: 'legal',
    submitted_at: '2025-01-12',
  },
  {
    id: 3,
    provider: 'MyRepublic',
    kecamatan: 'Laweyan',
    kelurahan: 'Sondakan',
    status: 'ilegal',
    submitted_at: '2025-01-15',
  },
  {
    id: 4,
    provider: 'PowerTel',
    kecamatan: 'Jebres',
    kelurahan: 'Pucangsawit',
    status: 'legal',
    submitted_at: '2025-01-18',
  },
  {
    id: 5,
    provider: 'IP-1',
    kecamatan: 'Pasar Kliwon',
    kelurahan: 'Kauman',
    status: 'ilegal',
    submitted_at: '2025-01-20',
  },
]

const legalPermitRequests = computed(() =>
  permitRequests.filter((request) => request.status === 'legal'),
)
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
            SIPJALIN · Sistem Informasi Provider Jalan lingkungan
          </div>
          <div class="space-y-4">
            <h1 class="text-4xl font-bold leading-tight text-gray-900 dark:text-white sm:text-5xl">
              SIPJALIN
            </h1>
            <h2 class="text-3xl font-bold leading-tight text-gray-900 dark:text-white sm:text-5xl">
              Sistem Informasi Provider Jalan lingkungan
            </h2>
            <p class="text-lg text-gray-600 dark:text-gray-300">
              Dinas Perumahan, Kawasan Permukiman, dan Pertanahan Surakarta.
            </p>
          </div>

        </div>

        <div class="lg:w-1/2">
          <div class="relative overflow-hidden rounded-3xl border border-white/60 bg-white/80 p-4 shadow-2xl backdrop-blur dark:border-gray-800 dark:bg-gray-900/70">
            <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/10 via-transparent to-blue-500/10"></div>
            <div class="relative">
              <img
                src="/images/hero.png"
                alt="Tampilan hero SIPJALIN"
                class="h-full w-full rounded-2xl object-cover"
              />
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
              Pulse data provider Surakarta
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

        <div class="mt-8 rounded-2xl border border-emerald-100/60 bg-white/80 p-5 shadow-sm backdrop-blur dark:border-gray-800 dark:bg-gray-900/70">
          <div class="flex flex-wrap items-center justify-between gap-2">
            <div>
              <p class="text-xs font-semibold uppercase tracking-wide text-emerald-600 dark:text-emerald-300">
                Grafik bar
              </p>
              <p class="text-lg font-semibold text-gray-900 dark:text-white">Sebaran wilayah</p>
            </div>
            <Badge variant="secondary" class="text-xs">Semua data</Badge>
          </div>

          <div class="mt-4 grid gap-4 sm:grid-cols-2">
            <div class="rounded-xl border border-emerald-100/60 bg-white/80 p-3 dark:border-gray-800 dark:bg-gray-900/70">
              <div class="flex items-center justify-between">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                  Kecamatan
                </p>
                <span class="text-xs text-gray-400 dark:text-gray-500">
                  {{ formatNumber(kecamatanDistribution.length) }} total
                </span>
              </div>
              <div v-if="kecamatanBarData.length" class="mt-2">
                <BarChart
                  class="h-[240px]"
                  :style="axisLabelStyle"
                  :data="kecamatanBarData"
                  :categories="[barCategoryKey]"
                  index="name"
                  :show-legend="false"
                  :show-grid-line="false"
                  :show-y-axis="false"
                  :x-formatter="formatBarLabel(kecamatanBarData)"
                  :y-formatter="(value) => formatNumber(value)"
                  :colors="['#10b981']"
                />
              </div>
              <p v-else class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                Belum ada data kecamatan.
              </p>
            </div>

            <div class="rounded-xl border border-emerald-100/60 bg-white/80 p-3 dark:border-gray-800 dark:bg-gray-900/70">
              <div class="flex items-center justify-between">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                  Kelurahan
                </p>
                <span class="text-xs text-gray-400 dark:text-gray-500">
                  {{ formatNumber(kelurahanDistribution.length) }} total
                </span>
              </div>
              <div v-if="kelurahanBarData.length" class="mt-2">
                <BarChart
                  class="h-[240px]"
                  :style="axisLabelStyle"
                  :data="kelurahanBarData"
                  :categories="[barCategoryKey]"
                  index="name"
                  :show-legend="false"
                  :show-grid-line="false"
                  :show-y-axis="false"
                  :x-formatter="formatBarLabel(kelurahanBarData)"
                  :y-formatter="(value) => formatNumber(value)"
                  :colors="['#3b82f6']"
                />
              </div>
              <p v-else class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                Belum ada data kelurahan.
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Sorotan data -->
    <section id="data" class="relative overflow-hidden bg-gradient-to-b from-emerald-50/40 via-white to-white py-16 dark:from-gray-950 dark:via-gray-900 dark:to-gray-900">
      <div class="absolute inset-0 -z-10 bg-[radial-gradient(circle_at_top,_#10b98115,_transparent_45%)]"></div>
      <div class="mx-auto max-w-6xl px-6">
        <!-- Section Header -->
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-8">
          <div>
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/70 dark:text-emerald-300 ring-1 ring-emerald-200/60 dark:ring-emerald-800/40 mb-3">
              <SatelliteDish class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" />
              Peta Sebaran & Utilitas
            </div>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 dark:text-white tracking-tight">
              Data Utilitas & Provider
            </h2>
            <p class="mt-2 text-sm sm:text-base text-gray-600 dark:text-gray-400 max-w-2xl leading-relaxed">
              Monitoring persebaran tiang utilitas jaringan, titik ODP, dan provider jalan lingkungan Kota Surakarta.
            </p>
          </div>

          <div class="flex items-center gap-2.5 flex-shrink-0">
            <div class="flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white/90 dark:bg-gray-800/90 border border-gray-200/80 dark:border-gray-700 shadow-sm backdrop-blur">
              <Radio class="w-4 h-4 text-emerald-600 dark:text-emerald-400" />
              <div class="text-left">
                <p class="text-[10px] uppercase font-bold text-gray-400 dark:text-gray-500 tracking-wider">Total Tiang</p>
                <p class="text-sm font-bold text-gray-900 dark:text-white">{{ formatNumber(filteredMapProviders.length) }}</p>
              </div>
            </div>
            <div class="flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white/90 dark:bg-gray-800/90 border border-gray-200/80 dark:border-gray-700 shadow-sm backdrop-blur">
              <ShieldCheck class="w-4 h-4 text-blue-600 dark:text-blue-400" />
              <div class="text-left">
                <p class="text-[10px] uppercase font-bold text-gray-400 dark:text-gray-500 tracking-wider">Provider</p>
                <p class="text-sm font-bold text-gray-900 dark:text-white">{{ formatNumber(filteredProviderNames.length) }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Filter Card -->
        <div class="mb-8 rounded-2xl border border-gray-200/70 bg-white/85 dark:border-gray-800 dark:bg-gray-900/80 p-5 shadow-sm backdrop-blur">
          <div class="grid gap-3.5 sm:grid-cols-2 lg:grid-cols-4 items-end">
            <!-- Kecamatan -->
            <div class="space-y-1.5">
              <label class="text-xs font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-1.5">
                <MapPin class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" />
                Kecamatan
              </label>
              <select
                v-model="selectedKecamatan"
                class="w-full h-10 rounded-xl border border-gray-200/90 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:focus:border-emerald-500 font-medium transition"
              >
                <option value="">Semua Kecamatan</option>
                <option v-for="kecamatan in kecamatanOptions" :key="kecamatan.value" :value="kecamatan.value">
                  {{ kecamatan.label }} ({{ kecamatan.total }})
                </option>
              </select>
            </div>

            <!-- Kelurahan -->
            <div class="space-y-1.5">
              <label class="text-xs font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-1.5">
                <Navigation class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" />
                Kelurahan
              </label>
              <select
                v-model="selectedKelurahan"
                :disabled="!selectedKecamatan"
                class="w-full h-10 rounded-xl border border-gray-200/90 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-400 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:focus:border-emerald-500 dark:disabled:bg-gray-800/40 dark:disabled:text-gray-500 font-medium transition"
              >
                <option value="">Semua Kelurahan</option>
                <option v-for="kelurahan in kelurahanOptions" :key="kelurahan.value" :value="kelurahan.value">
                  {{ kelurahan.label }} ({{ kelurahan.total }})
                </option>
              </select>
            </div>

            <!-- Nama Jalan -->
            <div class="space-y-1.5">
              <label class="text-xs font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-1.5">
                <Sparkles class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" />
                Nama Jalan Lingkungan
              </label>
              <select
                v-model="selectedJalan"
                :disabled="!jalanOptions.length"
                class="w-full h-10 rounded-xl border border-gray-200/90 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-400 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:focus:border-emerald-500 dark:disabled:bg-gray-800/40 dark:disabled:text-gray-500 font-medium transition"
              >
                <option value="">Semua Jalan</option>
                <option v-for="jalan in jalanOptions" :key="jalan.value" :value="jalan.value">
                  {{ jalan.label }} ({{ jalan.total }})
                </option>
              </select>
            </div>

            <!-- Reset Button -->
            <div>
              <Button
                variant="outline"
                class="w-full h-10 rounded-xl border-gray-200 hover:border-red-200 hover:bg-red-50 hover:text-red-700 dark:border-gray-700 dark:text-gray-300 dark:hover:border-red-900/60 dark:hover:bg-red-950/40 dark:hover:text-red-300 font-medium transition"
                :disabled="!selectedKecamatan && !selectedKelurahan && !selectedJalan && !tableSearchQuery"
                @click="resetLandingFilters"
              >
                <RotateCcw class="w-4 h-4 mr-2" />
                Reset Filter
              </Button>
            </div>
          </div>

          <!-- Active Filter Breadcrumb & Status -->
          <div class="mt-4 pt-3.5 border-t border-gray-100 dark:border-gray-800 flex flex-wrap items-center justify-between gap-3 text-xs">
            <div class="flex items-center gap-1.5 flex-wrap">
              <span class="font-semibold text-gray-400 dark:text-gray-500">Filter Aktif:</span>
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-medium">
                Kota Surakarta
              </span>
              <span v-if="selectedKecamatan" class="text-gray-300 dark:text-gray-600">/</span>
              <span v-if="selectedKecamatan" class="inline-flex items-center px-2.5 py-0.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 font-medium border border-emerald-200/60 dark:border-emerald-800/50">
                Kec. {{ selectedKecamatan }}
              </span>
              <span v-if="selectedKelurahan" class="text-gray-300 dark:text-gray-600">/</span>
              <span v-if="selectedKelurahan" class="inline-flex items-center px-2.5 py-0.5 rounded-lg bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 font-medium border border-blue-200/60 dark:border-blue-800/50">
                Kel. {{ selectedKelurahan }}
              </span>
              <span v-if="selectedJalan" class="text-gray-300 dark:text-gray-600">/</span>
              <span v-if="selectedJalan" class="inline-flex items-center px-2.5 py-0.5 rounded-lg bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-medium border border-purple-200/60 dark:border-purple-800/50">
                {{ selectedJalan }}
              </span>
            </div>

            <div class="text-gray-500 dark:text-gray-400 font-medium">
              Menampilkan <span class="font-bold text-gray-900 dark:text-white">{{ filteredMapProviders.length }}</span> data tiang
            </div>
          </div>
        </div>

        <!-- Grafik Donut/Pie Persebaran Data (AKAMSI Style) -->
        <div class="mb-10 rounded-2xl border border-gray-200/70 bg-white/90 p-6 shadow-sm backdrop-blur dark:border-gray-800 dark:bg-gray-900/80">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6 pb-4 border-b border-gray-100 dark:border-gray-800">
            <div>
              <div class="inline-flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">
                <PieChartIcon class="w-4 h-4" />
                Grafik Persebaran Utilitas
              </div>
              <h3 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white mt-1">
                {{ pieChartInfo.title }}
              </h3>
              <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                {{ pieChartInfo.subtitle }}
              </p>
            </div>
            <div class="flex items-center gap-2">
              <Badge variant="outline" class="text-xs px-3 py-1 font-semibold border-emerald-300 text-emerald-700 dark:border-emerald-800 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/40">
                Total {{ formatNumber(pieChartInfo.total) }} {{ pieChartInfo.unit }}
              </Badge>
            </div>
          </div>

          <div v-if="pieChartInfo.data.length" class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
            <!-- Donut Chart -->
            <div class="lg:col-span-5 flex flex-col items-center justify-center">
              <div class="relative w-full max-w-[280px] h-[260px] flex items-center justify-center">
                <DonutChart
                  class="h-[250px] w-full"
                  :data="pieChartInfo.data"
                  category="value"
                  index="name"
                  type="donut"
                  :colors="pieChartInfo.data.map(d => d.color)"
                  :value-formatter="(v) => `${formatNumber(v)} ${pieChartInfo.unit}`"
                  :show-legend="false"
                />
              </div>
            </div>

            <!-- Detail List with Progress Bar -->
            <div class="lg:col-span-7 space-y-2.5">
              <div class="flex items-center justify-between text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider pb-1">
                <span>Rincian Proporsi Wilayah / Provider</span>
                <span>Jumlah & Persentase</span>
              </div>
              <div class="max-h-[280px] overflow-y-auto pr-1 space-y-2">
                <div
                  v-for="(item, idx) in pieChartInfo.data"
                  :key="item.name"
                  class="p-3 rounded-xl border border-gray-100 dark:border-gray-800/80 bg-gray-50/60 dark:bg-gray-800/40 hover:bg-gray-100/60 dark:hover:bg-gray-800/80 transition"
                >
                  <div class="flex items-center justify-between text-sm mb-1.5">
                    <div class="flex items-center gap-2.5">
                      <span
                        class="w-3 h-3 rounded-full flex-shrink-0 ring-2 ring-white dark:ring-gray-900 shadow-sm"
                        :style="{ backgroundColor: item.color }"
                      ></span>
                      <span class="font-semibold text-gray-800 dark:text-gray-200 text-sm">{{ item.name }}</span>
                    </div>
                    <div class="flex items-center gap-2 text-xs">
                      <span class="font-bold text-gray-900 dark:text-white">{{ formatNumber(item.value) }} {{ pieChartInfo.unit }}</span>
                      <span class="font-bold px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                        {{ pieChartInfo.total ? ((item.value / pieChartInfo.total) * 100).toFixed(1) : 0 }}%
                      </span>
                    </div>
                  </div>
                  <!-- Progress Bar -->
                  <div class="w-full bg-gray-200/80 rounded-full h-2 dark:bg-gray-700/70 overflow-hidden">
                    <div
                      class="h-2 rounded-full transition-all duration-500"
                      :style="{
                        width: `${pieChartInfo.total ? (item.value / pieChartInfo.total) * 100 : 0}%`,
                        backgroundColor: item.color,
                      }"
                    ></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div v-else class="text-center py-8 text-gray-400 text-sm">
            Tidak ada data untuk grafik pada filter yang dipilih.
          </div>
        </div>

        <!-- Section Switcher: Tabel Data vs Kartu Provider -->
        <div class="mb-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
          <div class="flex items-center gap-2.5">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white tracking-tight">
              {{ activeViewMode === 'table' ? 'Tabel Data Utilitas Lingkungan' : 'Kartu Sebaran Provider' }}
            </h3>
            <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400">
              {{ activeViewMode === 'table' ? `${tableFilteredProviders.length} baris` : `${filteredProviderGroups.length} provider` }}
            </span>
          </div>

          <!-- Segmented Control Button -->
          <div class="inline-flex rounded-xl p-1 bg-gray-100 dark:bg-gray-800 border border-gray-200/70 dark:border-gray-700 shadow-inner">
            <button
              type="button"
              @click="activeViewMode = 'table'"
              :class="[
                'inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-semibold transition',
                activeViewMode === 'table'
                  ? 'bg-white dark:bg-gray-900 text-emerald-600 dark:text-emerald-400 shadow-sm'
                  : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'
              ]"
            >
              <TableIcon class="w-3.5 h-3.5" />
              Tabel Data
            </button>
            <button
              type="button"
              @click="activeViewMode = 'cards'"
              :class="[
                'inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-semibold transition',
                activeViewMode === 'cards'
                  ? 'bg-white dark:bg-gray-900 text-emerald-600 dark:text-emerald-400 shadow-sm'
                  : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'
              ]"
            >
              <LayoutGrid class="w-3.5 h-3.5" />
              Kartu Provider
            </button>
          </div>
        </div>

        <!-- Tampilan 1: Tabel Filter Data Provider (Level Sampai Jalan) -->
        <div v-if="activeViewMode === 'table'" class="mb-10 rounded-2xl border border-gray-200/70 bg-white/90 shadow-sm backdrop-blur dark:border-gray-800 dark:bg-gray-900/80 overflow-hidden">
          <div class="p-4 sm:p-5 border-b border-gray-100 dark:border-gray-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="relative w-full sm:w-72">
              <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
              <Input
                v-model="tableSearchQuery"
                placeholder="Cari provider, jalan, ODP..."
                class="pl-9 h-9 text-sm rounded-xl"
              />
            </div>

            <div class="flex items-center gap-2 text-xs text-gray-500 self-end sm:self-auto">
              <span>Tampilkan:</span>
              <select
                v-model="tablePageSize"
                class="h-8 rounded-lg border border-gray-200 bg-white px-2.5 text-xs text-gray-900 shadow-sm focus:border-emerald-400 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 font-medium"
              >
                <option v-for="size in tablePageSizeOptions" :key="size" :value="size">
                  {{ size }} baris
                </option>
              </select>
            </div>
          </div>

          <div class="overflow-x-auto">
            <Table>
              <TableHeader class="bg-gray-50/80 dark:bg-gray-800/60">
                <TableRow>
                  <TableHead class="w-12 text-center text-xs uppercase font-bold text-gray-500 tracking-wider">No</TableHead>
                  <TableHead class="text-xs uppercase font-bold text-gray-500 tracking-wider">Kecamatan</TableHead>
                  <TableHead class="text-xs uppercase font-bold text-gray-500 tracking-wider">Kelurahan</TableHead>
                  <TableHead class="text-xs uppercase font-bold text-gray-500 tracking-wider">Nama Jalan</TableHead>
                  <TableHead class="text-xs uppercase font-bold text-gray-500 tracking-wider">Provider</TableHead>
                  <TableHead class="text-xs uppercase font-bold text-gray-500 tracking-wider">ODP</TableHead>
                  <TableHead class="text-center text-xs uppercase font-bold text-gray-500 tracking-wider">Lokasi</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow
                  v-for="(item, idx) in tablePaginatedProviders"
                  :key="item.id || idx"
                  class="hover:bg-emerald-50/40 dark:hover:bg-gray-800/60 transition"
                >
                  <TableCell class="text-center font-medium text-xs text-gray-400">
                    {{ (tableCurrentPage - 1) * tablePageSize + idx + 1 }}
                  </TableCell>
                  <TableCell class="font-semibold text-sm text-gray-900 dark:text-gray-100">
                    {{ item.kecamatan || '-' }}
                  </TableCell>
                  <TableCell class="text-sm text-gray-700 dark:text-gray-300">
                    {{ item.kelurahan || '-' }}
                  </TableCell>
                  <TableCell class="text-sm text-emerald-700 dark:text-emerald-400 font-medium">
                    {{ item.sijali || 'Jalan Lingkungan' }}
                  </TableCell>
                  <TableCell>
                    <Badge variant="secondary" class="font-semibold text-xs px-2.5 py-0.5">
                      {{ item.name || '-' }}
                    </Badge>
                  </TableCell>
                  <TableCell class="font-mono text-xs text-gray-600 dark:text-gray-400">
                    {{ item.odp || '-' }}
                  </TableCell>
                  <TableCell class="text-center">
                    <a
                      v-if="item.maps_url"
                      :href="item.maps_url"
                      target="_blank"
                      rel="noopener noreferrer"
                      class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 dark:bg-blue-950/60 dark:text-blue-400 dark:hover:bg-blue-900/50 transition"
                    >
                      <MapPin class="w-3.5 h-3.5" />
                      Maps
                      <ExternalLink class="w-3 h-3" />
                    </a>
                    <span v-else class="text-xs text-gray-400">-</span>
                  </TableCell>
                </TableRow>
                <TableRow v-if="!tablePaginatedProviders.length">
                  <TableCell colspan="7" class="h-32 text-center text-gray-500">
                    Tidak ada data utilitas yang sesuai dengan filter atau pencarian Anda.
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>

          <!-- Table Pagination Footer -->
          <div
            v-if="tableFilteredProviders.length"
            class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-4 border-t border-gray-100 dark:border-gray-800 text-xs text-gray-600 dark:text-gray-400 bg-gray-50/50 dark:bg-gray-800/40"
          >
            <div>
              Menampilkan
              <strong class="text-gray-900 dark:text-gray-100">
                {{ (tableCurrentPage - 1) * tablePageSize + 1 }}
              </strong>
              -
              <strong class="text-gray-900 dark:text-gray-100">
                {{ Math.min(tableCurrentPage * tablePageSize, tableFilteredProviders.length) }}
              </strong>
              dari
              <strong class="text-gray-900 dark:text-gray-100">{{ formatNumber(tableFilteredProviders.length) }}</strong>
              baris data
            </div>
            <div class="flex items-center gap-2">
              <Button
                variant="outline"
                size="sm"
                class="h-8 px-3 text-xs rounded-lg"
                :disabled="tableCurrentPage <= 1"
                @click="tableCurrentPage = Math.max(1, tableCurrentPage - 1)"
              >
                Sebelumnya
              </Button>
              <span class="font-medium text-gray-700 dark:text-gray-300">Halaman {{ tableCurrentPage }} / {{ tableTotalPages }}</span>
              <Button
                variant="outline"
                size="sm"
                class="h-8 px-3 text-xs rounded-lg"
                :disabled="tableCurrentPage >= tableTotalPages"
                @click="tableCurrentPage = Math.min(tableTotalPages, tableCurrentPage + 1)"
              >
                Selanjutnya
              </Button>
            </div>
          </div>
        </div>

        <!-- Tampilan 2: Kartu Sebaran Provider -->
        <div v-else class="mb-10">
          <div v-if="filteredProviderGroups.length" class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            <div
              v-for="point in mapPaginatedProviders"
              :key="point.key"
              class="rounded-2xl border border-gray-200/70 bg-white/85 p-5 shadow-sm backdrop-blur hover:shadow-md hover:border-emerald-300 transition dark:border-gray-800 dark:bg-gray-900/80"
            >
              <div class="flex items-center justify-between mb-2">
                <span class="text-xs uppercase font-bold tracking-wider text-emerald-600 dark:text-emerald-400">Provider</span>
                <span class="text-xs font-semibold px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300">
                  {{ point.total }} Titik
                </span>
              </div>
              <p class="text-lg font-bold text-gray-900 dark:text-white">{{ point.label || 'Provider' }}</p>
              <p class="mt-2 text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1.5">
                <MapPin class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" />
                <span v-if="point.sample?.sijali">
                  {{ point.sample.sijali }}
                </span>
                <span v-else>Lokasi jalan belum tercatat</span>
              </p>
            </div>
          </div>

          <!-- Cards Pagination -->
          <div v-if="filteredProviderGroups.length" class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between text-sm text-gray-600 dark:text-gray-400">
            <div>
              Menampilkan
              <strong class="text-gray-900 dark:text-gray-100">
                {{ filteredProviderGroups.length ? (mapPage - 1) * mapPageSize + 1 : 0 }}
              </strong>
              -
              <strong class="text-gray-900 dark:text-gray-100">
                {{ Math.min(mapPage * mapPageSize, filteredProviderGroups.length) }}
              </strong>
              dari
              <strong class="text-gray-900 dark:text-gray-100">{{ filteredProviderGroups.length }}</strong>
              provider
            </div>
            <div class="flex items-center gap-2">
              <Button
                variant="outline"
                size="sm"
                class="rounded-lg"
                :disabled="mapPage <= 1"
                @click="mapPage = Math.max(1, mapPage - 1)"
              >
                Sebelumnya
              </Button>
              <span class="text-xs">Halaman {{ mapPage }} / {{ mapTotalPages }}</span>
              <Button
                variant="outline"
                size="sm"
                class="rounded-lg"
                :disabled="mapPage >= mapTotalPages"
                @click="mapPage = Math.min(mapTotalPages, mapPage + 1)"
              >
                Selanjutnya
              </Button>
            </div>
          </div>

          <div
            v-else
            class="rounded-2xl border border-dashed border-gray-300 dark:border-gray-800 bg-white/50 p-8 text-center text-gray-500 dark:bg-gray-900/50 dark:text-gray-400"
          >
            Tidak ada data provider yang cocok dengan filter yang dipilih.
          </div>
        </div>
      </div>
    </section>

    <!-- Permohonan -->
    <section id="permohonan" class="bg-white/90 py-16 dark:bg-gray-950/50">
      <div class="mx-auto max-w-6xl px-6">
        <div class="mb-8 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <p class="text-sm font-semibold uppercase tracking-wide text-emerald-600 dark:text-emerald-300">
              Permohonan izin provider
            </p>
            <h2 class="text-3xl font-bold text-gray-900 dark:text-white">Daftar permohonan legal</h2>
            <p class="mt-2 max-w-2xl text-gray-600 dark:text-gray-400">
              Data dummy pengajuan izin provider untuk pemantauan legalitas di landing page.
            </p>
          </div>
        </div>

        <div v-if="legalPermitRequests.length" class="grid gap-4 md:grid-cols-2">
          <div
            v-for="request in legalPermitRequests"
            :key="request.id"
            class="flex flex-col gap-3 rounded-2xl border border-emerald-100/60 bg-gradient-to-r from-white via-emerald-50/60 to-white px-5 py-4 shadow-sm backdrop-blur dark:border-gray-800 dark:from-gray-900 dark:via-emerald-900/20 dark:to-gray-900"
          >
            <div class="flex items-center justify-between gap-3">
              <div>
                <p class="text-xs uppercase tracking-wide text-emerald-600 dark:text-emerald-300">Provider</p>
                <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ request.provider }}</p>
              </div>
              <Badge variant="secondary" class="w-fit">Legal</Badge>
            </div>
            <div class="text-sm text-gray-600 dark:text-gray-400">
              {{ request.kelurahan }}, {{ request.kecamatan }}
            </div>
            <div class="text-xs text-gray-500 dark:text-gray-400">
              Diajukan {{ formatDate(request.submitted_at) }}
            </div>
          </div>
        </div>
        <div
          v-else
          class="rounded-2xl border border-dashed border-emerald-200 bg-white/70 p-8 text-center text-gray-500 backdrop-blur dark:border-emerald-900/50 dark:bg-gray-900/60 dark:text-gray-400"
        >
          Belum ada permohonan legal yang bisa ditampilkan.
        </div>
      </div>
    </section>
  </LandingLayout>
</template>
