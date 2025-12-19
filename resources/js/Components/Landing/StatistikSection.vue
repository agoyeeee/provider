<script setup>
import { computed } from 'vue';

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({})
    }
});

const statsData = computed(() => ({
    totalUmkm: Number(props.stats.total_umkm ?? props.stats.totalUmkm ?? 0),
    totalProduk: Number(props.stats.total_produk ?? props.stats.totalProduk ?? 0),
    totalKategori: Number(props.stats.total_kategori ?? props.stats.totalKategori ?? 0),
}));

const formatStat = (value) => {
    const numericValue = Number(value) || 0;
    return new Intl.NumberFormat('id-ID').format(numericValue);
};
</script>

<template>
<section class="py-20 bg-white dark:bg-gray-900 relative overflow-hidden">
            <!-- Background Pattern -->
            <div class="absolute inset-0 bg-grid-pattern opacity-5"></div>

            <div class="container mx-auto px-6 relative z-10">
                <!-- Section Header -->
                <div class="text-center mb-16">
                    <h2 class="text-4xl lg:text-5xl font-bold text-gray-900 dark:text-white mb-4">
                        Pertumbuhan <span class="bg-gradient-to-r from-green-600 to-blue-600 dark:from-green-400 dark:to-blue-400 bg-clip-text text-transparent">UMKM Karanganyar</span>
                    </h2>
                    <p class="text-lg text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
                        Platform digital yang mendukung perkembangan UMKM lokal dengan jangkauan yang lebih luas
                    </p>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto">
                    <!-- UMKM Bergabung -->
                    <div class="group bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 rounded-3xl p-8 shadow-lg hover:shadow-2xl transform hover:-translate-y-2 transition-all border border-green-200 dark:border-green-800">
                        <div class="flex items-center justify-between mb-6">
                            <div class="bg-green-600 dark:bg-green-500 rounded-2xl p-4 shadow-lg group-hover:scale-110 transition-transform">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </div>
                            <div class="text-green-600 dark:text-green-400">
                                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                </svg>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="text-5xl font-extrabold text-green-700 dark:text-green-400">
                                {{ formatStat(statsData.totalUmkm) }}+
                            </div>
                            <div class="text-lg font-semibold text-green-800 dark:text-green-300">UMKM Bergabung</div>
                            <div class="text-sm text-green-600 dark:text-green-500">Pelaku usaha terdaftar</div>
                        </div>
                        <div class="mt-6 pt-6 border-t border-green-200 dark:border-green-800">
                            <div class="flex items-center text-sm text-green-700 dark:text-green-400">
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                    </svg>
                                    +12% bulan ini
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Produk Tersedia -->
                    <div class="group bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 rounded-3xl p-8 shadow-lg hover:shadow-2xl transform hover:-translate-y-2 transition-all border border-blue-200 dark:border-blue-800">
                        <div class="flex items-center justify-between mb-6">
                            <div class="bg-blue-600 dark:bg-blue-500 rounded-2xl p-4 shadow-lg group-hover:scale-110 transition-transform">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            </div>
                            <div class="text-blue-600 dark:text-blue-400">
                                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                </svg>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="text-5xl font-extrabold text-blue-700 dark:text-blue-400">
                                {{ formatStat(statsData.totalProduk) }}+
                            </div>
                            <div class="text-lg font-semibold text-blue-800 dark:text-blue-300">Produk Tersedia</div>
                            <div class="text-sm text-blue-600 dark:text-blue-500">Varian produk lokal</div>
                        </div>
                        <div class="mt-6 pt-6 border-t border-blue-200 dark:border-blue-800">
                            <div class="flex items-center text-sm text-blue-700 dark:text-blue-400">
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                    </svg>
                                    +25% bulan ini
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Kategori -->
                    <div class="group bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20 rounded-3xl p-8 shadow-lg hover:shadow-2xl transform hover:-translate-y-2 transition-all border border-purple-200 dark:border-purple-800">
                        <div class="flex items-center justify-between mb-6">
                            <div class="bg-purple-600 dark:bg-purple-500 rounded-2xl p-4 shadow-lg group-hover:scale-110 transition-transform">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                </svg>
                            </div>
                            <div class="text-purple-600 dark:text-purple-400">
                                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="text-5xl font-extrabold text-purple-700 dark:text-purple-400">
                                {{ formatStat(statsData.totalKategori) }}+
                            </div>
                            <div class="text-lg font-semibold text-purple-800 dark:text-purple-300">Kategori Produk</div>
                            <div class="text-sm text-purple-600 dark:text-purple-500">Beragam pilihan kategori</div>
                        </div>
                        <div class="mt-6 pt-6 border-t border-purple-200 dark:border-purple-800">
                            <div class="flex items-center text-sm text-purple-700 dark:text-purple-400">
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Lengkap & Beragam
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Info -->
                <div class="mt-16 text-center">
                    <div class="inline-flex items-center gap-8 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-900 rounded-2xl px-8 py-6 shadow-lg border border-gray-200 dark:border-gray-700">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-green-600 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="text-left">
                                <div class="text-sm text-gray-600 dark:text-gray-400">Terverifikasi</div>
                                <div class="font-bold text-gray-900 dark:text-white">100% Legal</div>
                            </div>
                        </div>
                        <div class="w-px h-12 bg-gray-300 dark:bg-gray-700"></div>
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <div class="text-left">
                                <div class="text-sm text-gray-600 dark:text-gray-400">Aman & Terpercaya</div>
                                <div class="font-bold text-gray-900 dark:text-white">Transaksi Secure</div>
                            </div>
                        </div>
                        <div class="w-px h-12 bg-gray-300 dark:bg-gray-700"></div>
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-purple-600 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                            </div>
                            <div class="text-left">
                                <div class="text-sm text-gray-600 dark:text-gray-400">Support 24/7</div>
                                <div class="font-bold text-gray-900 dark:text-white">Siap Membantu</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
</template>
