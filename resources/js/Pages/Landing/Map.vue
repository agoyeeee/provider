<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import LandingLayout from '@/Layouts/LandingLayout.vue'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Badge } from '@/Components/ui/badge'
import { Navigation, Clock4, Search, Home, Globe2, X, RotateCcw, MapPinned } from 'lucide-vue-next'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'
import 'leaflet.markercluster'
import 'leaflet.markercluster/dist/MarkerCluster.css'
import 'leaflet.markercluster/dist/MarkerCluster.Default.css'

// Fix Leaflet default marker icon issue with Vite
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png'
import markerIcon from 'leaflet/dist/images/marker-icon.png'
import markerShadow from 'leaflet/dist/images/marker-shadow.png'

delete L.Icon.Default.prototype._getIconUrl
L.Icon.Default.mergeOptions({
  iconUrl: markerIcon,
  iconRetinaUrl: markerIcon2x,
  shadowUrl: markerShadow,
})

const props = defineProps({
  wilayah: { type: Array, default: () => [] }, // [{kecamatan, kelurahan}]
  providerOptions: { type: Array, default: () => [] },
  jalanOptions: { type: Array, default: () => [] },
  appName: { type: String, default: 'Portal Provider' },
  canLogin: { type: Boolean, default: true },
  canRegister: { type: Boolean, default: false },
})

const emptyFilter = () => ({ search: '', provider: 'all', kecamatan: 'all', kelurahan: 'all', jalan: 'all' })
// filter yang dipakai di peta (diisi saat TAMPILKAN) vs isian form
const applied = ref(emptyFilter())
const draft = ref(emptyFilter())
const activePanel = ref('search') // 'search' | 'base' | null
const baseKey = ref('osm')

const points = ref([]) // titik di area peta yang tampil (dari server)
const truncated = ref(false)
const loading = ref(false)

let map = null
let cluster = null
let activeBase = null
const baseLayers = {}
const markerById = {}
let fetchTimer = null
let abortCtl = null
let skipNextMove = false

const kecamatanOptions = computed(() => [...new Set(props.wilayah.map((w) => w.kecamatan).filter(Boolean))])
const kelurahanOptions = computed(() => [
  ...new Set(
    props.wilayah
      .filter((w) => draft.value.kecamatan === 'all' || w.kecamatan === draft.value.kecamatan)
      .map((w) => w.kelurahan),
  ),
])

watch(() => draft.value.kecamatan, () => { draft.value.kelurahan = 'all' })

// daftar hasil di panel: client-side paging dari titik yang sudah dimuat
const pageSize = 15
const mapPage = ref(1)
const mapTotalPages = computed(() => Math.max(1, Math.ceil(points.value.length / pageSize)))
const paginatedProviders = computed(() => points.value.slice((mapPage.value - 1) * pageSize, mapPage.value * pageSize))
const filteredProviders = points

function esc(v) {
  return String(v ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]))
}

function filterParams() {
  const f = applied.value
  const q = {}
  if (f.search.trim()) q.q = f.search.trim()
  if (f.provider !== 'all') q.provider = f.provider
  if (f.kecamatan !== 'all') q.kecamatan = f.kecamatan
  if (f.kelurahan !== 'all') q.kelurahan = f.kelurahan
  if (f.jalan !== 'all') q.jalan = f.jalan
  return q
}

