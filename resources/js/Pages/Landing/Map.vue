<script setup>
import { computed, nextTick, onMounted, ref, watch } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import LandingLayout from '@/Layouts/LandingLayout.vue'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Badge } from '@/Components/ui/badge'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/Components/ui/select'
import { Navigation, Clock4, Search, Home, Globe2, Satellite, X } from 'lucide-vue-next'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'

const props = defineProps({
  mapProviders: {
    type: Array,
    default: () => [],
  },
  appName: {
    type: String,
    default: 'Portal Provider',
  },
  canLogin: {
    type: Boolean,
    default: true,
  },
  canRegister: {
    type: Boolean,
    default: false,
  },
})

const search = ref('')
const isSearchOpen = ref(true)
const filterProvider = ref('all')
const filterKelurahan = ref('all')
const filterJalan = ref('all')
const jalanSearch = ref('')
const isJalanOpen = ref(false)
const jalanSearchInput = ref(null)
const mapInstance = ref(null)
const markerLayer = ref(null)
const activeBaseLayer = ref(null)
const baseLayers = {}
const markerById = ref({})

function collectSearchableValue(value) {
  if (value === null || value === undefined) return ''
  if (Array.isArray(value)) return value.map(collectSearchableValue).join(' ')
  if (typeof value === 'object') {
    return Object.values(value).map(collectSearchableValue).join(' ')
  }
  return String(value)
}

function normalizeProviderName(value) {
  return String(value ?? '').toLowerCase().replace(/[^a-z0-9]/g, '')
}

function splitProviderNames(value) {
  return String(value ?? '')
    .split(',')
    .map((part) => part.trim())
    .filter(Boolean)
}

const providerOptions = computed(() => {
  const optionMap = new Map()
  props.mapProviders.forEach((provider) => {
    const providerNames = splitProviderNames(provider?.name)
    providerNames.forEach((rawName) => {
      const key = normalizeProviderName(rawName)
      if (!key) return
      if (!optionMap.has(key)) {
        optionMap.set(key, rawName)
      }
    })
  })
  return Array.from(optionMap.values()).sort((a, b) => a.localeCompare(b))
})

const kelurahanOptions = computed(() => {
  const optionMap = new Map()
  props.mapProviders.forEach((provider) => {
    const rawName = String(provider?.kelurahan ?? '').trim()
    if (!rawName) return
    const key = rawName.toLowerCase()
    if (!optionMap.has(key)) {
      optionMap.set(key, rawName)
    }
  })
  return Array.from(optionMap.values()).sort((a, b) => a.localeCompare(b))
})

const jalanOptions = computed(() => {
  const optionMap = new Map()
  props.mapProviders.forEach((provider) => {
    const rawName = String(provider?.sijali ?? '').trim()
    if (!rawName) return
    const key = rawName.toLowerCase()
    if (!optionMap.has(key)) {
      optionMap.set(key, rawName)
    }
  })
  return Array.from(optionMap.values()).sort((a, b) => a.localeCompare(b))
})

const filteredJalanOptions = computed(() => {
  const keyword = jalanSearch.value.trim().toLowerCase()
  if (!keyword) return jalanOptions.value
  return jalanOptions.value.filter((jalan) => jalan.toLowerCase().includes(keyword))
})

const filteredProviders = computed(() => {
  const keyword = search.value.trim().toLowerCase()
  const providerFilter = filterProvider.value.trim().toLowerCase()
  const kelurahanFilter = filterKelurahan.value.trim().toLowerCase()
  const jalanFilter = filterJalan.value.trim().toLowerCase()
  const providerFilterKey = providerFilter === 'all' ? 'all' : normalizeProviderName(providerFilter)

  if (
    !keyword &&
    providerFilter === 'all' &&
    kelurahanFilter === 'all' &&
    jalanFilter === 'all'
  ) {
    return props.mapProviders
  }

  return props.mapProviders.filter((p) => {
    const kelurahanName = String(p?.kelurahan ?? '').toLowerCase()
    const jalanName = String(p?.sijali ?? '').trim().toLowerCase()
    const providerNames = splitProviderNames(p?.name).map((name) => normalizeProviderName(name))

    if (providerFilterKey !== 'all' && !providerNames.includes(providerFilterKey)) return false
    if (kelurahanFilter !== 'all' && kelurahanName !== kelurahanFilter) return false
    if (jalanFilter !== 'all' && jalanName !== jalanFilter) return false
    if (keyword && !collectSearchableValue(p).toLowerCase().includes(keyword)) return false
    return true
  })
})

