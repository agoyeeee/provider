<script setup>
import { computed, ref, watch } from 'vue'
import { ArrowDownRight, Box, Layers, MapPin, Sparkles } from 'lucide-vue-next'

const props = defineProps({
  kecamatanData: {
    type: Array,
    default: () => [],
  },
  kelurahanData: {
    type: Array,
    default: () => [],
  },
  totalRecords: {
    type: Number,
    default: 0,
  },
})

const emit = defineEmits(['selectArea'])

const activeTab = ref('kecamatan')
const useBalancedScale = ref(true)
const hoveredIndex = ref(0)
const stageTilt = ref({ x: 0, y: 0 })

const formatNumber = (value) => new Intl.NumberFormat('id-ID').format(value ?? 0)

const activeList = computed(() =>
  activeTab.value === 'kecamatan' ? props.kecamatanData : props.kelurahanData,
)

// Show up to 9 pillars in the 3D stage so columns stay spacious and readable
const chartItems = computed(() => activeList.value.slice(0, 9))

const totalActiveCount = computed(() =>
  activeList.value.reduce((acc, item) => acc + (Number(item.total) || 0), 0),
)

const maxVal = computed(() => {
  if (!chartItems.value.length) return 1
  return Math.max(...chartItems.value.map((i) => Number(i.total) || 0), 1)
})

const getSharePercent = (val) => {
  const denom = totalActiveCount.value || props.totalRecords || 1
  const pct = (Number(val) / denom) * 100
  if (pct > 0 && pct < 0.1) return '< 0,1'
  return pct.toFixed(1).replace('.', ',')
}

const getPillarHeight = (val) => {
  const num = Number(val) || 0
  if (num <= 0) return 12
  const maxHeight = 190
  const minHeight = 24
  if (useBalancedScale.value) {
    const ratio = Math.pow(num / maxVal.value, 0.45)
    return Math.round(minHeight + ratio * (maxHeight - minHeight))
  }
  return Math.max(10, Math.round((num / maxVal.value) * maxHeight))
}

const kecamatanPalettes = [
  {
    front: 'linear-gradient(180deg, #10b981 0%, #059669 100%)',
    top: '#34d399',
    side: '#047857',
    glow: 'rgba(16, 185, 129, 0.35)',
    dot: 'bg-emerald-500',
  },
  {
    front: 'linear-gradient(180deg, #14b8a6 0%, #0d9488 100%)',
    top: '#2dd4bf',
    side: '#0f766e',
    glow: 'rgba(20, 184, 166, 0.32)',
    dot: 'bg-teal-500',
  },
  {
    front: 'linear-gradient(180deg, #0ea5e9 0%, #0284c7 100%)',
    top: '#38bdf8',
    side: '#0369a1',
    glow: 'rgba(14, 165, 233, 0.30)',
    dot: 'bg-sky-500',
  },
  {
    front: 'linear-gradient(180deg, #6366f1 0%, #4f46e5 100%)',
    top: '#818cf8',
    side: '#4338ca',
    glow: 'rgba(99, 102, 241, 0.28)',
    dot: 'bg-indigo-500',
  },
]

const getPalette = (idx) => {
  if (activeTab.value === 'kelurahan') {
    const kelPalettes = [
      kecamatanPalettes[2],
      kecamatanPalettes[0],
      kecamatanPalettes[1],
      kecamatanPalettes[3],
    ]
    return kelPalettes[Math.min(idx, kelPalettes.length - 1)]
  }
  return kecamatanPalettes[Math.min(idx, kecamatanPalettes.length - 1)]
}

const focusedItem = computed(() => {
  if (!activeList.value.length) return null
  const idx = hoveredIndex.value ?? 0
  return activeList.value[idx] || activeList.value[0]
})

watch(activeTab, () => {
  hoveredIndex.value = 0
})

const handleStageMouseMove = (e) => {
  const rect = e.currentTarget.getBoundingClientRect()
  const x = (e.clientX - rect.left) / rect.width - 0.5
  const y = (e.clientY - rect.top) / rect.height - 0.5
  stageTilt.value = {
    x: Number((-y * 5).toFixed(2)),
    y: Number((x * 7).toFixed(2)),
  }
}

const handleStageMouseLeave = () => {
  stageTilt.value = { x: 0, y: 0 }
}

const handleFilterClick = (item) => {
  if (!item) return
  emit('selectArea', {
    type: activeTab.value,
    name: item.name,
  })
}
</script>

