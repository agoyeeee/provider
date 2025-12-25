<script setup>
import { computed, ref, watch } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import LandingLayout from '@/Layouts/LandingLayout.vue'
import { Button } from '@/Components/ui/button'
import { Card, CardContent } from '@/Components/ui/card'
import { Badge } from '@/Components/ui/badge'
import { BarChart } from '@/Components/ui/chart-bar'
import {
  MapPin,
  Navigation,
  Radio,
  ShieldCheck,
  Sparkles,
  Clock4,
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
    default: 'SIPJALIN',
  },
})

const heroStats = computed(() => [
  { label: 'Data tercatat', value: props.stats?.totalRecords ?? 0, icon: Radio },
  { label: 'Jumlah provider', value: props.stats?.uniqueProviders ?? 0, icon: ShieldCheck },
  { label: 'Kecamatan terjangkau', value: props.stats?.coverageDistricts ?? 0, icon: MapPin },
  { label: 'Kelurahan', value: props.stats?.coverageVillages ?? 0, icon: Navigation },
])

// Map pagination
const mapPageSize = 6
const mapPage = ref(1)
const selectedKecamatan = ref('')
const selectedKelurahan = ref('')

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

const filteredMapProviders = computed(() => {
  const kecamatan = normalizeArea(selectedKecamatan.value)
  const kelurahan = normalizeArea(selectedKelurahan.value)

  return props.mapProviders.filter((provider) => {
    if (kecamatan) {
      if (normalizeArea(provider.kecamatan) !== kecamatan) return false
    }
    if (kelurahan) {
      if (normalizeArea(provider.kelurahan) !== kelurahan) return false
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

watch(
  () => props.mapProviders.length,
  () => {
    mapPage.value = 1
  },
)
watch(
  () => filteredMapProviders.value.length,
  () => {
    mapPage.value = 1
  },
)
watch(selectedKecamatan, () => {
  selectedKelurahan.value = ''
  mapPage.value = 1
})
watch(selectedKelurahan, () => {
  mapPage.value = 1
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
          <div class="flex flex-wrap items-center gap-3">
            <Button as-child size="lg" class="shadow-lg shadow-emerald-500/20">
              <a href="#data">Lihat data provider</a>
            </Button>
            <Button as-child size="lg" variant="outline" class="backdrop-blur">
              <Link :href="route('login')">Masuk dashboard</Link>
            </Button>
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
    <section id="data" class="relative overflow-hidden bg-gradient-to-b from-emerald-50 via-white to-white py-16 dark:from-gray-950 dark:via-gray-900 dark:to-gray-900">
      <div class="absolute inset-0 -z-10 bg-[radial-gradient(circle_at_top,_#34d3991a,_transparent_40%)]"></div>
      <div class="mx-auto max-w-6xl px-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-6">
          <div>
            <p class="text-sm font-semibold uppercase tracking-wide text-emerald-600 dark:text-emerald-300">
              Peta sebaran
            </p>
            <h2 class="text-3xl font-bold text-gray-900 dark:text-white">Provider</h2>
            <p class="mt-2 max-w-2xl text-gray-600 dark:text-gray-400">
              Lihat posisi koordinat dan buka langsung di Google Maps untuk verifikasi lapangan.
            </p>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
              Menampilkan {{ filteredProviderGroups.length }} provider (total {{ allProviderNames.length }})
            </p>
          </div>
          <Badge variant="secondary" class="w-fit">
            {{ filteredProviderGroups.length }} provider (filter) / {{ allProviderNames.length }} total
          </Badge>
        </div>

        <div class="mb-6 rounded-2xl border border-emerald-100/70 bg-white/80 p-4 shadow-sm backdrop-blur dark:border-gray-800 dark:bg-gray-900/70">
          <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div class="grid gap-3 sm:grid-cols-2">
              <label class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                Kecamatan
                <select
                  v-model="selectedKecamatan"
                  class="mt-2 w-full rounded-lg border border-emerald-100/70 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-200 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:border-emerald-500 dark:focus:ring-emerald-900/40"
                >
                  <option value="">Semua kecamatan</option>
                  <option v-for="kecamatan in kecamatanOptions" :key="kecamatan.value" :value="kecamatan.value">
                    {{ kecamatan.label }}
                  </option>
                </select>
              </label>
              <label class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                Kelurahan
                <select
                  v-model="selectedKelurahan"
                  :disabled="!selectedKecamatan"
                  class="mt-2 w-full rounded-lg border border-emerald-100/70 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-200 disabled:cursor-not-allowed disabled:bg-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:border-emerald-500 dark:focus:ring-emerald-900/40 dark:disabled:bg-gray-800"
                >
                  <option value="">Semua kelurahan</option>
                  <option v-for="kelurahan in kelurahanOptions" :key="kelurahan.value" :value="kelurahan.value">
                    {{ kelurahan.label }}
                  </option>
                </select>
              </label>
            </div>
            <div class="text-sm text-gray-600 dark:text-gray-300">
              <span v-if="selectedKecamatan">{{ selectedKecamatan }}</span>
              <span v-if="selectedKelurahan"> / {{ selectedKelurahan }}</span>
              <span v-if="selectedKecamatan || selectedKelurahan"> | </span>
              <span>Jumlah Provider: {{ filteredProviderNames.length }}</span>
              <span class="block text-xs text-gray-500 dark:text-gray-400">
                {{ filteredProviderPreview }}
              </span>
            </div>
          </div>
        </div>

        <div v-if="filteredProviderGroups.length" class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
          <div
            v-for="point in mapPaginatedProviders"
            :key="point.key"
            class="rounded-2xl border border-emerald-100/70 bg-white/80 p-4 shadow-sm backdrop-blur hover:shadow-md transition dark:border-gray-800 dark:bg-gray-900/70"
          >
            <p class="text-xs uppercase tracking-wide text-emerald-600 dark:text-emerald-300">Provider</p>
            <p class="mt-2 text-lg font-semibold text-gray-900 dark:text-white">{{ point.label || 'Provider' }}</p>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
              <span v-if="point.sample?.sijali">
                Nama jalan: {{ point.sample.sijali }} ({{ formatNumber(getStreetProviderCount(point.sample.sijali)) }} provider)
              </span>
              <span v-else>Nama jalan belum tersedia</span>
            </p>
          </div>
        </div>

        <div v-if="filteredProviderGroups.length" class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between text-sm text-gray-600 dark:text-gray-300">
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
            <span class="text-gray-500 dark:text-gray-400"> (total {{ allProviderNames.length }})</span>
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
          Tidak ada data yang cocok dengan filter. Ubah filter kecamatan atau kelurahan untuk melihat provider.
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
