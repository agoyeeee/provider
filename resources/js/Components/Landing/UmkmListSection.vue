<template>
    <section id="umkm" class="py-20 bg-white dark:bg-gray-900">
        <div class="container mx-auto px-6">
            <!-- Section Header -->
            <div class="text-center mb-12">
                <h2 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    UMKM Karanganyar
                </h2>
                <p class="text-xl text-gray-600 dark:text-gray-300 max-w-3xl mx-auto">
                    Jelajahi UMKM berkualitas dari berbagai kategori di Karanganyar
                </p>
            </div>

            <!-- Filter Section -->
            <div class="bg-gray-50 dark:bg-gray-800 rounded-2xl p-6 mb-12 space-y-6">
                <!-- Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Cari UMKM
                    </label>
                    <input
                        v-model="searchTerm"
                        type="text"
                        placeholder="Cari nama atau deskripsi..."
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500 focus:border-transparent"
                    />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Category Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Filter Kategori
                        </label>
                        <select
                            v-model="selectedKategori"
                            @change="applyFilters"
                            class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500 focus:border-transparent"
                        >
                            <option value="">Semua Kategori</option>
                            <option v-for="kategori in kategoris" :key="kategori.id" :value="kategori.id">
                                {{ kategori.nama }} ({{ kategori.umkm_count || 0 }} UMKM)
                            </option>
                        </select>
                    </div>

                    <!-- Sub Category Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Filter Sub Kategori
                        </label>
                        <select
                            v-model="selectedSubKategori"
                            @change="applyFilters"
                            :disabled="!selectedKategori"
                            class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500 focus:border-transparent disabled:opacity-50"
                        >
                            <option value="">Semua Sub Kategori</option>
                            <option v-for="subKategori in filteredSubKategoris" :key="subKategori.id" :value="subKategori.id">
                                {{ subKategori.nama }}
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Active Filters -->
                <div v-if="activeFilters.length > 0" class="mt-6">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Filter aktif:</span>
                        <button
                            v-for="filter in activeFilters"
                            :key="filter.type"
                            @click="removeFilter(filter.type)"
                            class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 hover:bg-green-200 dark:hover:bg-green-800 transition-colors"
                        >
                            {{ filter.label }}
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                        <button
                            @click="clearAllFilters"
                            class="text-sm text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-200 underline"
                        >
                            Hapus semua filter
                        </button>
                    </div>
                </div>
            </div>

            <!-- UMKM Grid -->
            <div v-if="umkmList.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div
                    v-for="umkm in umkmList"
                    :key="umkm.id"
                    class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden flex flex-col h-full"
                >
                    <!-- UMKM Image/Logo -->
                    <div class="h-48 bg-gradient-to-br from-green-400 to-green-600 relative">
                        <img
                            v-if="umkm.logo"
                            :src="getImageUrl(umkm.logo)"
                            :alt="umkm.nama"
                            class="w-full h-full object-cover"
                        >
                        <div v-else class="flex items-center justify-center h-full">
                            <span class="text-white text-6xl font-bold">{{ umkm.nama.charAt(0) }}</span>
                        </div>

                        <!-- Category Badge -->
                        <div class="absolute top-4 left-4">
                            <span class="px-3 py-1 bg-white bg-opacity-90 text-green-600 text-sm font-medium rounded-full">
                                {{ umkm.kategori?.nama }}
                            </span>
                        </div>

                        <!-- Document Verification Badge -->
                        <div class="absolute top-4 right-4" v-if="umkm.dokumen_status">
                            <span :class="getDokumenBadge(umkm.dokumen_status).classes">
                                {{ getDokumenBadge(umkm.dokumen_status).label }}
                            </span>
                        </div>

                        <!-- Badge Verified (hanya muncul jika semua dokumen approved) -->
                        <div
                            v-if="isUmkmVerified(umkm)"
                            class="absolute top-3 right-3 z-10 bg-blue-600 text-white px-3 py-1 rounded-full text-xs font-semibold shadow-lg flex items-center gap-1.5"
                        >
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>Terverifikasi</span>
                        </div>
                    </div>

                    <!-- UMKM Content -->
                    <div class="p-6 flex flex-col flex-1">
                        <div class="mb-4">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">{{ umkm.nama }}</h3>
                            <p class="text-gray-600 dark:text-gray-400 text-sm line-clamp-2">{{ umkm.deskripsi }}</p>
                        </div>

                        <!-- Sub Category -->
                        <div v-if="umkm.sub_kategori" class="mb-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200">
                                {{ umkm.sub_kategori?.nama }}
                            </span>
                        </div>

                        <!-- Products Count -->
                        <div class="mb-4">
                        <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                                {{ umkm.produk?.length || 0 }} Produk
                            </div>
                        </div>

                        <!-- Sample Products -->
                        <div v-if="umkm.produk && umkm.produk.length > 0" class="mb-4">
                            <div class="text-sm text-gray-700 dark:text-gray-300 mb-2">Produk:</div>
                            <div class="flex flex-wrap gap-1">
                                <span
                                    v-for="produk in umkm.produk.slice(0, 3)"
                                    :key="produk.id"
                                    class="px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs rounded"
                                >
                                    {{ produk.nama_produk }}
                                </span>
                                <span
                                    v-if="umkm.produk.length > 3"
                                    class="px-2 py-1 bg-gray-200 dark:bg-gray-600 text-gray-600 dark:text-gray-400 text-xs rounded"
                                >
                                    +{{ umkm.produk.length - 3 }} lainnya
                                </span>
                            </div>
                        </div>

                        <!-- Action Button -->
                        <div class="flex justify-between items-center mt-auto">
                            <button
                                @click="viewUmkmDetail(umkm.id)"
                                class="flex-1 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200 mr-2"
                            >
                                Lihat Detail
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-16">
                <div class="mb-6">
                    <svg class="w-24 h-24 mx-auto text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                    Tidak ada UMKM ditemukan
                </h3>
                <p class="text-gray-600 dark:text-gray-400 mb-6">
                    Coba ubah filter atau hapus filter untuk melihat semua UMKM.
                </p>
                <button
                    @click="clearAllFilters"
                    class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors duration-200"
                >
                    Lihat Semua UMKM
                </button>
            </div>

            <!-- Load More Button -->
            <div v-if="umkmList.length >= 12" class="text-center mt-12">
                <button class="px-8 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors duration-200">
                    Lihat Lebih Banyak UMKM
                </button>
            </div>
        </div>
    </section>