<template>
  <div
    class="relative overflow-hidden rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm sm:p-8"
  >
    <!-- Header Row -->
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
      <div>
        <div class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-widest text-emerald-600">
          <Box class="h-3.5 w-3.5" />
          Visualisasi Sebaran 3D
        </div>
        <h3 class="mt-1 text-xl font-bold text-slate-900 sm:text-2xl">
          Distribusi Titik per
          {{ activeTab === 'kecamatan' ? 'Kecamatan' : 'Kelurahan' }}
        </h3>
      </div>

      <!-- Controls: Scale Mode + Tab Switcher -->
      <div class="flex flex-wrap items-center gap-2.5">
        <button
          type="button"
          @click="useBalancedScale = !useBalancedScale"
          class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-[11px] font-semibold transition-all"
          :class="
            useBalancedScale
              ? 'border-emerald-200 bg-emerald-50/70 text-emerald-700 hover:bg-emerald-100/70'
              : 'border-slate-200 bg-slate-50 text-slate-600 hover:bg-slate-100'
          "
          title="Ubah skala tinggi pilar 3D agar data kecil tetap terlihat jelas"
        >
          <Sparkles class="h-3 w-3" />
          {{ useBalancedScale ? 'Skala 3D Seimbang' : 'Skala Linear Asli' }}
        </button>

        <div
          class="inline-flex items-center rounded-full border border-slate-200/80 bg-slate-100/80 p-1"
        >
          <button
            type="button"
            @click="activeTab = 'kecamatan'"
            class="rounded-full px-4 py-1.5 text-xs font-semibold transition-all duration-200"
            :class="
              activeTab === 'kecamatan'
                ? 'bg-slate-900 text-white shadow-sm'
                : 'text-slate-600 hover:text-slate-900'
            "
          >
            Kecamatan ({{ formatNumber(kecamatanData.length) }})
          </button>
          <button
            type="button"
            @click="activeTab = 'kelurahan'"
            class="rounded-full px-4 py-1.5 text-xs font-semibold transition-all duration-200"
            :class="
              activeTab === 'kelurahan'
                ? 'bg-slate-900 text-white shadow-sm'
                : 'text-slate-600 hover:text-slate-900'
            "
          >
            Kelurahan ({{ formatNumber(kelurahanData.length) }})
          </button>
        </div>
      </div>
    </div>

    <!-- Main Content: 3D Stage + Ranked List -->
    <div v-if="activeList.length" class="mt-6 grid gap-6 lg:grid-cols-12 lg:items-stretch">
      <!-- LEFT: 3D Isometric Stage -->
      <div
        class="lg:col-span-7 relative flex flex-col justify-between overflow-hidden rounded-2xl border border-slate-200/70 bg-gradient-to-b from-slate-50/90 via-white to-slate-100/60 p-5 sm:p-6"
        @mousemove="handleStageMouseMove"
        @mouseleave="handleStageMouseLeave"
      >
        <!-- Focused HUD Banner inside 3D Stage -->
        <div
          v-if="focusedItem"
          class="relative z-20 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-slate-200/80 bg-white/90 px-4 py-3 shadow-sm backdrop-blur-md"
        >
          <div class="flex items-center gap-3">
            <span
              class="flex h-9 w-9 items-center justify-center rounded-xl text-xs font-extrabold text-white shadow-sm"
              :style="{ background: getPalette(hoveredIndex).front }"
            >
              #{{ hoveredIndex + 1 }}
            </span>
            <div>
              <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                {{ activeTab === 'kecamatan' ? 'Kecamatan' : 'Kelurahan' }} Terpilih
              </p>
              <p class="text-sm font-bold text-slate-900">
                {{ focusedItem.name }}
                <span class="ml-1.5 font-extrabold text-emerald-600 tabular-nums">
                  {{ formatNumber(focusedItem.total) }} titik
                </span>
                <span class="ml-1 text-xs font-medium text-slate-400">
                  ({{ getSharePercent(focusedItem.total) }}%)
                </span>
              </p>
            </div>
          </div>

          <button
            type="button"
            @click="handleFilterClick(focusedItem)"
            class="inline-flex items-center gap-1 rounded-full bg-slate-900 px-3 py-1.5 text-[11px] font-semibold text-white transition hover:bg-emerald-600"
          >
            Lihat Provider
            <ArrowDownRight class="h-3.5 w-3.5" />
          </button>
        </div>

        <!-- 3D Perspective Floor + Extruded Pillars -->
        <div
          class="relative mt-6 flex flex-1 flex-col justify-end min-h-[285px] transition-transform duration-300 ease-out"
          :style="{
            transform: `perspective(1200px) rotateX(${stageTilt.x}deg) rotateY(${stageTilt.y}deg)`,
          }"
        >
          <!-- Isometric 3D Floor Plane -->
          <div
            class="pointer-events-none absolute inset-x-2 bottom-9 h-14 rounded-2xl border border-slate-200/70 bg-gradient-to-b from-slate-100/80 to-slate-200/40 shadow-inner"
            style="transform: perspective(600px) rotateX(55deg); transform-origin: bottom;"
          >
            <div class="floor-grid h-full w-full opacity-50"></div>
          </div>

          <!-- 3D Pillars Row -->
          <transition name="fade-slide" mode="out-in">
            <div
              :key="activeTab"
              class="relative z-10 grid items-end gap-2 sm:gap-3 px-2 pt-10 pb-2"
              :style="{
                gridTemplateColumns: `repeat(${chartItems.length}, minmax(0, 1fr))`,
              }"
            >
              <div
                v-for="(item, idx) in chartItems"
                :key="item.name"
                @mouseenter="hoveredIndex = idx"
                @click="hoveredIndex = idx"
                class="group relative flex cursor-pointer flex-col items-center select-none"
              >
                <!-- Value Badge above pillar -->
                <div
                  class="mb-2 rounded-full px-2 py-0.5 text-[10px] sm:text-[11px] font-bold tabular-nums transition-all duration-300"
                  :class="
                    hoveredIndex === idx
                      ? 'bg-slate-900 text-white shadow-md -translate-y-1.5 scale-105'
                      : 'bg-white/90 text-slate-700 ring-1 ring-slate-200/80 group-hover:-translate-y-1'
                  "
                >
                  {{ formatNumber(item.total) }}
                </div>

                <!-- 3D Extruded Pillar Box -->
                <div
                  class="pillar-3d relative mx-auto w-7 sm:w-9 md:w-11 transition-all duration-500 ease-out"
                  :class="{ 'is-active': hoveredIndex === idx }"
                  :style="{
                    height: `${getPillarHeight(item.total)}px`,
                    '--pillar-glow': getPalette(idx).glow,
                  }"
                >
                  <!-- Top Cap (3D Isometric Diamond/Parallelogram) -->
                  <div
                    class="pillar-top absolute -top-[9px] left-0 h-[9px] w-full origin-bottom-left"
                    :style="{ backgroundColor: getPalette(idx).top }"
                  ></div>

                  <!-- Front Face -->
                  <div
                    class="pillar-front relative h-full w-full overflow-hidden"
                    :style="{ background: getPalette(idx).front }"
                  >
                    <!-- Specular vertical light highlight -->
                    <div
                      class="absolute inset-y-0 left-0 w-1/3 bg-gradient-to-r from-white/25 to-transparent"
                    ></div>
                  </div>

                  <!-- Right Side Face (3D Depth) -->
                  <div
                    class="pillar-side absolute top-0 -right-[9px] h-full w-[9px] origin-top-left"
                    :style="{ backgroundColor: getPalette(idx).side }"
                  ></div>

                  <!-- Ground Drop Shadow -->
                  <div
                    class="pointer-events-none absolute -bottom-2 left-1/2 h-2.5 w-[130%] -translate-x-1/2 rounded-full blur-sm transition-all duration-300"
                    :style="{
                      background:
                        hoveredIndex === idx
                          ? getPalette(idx).glow
                          : 'rgba(15, 23, 42, 0.14)',
                    }"
                  ></div>
                </div>

                <!-- Axis Label below floor -->
                <div class="mt-3.5 w-full text-center">
                  <p
                    class="truncate text-[10px] sm:text-[11px] font-semibold transition-colors"
                    :class="
                      hoveredIndex === idx
                        ? 'text-slate-900 font-bold'
                        : 'text-slate-500 group-hover:text-slate-800'
                    "
                    :title="item.name"
                  >
                    {{ item.name }}
                  </p>
                </div>
              </div>
            </div>
          </transition>
        </div>

        <!-- Subtle footer note when showing top 9 of 30 -->
        <p
          v-if="activeList.length > 9"
          class="mt-2 text-center text-[11px] text-slate-400"
        >
          Menampilkan 9 {{ activeTab }} teratas di panggung 3D · Lihat daftar lengkap di sebelah kanan
        </p>
      </div>

      <!-- RIGHT: Synchronized Interactive Ranking Breakdown -->
      <div
        class="lg:col-span-5 flex flex-col rounded-2xl border border-slate-200/70 bg-slate-50/50 p-4 sm:p-5"
      >
        <div class="flex items-center justify-between pb-3 border-b border-slate-200/70">
          <div class="flex items-center gap-2">
            <Layers class="h-4 w-4 text-emerald-600" />
            <h4 class="text-sm font-bold text-slate-800">
              Peringkat {{ activeTab === 'kecamatan' ? 'Kecamatan' : 'Kelurahan' }}
            </h4>
          </div>
          <span class="text-xs font-semibold text-slate-500 tabular-nums">
            Total {{ formatNumber(totalActiveCount) }}
          </span>
        </div>

        <div class="mt-3 flex-1 space-y-2 max-h-[320px] overflow-y-auto pr-1 custom-scroll">
          <div
            v-for="(item, idx) in activeList"
            :key="item.name"
            @mouseenter="hoveredIndex = idx"
            @click="handleFilterClick(item)"
            class="group cursor-pointer rounded-xl border px-3.5 py-2.5 transition-all duration-200"
            :class="
              hoveredIndex === idx
                ? 'border-emerald-300 bg-white shadow-sm'
                : 'border-transparent bg-white/70 hover:border-slate-200 hover:bg-white'
            "
          >
            <div class="flex items-center justify-between gap-2 text-xs">
              <div class="flex items-center gap-2.5 min-w-0">
                <span
                  class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg text-[11px] font-bold"
                  :class="
                    idx === 0
                      ? 'bg-emerald-500/15 text-emerald-700'
                      : idx === 1
                        ? 'bg-teal-500/15 text-teal-700'
                        : idx === 2
                          ? 'bg-sky-500/15 text-sky-700'
                          : 'bg-slate-100 text-slate-500'
                  "
                >
                  {{ idx + 1 }}
                </span>
                <span class="truncate font-semibold text-slate-800 group-hover:text-emerald-700">
                  {{ item.name }}
                </span>
              </div>

              <div class="flex items-center gap-2 shrink-0 tabular-nums">
                <span class="font-bold text-slate-900">
                  {{ formatNumber(item.total) }}
                </span>
                <span class="w-11 text-right text-[11px] font-medium text-slate-400">
                  {{ getSharePercent(item.total) }}%
                </span>
              </div>
            </div>

            <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-slate-100">
              <div
                class="h-full rounded-full transition-all duration-500"
                :style="{
                  width: `${Math.max(3, (Number(item.total) / maxVal) * 100)}%`,
                  background: getPalette(idx).front,
                }"
              ></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div
      v-else
      class="mt-6 flex h-56 flex-col items-center justify-center rounded-2xl border border-dashed border-slate-200 bg-slate-50/50 text-center"
    >
      <MapPin class="h-8 w-8 text-slate-300 mb-2" />
      <p class="text-sm font-semibold text-slate-600">
        Belum ada data sebaran untuk kategori ini.
      </p>
    </div>
  </div>
