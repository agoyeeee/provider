<script setup>
import { computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/Components/ui/card'
import { Button } from '@/Components/ui/button'
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/Components/ui/table'
import { Badge } from '@/Components/ui/badge'
import {
    Users,
    Wifi,
    MapPin,
    Building,
    TrendingUp,
    ArrowRight,
    Calendar,
} from 'lucide-vue-next'

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({
            total_users: 0,
            total_provider: 0,
            total_provider_unique: 0,
            total_kecamatan: 0,
            total_kelurahan: 0,
        }),
    },
    recent_providers: {
        type: Array,
        default: () => [],
    },
    provider_by_kecamatan: {
        type: Array,
        default: () => [],
    },
    provider_by_kelurahan: {
        type: Array,
        default: () => [],
    },
})

const distKecamatan = computed(() =>
    props.provider_by_kecamatan?.length ? props.provider_by_kecamatan : (props.provider_by_provinsi || []),
)
const distKelurahan = computed(() =>
    props.provider_by_kelurahan?.length ? props.provider_by_kelurahan : (props.provider_by_name || []),
)

// Format date
const formatDate = (dateStr) => {
    if (!dateStr) return '-'
    const date = new Date(dateStr)
    return date.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    })
}

// Stats cards data
const statsCards = computed(() => [
    {
        title: 'Total Data Provider',
        value: props.stats.total_provider,
        icon: Wifi,
        color: 'text-blue-600',
        bgColor: 'bg-blue-100 dark:bg-blue-900/30',
    },
    {
        title: 'Jumlah Provider',
        value: props.stats.total_provider_unique,
        icon: Building,
        color: 'text-green-600',
        bgColor: 'bg-green-100 dark:bg-green-900/30',
    },
    // {
    //     title: 'Jumlah Provinsi',
    //     value: props.stats.total_provinsi,
    //     icon: MapPin,
    //     color: 'text-purple-600',
    //     bgColor: 'bg-purple-100 dark:bg-purple-900/30',
    // },
    // {
    //     title: 'Jumlah Kota',
    //     value: props.stats.total_kota,
    //     icon: TrendingUp,
    //     color: 'text-orange-600',
    //     bgColor: 'bg-orange-100 dark:bg-orange-900/30',
    // },
    {
        title: 'Total Users',
        value: props.stats.total_users,
        icon: Users,
        color: 'text-cyan-600',
        bgColor: 'bg-cyan-100 dark:bg-cyan-900/30',
    },
])
</script>

<template>

    <Head title="Dashboard" />

    <DashboardLayout>
        <template #header>
            <div class="flex items-center justify-between w-full">
                <h1 class="text-lg font-semibold">Dashboard</h1>
                <Link :href="route('admin.provider.index')">
                    <Button size="sm">
                        <Wifi class="w-4 h-4 mr-2" />
                        Kelola Provider
                    </Button>
                </Link>
            </div>
        </template>

        <div class="p-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <Card v-for="stat in statsCards" :key="stat.title" class="overflow-hidden">
                        <CardContent class="p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                        {{ stat.title }}
                                    </p>
                                    <p class="text-3xl font-bold mt-2">{{ stat.value }}</p>
                                </div>
                                <div :class="[
                                    'rounded-full p-3',
                                    stat.bgColor,
                                ]">
                                    <component :is="stat.icon" :class="['w-6 h-6', stat.color]" />
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Provider by Kecamatan Chart -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <MapPin class="w-5 h-5 text-purple-600" />
                                Distribusi per Kecamatan
                            </CardTitle>
                            <CardDescription>
                                Jumlah data provider berdasarkan kecamatan
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div v-if="distKecamatan.length > 0" class="space-y-3">
                                <div v-for="item in distKecamatan" :key="item.name"
                                    class="flex items-center justify-between">
                                    <span class="text-sm font-medium">{{ item.name }}</span>
                                    <div class="flex items-center gap-2">
                                        <div class="w-32 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                            <div class="h-2 rounded-full" :style="{
                                                width: `${(item.value / stats.total_provider) * 100}%`,
                                                backgroundColor: item.color,
                                            }"></div>
                                        </div>
                                        <Badge variant="secondary">{{ item.value }}</Badge>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-center py-8 text-gray-500">
                                Belum ada data
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Provider by Kelurahan Chart -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Building class="w-5 h-5 text-green-600" />
                                Distribusi per Kelurahan
                            </CardTitle>
                            <CardDescription>
                                Jumlah data berdasarkan kelurahan
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div v-if="distKelurahan.length > 0" class="space-y-3">
                                <div v-for="item in distKelurahan" :key="item.name"
                                    class="flex items-center justify-between">
                                    <span class="text-sm font-medium">{{ item.name }}</span>
                                    <div class="flex items-center gap-2">
                                        <div class="w-32 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                            <div class="h-2 rounded-full" :style="{
                                                width: `${(item.value / stats.total_provider) * 100}%`,
                                                backgroundColor: item.color,
                                            }"></div>
                                        </div>
                                        <Badge variant="secondary">{{ item.value }}</Badge>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-center py-8 text-gray-500">
                                Belum ada data
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Recent Providers Table -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div>
                                <CardTitle class="flex items-center gap-2">
                                    <Calendar class="w-5 h-5 text-blue-600" />
                                    Data Provider Terbaru
                                </CardTitle>
                                <CardDescription>5 data provider yang terakhir ditambahkan</CardDescription>
                            </div>
                            <Link :href="route('admin.provider.index')">
                                <Button variant="outline" size="sm">
                                    Lihat Semua
                                    <ArrowRight class="w-4 h-4 ml-2" />
                                </Button>
                            </Link>
                        </div>
                    </CardHeader>
                    <CardContent class="p-0">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Provider</TableHead>
                                    <TableHead>Kota</TableHead>
                                    <TableHead>Kecamatan</TableHead>
                                    <TableHead>ODP</TableHead>
                                    <TableHead>Tgl Survey</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="provider in recent_providers" :key="provider.id"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                    <TableCell>
                                        <Badge variant="secondary">
                                            {{ provider.n_provider || '-' }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>{{ provider.kota || '-' }}</TableCell>
                                    <TableCell>{{ provider.kecamatan || '-' }}</TableCell>
                                    <TableCell class="font-mono text-sm">
                                        {{ provider.odp || '-' }}
                                    </TableCell>
                                    <TableCell>{{ formatDate(provider.tgl_survey) }}</TableCell>
                                </TableRow>
                                <TableRow v-if="recent_providers.length === 0">
                                    <TableCell colspan="6" class="text-center py-8 text-gray-500">
                                        Belum ada data provider
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>
            </div>
        </div>
    </DashboardLayout>
</template>
