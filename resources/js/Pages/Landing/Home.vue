<script setup>
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import LandingLayout from '@/Layouts/LandingLayout.vue'
import { Button } from '@/Components/ui/button'
import { Badge } from '@/Components/ui/badge'
import Distribution3DChart from '@/Components/Landing/Distribution3DChart.vue'
import {
  ArrowRight,
  ArrowUpRight,
  Building2,
  CheckCircle2,
  Clock4,
  ExternalLink,
  Map as MapIcon,
  MapPin,
  Navigation,
  Radio,
  RotateCcw,
  Search,
  ShieldAlert,
  ShieldCheck,
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

// ------------------------------------------------------------
// Scroll Reveal Directive (IntersectionObserver)
// ------------------------------------------------------------
let revealObserver = null
const getRevealObserver = () => {
  if (revealObserver || typeof window === 'undefined') return revealObserver
  revealObserver = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-revealed')
          revealObserver?.unobserve(entry.target)
        }
      })
    },
    {
      threshold: 0.12,
      rootMargin: '0px 0px -36px 0px',
    },
  )
  return revealObserver
}

const vReveal = {
  mounted(el, binding) {
    const opts = typeof binding.value === 'object' && binding.value !== null ? binding.value : {}
    const delay = opts.delay ?? 0
    const direction = opts.direction ?? 'up'

    el.classList.add('reveal-base', `reveal-${direction}`)
    if (delay) {
      el.style.transitionDelay = `${delay}ms`
    }

    const prefersReduced =
      typeof window !== 'undefined' &&
      window.matchMedia('(prefers-reduced-motion: reduce)').matches

    if (prefersReduced || typeof IntersectionObserver === 'undefined') {
      el.classList.add('is-revealed')
      return
    }

    getRevealObserver()?.observe(el)
  },
  unmounted(el) {
    revealObserver?.unobserve(el)
  },
}

// ------------------------------------------------------------
// Hero Entrance + Scroll Parallax + Interactive Hotspots
// ------------------------------------------------------------
const heroEntered = ref(false)
const scrollY = ref(0)
const activeHotspot = ref(1)

const handleHeroScroll = () => {
  scrollY.value = window.scrollY || 0
}

const textParallaxStyle = computed(() => {
  const offset = Math.min(scrollY.value * 0.16, 56)
  return { transform: `translate3d(0, ${offset.toFixed(1)}px, 0)` }
})

const towerParallaxStyle = computed(() => {
  const offset = Math.max(scrollY.value * -0.07, -28)
  return { transform: `translate3d(0, ${offset.toFixed(1)}px, 0)` }
})

// ------------------------------------------------------------
// Animated Stat Counters
// ------------------------------------------------------------
const statsSectionRef = ref(null)
const hasAnimatedStats = ref(false)
const animatedValues = ref({
  totalRecords: 0,
  uniqueProviders: 0,
  coverageDistricts: 0,
  coverageVillages: 0,
})

let statsObserver = null
let animationFrameId = null

const animateNumbers = () => {
  const targets = {
    totalRecords: Number(props.stats?.totalRecords ?? 0),
    uniqueProviders: Number(props.stats?.uniqueProviders ?? 0),
    coverageDistricts: Number(props.stats?.coverageDistricts ?? 0),
    coverageVillages: Number(props.stats?.coverageVillages ?? 0),
  }

  const duration = 950
  const start = performance.now()

  const step = (now) => {
    const elapsed = now - start
    const progress = Math.min(elapsed / duration, 1)
    // easeOutExpo
    const eased = progress === 1 ? 1 : 1 - Math.pow(2, -10 * progress)

    animatedValues.value = {
      totalRecords: Math.round(targets.totalRecords * eased),
      uniqueProviders: Math.round(targets.uniqueProviders * eased),
      coverageDistricts: Math.round(targets.coverageDistricts * eased),
      coverageVillages: Math.round(targets.coverageVillages * eased),
    }

    if (progress < 1) {
      animationFrameId = requestAnimationFrame(step)
    }
  }

  animationFrameId = requestAnimationFrame(step)
}

onMounted(() => {
  requestAnimationFrame(() => {
    heroEntered.value = true
  })

  window.addEventListener('scroll', handleHeroScroll, { passive: true })

  if (typeof IntersectionObserver !== 'undefined' && statsSectionRef.value) {
    statsObserver = new IntersectionObserver(
      (entries) => {
        if (entries[0]?.isIntersecting && !hasAnimatedStats.value) {
          hasAnimatedStats.value = true
          animateNumbers()
          statsObserver?.disconnect()
        }
      },
      { threshold: 0.2 },
    )
    statsObserver.observe(statsSectionRef.value)
  } else {
    hasAnimatedStats.value = true
    animateNumbers()
  }
})

onUnmounted(() => {
  window.removeEventListener('scroll', handleHeroScroll)
  statsObserver?.disconnect()
  revealObserver?.disconnect()
  if (animationFrameId) cancelAnimationFrame(animationFrameId)
})