</template>

<style scoped>
/* Isometric 3D top & side faces */
.pillar-top {
  transform: skewX(-45deg);
  filter: brightness(1.06);
}

.pillar-side {
  transform: skewY(-45deg);
  filter: brightness(0.92);
}

.pillar-3d {
  box-shadow: 0 8px 18px -6px var(--pillar-glow, rgba(16, 185, 129, 0.25));
}

.pillar-3d.is-active,
.group:hover .pillar-3d {
  transform: translateY(-8px) scale(1.04);
  filter: brightness(1.06);
  box-shadow: 0 18px 28px -6px var(--pillar-glow, rgba(16, 185, 129, 0.45));
}

.floor-grid {
  background-image: linear-gradient(to right, rgba(148, 163, 184, 0.25) 1px, transparent 1px),
    linear-gradient(to bottom, rgba(148, 163, 184, 0.25) 1px, transparent 1px);
  background-size: 20px 14px;
}

.custom-scroll::-webkit-scrollbar {
  width: 5px;
}
.custom-scroll::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scroll::-webkit-scrollbar-thumb {
  background: rgba(148, 163, 184, 0.4);
  border-radius: 9999px;
}

.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: opacity 0.22s ease, transform 0.22s ease;
}

.fade-slide-enter-from {
  opacity: 0;
  transform: translateY(8px);
}

.fade-slide-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}
</style>
