<template>
    <section id="produk" class="py-20 bg-white dark:bg-gray-900">
        <div class="container mx-auto px-6">
            <!-- Section Header -->
            <div class="text-center mb-12">
                <h2 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Produk UMKM Karanganyar
                </h2>
                <p class="text-xl text-gray-600 dark:text-gray-300 max-w-3xl mx-auto">
                    Jelajahi produk berkualitas dari UMKM terbaik Karanganyar
                </p>
            </div>

            <!-- Category Filter -->
            <div class="bg-gray-50 dark:bg-gray-800 rounded-2xl p-6 mb-12">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Category Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Kategori
                        </label>
                        <select
                            v-model="selectedKategori"
                            @change="onKategoriChange"
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                        >
                            <option value="">Semua Kategori</option>
                            <option v-for="kategori in kategoris" :key="kategori.id" :value="kategori.id">
                                {{ kategori.nama }}
                            </option>
                        </select>
                    </div>

                    <!-- Subcategory Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Sub Kategori
                        </label>
                        <select
                            v-model="selectedSubKategori"
                            @change="onSubKategoriChange"
                            :disabled="!selectedKategori"
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <option value="">Semua Sub Kategori</option>
                            <option v-for="subKategori in filteredSubKategoris" :key="subKategori.id" :value="subKategori.id">
                                {{ subKategori.nama }}
                            </option>
                        </select>
                    </div>

                    <!-- Filter Actions -->
                    <div class="flex items-end">
                        <button
                            @click="resetFilters"
                            class="w-full px-6 py-3 bg-gray-500 hover:bg-gray-600 text-white rounded-lg font-medium transition-colors duration-200"
                        >
                            Reset Filter
                        </button>
                    </div>
                </div>

                <!-- Active Filters Display -->
                <div v-if="activeFilters.length > 0" class="mt-4 flex flex-wrap gap-2">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Filter aktif:</span>
                    <span
                        v-for="filter in activeFilters"
                        :key="filter.key"
                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200"
                    >
                        {{ filter.label }}
                        <button
                            @click="removeFilter(filter.key)"
                            class="ml-1 text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-200"
                        >
                            ×
                        </button>
                    </span>
                </div>
            </div>

            <!-- Products Grid -->
            <div v-if="produk.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                <div
                    v-for="product in produk"
                    :key="product.id"
                    class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition-all duration-300 hover:-translate-y-2"
                >
                    <!-- Product Image -->
                    <div class="relative">
                        <div v-if="product.gambar" class="h-48 overflow-hidden">
                            <img
                                :src="`/storage/${product.gambar}`"
                                :alt="product.nama"
                                class="w-full h-full object-cover"
                            />
                        </div>
                        <div v-else class="h-48 bg-gradient-to-br from-gray-200 to-gray-300 dark:from-gray-600 dark:to-gray-700 flex items-center justify-center">
                            <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        <!-- Category Badge -->
                        <div class="absolute top-3 left-3 bg-green-500 text-white px-2 py-1 rounded-full text-xs font-medium">
                            {{ product.umkm?.kategori?.nama || 'Kategori' }}
                        </div>
                    </div>

                    <!-- Product Info -->
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2 line-clamp-2">
                            {{ product.nama }}
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                            {{ product.umkm?.nama || 'UMKM' }}
                        </p>
                        <p class="text-sm text-gray-600 dark:text-gray-300 mb-4 line-clamp-2">
                            {{ product.deskripsi }}
                        </p>

                        <div class="flex items-center justify-between">
                            <span class="text-xl font-bold text-green-600 dark:text-green-400">
                                Rp {{ formatPrice(product.harga) }}
                            </span>
                            <button class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-medium transition-colors duration-200">
                                Lihat Detail
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-16">
                <svg class="w-24 h-24 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                    Produk Tidak Ditemukan
                </h3>
                <p class="text-gray-600 dark:text-gray-400 mb-6">
                    Tidak ada produk yang sesuai dengan filter yang dipilih.
                </p>
                <button
                    @click="resetFilters"
                    class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors duration-200"
                >
                    Reset Filter
                </button>
            </div>

            <!-- Load More Button -->
            <div v-if="produk.length > 0" class="text-center mt-12">
                <button class="px-8 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors duration-200">
                    Lihat Lebih Banyak
                </button>
            </div>
        </div>
    </section>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    kategoris: Array,
    subKategoris: Array,
    produk: Array,
    filters: Object
})

// Reactive filter values
const selectedKategori = ref(props.filters?.kategori || '')
const selectedSubKategori = ref(props.filters?.sub_kategori || '')

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
                key: 'kategori',
                label: `Kategori: ${kategori.nama}`
            })
        }
    }

    if (selectedSubKategori.value) {
        const subKategori = filteredSubKategoris.value.find(sk => sk.id == selectedSubKategori.value)
        if (subKategori) {
            filters.push({
                key: 'sub_kategori',
                label: `Sub Kategori: ${subKategori.nama}`
            })
        }
    }

    return filters
})

// Format price helper
const formatPrice = (price) => {
    if (!price) return '0'
    return new Intl.NumberFormat('id-ID').format(price)
}

// Filter change handlers
const onKategoriChange = () => {
    selectedSubKategori.value = ''
    applyFilters()
}

const onSubKategoriChange = () => {
    applyFilters()
}

const resetFilters = () => {
    selectedKategori.value = ''
    selectedSubKategori.value = ''
    applyFilters()
}

const removeFilter = (filterKey) => {
    if (filterKey === 'kategori') {
        selectedKategori.value = ''
        selectedSubKategori.value = ''
    } else if (filterKey === 'sub_kategori') {
        selectedSubKategori.value = ''
    }
    applyFilters()
}

const applyFilters = () => {
    const params = {}

    if (selectedKategori.value) {
        params.kategori = selectedKategori.value
    }

    if (selectedSubKategori.value) {
        params.sub_kategori = selectedSubKategori.value
    }

    // Use Inertia to navigate to the UMKM listing with filters
    router.get(route('umkm.index'), params, {
        preserveState: true,
        preserveScroll: true,
        only: ['produk', 'subKategoris', 'filters']
    })
}
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
