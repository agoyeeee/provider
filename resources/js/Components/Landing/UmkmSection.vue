<template>
<section class="py-20 bg-gray-50 dark:bg-gray-800">
            <div class="container mx-auto px-6">
                <div class="text-center mb-16">
                    <h2 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">
                        Kategori UMKM
                    </h2>
                    <p class="text-xl text-gray-600 dark:text-gray-300 max-w-3xl mx-auto">
                        Jelajahi berbagai kategori produk UMKM Karanganyar yang beragam dan
                        berkualitas tinggi
                    </p>
                </div>

                <div class="flex justify-center">
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-8">
                        <!-- Dynamic Categories -->
                        <button
                            v-for="(kategori, index) in displayKategoris"
                            :key="kategori.id"
                            @click="scrollToUmkm(kategori.id)"
                            class="bg-white dark:bg-gray-700 rounded-2xl p-6 text-center hover:shadow-lg transition-all duration-300 hover:-translate-y-1 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 w-full max-w-xs"
                        >
                            <div :class="[getIconContainerClass(index), 'w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-4']">
                                <component :is="getIcon(index)" class="w-8 h-8" :class="getIconClass(index)" />
                            </div>
                            <h3 class="font-semibold text-gray-900 dark:text-white mb-2">{{ kategori.nama }}</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ kategori.umkm_count || 0 }} UMKM</p>
                        </button>
                    </div>
                </div>

                <!-- View All Categories Button -->
                <div v-if="kategoris.length > 6" class="text-center mt-12">
                    <button
                        @click="showAllCategories = !showAllCategories"
                        class="px-8 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors duration-200"
                    >
                        {{ showAllCategories ? 'Lihat Lebih Sedikit' : 'Lihat Semua Kategori' }}
                    </button>
                </div>
            </div>
        </section>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    kategoris: {
        type: Array,
        default: () => []
    }
})

const showAllCategories = ref(false)

// Display only first 6 categories by default
const displayKategoris = computed(() => {
    if (showAllCategories.value || props.kategoris.length <= 6) {
        return props.kategoris
    }
    return props.kategoris.slice(0, 6)
})

// Icon mapping for categories
const iconMapping = [
    'CookingPot',    // Kuliner
    'Shirt',         // Fashion
    'Palette',       // Kerajinan
    'Coffee',        // Minuman
    'Package',       // Olahan
    'Gift'           // Souvenir
]

const colorClasses = [
    { container: 'bg-red-100', icon: 'text-red-500' },
    { container: 'bg-purple-100', icon: 'text-purple-500' },
    { container: 'bg-blue-100', icon: 'text-blue-500' },
    { container: 'bg-orange-100', icon: 'text-orange-500' },
    { container: 'bg-green-100', icon: 'text-green-500' },
    { container: 'bg-pink-100', icon: 'text-pink-500' }
]

const getIconContainerClass = (index) => {
    const colorIndex = index % colorClasses.length
    return colorClasses[colorIndex].container
}

const getIconClass = (index) => {
    const colorIndex = index % colorClasses.length
    return colorClasses[colorIndex].icon
}

const getIcon = (index) => {
    // Return SVG components or use a default icon
    const icons = {
        'CookingPot': 'svg',
        'Shirt': 'svg',
        'Palette': 'svg',
        'Coffee': 'svg',
        'Package': 'svg',
        'Gift': 'svg'
    }

    // For now, return a generic SVG component name
    return 'svg'
}

const scrollToUmkm = (kategoriId) => {
    // Navigate to the dedicated UMKM listing page with category filter
    router.get(route('umkm.index'), { kategori: kategoriId }, {
        preserveState: false,
    })
}
</script>