</template>

<script setup>
import { ref, computed, watch, onBeforeUnmount } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    kategoris: Array,
    subKategoris: Array,
    umkmList: Array,
    filters: Object
})

// Reactive filter states
const selectedKategori = ref(props.filters?.kategori || '')
const selectedSubKategori = ref(props.filters?.sub_kategori || '')
const searchTerm = ref(props.filters?.search || '')

// Computed filtered subcategories
const filteredSubKategoris = computed(() => {
    if (!selectedKategori.value) return []
    const kategori = props.kategoris.find(k => k.id == selectedKategori.value)
    return kategori?.sub_kategoris || []
})

// Computed active filters for display
const activeFilters = computed(() => {
    const filters = []

    if (selectedKategori.value) {
        const kategori = props.kategoris.find(k => k.id == selectedKategori.value)
        if (kategori) {
            filters.push({
                type: 'kategori',
                label: kategori.nama
            })
        }
    }

    if (selectedSubKategori.value) {
        const subKategori = filteredSubKategoris.value.find(sk => sk.id == selectedSubKategori.value)
        if (subKategori) {
            filters.push({
                type: 'sub_kategori',
                label: subKategori.nama
            })
        }
    }

    if (searchTerm.value) {
        filters.push({
            type: 'search',
            label: `Cari: "${searchTerm.value}"`
        })
    }

    return filters
})

// Watch for kategori changes to reset sub kategori
watch(selectedKategori, (newValue) => {
    if (!newValue) {
        selectedSubKategori.value = ''
    } else {
        // Reset sub kategori if current selection is not valid for new kategori
        const validSubKategoris = filteredSubKategoris.value
        if (selectedSubKategori.value && !validSubKategoris.find(sk => sk.id == selectedSubKategori.value)) {
            selectedSubKategori.value = ''
        }
    }
})

// Apply filters
const applyFilters = () => {
    if (searchTimeout) {
        clearTimeout(searchTimeout)
        searchTimeout = null
    }

    const filters = {}

    if (selectedKategori.value) {
        filters.kategori = selectedKategori.value
    }

    if (selectedSubKategori.value) {
        filters.sub_kategori = selectedSubKategori.value
    }

    if (searchTerm.value) {
        filters.search = searchTerm.value
    }

    router.get(route('umkm.index'), filters, {
        preserveState: true,
        only: ['umkmList', 'subKategoris', 'filters']
    })
}

// Remove specific filter
const removeFilter = (filterType) => {
    if (filterType === 'kategori') {
        selectedKategori.value = ''
        selectedSubKategori.value = ''
    } else if (filterType === 'sub_kategori') {
        selectedSubKategori.value = ''
    } else if (filterType === 'search') {
        skipNextSearch = true
        searchTerm.value = ''
    }

    applyFilters()
}

// Clear all filters
const clearAllFilters = () => {
    selectedKategori.value = ''
    selectedSubKategori.value = ''
    skipNextSearch = true
    searchTerm.value = ''

    router.get(route('umkm.index'), {}, {
        preserveState: true,
        only: ['umkmList', 'subKategoris', 'filters']
    })
}

// Navigate to UMKM detail page
const viewUmkmDetail = (umkmId) => {
    router.visit(route('umkm.show', umkmId))
}

const getImageUrl = (path) => {
    if (!path) return null
    return path.startsWith('http') ? path : `/storage/${path}`
}

const getDokumenBadge = (status) => {
    const config = {
        approved: {
            classes: 'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-500 text-white',
            label: 'Terverifikasi',
        },
        pending: {
            classes: 'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-400 text-yellow-900',
            label: 'Menunggu Verifikasi',
        },
        rejected: {
            classes: 'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-500 text-white',
            label: 'Dokumen Ditolak',
        },
    }

    return config[status] || config.pending
}

// Cek apakah UMKM terverifikasi (semua dokumen approved)
const isUmkmVerified = (umkm) => {
  const docs = umkm?.dokumen_umkm ?? umkm?.dokumenUmkm ?? [];

  // Tidak terverifikasi jika tidak ada dokumen
  if (docs.length === 0) {
    return false;
  }

  // Terverifikasi jika semua dokumen berstatus 'approved'
  return docs.every(doc => {
    const status = doc?.status ?? doc?.status_dokumen ?? 'pending';
    return status === 'approved';
  });
};

let searchTimeout = null
const debounceDelay = 400
let skipNextSearch = false

watch(searchTerm, () => {
    if (skipNextSearch) {
        skipNextSearch = false
        return
    }

    if (searchTimeout) {
        clearTimeout(searchTimeout)
    }
    searchTimeout = setTimeout(() => {
        applyFilters()
    }, debounceDelay)
})

onBeforeUnmount(() => {
    if (searchTimeout) {
        clearTimeout(searchTimeout)
    }
})
</script>