const heroStats = computed(() => [
  {
    key: 'totalRecords',
    label: 'Titik Data Tercatat',
    hint: 'Total titik survei utilitas',
    value: animatedValues.value.totalRecords,
    icon: Radio,
    accent: 'text-emerald-600 bg-emerald-500/10 ring-emerald-500/20 dark:text-emerald-400',
  },
  {
    key: 'uniqueProviders',
    label: 'Provider Terdata',
    hint: 'Operator jaringan aktif',
    value: animatedValues.value.uniqueProviders,
    icon: ShieldCheck,
    accent: 'text-sky-600 bg-sky-500/10 ring-sky-500/20 dark:text-sky-400',
  },
  {
    key: 'coverageDistricts',
    label: 'Kecamatan',
    hint: 'Wilayah kecamatan tercakup',
    value: animatedValues.value.coverageDistricts,
    icon: MapPin,
    accent: 'text-violet-600 bg-violet-500/10 ring-violet-500/20 dark:text-violet-400',
  },
  {
    key: 'coverageVillages',
    label: 'Kelurahan',
    hint: 'Titik kelurahan terpetakan',
    value: animatedValues.value.coverageVillages,
    icon: Navigation,
    accent: 'text-amber-600 bg-amber-500/10 ring-amber-500/20 dark:text-amber-400',
  },
])