// useBounds=false: ambil semua titik yang cocok filter lalu zoom ke hasilnya
async function loadPoints(useBounds) {
  if (!map) return
  const params = new URLSearchParams(filterParams())
  if (useBounds) {
    const b = map.getBounds()
    params.set('west', b.getWest()); params.set('east', b.getEast())
    params.set('south', b.getSouth()); params.set('north', b.getNorth())
  }
  abortCtl?.abort()
  abortCtl = new AbortController()
  loading.value = true
  try {
    const res = await fetch(`${route('landing.map.points')}?${params}`, {
      headers: { Accept: 'application/json' },
      signal: abortCtl.signal,
    })
    if (!res.ok) throw new Error(res.status)
    const data = await res.json()
    points.value = data.points
    truncated.value = data.truncated
    mapPage.value = 1
    renderMarkers()
    if (!useBounds && data.points.length) {
      skipNextMove = true // fitBounds memicu moveend; data sudah lengkap
      const bounds = L.latLngBounds(data.points.map((p) => [p.coordinates.lat, p.coordinates.lng]))
      map.fitBounds(bounds, { padding: [40, 40], maxZoom: 17, animate: false })
    }
  } catch (e) {
    if (e.name !== 'AbortError') console.error('Gagal memuat titik peta', e)
  } finally {
    loading.value = false
  }
}

function onMoveEnd() {
  if (skipNextMove) { skipNextMove = false; return }
  clearTimeout(fetchTimer)
  fetchTimer = setTimeout(() => loadPoints(true), 300)
}

function applyFilter() {
  applied.value = { ...draft.value }
  loadPoints(false)
}

function resetFilter() {
  draft.value = emptyFilter()
  applyFilter()
}

function togglePanel(name) {
  activePanel.value = activePanel.value === name ? null : name
}

onMounted(() => {
  initMap()
  loadPoints(false)
})

onUnmounted(() => {
  clearTimeout(fetchTimer)
  abortCtl?.abort()
  map?.remove()
})

function initMap() {
  map = L.map('provider-map', { center: [-7.57, 110.79], zoom: 12, zoomControl: false })

  baseLayers.osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors', maxZoom: 19,
  })
  baseLayers.satellite = L.tileLayer('https://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
    attribution: '© Google Satellite', maxZoom: 20,
  })
  baseLayers.hybrid = L.tileLayer('https://mt1.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', {
    attribution: '© Google Hybrid', maxZoom: 20,
  })
  baseLayers.esri = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
    attribution: '© Esri World Imagery', maxZoom: 19,
  })
  activeBase = baseLayers.osm.addTo(map)

  L.control.zoom({ position: 'topright' }).addTo(map)
  cluster = L.markerClusterGroup({ chunkedLoading: true, maxClusterRadius: 50 }).addTo(map)
  map.on('moveend', onMoveEnd)
}

function switchBase(layerKey) {
  if (!map || !baseLayers[layerKey]) return
  if (activeBase) map.removeLayer(activeBase)
  activeBase = baseLayers[layerKey].addTo(map)
  baseKey.value = layerKey
}

