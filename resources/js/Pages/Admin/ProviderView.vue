<script setup>
import { ref, computed, watch } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Label } from '@/Components/ui/label'
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/Components/ui/dialog'
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/Components/ui/table'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/Components/ui/select'
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/Components/ui/card'
import { Badge } from '@/Components/ui/badge'
import { toast } from 'vue-sonner'
import { Toaster } from '@/Components/ui/sonner'
import {
    Plus,
    Pencil,
    Trash2,
    Search,
    MapPin,
    Image,
    Calendar,
    Filter,
    X,
    Download,
    Eye,
} from 'lucide-vue-next'

const props = defineProps({
    providers: {
        type: Array,
        default: () => [],
    },
    users: {
        type: Array,
        default: () => [],
    },
    filterOptions: {
        type: Object,
        default: () => ({
            provinsi: [],
            kota: [],
            kecamatan: [],
            n_provider: [],
        }),
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
})

const page = usePage()

// Dialog states
const showAddDialog = ref(false)
const showEditDialog = ref(false)
const showDeleteDialog = ref(false)
const showDetailDialog = ref(false)

// Form data
const form = ref({
    fid: '',
    provinsi: '',
    kota: '',
    kecamatan: '',
    kelurahan: '',
    k_ref_wil: '',
    utilitas: '',
    n_provider: '',
    odp: '',
    sjalu_21: '',
    x: '',
    y: '',
    foto: null,
    tgl_survey: '',
    id_user: 'null',
})

// Selected items
const selectedProvider = ref(null)
const detailProvider = ref(null)

// Search and filter
const searchQuery = ref(props.filters?.search || '')
const filterProvinsi = ref(props.filters?.provinsi || 'all')
const filterKota = ref(props.filters?.kota || 'all')
const filterKecamatan = ref(props.filters?.kecamatan || 'all')
const filterNProvider = ref(props.filters?.n_provider || 'all')

// File input ref
const fotoInput = ref(null)

// Reset form
const resetForm = () => {
    form.value = {
        fid: '',
        provinsi: '',
        kota: '',
        kecamatan: '',
        kelurahan: '',
        k_ref_wil: '',
        utilitas: '',
        n_provider: '',
        odp: '',
        sjalu_21: '',
        x: '',
        y: '',
        foto: null,
        tgl_survey: '',
        id_user: 'null',
    }
    if (fotoInput.value) {
        fotoInput.value.value = ''
    }
}

// Open add dialog
const openAddDialog = () => {
    resetForm()
    showAddDialog.value = true
}

// Open edit dialog
const openEditDialog = (provider) => {
    selectedProvider.value = provider
    form.value = {
        fid: provider.fid || '',
        provinsi: provider.provinsi || '',
        kota: provider.kota || '',
        kecamatan: provider.kecamatan || '',
        kelurahan: provider.kelurahan || '',
        k_ref_wil: provider.k_ref_wil || '',
        utilitas: provider.utilitas || '',
        n_provider: provider.n_provider || '',
        odp: provider.odp || '',
        sjalu_21: provider.sjalu_21 || '',
        x: provider.x || '',
        y: provider.y || '',
        foto: null,
        tgl_survey: provider.tgl_survey || '',
        id_user: provider.id_user ? String(provider.id_user) : 'null',
    }
    showEditDialog.value = true
}

// Open delete dialog
const openDeleteDialog = (provider) => {
    selectedProvider.value = provider
    showDeleteDialog.value = true
}

// Open detail dialog
const openDetailDialog = (provider) => {
    detailProvider.value = provider
    showDetailDialog.value = true
}

// Handle file change
const handleFileChange = (event) => {
    const file = event.target.files[0]
    if (file) {
        form.value.foto = file
    }
}

// Submit add form
const submitAdd = () => {
    const formData = new FormData()
    Object.keys(form.value).forEach((key) => {
        const value = form.value[key]
        // Convert 'null' string to actual null, skip empty strings and null
        if (value === 'null') {
            formData.append(key, '')
        } else if (value !== null && value !== '') {
            formData.append(key, value)
        }
    })

    router.post(route('admin.provider.store'), formData, {
        forceFormData: true,
        onSuccess: () => {
            showAddDialog.value = false
            resetForm()
            toast.success('Data provider berhasil ditambahkan')
        },
        onError: (errors) => {
            toast.error(Object.values(errors).flat().join(', '))
        },
    })
}

// Submit edit form
const submitEdit = () => {
    const formData = new FormData()
    formData.append('_method', 'PUT')
    Object.keys(form.value).forEach((key) => {
        const value = form.value[key]
        // Convert 'null' string to actual null, skip empty strings and null
        if (value === 'null') {
            formData.append(key, '')
        } else if (value !== null && value !== '') {
            formData.append(key, value)
        }
    })

    router.post(route('admin.provider.update', selectedProvider.value.id), formData, {
        forceFormData: true,
        onSuccess: () => {
            showEditDialog.value = false
            resetForm()
            toast.success('Data provider berhasil diupdate')
        },
        onError: (errors) => {
            toast.error(Object.values(errors).flat().join(', '))
        },
    })
}

// Submit delete
const submitDelete = () => {
    router.delete(route('admin.provider.destroy', selectedProvider.value.id), {
        onSuccess: () => {
            showDeleteDialog.value = false
            selectedProvider.value = null
            toast.success('Data provider berhasil dihapus')
        },
        onError: (errors) => {
            toast.error('Gagal menghapus data provider')
        },
    })
}

// Apply filters
const applyFilters = () => {
    router.get(
        route('admin.provider.index'),
        {
            search: searchQuery.value || undefined,
            provinsi: (filterProvinsi.value && filterProvinsi.value !== 'all') ? filterProvinsi.value : undefined,
            kota: (filterKota.value && filterKota.value !== 'all') ? filterKota.value : undefined,
            kecamatan: (filterKecamatan.value && filterKecamatan.value !== 'all') ? filterKecamatan.value : undefined,
            n_provider: (filterNProvider.value && filterNProvider.value !== 'all') ? filterNProvider.value : undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
        }
    )
}

// Reset filters
const resetFilters = () => {
    searchQuery.value = ''
    filterProvinsi.value = 'all'
    filterKota.value = 'all'
    filterKecamatan.value = 'all'
    filterNProvider.value = 'all'
    router.get(route('admin.provider.index'))
}

// Watch for search changes with debounce
let searchTimeout = null
watch(searchQuery, (newValue) => {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => {
        applyFilters()
    }, 500)
})