// ------------------------------------------------------------
// Data Normalization & Filtering
// ------------------------------------------------------------
const mapPageSize = 6
const mapPage = ref(1)
const selectedKecamatan = ref('')
const selectedKelurahan = ref('')
const searchQuery = ref('')

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
    .filter((provider) => normalizeArea(provider.kecamatan) === kecamatanKey)
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
  const query = normalizeArea(searchQuery.value)

  return props.mapProviders.filter((provider) => {
    if (kecamatan && normalizeArea(provider.kecamatan) !== kecamatan) return false
    if (kelurahan && normalizeArea(provider.kelurahan) !== kelurahan) return false
    if (query) {
      const haystack = [
        provider.name,
        provider.sijali,
        provider.kecamatan,
        provider.kelurahan,
      ]
        .filter(Boolean)
        .join(' ')
        .toLowerCase()
      if (!haystack.includes(query)) return false
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
  return Array.from(names.values()).sort((a, b) =>
    a.localeCompare(b, 'id', { sensitivity: 'base' }),
  )
}

const allProviderNames = computed(() => collectUniqueProviders(props.mapProviders))

const mapTotalPages = computed(() =>
  Math.max(1, Math.ceil((filteredProviderGroups.value.length || 0) / mapPageSize)),
)
const mapPaginatedProviders = computed(() => {
  const start = (mapPage.value - 1) * mapPageSize
  return filteredProviderGroups.value.slice(start, start + mapPageSize)
})

const hasActiveFilter = computed(
  () => Boolean(selectedKecamatan.value || selectedKelurahan.value || searchQuery.value),
)

const resetFilters = () => {
  selectedKecamatan.value = ''
  selectedKelurahan.value = ''
  searchQuery.value = ''
  mapPage.value = 1
}

watch([() => props.mapProviders.length, () => filteredMapProviders.value.length], () => {
  mapPage.value = 1
})
watch(selectedKecamatan, () => {
  selectedKelurahan.value = ''
  mapPage.value = 1
})
watch([selectedKelurahan, searchQuery], () => {
  mapPage.value = 1
})

const formatNumber = (value) => new Intl.NumberFormat('id-ID').format(value ?? 0)
const formatDate = (value) =>
  value ? new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium' }).format(new Date(value)) : 'Belum ada'

// ------------------------------------------------------------
// 3D Distribution Data & Area Drill-down
// ------------------------------------------------------------
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

const kecamatanDistribution = computed(() => buildAreaDistribution(props.mapProviders, 'kecamatan'))
const kelurahanDistribution = computed(() => buildAreaDistribution(props.mapProviders, 'kelurahan'))

const handleSelectArea = async ({ type, name }) => {
  searchQuery.value = ''
  if (type === 'kecamatan') {
    const match = kecamatanOptions.value.find(
      (opt) => normalizeArea(opt.value) === normalizeArea(name),
    )
    selectedKecamatan.value = match ? match.value : name
    await nextTick()
    selectedKelurahan.value = ''
  } else if (type === 'kelurahan') {
    const sample = props.mapProviders.find(
      (p) => normalizeArea(p.kelurahan) === normalizeArea(name) && p.kecamatan,
    )
    if (sample?.kecamatan) {
      const kecMatch = kecamatanOptions.value.find(
        (opt) => normalizeArea(opt.value) === normalizeArea(sample.kecamatan),
      )
      selectedKecamatan.value = kecMatch ? kecMatch.value : sample.kecamatan
      await nextTick()
      const kelMatch = kelurahanOptions.value.find(
        (opt) => normalizeArea(opt.value) === normalizeArea(name),
      )
      selectedKelurahan.value = kelMatch ? kelMatch.value : name
    } else {
      searchQuery.value = name
    }
  }
  document.getElementById('data')?.scrollIntoView({ behavior: 'smooth', block: 'start' })
}

// ------------------------------------------------------------
// Permohonan Interactive Tabs
// ------------------------------------------------------------
const activePermitTab = ref('all')

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

const filteredPermitRequests = computed(() => {
  if (activePermitTab.value === 'all') return permitRequests
  return permitRequests.filter((request) => request.status === activePermitTab.value)
})
</script>

<template>
  <LandingLayout :can-login="canLogin" :can-register="canRegister">
    <Head title="Beranda" />

    <!-- ===================================================== -->
    <!-- HERO SECTION (Editorial Layered SIPJALIN + Tower)     -->
    <!-- ===================================================== -->
    <section
      id="hero"
      class="relative flex min-h-[620px] md:h-[calc(100vh-68px)] md:max-h-[840px] flex-col justify-between overflow-hidden bg-gradient-to-br from-emerald-50 via-white to-sky-100/70 pt-4 pb-5 sm:pt-5 sm:pb-7"
    >
      <!-- Gradient Mesh, Soft Blobs & Subtle Grid -->
      <div class="pointer-events-none absolute inset-0 -z-10 overflow-hidden">
        <!-- Base color washes -->
        <div
          class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_left,_rgba(16,185,129,0.20),_transparent_55%),radial-gradient(ellipse_at_top_right,_rgba(56,189,248,0.22),_transparent_55%),radial-gradient(ellipse_at_bottom,_rgba(167,139,250,0.14),_transparent_60%)]"
        ></div>

        <!-- Animated blurred blobs -->
        <div
          class="animate-blob absolute -top-24 -left-20 h-[26rem] w-[26rem] rounded-full bg-emerald-300/40 blur-3xl"
        ></div>
        <div
          class="animate-blob animation-delay-2000 absolute top-1/4 -right-24 h-[28rem] w-[28rem] rounded-full bg-sky-300/40 blur-3xl"
        ></div>
        <div
          class="animate-blob absolute -bottom-32 left-1/3 h-[24rem] w-[24rem] rounded-full bg-violet-300/25 blur-3xl"
        ></div>

        <!-- Subtle grid pattern -->
        <div class="hero-grid-pattern absolute inset-0 opacity-[0.22]"></div>

        <!-- Soft fade into the next section -->
        <div
          class="absolute inset-x-0 bottom-0 h-24 bg-gradient-to-t from-white/80 to-transparent"
        ></div>
      </div>

      <!-- Layer 2 (Front Desktop): Cell Tower grounded cleanly to the bottom edge -->
      <div
        class="hero-enter pointer-events-none hidden md:flex absolute inset-x-0 bottom-0 z-20 items-end justify-center"
        :class="{ 'is-entered': heroEntered }"
        style="--enter-delay: 180ms"
      >
        <div
          :style="towerParallaxStyle"
          class="relative flex items-end justify-center will-change-transform"
        >
          <img
            src="/images/CellTowerRichmondHill-removebg-preview.png"
            alt="Menara Telekomunikasi SIPJALIN"
            class="h-[74vh] max-h-[600px] min-h-[400px] w-auto object-contain object-bottom drop-shadow-[0_22px_36px_rgba(15,23,42,0.16)]"
          />

          <!-- Hotspot 1 (Microwave Dish) -->
          <div
            class="pointer-events-auto absolute top-[48%] left-[52%] -translate-x-1/2 -translate-y-1/2"
          >
            <button
              type="button"
              @mouseenter="activeHotspot = 1"
              @click="activeHotspot = activeHotspot === 1 ? 0 : 1"
              class="group relative flex h-5 w-5 items-center justify-center rounded-full bg-white shadow-md ring-2 ring-slate-900/20 transition-transform duration-200 hover:scale-125 focus:outline-none"
              aria-label="Info Titik Survei"
            >
              <span class="absolute inset-0 animate-ping rounded-full bg-emerald-400/60"></span>
              <span class="relative h-2 w-2 rounded-full bg-emerald-600"></span>
            </button>

            <transition name="fade-slide">
              <div
                v-if="activeHotspot === 1"
                class="absolute left-7 top-1/2 z-30 -translate-y-1/2 whitespace-nowrap rounded-full bg-slate-900/90 px-3.5 py-1.5 text-[11px] font-medium text-white shadow-lg backdrop-blur-md"
              >
                <span class="font-bold text-emerald-400">
                  {{ formatNumber(stats?.totalRecords ?? 0) }}
                </span>
                Titik Survei · Terverifikasi
              </div>
            </transition>
          </div>

          <!-- Hotspot 2 (Upper Antenna Array) -->
          <div
            class="pointer-events-auto absolute top-[24%] left-[58%] -translate-x-1/2 -translate-y-1/2"
          >
            <button
              type="button"
              @mouseenter="activeHotspot = 2"
              @click="activeHotspot = activeHotspot === 2 ? 0 : 2"
              class="group relative flex h-5 w-5 items-center justify-center rounded-full bg-white shadow-md ring-2 ring-slate-900/20 transition-transform duration-200 hover:scale-125 focus:outline-none"
              aria-label="Info Cakupan Provider"
            >
              <span
                class="relative h-2 w-2 rounded-full bg-slate-900 group-hover:bg-emerald-600 transition-colors"
              ></span>
            </button>

            <transition name="fade-slide">
              <div
                v-if="activeHotspot === 2"
                class="absolute left-7 top-1/2 z-30 -translate-y-1/2 whitespace-nowrap rounded-full bg-slate-900/90 px-3.5 py-1.5 text-[11px] font-medium text-white shadow-lg backdrop-blur-md"
              >
                <span class="font-bold text-emerald-400">
                  {{ formatNumber(stats?.uniqueProviders ?? 0) }}
                </span>
                Provider ·
                {{ formatNumber(stats?.coverageDistricts ?? 0) }} Kecamatan
              </div>
            </transition>
          </div>
        </div>
      </div>

      <div class="mx-auto flex w-full max-w-7xl flex-1 flex-col justify-between px-6 lg:px-8">
        <!-- Top Eyebrow Badge -->
        <div
          class="hero-enter relative z-30 flex justify-center"
          :class="{ 'is-entered': heroEntered }"
          style="--enter-delay: 40ms"
        >
          <div
            class="inline-flex items-center gap-2 rounded-full border border-slate-200/90 bg-white/90 px-3.5 py-1 text-[10px] sm:text-[11px] font-bold uppercase tracking-[0.2em] text-slate-700 shadow-sm backdrop-blur-md"
          >
            <span class="relative flex h-2 w-2">
              <span
                class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"
              ></span>
              <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
            </span>
            DISPERUMKIMTAN · Kota Surakarta
          </div>
        </div>

        <!-- Centerpiece Typography Layer (Behind Tower on Desktop, Layered on Mobile) -->
        <div
          class="relative my-4 md:my-auto flex flex-1 flex-col items-center justify-center md:pb-14"
        >
          <div
            class="hero-enter relative z-10 flex w-full flex-col items-center justify-center text-center select-none"
            :class="{ 'is-entered': heroEntered }"
            style="--enter-delay: 100ms"
          >
            <div :style="textParallaxStyle" class="relative will-change-transform w-full">
              <!-- Blurred glow orbs on the left & right sides of the title -->
              <div
                class="pointer-events-none absolute left-[2%] top-1/2 -z-10 h-40 w-40 sm:h-56 sm:w-56 lg:h-72 lg:w-72 -translate-y-[60%] rounded-full bg-gradient-to-br from-emerald-400/60 to-teal-300/40 blur-3xl"
                aria-hidden="true"
              ></div>
              <div
                class="pointer-events-none absolute right-[2%] top-1/2 -z-10 h-40 w-40 sm:h-56 sm:w-56 lg:h-72 lg:w-72 -translate-y-[60%] rounded-full bg-gradient-to-bl from-sky-400/60 to-violet-300/40 blur-3xl"
                aria-hidden="true"
              ></div>

              <!-- Blurred gradient ghost of the title (soft halo behind the letters) -->
              <span
                class="pointer-events-none absolute inset-0 -z-10 flex items-start justify-center font-sans font-black uppercase tracking-[-0.02em] leading-[0.88] text-[clamp(3.75rem,15.5vw,13.5rem)] bg-gradient-to-r from-emerald-400 via-teal-300 to-sky-400 bg-clip-text text-transparent opacity-70 blur-2xl scale-[1.04]"
                aria-hidden="true"
              >
                SIPJALIN
              </span>

              <h1
                class="relative font-sans font-black uppercase tracking-[-0.02em] leading-[0.88] text-slate-900 text-[clamp(3.75rem,15.5vw,13.5rem)]"
              >
                SIPJALIN
              </h1>
              <p
                class="relative mt-3 sm:mt-4 font-script font-normal tracking-normal leading-snug text-slate-700 text-2xl sm:text-3xl md:text-[2.6rem] px-2"
              >
                Sistem Informasi Provider Jalan Lingkungan
              </p>
            </div>
          </div>

          <!-- Mobile-only Centered Tower -->
          <div
            class="hero-enter relative z-20 -mt-6 flex md:hidden items-end justify-center"
            :class="{ 'is-entered': heroEntered }"
            style="--enter-delay: 180ms"
          >
            <img
              src="/images/CellTowerRichmondHill-removebg-preview.png"
              alt="Menara Telekomunikasi SIPJALIN"
              class="h-[290px] sm:h-[340px] w-auto object-contain object-bottom drop-shadow-[0_18px_28px_rgba(15,23,42,0.16)]"
            />
          </div>
        </div>

        <!-- Bottom Row: Left Copy + CTA | Right Mini Preview Card -->
        <div
          class="relative z-30 flex flex-col items-start justify-between gap-5 md:flex-row md:items-end"
        >
          <!-- Bottom Left: Description & Twin-Pill CTA -->
          <div
            class="hero-enter max-w-[330px] lg:max-w-sm space-y-3.5"
            :class="{ 'is-entered': heroEntered }"
            style="--enter-delay: 260ms"
          >
            <p class="text-xs sm:text-sm leading-relaxed text-slate-600">
              Portal pemetaan infrastruktur dan jaringan provider jalan lingkungan oleh Dinas Perumahan, Kawasan Permukiman, dan Pertanahan Kota Surakarta.
            </p>

            <div class="flex items-center gap-2">
              <a
                href="#data"
                class="group inline-flex items-center gap-1.5"
              >
                <span
                  class="inline-flex h-10 sm:h-11 items-center rounded-full bg-slate-900 px-5 sm:px-6 text-xs sm:text-sm font-semibold text-white shadow-lg shadow-slate-900/15 transition-all duration-300 group-hover:bg-emerald-600"
                >
                  Jelajahi Data
                </span>
                <span
                  class="inline-flex h-10 w-10 sm:h-11 sm:w-11 items-center justify-center rounded-full bg-slate-900 text-white shadow-lg shadow-slate-900/15 transition-all duration-300 group-hover:rotate-45 group-hover:bg-emerald-600"
                >
                  <ArrowUpRight class="h-4 w-4" />
                </span>
              </a>

              <a
                href="#statistik"
                class="inline-flex h-10 sm:h-11 items-center rounded-full border border-slate-200 bg-white/95 px-4 sm:px-5 text-xs sm:text-sm font-semibold text-slate-700 shadow-sm transition-all duration-300 hover:border-slate-300 hover:bg-slate-50"
              >
                Statistik
              </a>
            </div>
          </div>

          <!-- Bottom Right: Compact Interactive Map Preview Card -->
          <div
            class="hero-enter w-full sm:w-auto"
            :class="{ 'is-entered': heroEntered }"
            style="--enter-delay: 320ms"
          >
            <Link
              :href="route('landing.map')"
              class="group block w-full sm:w-68 lg:w-72 rounded-2xl border border-slate-200/90 bg-white/95 p-3 shadow-xl shadow-slate-900/[0.06] backdrop-blur-md transition-all duration-300 hover:-translate-y-1 hover:border-emerald-300 hover:shadow-emerald-500/10"
            >
              <div class="flex items-center justify-between gap-2 mb-2">
                <div class="flex items-center gap-2">
                  <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                  <span class="text-[10px] font-bold uppercase tracking-wider text-slate-700">
                    Peta Sebaran
                  </span>
                </div>
                <ArrowUpRight
                  class="h-3.5 w-3.5 text-slate-400 transition-transform duration-300 group-hover:translate-x-0.5 group-hover:-translate-y-0.5 group-hover:text-emerald-600"
                />
              </div>

              <!-- Stylized Mini Preview Canvas -->
              <div
                class="relative flex h-14 sm:h-16 items-center justify-between overflow-hidden rounded-xl border border-slate-100 bg-slate-50 px-3.5"
              >
                <div class="hero-grid-pattern absolute inset-0 opacity-60"></div>
                <div class="relative z-10">
                  <p class="text-base sm:text-lg font-extrabold text-slate-900 tabular-nums leading-none">
                    {{ formatNumber(stats?.totalRecords ?? 0) }}
                  </p>
                  <p class="mt-1 text-[11px] font-medium text-slate-500">
                    Titik · {{ formatNumber(stats?.coverageVillages ?? 0) }} Kelurahan
                  </p>
                </div>
                <div
                  class="relative z-10 flex h-9 w-9 items-center justify-center rounded-xl bg-white text-emerald-600 shadow-sm ring-1 ring-slate-200/70 transition-transform duration-300 group-hover:scale-110"
                >
                  <MapIcon class="h-4 w-4" />
                </div>
              </div>

              <div class="mt-2 flex items-center justify-between text-xs font-semibold text-emerald-600">
                <span>Buka Peta Interaktif</span>
                <ArrowRight class="h-3.5 w-3.5 transition-transform duration-300 group-hover:translate-x-1" />
              </div>
            </Link>
          </div>
        </div>
      </div>
    </section>

    <!-- ===================================================== -->
    <!-- STATISTIK & GRAFIK WILAYAH                            -->
    <!-- ===================================================== -->
    <section
      id="statistik"
      ref="statsSectionRef"
      class="relative border-y border-slate-200/70 bg-white/70 py-20 backdrop-blur-sm dark:border-slate-800/70 dark:bg-slate-950/50"
    >
      <div class="mx-auto max-w-7xl px-6 lg:px-8">
        <!-- Section Header -->
        <div
          v-reveal="{ direction: 'up' }"
          class="mb-12 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"
        >
          <div class="space-y-2">
            <span
              class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-widest text-emerald-600 dark:text-emerald-400"
            >
              Statistik Cepat
            </span>
            <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-3xl">
              Ringkasan Data Utilitas Surakarta
            </h2>
            <p class="max-w-xl text-sm text-slate-600 dark:text-slate-400">
              Data lapangan tersinkronisasi langsung dengan sistem pemantauan DISPERUMKIMTAN.
            </p>
          </div>

          <Badge
            variant="secondary"
            class="w-fit rounded-full border border-slate-200/80 bg-slate-100/80 px-3.5 py-1.5 text-xs font-medium text-slate-700 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300"
          >
            <Clock4 class="mr-1.5 h-3.5 w-3.5 text-emerald-500" />
            Survei terakhir: {{ formatDate(stats?.latestSurvey) }}
          </Badge>
        </div>

        <!-- 4 Metric Cards with Stagger Reveal + Count Up -->
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
          <div
            v-for="(item, idx) in heroStats"
            :key="item.key"
            v-reveal="{ direction: 'up', delay: idx * 90 }"
            class="group relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-emerald-300/80 hover:shadow-xl hover:shadow-emerald-500/[0.06] dark:border-slate-800 dark:bg-slate-900/80 dark:hover:border-emerald-700/70"
          >
            <div class="flex items-start justify-between">
              <div
                class="flex h-11 w-11 items-center justify-center rounded-xl ring-1 transition-transform duration-300 group-hover:scale-110"
                :class="item.accent"
              >
                <component :is="item.icon" class="h-5 w-5" />
              </div>
              <span
                class="text-[11px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500"
              >
                {{ item.label }}
              </span>
            </div>

            <div class="mt-5">
              <p class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white tabular-nums">
                {{ formatNumber(item.value) }}
              </p>
              <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                {{ item.hint }}
              </p>
            </div>
          </div>
        </div>

        <!-- Interactive 3D Distribution Chart -->
        <div v-reveal="{ direction: 'up', delay: 160 }" class="mt-8">
          <Distribution3DChart
            :kecamatan-data="kecamatanDistribution"
            :kelurahan-data="kelurahanDistribution"
            :total-records="Number(stats?.totalRecords ?? 0)"
            @select-area="handleSelectArea"
          />
        </div>
      </div>
    </section>

    <!-- ===================================================== -->
    <!-- EKSPLORASI DATA PROVIDER                              -->
    <!-- ===================================================== -->
    <section id="data" class="relative py-20">
      <div class="mx-auto max-w-7xl px-6 lg:px-8">
        <!-- Section Header -->
        <div
          v-reveal="{ direction: 'up' }"
          class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"
        >
          <div class="space-y-2">
            <span
              class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-widest text-emerald-600 dark:text-emerald-400"
            >
              Eksplorasi Data
            </span>
            <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-3xl">
              Direktori Provider per Wilayah
            </h2>
            <p class="max-w-xl text-sm text-slate-600 dark:text-slate-400">
              Cari operator jaringan, filter berdasarkan wilayah administrasi, atau cek langsung titik lokasi di Google Maps.
            </p>
          </div>

          <Link
            :href="route('landing.map')"
            class="group inline-flex w-fit items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50/70 px-4 py-2 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-100 dark:border-emerald-800/70 dark:bg-emerald-950/60 dark:text-emerald-300 dark:hover:bg-emerald-900/60"
          >
            <MapIcon class="h-3.5 w-3.5" />
            Lihat Semua di Peta
            <ArrowRight class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5" />
          </Link>
        </div>

        <!-- Filter & Search Bar -->
        <div
          v-reveal="{ direction: 'up', delay: 80 }"
          class="mb-8 rounded-2xl border border-slate-200/80 bg-white/90 p-4 shadow-sm backdrop-blur-md dark:border-slate-800 dark:bg-slate-900/80 sm:p-5"
        >
          <div class="grid gap-3 md:grid-cols-12 md:items-end">
            <!-- Search Input -->
            <div class="md:col-span-5">
              <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5">
                Cari Provider atau Nama Jalan
              </label>
              <div class="relative">
                <Search
                  class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                />
                <input
                  v-model="searchQuery"
                  type="text"
                  placeholder="Contoh: Telkom, Biznet, Slamet Riyadi..."
                  class="w-full rounded-xl border border-slate-200 bg-slate-50/70 py-2.5 pl-10 pr-4 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 dark:border-slate-700 dark:bg-slate-950/60 dark:text-slate-100 dark:focus:border-emerald-500"
                />
              </div>
            </div>

            <!-- Kecamatan -->
            <div class="md:col-span-3">
              <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5">
                Kecamatan
              </label>
              <select
                v-model="selectedKecamatan"
                class="w-full rounded-xl border border-slate-200 bg-slate-50/70 px-3.5 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 dark:border-slate-700 dark:bg-slate-950/60 dark:text-slate-100"
              >
                <option value="">Semua kecamatan</option>
                <option
                  v-for="kecamatan in kecamatanOptions"
                  :key="kecamatan.value"
                  :value="kecamatan.value"
                >
                  {{ kecamatan.label }}
                </option>
              </select>
            </div>

            <!-- Kelurahan -->
            <div class="md:col-span-3">
              <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5">
                Kelurahan
              </label>
              <select
                v-model="selectedKelurahan"
                :disabled="!selectedKecamatan"
                class="w-full rounded-xl border border-slate-200 bg-slate-50/70 px-3.5 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:bg-slate-950/60 dark:text-slate-100"
              >
                <option value="">Semua kelurahan</option>
                <option
                  v-for="kelurahan in kelurahanOptions"
                  :key="kelurahan.value"
                  :value="kelurahan.value"
                >
                  {{ kelurahan.label }}
                </option>
              </select>
            </div>

            <!-- Reset Action -->
            <div class="md:col-span-1 flex justify-end">
              <Button
                v-if="hasActiveFilter"
                type="button"
                variant="outline"
                size="icon"
                @click="resetFilters"
                title="Reset Filter"
                class="h-10 w-10 rounded-xl border-slate-200 text-slate-600 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300"
              >
                <RotateCcw class="h-4 w-4" />
              </Button>
              <div
                v-else
                class="hidden md:flex h-10 items-center justify-center rounded-xl bg-slate-100/80 px-3 text-xs font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-300"
              >
                {{ filteredProviderGroups.length }}
              </div>
            </div>
          </div>
        </div>

        <!-- Provider Grid -->
        <div v-if="filteredProviderGroups.length">
          <TransitionGroup
            name="card-grid"
            tag="div"
            class="grid gap-4 md:grid-cols-2 lg:grid-cols-3"
          >
            <div
              v-for="point in mapPaginatedProviders"
              :key="point.key"
              class="group relative flex flex-col justify-between rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-emerald-300 hover:shadow-lg hover:shadow-emerald-500/[0.06] dark:border-slate-800 dark:bg-slate-900/80 dark:hover:border-emerald-700/70"
            >
              <div>
                <div class="flex items-start justify-between gap-3">
                  <div class="flex items-center gap-3">
                    <div
                      class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 ring-1 ring-emerald-500/20 transition-transform duration-300 group-hover:scale-110 dark:text-emerald-400"
                    >
                      <Building2 class="h-5 w-5" />
                    </div>
                    <div>
                      <p class="text-[11px] font-semibold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">
                        Provider
                      </p>
                      <h3 class="text-base font-bold text-slate-900 dark:text-white line-clamp-1">
                        {{ point.label || 'Provider' }}
                      </h3>
                    </div>
                  </div>
                  <span
                    class="shrink-0 rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300"
                  >
                    {{ formatNumber(point.total) }} titik
                  </span>
                </div>

                <div class="mt-4 space-y-1.5 border-t border-slate-100 pt-3.5 text-xs text-slate-600 dark:border-slate-800/80 dark:text-slate-400">
                  <p class="flex items-center gap-1.5">
                    <MapPin class="h-3.5 w-3.5 shrink-0 text-slate-400" />
                    <span v-if="point.sample?.sijali" class="line-clamp-1">
                      {{ point.sample.sijali }} ({{ formatNumber(getStreetProviderCount(point.sample.sijali)) }} provider)
                    </span>
                    <span v-else class="italic text-slate-400">Nama jalan belum tersedia</span>
                  </p>
                  <p v-if="point.sample?.kecamatan || point.sample?.kelurahan" class="pl-5 text-[11px] text-slate-400 line-clamp-1">
                    {{ [point.sample?.kelurahan, point.sample?.kecamatan].filter(Boolean).join(', ') }}
                  </p>
                </div>
              </div>

              <div v-if="point.sample?.maps_url" class="mt-4 flex justify-end">
                <a
                  :href="point.sample.maps_url"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-600 transition hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300"
                >
                  Buka Google Maps
                  <ExternalLink class="h-3 w-3" />
                </a>
              </div>
            </div>
          </TransitionGroup>

          <!-- Pagination -->
          <div
            class="mt-8 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between text-xs text-slate-500 dark:text-slate-400"
          >
            <div>
              Menampilkan
              <strong class="text-slate-900 dark:text-slate-100">
                {{ filteredProviderGroups.length ? (mapPage - 1) * mapPageSize + 1 : 0 }}
              </strong>
              –
              <strong class="text-slate-900 dark:text-slate-100">
                {{ Math.min(mapPage * mapPageSize, filteredProviderGroups.length) }}
              </strong>
              dari
              <strong class="text-slate-900 dark:text-slate-100">
                {{ filteredProviderGroups.length }}
              </strong>
              provider (total {{ allProviderNames.length }})
            </div>
            <div class="flex items-center gap-2">
              <Button
                variant="outline"
                size="sm"
                class="rounded-full px-4"
                :disabled="mapPage <= 1"
                @click="mapPage = Math.max(1, mapPage - 1)"
              >
                Sebelumnya
              </Button>
              <span class="px-2 font-medium text-slate-600 dark:text-slate-300">
                {{ mapPage }} / {{ mapTotalPages }}
              </span>
              <Button
                variant="outline"
                size="sm"
                class="rounded-full px-4"
                :disabled="mapPage >= mapTotalPages"
                @click="mapPage = Math.min(mapTotalPages, mapPage + 1)"
              >
                Selanjutnya
              </Button>
            </div>
          </div>
        </div>

        <div
          v-else
          class="rounded-3xl border border-dashed border-slate-300 bg-white/70 p-12 text-center dark:border-slate-700 dark:bg-slate-900/50"
        >
          <Search class="mx-auto h-8 w-8 text-slate-400 mb-3" />
          <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">
            Data tidak ditemukan
          </p>
          <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
            Coba ubah kata kunci pencarian atau pilih kecamatan/kelurahan lain.
          </p>
          <Button
            v-if="hasActiveFilter"
            variant="outline"
            size="sm"
            class="mt-4 rounded-full"
            @click="resetFilters"
          >
            Reset Filter
          </Button>
        </div>
      </div>
    </section>

    <!-- ===================================================== -->
    <!-- PERMOHONAN IZIN PROVIDER                              -->
    <!-- ===================================================== -->
    <section
      id="permohonan"
      class="border-t border-slate-200/70 bg-gradient-to-b from-white/70 via-white/70 to-transparent pt-20 pb-10 backdrop-blur-sm dark:border-slate-800/70 dark:from-slate-950/50 dark:via-slate-950/50 dark:to-transparent"
    >
      <div class="mx-auto max-w-7xl px-6 lg:px-8">
        <div
          v-reveal="{ direction: 'up' }"
          class="mb-10 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between"
        >
          <div class="space-y-2">
            <span
              class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-widest text-emerald-600 dark:text-emerald-400"
            >
              Monitoring Legalitas
            </span>
            <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-3xl">
              Status Permohonan Izin
            </h2>
            <p class="max-w-xl text-sm text-slate-600 dark:text-slate-400">
              Pemantauan status perizinan jaringan utilitas provider berdasarkan wilayah kelurahan.
            </p>
          </div>

          <!-- Filter Tabs -->
          <div
            class="inline-flex w-fit items-center rounded-full border border-slate-200/80 bg-slate-100/80 p-1 dark:border-slate-800 dark:bg-slate-900"
          >
            <button
              type="button"
              @click="activePermitTab = 'all'"
              class="rounded-full px-3.5 py-1.5 text-xs font-semibold transition"
              :class="
                activePermitTab === 'all'
                  ? 'bg-white text-slate-900 shadow-sm dark:bg-emerald-500 dark:text-slate-950'
                  : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white'
              "
            >
              Semua ({{ permitRequests.length }})
            </button>
            <button
              type="button"
              @click="activePermitTab = 'legal'"
              class="rounded-full px-3.5 py-1.5 text-xs font-semibold transition"
              :class="
                activePermitTab === 'legal'
                  ? 'bg-white text-slate-900 shadow-sm dark:bg-emerald-500 dark:text-slate-950'
                  : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white'
              "
            >
              Legal
            </button>
            <button
              type="button"
              @click="activePermitTab = 'ilegal'"
              class="rounded-full px-3.5 py-1.5 text-xs font-semibold transition"
              :class="
                activePermitTab === 'ilegal'
                  ? 'bg-white text-slate-900 shadow-sm dark:bg-emerald-500 dark:text-slate-950'
                  : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white'
              "
            >
              Peninjauan
            </button>
          </div>
        </div>

        <TransitionGroup
          name="card-grid"
          tag="div"
          class="grid gap-4 md:grid-cols-2 lg:grid-cols-3"
        >
          <div
            v-for="request in filteredPermitRequests"
            :key="request.id"
            class="group rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md dark:border-slate-800 dark:bg-slate-900/80"
          >
            <div class="flex items-start justify-between gap-3">
              <div>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                  Provider
                </p>
                <p class="mt-0.5 text-base font-bold text-slate-900 dark:text-white">
                  {{ request.provider }}
                </p>
              </div>
              <span
                class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[11px] font-semibold"
                :class="
                  request.status === 'legal'
                    ? 'bg-emerald-500/10 text-emerald-700 ring-1 ring-emerald-500/20 dark:text-emerald-300'
                    : 'bg-amber-500/10 text-amber-700 ring-1 ring-amber-500/20 dark:text-amber-300'
                "
              >
                <CheckCircle2 v-if="request.status === 'legal'" class="h-3 w-3" />
                <ShieldAlert v-else class="h-3 w-3" />
                {{ request.status === 'legal' ? 'Legal' : 'Dalam Peninjauan' }}
              </span>
            </div>

            <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-3 text-xs text-slate-500 dark:border-slate-800 dark:text-slate-400">
              <span>{{ request.kelurahan }}, {{ request.kecamatan }}</span>
              <span>{{ formatDate(request.submitted_at) }}</span>
            </div>
          </div>
        </TransitionGroup>

        <!-- Minimal CTA Banner -->
        <div
          v-reveal="{ direction: 'up', delay: 120 }"
          class="mt-16 relative overflow-hidden rounded-3xl bg-slate-900 px-6 py-10 text-white shadow-xl dark:border dark:border-slate-800 dark:bg-slate-900/90 sm:px-12 sm:py-12"
        >
          <div
            class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-emerald-500/20 blur-3xl"
          ></div>
          <div
            class="pointer-events-none absolute -bottom-16 left-1/3 h-56 w-56 rounded-full bg-sky-500/20 blur-3xl"
          ></div>

          <div class="relative flex flex-col items-start justify-between gap-6 md:flex-row md:items-center">
            <div class="space-y-2 max-w-xl">
              <h3 class="text-2xl font-bold tracking-tight sm:text-3xl">
                Eksplorasi Sebaran Titik Secara Visual
              </h3>
              <p class="text-sm text-slate-300 leading-relaxed">
                Buka peta interaktif untuk melihat koordinat GPS, jalur ruas jalan, foto lapangan, dan detail tiap ODP di Surakarta.
              </p>
            </div>
            <Link
              :href="route('landing.map')"
              class="group inline-flex shrink-0 items-center gap-2 rounded-full bg-emerald-500 px-6 py-3 text-sm font-semibold text-slate-950 shadow-lg shadow-emerald-500/20 transition-all duration-300 hover:-translate-y-0.5 hover:bg-emerald-400"
            >
              <MapIcon class="h-4 w-4" />
              Buka Peta Sekarang
              <ArrowRight class="h-4 w-4 transition-transform group-hover:translate-x-1" />
            </Link>
          </div>
        </div>
      </div>
    </section>
  </LandingLayout>