const initialCenter = computed(() => {
  if (props.mapProviders.length && props.mapProviders[0].coordinates) {
    return [
      props.mapProviders[0].coordinates.lat || -7.57,
      props.mapProviders[0].coordinates.lng || 110.79,
    ]
  }
  return [-7.57, 110.79]
})

const mapPageSize = 15
const mapPage = ref(1)
const mapTotalPages = computed(() => Math.max(1, Math.ceil(filteredProviders.value.length / mapPageSize)))
const paginatedProviders = computed(() => {
  const start = (mapPage.value - 1) * mapPageSize
  return filteredProviders.value.slice(start, start + mapPageSize)
})

watch(
  () => filteredProviders.value.length,
  () => {
    mapPage.value = 1
    renderMarkers()
  },
)
watch([search, filterProvider, filterKelurahan, filterJalan], () => {
  mapPage.value = 1
  renderMarkers()
})
watch(mapPage, () => {
  renderMarkers()
})
watch(isJalanOpen, (open) => {
  if (!open) {
    jalanSearch.value = ''
    return
  }
  jalanSearch.value = ''
  nextTick(() => {
    jalanSearchInput.value?.focus?.()
  })
})
watch(jalanSearch, (value) => {
  const keyword = value.trim().toLowerCase()
  if (!keyword) {
    if (filterJalan.value !== 'all') {
      filterJalan.value = 'all'
    }
    return
  }
  if (filterJalan.value !== 'all') {
    const current = filterJalan.value.trim().toLowerCase()
    if (!current.includes(keyword)) {
      filterJalan.value = 'all'
    }
  }
})

onMounted(() => {
  initMap()
  renderMarkers()
})

function initMap() {
  if (mapInstance.value) return

  mapInstance.value = L.map('provider-map', {
    center: initialCenter.value,
    zoom: 12,
    zoomControl: false,
  })

  baseLayers.osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors',
    maxZoom: 19,
  })
  baseLayers.satellite = L.tileLayer('https://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
    attribution: '© Google Satellite',
    maxZoom: 20,
  })
  activeBaseLayer.value = baseLayers.osm.addTo(mapInstance.value)

  L.control.zoom({ position: 'topright' }).addTo(mapInstance.value)
}

function switchBase(layerKey) {
  if (!mapInstance.value || !baseLayers[layerKey]) return
  if (activeBaseLayer.value) mapInstance.value.removeLayer(activeBaseLayer.value)
  activeBaseLayer.value = baseLayers[layerKey].addTo(mapInstance.value)
}

function renderMarkers() {
  if (!mapInstance.value) return
  markerById.value = {}
  if (markerLayer.value) {
    markerLayer.value.clearLayers()
  } else {
    markerLayer.value = L.layerGroup().addTo(mapInstance.value)
  }

  const bounds = L.latLngBounds()

  paginatedProviders.value.forEach((p) => {
    const lat = p.coordinates?.lat
    const lng = p.coordinates?.lng
    if (lat === undefined || lng === undefined) return
    const photoHtml = p.foto_url
      ? `<img src="${p.foto_url}" alt="${p.name || 'Provider'}" style="width:100%;max-height:180px;object-fit:cover;border-radius:8px;margin-bottom:8px;">`
      : `<div style="width:100%;height:160px;display:flex;align-items:center;justify-content:center;background:#f3f4f6;color:#6b7280;border-radius:8px;margin-bottom:8px;">Foto tidak tersedia</div>`

    const marker = L.marker([lat, lng], {
      title: p.name || 'Provider',
    }).bindPopup(
      `
        <div style="min-width: 240px">
          ${photoHtml}
          <strong>${p.name || 'Provider'}</strong><br/>
          <small>${p.location || 'Lokasi tidak tersedia'}</small><br/>
          <small>Kecamatan: ${p.kecamatan || '-'}</small><br/>
          <small>Kelurahan: ${p.kelurahan || '-'}</small><br/>
          <small>ODP: ${p.odp || '-'}</small><br/>
          <small>Utilitas: ${p.utilitas || '-'}</small><br/>
          <small>Sijali: ${p.sijali || '-'}</small><br/>
          <small>Tgl Survey: ${p.surveyed_at || '-'}</small><br/>
          ${p.sijali_link ? `<a href="${p.sijali_link}" target="_blank" style="color:#d97706;font-weight:600;">Buka Sijali</a><br/>` : ''}
          ${p.maps_url ? `<a href="${p.maps_url}" target="_blank" style="color:#2563eb">Buka di Google Maps</a>` : ''}
        </div>
      `,
    )

    marker.addTo(markerLayer.value)
    markerById.value[p.id] = marker
    bounds.extend([lat, lng])
  })

  if (paginatedProviders.value.length && bounds.isValid()) {
    mapInstance.value.fitBounds(bounds, { padding: [24, 24] })
  }
}