// Export data
const exportData = () => {
    // Simple CSV export
    const headers = [
        'FID',
        'Provinsi',
        'Kota',
        'Kecamatan',
        'Kelurahan',
        'K Ref Wil',
        'Utilitas',
        'N Provider',
        'ODP',
        'SJALU 21',
        'X',
        'Y',
        'Tgl Survey',
    ]
    const rows = props.providers.map((p) => [
        p.fid || '',
        p.provinsi || '',
        p.kota || '',
        p.kecamatan || '',
        p.kelurahan || '',
        p.k_ref_wil || '',
        p.utilitas || '',
        p.n_provider || '',
        p.odp || '',
        p.sjalu_21 || '',
        p.x || '',
        p.y || '',
        p.tgl_survey || '',
    ])

    const csvContent = [headers.join(','), ...rows.map((row) => row.join(','))].join('\n')

    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
    const link = document.createElement('a')
    link.href = URL.createObjectURL(blob)
    link.download = `providers_${new Date().toISOString().slice(0, 10)}.csv`
    link.click()
}

// Stats
const stats = computed(() => ({
    total: props.providers.length,
    totalProvinsi: [...new Set(props.providers.map((p) => p.provinsi).filter(Boolean))].length,
    totalKota: [...new Set(props.providers.map((p) => p.kota).filter(Boolean))].length,
    totalProvider: [...new Set(props.providers.map((p) => p.n_provider).filter(Boolean))].length,
}))