</template>

<style scoped>
/* Subtle grid pattern for Hero */
.hero-grid-pattern {
  background-image: linear-gradient(to right, rgba(148, 163, 184, 0.14) 1px, transparent 1px),
    linear-gradient(to bottom, rgba(148, 163, 184, 0.14) 1px, transparent 1px);
  background-size: 40px 40px;
  mask-image: radial-gradient(ellipse 75% 70% at 50% 35%, #000 40%, transparent 100%);
}

/* Entrance Animation on Page Load */
.hero-enter {
  opacity: 0;
  transform: translateY(22px);
  filter: blur(4px);
  transition:
    opacity 0.75s cubic-bezier(0.16, 1, 0.3, 1),
    transform 0.75s cubic-bezier(0.16, 1, 0.3, 1),
    filter 0.75s cubic-bezier(0.16, 1, 0.3, 1);
  transition-delay: var(--enter-delay, 0ms);
}

.hero-enter.is-entered {
  opacity: 1;
  transform: translateY(0);
  filter: blur(0);
}

/* Scroll Reveal Animations */
.reveal-base {
  opacity: 0;
  will-change: opacity, transform;
  transition:
    opacity 0.7s cubic-bezier(0.16, 1, 0.3, 1),
    transform 0.7s cubic-bezier(0.16, 1, 0.3, 1);
}

.reveal-up {
  transform: translateY(28px);
}

.reveal-left {
  transform: translateX(-28px);
}

.reveal-right {
  transform: translateX(28px);
}

.reveal-scale {
  transform: scale(0.95);
}

.reveal-base.is-revealed {
  opacity: 1;
  transform: translate3d(0, 0, 0) scale(1);
}

/* Ambient Float & Blob Animations */
@keyframes blobFloat {
  0%,
  100% {
    transform: translate(0, 0) scale(1);
  }
  50% {
    transform: translate(18px, -22px) scale(1.06);
  }
}

.animate-blob {
  animation: blobFloat 10s ease-in-out infinite;
}

.animation-delay-2000 {
  animation-delay: 2.5s;
}

@keyframes floatSlow {
  0%,
  100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-6px);
  }
}

.animate-float-slow {
  animation: floatSlow 5s ease-in-out infinite;
}

.animate-float-delayed {
  animation: floatSlow 6s ease-in-out 1.2s infinite;
}

/* Tab & Card Transitions */
.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: opacity 0.22s ease, transform 0.22s ease;
}

.fade-slide-enter-from {
  opacity: 0;
  transform: translateY(6px);
}

.fade-slide-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}

.card-grid-enter-active,
.card-grid-leave-active {
  transition: all 0.28s cubic-bezier(0.16, 1, 0.3, 1);
}

.card-grid-enter-from,
.card-grid-leave-to {
  opacity: 0;
  transform: translateY(10px) scale(0.98);
}

@media (prefers-reduced-motion: reduce) {
  .hero-enter,
  .reveal-base {
    opacity: 1 !important;
    transform: none !important;
    filter: none !important;
    transition: none !important;
  }
  .animate-blob,
  .animate-float-slow,
  .animate-float-delayed {
    animation: none !important;
  }
}
</style>