function popupHtml(p) {
  const photo = p.foto_url
    ? `<img src="${esc(p.foto_url)}" alt="${esc(p.name)}" style="width:100%;max-height:180px;object-fit:cover;border-radius:8px;margin-bottom:8px;">`
    : `<div style="width:100%;height:160px;display:flex;align-items:center;justify-content:center;background:#f3f4f6;color:#6b7280;border-radius:8px;margin-bottom:8px;">Foto tidak tersedia</div>`
  const izin = { berizin: 'Berizin', tidak_berizin: 'Tidak berizin' }
  const tiang = (p.tiang || [])
    .map((t) => `<span style="font-size:12px;color:#374151">${esc(t.n_provider)}: ODP <b>${t.odp ? 'Ada' : 'Tidak Ada'}</b> · Izin <b>${izin[t.status_izin] || '-'}</b></span><br/>`)
    .join('')
  // href hanya http(s) agar tidak bisa disisipi javascript:
  const safe = (u) => (/^https?:\/\//i.test(u || '') ? esc(u) : null)
  const sijali = safe(p.sijali_link)
  const maps = safe(p.maps_url)
  return `
    <div style="min-width:240px;font-family:inherit">
      ${photo}
      <strong style="font-size:14px;color:#111827">${esc(p.name) || 'Provider'}</strong><br/>
      <span style="font-size:12px;color:#6b7280">${esc(p.location) || 'Lokasi tidak tersedia'}</span><br/>
      <span style="font-size:12px;color:#374151">Kecamatan: <b>${esc(p.kecamatan) || '-'}</b></span><br/>
      <span style="font-size:12px;color:#374151">Kelurahan: <b>${esc(p.kelurahan) || '-'}</b></span><br/>
      ${tiang}
      <span style="font-size:12px;color:#374151">Utilitas: <b>${esc(p.utilitas) || '-'}</b></span><br/>
      <span style="font-size:12px;color:#d97706">Sijali: <b>${esc(p.sijali) || '-'}</b></span><br/>
      <span style="font-size:12px;color:#6b7280">Tgl Survey: ${esc(p.surveyed_at) || '-'}</span><br/>
      <div style="margin-top:8px;display:flex;gap:8px;flex-wrap:wrap">
        ${sijali ? `<a href="${sijali}" target="_blank" rel="noopener" style="display:inline-block;padding:4px 10px;background:#d97706;color:#fff;border-radius:6px;font-size:11px;font-weight:600;text-decoration:none">Buka Sijali</a>` : ''}
        ${maps ? `<a href="${maps}" target="_blank" rel="noopener" style="display:inline-block;padding:4px 10px;background:#2563eb;color:#fff;border-radius:6px;font-size:11px;font-weight:600;text-decoration:none">Google Maps</a>` : ''}
      </div>
    </div>`
}

function renderMarkers() {
  cluster.clearLayers()
  Object.keys(markerById).forEach((k) => delete markerById[k])
  const markers = points.value.map((p) => {
    const m = L.marker([p.coordinates.lat, p.coordinates.lng], { title: p.name || 'Provider' })
      .bindPopup(() => popupHtml(p), { maxWidth: 280 }) // HTML dibuat saat popup dibuka
    markerById[p.id] = m
    return m
  })
  cluster.addLayers(markers)
}

function handleSelectProvider(p) {
  const m = markerById[p.id]
  if (!map || !m) return
  // zoomToShowLayer membuka cluster lalu callback dipanggil saat marker terlihat
  cluster.zoomToShowLayer(m, () => m.openPopup())
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
      <div class="fixed top-20 right-4 z-40 flex w-[calc(100%-2rem)] flex-col items-end gap-3 sm:top-24 sm:w-80">
        <div class="flex gap-3">
          <Button size="icon" class="h-11 w-11 rounded-full shadow-lg" as-child title="Beranda">
            <Link :href="route('landing')">
              <Home class="h-5 w-5" />
            </Link>
          </Button>
          <Button size="icon" class="h-11 w-11 rounded-full shadow-lg" @click="togglePanel('search')" title="Pencarian">
            <Search class="h-5 w-5" />
          </Button>
          <Button size="icon" class="h-11 w-11 rounded-full shadow-lg" @click="togglePanel('base')" title="Base Map">
            <Globe2 class="h-5 w-5" />
          </Button>
        </div>

        <!-- Base map -->
        <div v-if="activePanel === 'base'"
          class="w-full overflow-hidden rounded-xl bg-white shadow-xl dark:bg-gray-900">
          <div class="bg-primary px-4 py-2 text-center text-sm font-bold uppercase text-primary-foreground">Base Map</div>
          <div class="space-y-2 p-4 text-sm text-gray-800 dark:text-gray-100">
            <label v-for="opt in [['osm', 'OpenStreetMap (Default)'], ['satellite', 'Google Satellite'], ['hybrid', 'Google Hybrid'], ['esri', 'ESRI World Imagery']]"
              :key="opt[0]" class="flex cursor-pointer items-center gap-2">
              <input type="radio" name="basemap" :checked="baseKey === opt[0]" @change="switchBase(opt[0])" />
              {{ opt[1] }}
            </label>
          </div>
        </div>

        <!-- Pencarian -->
        <div v-if="activePanel === 'search'"
          class="w-full overflow-hidden rounded-xl bg-white shadow-xl dark:bg-gray-900">
          <div class="relative bg-primary px-4 py-2 text-center text-sm font-bold uppercase text-primary-foreground">
            Pencarian
            <button class="absolute right-3 top-1/2 -translate-y-1/2" @click="activePanel = null" title="Tutup">
              <X class="h-4 w-4" />
            </button>
          </div>
          <div class="max-h-[calc(100vh-14rem)] space-y-3 overflow-y-auto p-4 text-sm text-gray-800 dark:text-gray-100">
            <div v-for="f in [
              { label: 'Kecamatan', key: 'kecamatan', all: 'Pilih Kecamatan', opts: kecamatanOptions },
              { label: 'Kelurahan', key: 'kelurahan', all: 'Pilih Kelurahan', opts: kelurahanOptions },
              { label: 'Provider', key: 'provider', all: 'Pilih Provider', opts: providerOptions },
              { label: 'Nama Jalan (Sijali)', key: 'jalan', all: 'Pilih Jalan', opts: jalanOptions },
            ]" :key="f.key">
              <label class="mb-1 block font-semibold">{{ f.label }}</label>
              <select v-model="draft[f.key]"
                class="w-full rounded-md border border-gray-300 bg-white px-2 py-2 dark:border-gray-700 dark:bg-gray-800">
                <option value="all">{{ f.all }}</option>
                <option v-for="o in f.opts" :key="o" :value="o">{{ o }}</option>
              </select>
            </div>
            <div>
              <label class="mb-1 block font-semibold">Kata Kunci</label>
              <Input v-model="draft.search" placeholder="Provider, ODP, Sijali..." @keydown.enter="applyFilter" />
            </div>
            <Button class="w-full" @click="applyFilter">
              <MapPinned class="mr-2 h-4 w-4" /> TAMPILKAN
            </Button>
            <Button variant="secondary" size="sm" @click="resetFilter">
              <RotateCcw class="mr-2 h-4 w-4" /> RESET
            </Button>

            <div class="-mx-4 bg-primary px-4 py-2 text-center text-sm font-bold uppercase text-primary-foreground">
              Hasil Pencarian
            </div>
            <div class="space-y-2">
              <div v-for="point in paginatedProviders" :key="point.id"
                class="cursor-pointer rounded-xl border border-gray-100 bg-white p-3 shadow-sm transition hover:shadow-md dark:border-gray-800 dark:bg-gray-800/80"
                @click="handleSelectProvider(point)">
                <div class="flex items-start justify-between gap-2">
                  <div>
                    <p class="text-sm font-semibold">{{ point.name || 'Provider' }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ point.location || 'Lokasi tidak tersedia' }}</p>
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
              <div v-if="!paginatedProviders.length" class="text-gray-500 dark:text-gray-400">
                Tidak ada hasil.
              </div>
            </div>
            <p v-if="truncated" class="text-xs text-amber-600">Titik dibatasi 3000. Perbesar peta atau persempit filter.</p>
            <div class="flex items-center justify-between text-xs text-gray-600 dark:text-gray-300">
              <span>{{ filteredProviders.length }} titik - Hal {{ mapPage }} / {{ mapTotalPages }}</span>
              <div class="flex gap-2">
                <Button size="sm" variant="outline" :disabled="mapPage <= 1"
                  @click="mapPage = Math.max(1, mapPage - 1)">Prev</Button>
                <Button size="sm" variant="outline" :disabled="mapPage >= mapTotalPages"
                  @click="mapPage = Math.min(mapTotalPages, mapPage + 1)">Next</Button>
              </div>
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

/* Smooth popup animation */
#provider-map :global(.leaflet-popup) {
  animation: popupFadeIn 0.2s ease-out;
}

@keyframes popupFadeIn {
  from {
    opacity: 0;
    transform: translateY(6px) scale(0.97);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

/* Pulse ring around selected marker */
#provider-map :global(.leaflet-marker-icon) {
  transition: filter 0.2s ease;
}
#provider-map :global(.leaflet-marker-icon:hover) {
  filter: drop-shadow(0 0 6px rgba(16, 185, 129, 0.8));
}
</style>