function handleSelectProvider(p) {
  const lat = p.coordinates?.lat
  const lng = p.coordinates?.lng
  if (!mapInstance.value || lat === undefined || lng === undefined) return
  mapInstance.value.setView([lat, lng], 25)
  const marker = markerById.value[p.id]
  if (marker) {
    marker.openPopup()
  }
}
</script>

<template>
  <LandingLayout :can-login="canLogin" :can-register="canRegister" :show-footer="false">
    <Head title="Peta Provider" />

    <section class="relative min-h-screen bg-gray-100 dark:bg-gray-900">
      <div class="absolute inset-0 z-0">
        <div id="provider-map" class="h-full w-full"></div>
      </div>
      <!-- Controls -->
      <div class="fixed top-20 right-4 left-4 z-40 flex flex-col items-stretch gap-3 sm:top-24 md:left-auto md:items-end">
        <div class="flex w-full flex-wrap items-center gap-2 rounded-2xl bg-white/90 px-3 py-2 shadow-lg backdrop-blur dark:bg-gray-800/90 md:w-auto md:rounded-full">
          <Button size="sm" variant="ghost" class="text-gray-700 dark:text-gray-200" as-child title="Beranda">
            <Link :href="route('landing')">
              <Home class="h-4 w-4" />
            </Link>
          </Button>
          <Button size="sm" variant="ghost" class="text-gray-700 dark:text-gray-200" @click="switchBase('osm')" title="OpenStreetMap">
            <Globe2 class="h-4 w-4" />
          </Button>
          <Button size="sm" variant="ghost" class="text-gray-700 dark:text-gray-200" @click="switchBase('satellite')" title="Satellite">
            <Satellite class="h-4 w-4" />
          </Button>
          <Button
            size="sm"
            variant="ghost"
            class="text-gray-700 dark:text-gray-200"
            @click="isSearchOpen = !isSearchOpen"
            :title="isSearchOpen ? 'Tutup Filter' : 'Buka Filter'"
          >
            <Search class="h-4 w-4" />
            <span class="ml-2 hidden text-xs font-medium sm:inline">
              {{ isSearchOpen ? 'Tutup Filter' : 'Buka Filter' }}
            </span>
          </Button>
          <Select v-model="filterProvider">
            <SelectTrigger class="w-full sm:w-52 md:w-44">
              <SelectValue placeholder="Pilih Provider" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">Semua Provider</SelectItem>
              <SelectItem v-for="provider in providerOptions" :key="provider" :value="provider">
                {{ provider }}
              </SelectItem>
            </SelectContent>
          </Select>
          <Select v-model="filterKelurahan">
            <SelectTrigger class="w-full sm:w-52 md:w-44">
              <SelectValue placeholder="Pilih Kelurahan" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">Semua Kelurahan</SelectItem>
              <SelectItem v-for="kelurahan in kelurahanOptions" :key="kelurahan" :value="kelurahan">
                {{ kelurahan }}
              </SelectItem>
            </SelectContent>
          </Select>
          <Select v-model="filterJalan" v-model:open="isJalanOpen">
            <SelectTrigger class="w-full sm:w-52 md:w-44">
              <SelectValue placeholder="Pilih Nama Jalan" />
            </SelectTrigger>
            <SelectContent>
              <div class="p-2">
                <Input
                  ref="jalanSearchInput"
                  v-model="jalanSearch"
                  placeholder="Cari nama jalan..."
                  class="h-8"
                  @keydown.stop
                />
              </div>
              <SelectItem value="all">Semua Jalan</SelectItem>
              <SelectItem v-for="jalan in filteredJalanOptions" :key="jalan" :value="jalan">
                {{ jalan }}
              </SelectItem>
              <div v-if="!filteredJalanOptions.length" class="px-3 pb-2 text-xs text-gray-500 dark:text-gray-400">
                Jalan tidak ditemukan.
              </div>
            </SelectContent>
          </Select>
        </div>

        <div v-show="isSearchOpen" class="w-full rounded-2xl bg-white/95 p-4 shadow-xl backdrop-blur dark:bg-gray-900/90 sm:w-96 md:w-72 md:max-w-sm">
          <div class="flex items-center gap-2 mb-3">
            <Search class="h-4 w-4 text-emerald-600" />
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Cari Provider / ODP / Sijali / Kecamatan / Kelurahan</h3>
            <Button
              size="icon"
              variant="ghost"
              class="ml-auto h-8 w-8 rounded-full bg-rose-50 text-rose-600 hover:bg-rose-100 dark:bg-rose-500/10 dark:text-rose-300"
              @click="isSearchOpen = false"
              title="Tutup"
            >
              <X class="h-4 w-4" />
            </Button>
          </div>
          <div class="space-y-2 mb-3">
            <Input
              v-model="search"
              placeholder="Cari provider, ODP, Sijali, Kecamatan, Kelurahan..."
              class="w-full"
            />
          </div>
          <div class="max-h-52 overflow-y-auto space-y-2 sm:max-h-64">
            <div
              v-for="point in paginatedProviders"
              :key="point.id"
              class="cursor-pointer rounded-xl border border-gray-100 bg-white p-3 shadow-sm hover:shadow-md transition dark:border-gray-800 dark:bg-gray-800/80"
              @click="handleSelectProvider(point)"
            >
              <div class="flex items-start justify-between gap-2">
                <div>
                  <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ point.name || 'Provider' }}</p>
                  <p class="text-xs text-gray-500 dark:text-gray-400">{{ point.location || 'Lokasi tidak tersedia' }}</p>
                  <p class="text-xs text-gray-500 dark:text-gray-400">Kecamatan: {{ point.kecamatan || '-' }}</p>
                  <p class="text-xs text-gray-500 dark:text-gray-400">Kelurahan: {{ point.kelurahan || '-' }}</p>
                  <p class="text-xs text-amber-600 dark:text-amber-300">Sijali: {{ point.sijali || '-' }}</p>
                </div>
                <Badge variant="secondary">{{ point.odp || 'ODP' }}</Badge>
              </div>
              <div class="mt-1 flex flex-wrap items-center gap-3 text-[11px] text-gray-600 dark:text-gray-400">
                <span class="inline-flex items-center gap-1">
                  <Navigation class="h-3 w-3" /> {{ point.coordinates?.lat ?? '-' }}, {{ point.coordinates?.lng ?? '-' }}
                </span>
                <span class="inline-flex items-center gap-1">
                  <Clock4 class="h-3 w-3" /> {{ point.surveyed_at || '-' }}
                </span>
              </div>
            </div>
            <div v-if="!paginatedProviders.length" class="text-sm text-gray-500 dark:text-gray-400">
              Tidak ada hasil untuk kata kunci ini.
            </div>
          </div>
          <div class="mt-3 flex items-center justify-between text-xs text-gray-600 dark:text-gray-300">
            <span>{{ filteredProviders.length }} titik - Hal {{ mapPage }} / {{ mapTotalPages }}</span>
            <div class="flex gap-2">
              <Button size="sm" variant="outline" :disabled="mapPage <= 1" @click="mapPage = Math.max(1, mapPage - 1)">Prev</Button>
              <Button size="sm" variant="outline" :disabled="mapPage >= mapTotalPages" @click="mapPage = Math.min(mapTotalPages, mapPage + 1)">Next</Button>
            </div>
          </div>
        </div>
      </div>
    </section>
  </LandingLayout>
</template>

<style scoped>
#provider-map :global(.leaflet-container) {
  height: 100%;
  width: 100%;
}
</style>