// Format date
const formatDate = (dateStr) => {
    if (!dateStr) return '-'
    const date = new Date(dateStr)
    return date.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
    })
}
</script>

<template>
    <Head title="Data Provider" />
    <Toaster />

    <DashboardLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        Data Provider
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Kelola data provider dan utilitas
                    </p>
                </div>
                <div class="flex gap-2">
                    <Button variant="outline" @click="exportData">
                        <Download class="w-4 h-4 mr-2" />
                        Export CSV
                    </Button>
                    <Button @click="openAddDialog">
                        <Plus class="w-4 h-4 mr-2" />
                        Tambah Provider
                    </Button>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <Card>
                        <CardHeader class="pb-2">
                            <CardDescription>Total Data</CardDescription>
                            <CardTitle class="text-3xl">{{ stats.total }}</CardTitle>
                        </CardHeader>
                    </Card>
                    <Card>
                        <CardHeader class="pb-2">
                            <CardDescription>Jumlah Provinsi</CardDescription>
                            <CardTitle class="text-3xl">{{ stats.totalProvinsi }}</CardTitle>
                        </CardHeader>
                    </Card>
                    <Card>
                        <CardHeader class="pb-2">
                            <CardDescription>Jumlah Kota</CardDescription>
                            <CardTitle class="text-3xl">{{ stats.totalKota }}</CardTitle>
                        </CardHeader>
                    </Card>
                    <Card>
                        <CardHeader class="pb-2">
                            <CardDescription>Jumlah Provider</CardDescription>
                            <CardTitle class="text-3xl">{{ stats.totalProvider }}</CardTitle>
                        </CardHeader>
                    </Card>
                </div>

                <!-- Filters -->
                <Card>
                    <CardHeader>
                        <CardTitle class="text-lg flex items-center gap-2">
                            <Filter class="w-5 h-5" />
                            Filter & Pencarian
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                            <!-- Search -->
                            <div class="md:col-span-2">
                                <div class="relative">
                                    <Search
                                        class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"
                                    />
                                    <Input
                                        v-model="searchQuery"
                                        placeholder="Cari FID, Provider, ODP..."
                                        class="pl-10"
                                    />
                                </div>
                            </div>

                            <!-- Provinsi Filter -->
                            <Select v-model="filterProvinsi" @update:modelValue="applyFilters">
                                <SelectTrigger>
                                    <SelectValue placeholder="Pilih Provinsi" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">Semua Provinsi</SelectItem>
                                    <SelectItem
                                        v-for="prov in filterOptions.provinsi"
                                        :key="prov"
                                        :value="prov"
                                    >
                                        {{ prov }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>

                            <!-- Kota Filter -->
                            <Select v-model="filterKota" @update:modelValue="applyFilters">
                                <SelectTrigger>
                                    <SelectValue placeholder="Pilih Kota" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">Semua Kota</SelectItem>
                                    <SelectItem
                                        v-for="kota in filterOptions.kota"
                                        :key="kota"
                                        :value="kota"
                                    >
                                        {{ kota }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>

                            <!-- Provider Filter -->
                            <Select v-model="filterNProvider" @update:modelValue="applyFilters">
                                <SelectTrigger>
                                    <SelectValue placeholder="Pilih Provider" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">Semua Provider</SelectItem>
                                    <SelectItem
                                        v-for="prov in filterOptions.n_provider"
                                        :key="prov"
                                        :value="prov"
                                    >
                                        {{ prov }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>

                            <!-- Reset Button -->
                            <Button variant="outline" @click="resetFilters">
                                <X class="w-4 h-4 mr-2" />
                                Reset
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <!-- Table -->
                <Card>
                    <CardContent class="p-0">
                        <div class="overflow-x-auto">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead class="w-12">No</TableHead>
                                        <TableHead>FID</TableHead>
                                        <TableHead>Provinsi</TableHead>
                                        <TableHead>Kota</TableHead>
                                        <TableHead>Kecamatan</TableHead>
                                        <TableHead>Kelurahan</TableHead>
                                        <TableHead>Provider</TableHead>
                                        <TableHead>ODP</TableHead>
                                        <TableHead>Tgl Survey</TableHead>
                                        <TableHead class="text-center">Aksi</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow
                                        v-for="(provider, index) in providers"
                                        :key="provider.id"
                                        class="hover:bg-gray-50 dark:hover:bg-gray-800"
                                    >
                                        <TableCell class="font-medium">{{ index + 1 }}</TableCell>
                                        <TableCell>
                                            <span class="font-mono text-sm">{{
                                                provider.fid || '-'
                                            }}</span>
                                        </TableCell>
                                        <TableCell>{{ provider.provinsi || '-' }}</TableCell>
                                        <TableCell>{{ provider.kota || '-' }}</TableCell>
                                        <TableCell>{{ provider.kecamatan || '-' }}</TableCell>
                                        <TableCell>{{ provider.kelurahan || '-' }}</TableCell>
                                        <TableCell>
                                            <Badge variant="secondary">
                                                {{ provider.n_provider || '-' }}
                                            </Badge>
                                        </TableCell>
                                        <TableCell>
                                            <span class="font-mono text-sm">{{
                                                provider.odp || '-'
                                            }}</span>
                                        </TableCell>
                                        <TableCell>{{ formatDate(provider.tgl_survey) }}</TableCell>
                                        <TableCell>
                                            <div class="flex items-center justify-center gap-2">
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    @click="openDetailDialog(provider)"
                                                    title="Lihat Detail"
                                                >
                                                    <Eye class="w-4 h-4" />
                                                </Button>
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    @click="openEditDialog(provider)"
                                                    title="Edit"
                                                >
                                                    <Pencil class="w-4 h-4" />
                                                </Button>
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    class="text-red-500 hover:text-red-700"
                                                    @click="openDeleteDialog(provider)"
                                                    title="Hapus"
                                                >
                                                    <Trash2 class="w-4 h-4" />
                                                </Button>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                    <TableRow v-if="providers.length === 0">
                                        <TableCell
                                            colspan="10"
                                            class="text-center py-8 text-gray-500"
                                        >
                                            Tidak ada data provider
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>

        <!-- Add Dialog -->
        <Dialog v-model:open="showAddDialog">
            <DialogContent class="max-w-3xl max-h-[90vh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle>Tambah Data Provider</DialogTitle>
                    <DialogDescription>
                        Masukkan data provider baru ke dalam sistem
                    </DialogDescription>
                </DialogHeader>
                <form @submit.prevent="submitAdd" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- FID -->
                        <div class="space-y-2">
                            <Label for="add-fid">FID</Label>
                            <Input id="add-fid" v-model="form.fid" placeholder="Feature ID" />
                        </div>

                        <!-- Provinsi -->
                        <div class="space-y-2">
                            <Label for="add-provinsi">Provinsi *</Label>
                            <Input
                                id="add-provinsi"
                                v-model="form.provinsi"
                                placeholder="Nama Provinsi"
                                required
                            />
                        </div>

                        <!-- Kota -->
                        <div class="space-y-2">
                            <Label for="add-kota">Kota *</Label>
                            <Input
                                id="add-kota"
                                v-model="form.kota"
                                placeholder="Nama Kota"
                                required
                            />
                        </div>

                        <!-- Kecamatan -->
                        <div class="space-y-2">
                            <Label for="add-kecamatan">Kecamatan *</Label>
                            <Input
                                id="add-kecamatan"
                                v-model="form.kecamatan"
                                placeholder="Nama Kecamatan"
                                required
                            />
                        </div>

                        <!-- Kelurahan -->
                        <div class="space-y-2">
                            <Label for="add-kelurahan">Kelurahan *</Label>
                            <Input
                                id="add-kelurahan"
                                v-model="form.kelurahan"
                                placeholder="Nama Kelurahan"
                                required
                            />
                        </div>

                        <!-- K Ref Wil -->
                        <div class="space-y-2">
                            <Label for="add-k_ref_wil">K Ref Wil</Label>
                            <Input
                                id="add-k_ref_wil"
                                v-model="form.k_ref_wil"
                                placeholder="Kode Referensi Wilayah"
                            />
                        </div>

                        <!-- Utilitas -->
                        <div class="space-y-2">
                            <Label for="add-utilitas">Utilitas</Label>
                            <Input
                                id="add-utilitas"
                                v-model="form.utilitas"
                                placeholder="Jenis Utilitas"
                            />
                        </div>

                        <!-- N Provider -->
                        <div class="space-y-2">
                            <Label for="add-n_provider">Nama Provider *</Label>
                            <Input
                                id="add-n_provider"
                                v-model="form.n_provider"
                                placeholder="Nama Provider"
                                required
                            />
                        </div>

                        <!-- ODP -->
                        <div class="space-y-2">
                            <Label for="add-odp">ODP</Label>
                            <Input
                                id="add-odp"
                                v-model="form.odp"
                                placeholder="Optical Distribution Point"
                            />
                        </div>

                        <!-- SJALU 21 -->
                        <div class="space-y-2">
                            <Label for="add-sjalu_21">SJALU 21</Label>
                            <Input
                                id="add-sjalu_21"
                                v-model="form.sjalu_21"
                                placeholder="SJALU 21"
                            />
                        </div>

                        <!-- X (Longitude) -->
                        <div class="space-y-2">
                            <Label for="add-x">X (Longitude)</Label>
                            <Input
                                id="add-x"
                                v-model="form.x"
                                type="number"
                                step="any"
                                placeholder="Longitude"
                            />
                        </div>

                        <!-- Y (Latitude) -->
                        <div class="space-y-2">
                            <Label for="add-y">Y (Latitude)</Label>
                            <Input
                                id="add-y"
                                v-model="form.y"
                                type="number"
                                step="any"
                                placeholder="Latitude"
                            />
                        </div>

                        <!-- Tanggal Survey -->
                        <div class="space-y-2">
                            <Label for="add-tgl_survey">Tanggal Survey</Label>
                            <Input id="add-tgl_survey" v-model="form.tgl_survey" type="date" />
                        </div>

                        <!-- User -->
                        <div class="space-y-2">
                            <Label for="add-id_user">User</Label>
                            <Select v-model="form.id_user">
                                <SelectTrigger>
                                    <SelectValue placeholder="Pilih User" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="null">Tidak ada</SelectItem>
                                    <SelectItem
                                        v-for="user in users"
                                        :key="user.id"
                                        :value="String(user.id)"
                                    >
                                        {{ user.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <!-- Foto -->
                        <div class="space-y-2 md:col-span-2">
                            <Label for="add-foto">Foto</Label>
                            <Input
                                id="add-foto"
                                ref="fotoInput"
                                type="file"
                                accept="image/*"
                                @change="handleFileChange"
                            />
                        </div>
                    </div>

                    <DialogFooter>
                        <Button type="button" variant="outline" @click="showAddDialog = false">
                            Batal
                        </Button>
                        <Button type="submit">Simpan</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Edit Dialog -->
        <Dialog v-model:open="showEditDialog">
            <DialogContent class="max-w-3xl max-h-[90vh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle>Edit Data Provider</DialogTitle>
                    <DialogDescription> Ubah data provider yang sudah ada </DialogDescription>
                </DialogHeader>
                <form @submit.prevent="submitEdit" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- FID -->
                        <div class="space-y-2">
                            <Label for="edit-fid">FID</Label>
                            <Input id="edit-fid" v-model="form.fid" placeholder="Feature ID" />
                        </div>

                        <!-- Provinsi -->
                        <div class="space-y-2">
                            <Label for="edit-provinsi">Provinsi *</Label>
                            <Input
                                id="edit-provinsi"
                                v-model="form.provinsi"
                                placeholder="Nama Provinsi"
                                required
                            />
                        </div>

                        <!-- Kota -->
                        <div class="space-y-2">
                            <Label for="edit-kota">Kota *</Label>
                            <Input
                                id="edit-kota"
                                v-model="form.kota"
                                placeholder="Nama Kota"
                                required
                            />
                        </div>

                        <!-- Kecamatan -->
                        <div class="space-y-2">
                            <Label for="edit-kecamatan">Kecamatan *</Label>
                            <Input
                                id="edit-kecamatan"
                                v-model="form.kecamatan"
                                placeholder="Nama Kecamatan"
                                required
                            />
                        </div>

                        <!-- Kelurahan -->
                        <div class="space-y-2">
                            <Label for="edit-kelurahan">Kelurahan *</Label>
                            <Input
                                id="edit-kelurahan"
                                v-model="form.kelurahan"
                                placeholder="Nama Kelurahan"
                                required
                            />
                        </div>

                        <!-- K Ref Wil -->
                        <div class="space-y-2">
                            <Label for="edit-k_ref_wil">K Ref Wil</Label>
                            <Input
                                id="edit-k_ref_wil"
                                v-model="form.k_ref_wil"
                                placeholder="Kode Referensi Wilayah"
                            />
                        </div>

                        <!-- Utilitas -->
                        <div class="space-y-2">
                            <Label for="edit-utilitas">Utilitas</Label>
                            <Input
                                id="edit-utilitas"
                                v-model="form.utilitas"
                                placeholder="Jenis Utilitas"
                            />
                        </div>

                        <!-- N Provider -->
                        <div class="space-y-2">
                            <Label for="edit-n_provider">Nama Provider *</Label>
                            <Input
                                id="edit-n_provider"
                                v-model="form.n_provider"
                                placeholder="Nama Provider"
                                required
                            />
                        </div>

                        <!-- ODP -->
                        <div class="space-y-2">
                            <Label for="edit-odp">ODP</Label>
                            <Input
                                id="edit-odp"
                                v-model="form.odp"
                                placeholder="Optical Distribution Point"
                            />
                        </div>

                        <!-- SJALU 21 -->
                        <div class="space-y-2">
                            <Label for="edit-sjalu_21">SJALU 21</Label>
                            <Input
                                id="edit-sjalu_21"
                                v-model="form.sjalu_21"
                                placeholder="SJALU 21"
                            />
                        </div>

                        <!-- X (Longitude) -->
                        <div class="space-y-2">
                            <Label for="edit-x">X (Longitude)</Label>
                            <Input
                                id="edit-x"
                                v-model="form.x"
                                type="number"
                                step="any"
                                placeholder="Longitude"
                            />
                        </div>

                        <!-- Y (Latitude) -->
                        <div class="space-y-2">
                            <Label for="edit-y">Y (Latitude)</Label>
                            <Input
                                id="edit-y"
                                v-model="form.y"
                                type="number"
                                step="any"
                                placeholder="Latitude"
                            />
                        </div>

                        <!-- Tanggal Survey -->
                        <div class="space-y-2">
                            <Label for="edit-tgl_survey">Tanggal Survey</Label>
                            <Input id="edit-tgl_survey" v-model="form.tgl_survey" type="date" />
                        </div>

                        <!-- User -->
                        <div class="space-y-2">
                            <Label for="edit-id_user">User</Label>
                            <Select v-model="form.id_user">
                                <SelectTrigger>
                                    <SelectValue placeholder="Pilih User" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="null">Tidak ada</SelectItem>
                                    <SelectItem
                                        v-for="user in users"
                                        :key="user.id"
                                        :value="String(user.id)"
                                    >
                                        {{ user.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <!-- Foto -->
                        <div class="space-y-2 md:col-span-2">
                            <Label for="edit-foto">Foto</Label>
                            <div v-if="selectedProvider?.foto_url" class="mb-2">
                                <img
                                    :src="selectedProvider.foto_url"
                                    alt="Current photo"
                                    class="h-24 w-auto rounded-lg object-cover"
                                />
                            </div>
                            <Input
                                id="edit-foto"
                                type="file"
                                accept="image/*"
                                @change="handleFileChange"
                            />
                            <p class="text-xs text-gray-500">
                                Biarkan kosong jika tidak ingin mengubah foto
                            </p>
                        </div>
                    </div>

                    <DialogFooter>
                        <Button type="button" variant="outline" @click="showEditDialog = false">
                            Batal
                        </Button>
                        <Button type="submit">Update</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Delete Dialog -->
        <Dialog v-model:open="showDeleteDialog">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Hapus Data Provider</DialogTitle>
                    <DialogDescription>
                        Apakah Anda yakin ingin menghapus data provider ini? Tindakan ini tidak dapat
                        dibatalkan.
                    </DialogDescription>
                </DialogHeader>
                <div v-if="selectedProvider" class="py-4">
                    <p class="text-sm">
                        <strong>FID:</strong> {{ selectedProvider.fid || '-' }}
                    </p>
                    <p class="text-sm">
                        <strong>Provider:</strong> {{ selectedProvider.n_provider || '-' }}
                    </p>
                    <p class="text-sm">
                        <strong>Lokasi:</strong>
                        {{ selectedProvider.kelurahan }}, {{ selectedProvider.kecamatan }},
                        {{ selectedProvider.kota }}
                    </p>
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="showDeleteDialog = false">Batal</Button>
                    <Button variant="destructive" @click="submitDelete">Hapus</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Detail Dialog -->
        <Dialog v-model:open="showDetailDialog">
            <DialogContent class="max-w-2xl">
                <DialogHeader>
                    <DialogTitle>Detail Provider</DialogTitle>
                </DialogHeader>
                <div v-if="detailProvider" class="space-y-4">
                    <!-- Foto -->
                    <div v-if="detailProvider.foto_url" class="flex justify-center">
                        <img
                            :src="detailProvider.foto_url"
                            alt="Provider photo"
                            class="max-h-48 rounded-lg object-cover"
                        />
                    </div>

                    <!-- Info Grid -->
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-gray-500">FID</p>
                            <p class="font-medium">{{ detailProvider.fid || '-' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Nama Provider</p>
                            <p class="font-medium">{{ detailProvider.n_provider || '-' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Provinsi</p>
                            <p class="font-medium">{{ detailProvider.provinsi || '-' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Kota</p>
                            <p class="font-medium">{{ detailProvider.kota || '-' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Kecamatan</p>
                            <p class="font-medium">{{ detailProvider.kecamatan || '-' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Kelurahan</p>
                            <p class="font-medium">{{ detailProvider.kelurahan || '-' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">K Ref Wil</p>
                            <p class="font-medium">{{ detailProvider.k_ref_wil || '-' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Utilitas</p>
                            <p class="font-medium">{{ detailProvider.utilitas || '-' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">ODP</p>
                            <p class="font-medium">{{ detailProvider.odp || '-' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">SJALU 21</p>
                            <p class="font-medium">{{ detailProvider.sjalu_21 || '-' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Koordinat X (Longitude)</p>
                            <p class="font-medium font-mono">{{ detailProvider.x || '-' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Koordinat Y (Latitude)</p>
                            <p class="font-medium font-mono">{{ detailProvider.y || '-' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Tanggal Survey</p>
                            <p class="font-medium">{{ formatDate(detailProvider.tgl_survey) }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">User</p>
                            <p class="font-medium">{{ detailProvider.user?.name || '-' }}</p>
                        </div>
                    </div>

                    <!-- Map Link -->
                    <div v-if="detailProvider.x && detailProvider.y" class="pt-4">
                        <a
                            :href="`https://www.google.com/maps?q=${detailProvider.y},${detailProvider.x}`"
                            target="_blank"
                            class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-800"
                        >
                            <MapPin class="w-4 h-4" />
                            Lihat di Google Maps
                        </a>
                    </div>
                </div>
                <DialogFooter>
                    <Button @click="showDetailDialog = false">Tutup</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </DashboardLayout>
</template>
