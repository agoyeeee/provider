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
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu'
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
    MoreVertical,
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
            kecamatan: [],
            kelurahan: [],
            sijali: [],
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
    kecamatan: '',
    kelurahan: '',
    utilitas: '',
    tiang: [{ n_provider: '', odp: false, status_izin: '' }],
    sijali: '',
    x: '',
    y: '',
    foto: null,
    tgl_survey: '',
})

// Selected items
const selectedProvider = ref(null)
const detailProvider = ref(null)

// Search and filter
const searchQuery = ref(props.filters?.search || '')
const filterKecamatan = ref(props.filters?.kecamatan || 'all')
const filterKelurahan = ref(props.filters?.kelurahan || 'all')
const filterSijali = ref(props.filters?.sijali || 'all')
const filterNProvider = ref(props.filters?.n_provider || 'all')

// File input ref
const fotoInput = ref(null)

// Reset form
const resetForm = () => {
    form.value = {
        kecamatan: '',
        kelurahan: '',
        utilitas: '',
        tiang: [{ n_provider: '', odp: false, status_izin: '' }],
        sijali: '',
        x: '',
        y: '',
        foto: null,
        tgl_survey: '',
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
        kecamatan: provider.kecamatan || '',
        kelurahan: provider.kelurahan || '',
        utilitas: provider.utilitas || '',
        tiang: provider.tiang?.length
            ? provider.tiang.map((t) => ({ n_provider: t.n_provider, odp: !!t.odp, status_izin: t.status_izin || '' }))
            : [{ n_provider: '', odp: false, status_izin: '' }],
        sijali: provider.sijali || '',
        x: provider.x || '',
        y: provider.y || '',
        foto: null,
        tgl_survey: provider.tgl_survey || '',
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

// ---- Import Excel: template -> cek file (preview) -> import (commit) ----
const showImportDialog = ref(false)
const importFile = ref(null)
const importPreview = ref(null) // { token, summary, rows, truncated }
const importLoading = ref(false)
const importError = ref('')
const allowOverwrite = ref(false)

const importStatusLabel = {
    baru: { text: 'Baru', class: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' },
    timpa: { text: 'Menimpa', class: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' },
    duplikat: { text: 'Duplikat', class: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' },
    error: { text: 'Error', class: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' },
}

// Jumlah baris yang akan disimpan: baru + (menimpa jika dicentang)
const importWillSave = computed(() => {
    const s = importPreview.value?.summary
    return s ? s.baru + (allowOverwrite.value ? s.timpa : 0) : 0
})

const resetImport = () => {
    importFile.value = null
    importPreview.value = null
    importError.value = ''
    allowOverwrite.value = false
}

const openImportDialog = () => {
    resetImport()
    showImportDialog.value = true
}

const handleImportFile = (event) => {
    importFile.value = event.target.files?.[0] || null
    importPreview.value = null
    importError.value = ''
}

const checkImportFile = async () => {
    if (!importFile.value) return
    importLoading.value = true
    importError.value = ''
    importPreview.value = null
    allowOverwrite.value = false

    const formData = new FormData()
    formData.append('file', importFile.value)
    try {
        const { data } = await window.axios.post(route('admin.provider.import.preview'), formData)
        importPreview.value = data
    } catch (e) {
        importError.value = e.response?.data?.message || Object.values(e.response?.data?.errors || {}).flat().join(', ') || 'File gagal diperiksa'
    } finally {
        importLoading.value = false
    }
}

const commitImport = async () => {
    importLoading.value = true
    try {
        const { data } = await window.axios.post(route('admin.provider.import.commit'), {
            token: importPreview.value.token,
            allow_overwrite: allowOverwrite.value,
        })
        showImportDialog.value = false
        toast.success(`Import selesai. Baru: ${data.created}, ditimpa: ${data.updated}, dilewati: ${data.skipped}`)
        router.reload()
    } catch (e) {
        importError.value = e.response?.data?.message || 'Import gagal'
    } finally {
        importLoading.value = false
    }
}

// Isi FormData dari form; tiang dikirim sebagai tiang[i][field]
const appendFormData = (formData) => {
    Object.keys(form.value).forEach((key) => {
        const value = form.value[key]
        if (key === 'tiang') {
            value.forEach((t, i) => {
                formData.append(`tiang[${i}][n_provider]`, t.n_provider)
                formData.append(`tiang[${i}][odp]`, t.odp ? '1' : '0')
                formData.append(`tiang[${i}][status_izin]`, t.status_izin || '')
            })
        } else if (value === 'null') {
            formData.append(key, '')
        } else if (value !== null && value !== '') {
            formData.append(key, value)
        }
    })
}

const addTiang = () => form.value.tiang.push({ n_provider: '', odp: false, status_izin: '' })
const removeTiang = (i) => form.value.tiang.splice(i, 1)

// Submit add form
const submitAdd = () => {
    const formData = new FormData()
    appendFormData(formData)

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
    appendFormData(formData)

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
            kecamatan: (filterKecamatan.value && filterKecamatan.value !== 'all') ? filterKecamatan.value : undefined,
            kelurahan: (filterKelurahan.value && filterKelurahan.value !== 'all') ? filterKelurahan.value : undefined,
            sijali: (filterSijali.value && filterSijali.value !== 'all') ? filterSijali.value : undefined,
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
    filterKecamatan.value = 'all'
    filterKelurahan.value = 'all'
    filterSijali.value = 'all'
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
    window.location.href = route('provider.export')
}

// Stats
const stats = computed(() => ({
    total: props.providers.length,
    totalKecamatan: [...new Set(props.providers.map((p) => p.kecamatan).filter(Boolean))].length,
    totalKelurahan: [...new Set(props.providers.map((p) => p.kelurahan).filter(Boolean))].length,
    totalProvider: [...new Set(props.providers.flatMap((p) => (p.tiang || []).map((t) => t.n_provider)))].length,
}))

// Pagination
const pageSizeOptions = [5, 10, 25, 50]
const pageSize = ref(10)
const currentPage = ref(1)

const totalPages = computed(() =>
    Math.max(1, Math.ceil((props.providers.length || 1) / pageSize.value))
)

const paginatedProviders = computed(() => {
    const start = (currentPage.value - 1) * pageSize.value
    return props.providers.slice(start, start + pageSize.value)
})

watch(() => props.providers.length, () => {
    currentPage.value = 1
})

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
            <div class="flex items-center justify-between w-full">
                <h1 class="text-lg font-semibold">Data Provider</h1>
                <div class="flex gap-2">
                    <Button variant="outline" size="sm" @click="openImportDialog">
                        Import
                    </Button>
                    <Button variant="outline" size="sm" @click="exportData">
                        <Download class="w-4 h-4 mr-2" />
                        Export
                    </Button>
                    <Button size="sm" @click="openAddDialog">
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
                            <CardDescription>Jumlah Kecamatan</CardDescription>
                            <CardTitle class="text-3xl">{{ stats.totalKecamatan }}</CardTitle>
                        </CardHeader>
                    </Card>
                    <Card>
                        <CardHeader class="pb-2">
                            <CardDescription>Jumlah Kelurahan</CardDescription>
                            <CardTitle class="text-3xl">{{ stats.totalKelurahan }}</CardTitle>
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
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3">
                            <!-- Search -->
                            <div class="sm:col-span-2 md:col-span-3 xl:col-span-2">
                                <div class="relative">
                                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                                    <Input v-model="searchQuery" placeholder="Cari Provider, Jalan, ODP..." class="pl-10" />
                                </div>
                            </div>

                            <!-- Kecamatan Filter -->
                            <Select v-model="filterKecamatan" @update:modelValue="applyFilters">
                                <SelectTrigger>
                                    <SelectValue placeholder="Pilih Kecamatan" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">Semua Kecamatan</SelectItem>
                                    <SelectItem v-for="kec in filterOptions.kecamatan" :key="kec" :value="kec">
                                        {{ kec }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>

                            <!-- Kelurahan Filter -->
                            <Select v-model="filterKelurahan" @update:modelValue="applyFilters">
                                <SelectTrigger>
                                    <SelectValue placeholder="Pilih Kelurahan" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">Semua Kelurahan</SelectItem>
                                    <SelectItem v-for="kel in filterOptions.kelurahan" :key="kel" :value="kel">
                                        {{ kel }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>

                            <!-- Nama Jalan Filter -->
                            <Select v-model="filterSijali" @update:modelValue="applyFilters">
                                <SelectTrigger>
                                    <SelectValue placeholder="Pilih Nama Jalan" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">Semua Jalan</SelectItem>
                                    <SelectItem v-for="jl in filterOptions.sijali" :key="jl" :value="jl">
                                        {{ jl }}
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
                                    <SelectItem v-for="prov in filterOptions.n_provider" :key="prov" :value="prov">
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
                                        <TableHead>Provinsi</TableHead>
                                        <TableHead>Kota</TableHead>
                                        <TableHead>Kecamatan</TableHead>
                                        <TableHead>Kelurahan</TableHead>
                                        <TableHead>Provider</TableHead>
                                        <TableHead>Sijali</TableHead>
                                        <TableHead>ODP</TableHead>
                                        <TableHead>Tgl Survey</TableHead>
                                        <TableHead class="text-center">Aksi</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="(provider, index) in paginatedProviders" :key="provider.id"
                                        class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                        <TableCell class="font-medium">{{ (currentPage - 1) * pageSize + index + 1 }}
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
                                            <template v-if="provider.sijali_link">
                                                <a :href="provider.sijali_link" target="_blank"
                                                    rel="noopener noreferrer"
                                                    class="text-blue-600 hover:underline dark:text-blue-300">
                                                    {{ provider.sijali || 'Detail Sijali' }}
                                                </a>
                                            </template>
                                            <span v-else class="font-mono text-sm">
                                                {{ provider.sijali || '-' }}
                                            </span>
                                        </TableCell>
                                        <TableCell>
                                            <span class="font-mono text-sm">{{
                                                provider.odp || '-'
                                                }}</span>
                                        </TableCell>
                                        <TableCell>{{ formatDate(provider.tgl_survey) }}</TableCell>
                                        <TableCell>
                                            <DropdownMenu>
                                                <DropdownMenuTrigger as-child>
                                                    <Button variant="ghost" size="icon" aria-label="Aksi">
                                                        <MoreVertical class="w-4 h-4" />
                                                    </Button>
                                                </DropdownMenuTrigger>
                                                <DropdownMenuContent align="end" class="w-40">
                                                    <DropdownMenuItem @click="openDetailDialog(provider)">
                                                        <Eye class="mr-2 h-4 w-4" />
                                                        Lihat Detail
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem @click="openEditDialog(provider)">
                                                        <Pencil class="mr-2 h-4 w-4" />
                                                        Edit
                                                    </DropdownMenuItem>
                                                    <DropdownMenuSeparator />
                                                    <DropdownMenuItem class="text-red-600 focus:text-red-700"
                                                        @click="openDeleteDialog(provider)">
                                                        <Trash2 class="mr-2 h-4 w-4" />
                                                        Hapus
                                                    </DropdownMenuItem>
                                                </DropdownMenuContent>
                                            </DropdownMenu>
                                        </TableCell>
                                    </TableRow>
                                    <TableRow v-if="providers.length === 0">
                                        <TableCell colspan="11" class="text-center py-8 text-gray-500">
                                            Tidak ada data provider
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </div>
                        <div
                            class="flex items-center justify-between border-t px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                            <div class="flex items-center gap-4">
                                <div>
                                    Menampilkan
                                    <strong class="text-gray-900 dark:text-gray-100">
                                        {{ props.providers.length ? (currentPage - 1) * pageSize + 1 : 0 }}
                                    </strong>
                                    -
                                    <strong class="text-gray-900 dark:text-gray-100">
                                        {{ Math.min(currentPage * pageSize, props.providers.length) }}
                                    </strong>
                                    dari
                                    <strong class="text-gray-900 dark:text-gray-100">{{ props.providers.length
                                    }}</strong>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="hidden sm:inline">Baris per halaman</span>
                                    <Select :modelValue="String(pageSize)"
                                        @update:modelValue="(val) => { pageSize = Number(val); currentPage = 1; }">
                                        <SelectTrigger class="w-[70px]">
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="size in pageSizeOptions" :key="size"
                                                :value="String(size)">
                                                {{ size }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <Button variant="outline" size="sm" :disabled="currentPage <= 1"
                                    @click="currentPage = Math.max(1, currentPage - 1)">
                                    &laquo;
                                </Button>
                                <span class="px-2 text-sm">
                                    {{ currentPage }} / {{ totalPages }}
                                </span>
                                <Button variant="outline" size="sm" :disabled="currentPage >= totalPages"
                                    @click="currentPage = Math.min(totalPages, currentPage + 1)">
                                    &raquo;
                                </Button>
                            </div>
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
                        <!-- Kecamatan -->
                        <div class="space-y-2">
                            <Label for="add-kecamatan">Kecamatan *</Label>
                            <Input id="add-kecamatan" v-model="form.kecamatan" placeholder="Nama Kecamatan" required />
                        </div>

                        <!-- Kelurahan -->
                        <div class="space-y-2">
                            <Label for="add-kelurahan">Kelurahan *</Label>
                            <Input id="add-kelurahan" v-model="form.kelurahan" placeholder="Nama Kelurahan" required />
                        </div>

                        <!-- Utilitas -->
                        <div class="space-y-2">
                            <Label for="add-utilitas">Utilitas</Label>
                            <Input id="add-utilitas" v-model="form.utilitas" placeholder="Jenis Utilitas" />
                        </div>

                        <!-- Tiang (satu tiang = satu provider) -->
                        <div class="space-y-2 md:col-span-2">
                            <Label>Tiang / Provider *</Label>
                            <div v-for="(t, i) in form.tiang" :key="i" class="flex flex-wrap items-center gap-2">
                                <Input v-model="t.n_provider" placeholder="Nama Provider" class="flex-1 min-w-[140px]" required />
                                <label class="flex items-center gap-1 text-sm">
                                    <input type="checkbox" v-model="t.odp" /> ODP ada
                                </label>
                                <select v-model="t.status_izin"
                                    class="h-9 rounded-md border border-input bg-background text-foreground px-2 text-sm [&>option]:bg-background [&>option]:text-foreground">
                                    <option value="">Izin: -</option>
                                    <option value="berizin">Berizin</option>
                                    <option value="tidak_berizin">Tidak berizin</option>
                                </select>
                                <Button type="button" variant="outline" size="sm" :disabled="form.tiang.length === 1"
                                    @click="removeTiang(i)">Hapus</Button>
                            </div>
                            <Button type="button" variant="outline" size="sm" @click="addTiang">+ Tambah tiang</Button>
                        </div>

                        <!-- Sijali -->
                        <div class="space-y-2">
                            <Label for="add-sijali">Sijali</Label>
                            <Input id="add-sijali" v-model="form.sijali" placeholder="Nama jalan Sijali" />
                        </div>

                        <!-- X (Longitude) -->
                        <div class="space-y-2">
                            <Label for="add-x">X (Longitude)</Label>
                            <Input id="add-x" v-model="form.x" type="number" step="any" placeholder="Longitude" />
                        </div>

                        <!-- Y (Latitude) -->
                        <div class="space-y-2">
                            <Label for="add-y">Y (Latitude)</Label>
                            <Input id="add-y" v-model="form.y" type="number" step="any" placeholder="Latitude" />
                        </div>

                        <!-- Tanggal Survey -->
                        <div class="space-y-2">
                            <Label for="add-tgl_survey">Tanggal Survey</Label>
                            <Input id="add-tgl_survey" v-model="form.tgl_survey" type="date"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:[color-scheme:dark]" />
                        </div>
                        <!-- Foto -->
                        <div class="space-y-2 md:col-span-2">
                            <Label for="add-foto">Foto</Label>
                            <Input id="add-foto" ref="fotoInput" type="file" accept="image/*"
                                @change="handleFileChange" />
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
                        <!-- Kecamatan -->
                        <div class="space-y-2">
                            <Label for="edit-kecamatan">Kecamatan *</Label>
                            <Input id="edit-kecamatan" v-model="form.kecamatan" placeholder="Nama Kecamatan" required />
                        </div>

                        <!-- Kelurahan -->
                        <div class="space-y-2">
                            <Label for="edit-kelurahan">Kelurahan *</Label>
                            <Input id="edit-kelurahan" v-model="form.kelurahan" placeholder="Nama Kelurahan" required />
                        </div>

                        <!-- Utilitas -->
                        <div class="space-y-2">
                            <Label for="edit-utilitas">Utilitas</Label>
                            <Input id="edit-utilitas" v-model="form.utilitas" placeholder="Jenis Utilitas" />
                        </div>

                        <!-- Tiang (satu tiang = satu provider) -->
                        <div class="space-y-2 md:col-span-2">
                            <Label>Tiang / Provider *</Label>
                            <div v-for="(t, i) in form.tiang" :key="i" class="flex flex-wrap items-center gap-2">
                                <Input v-model="t.n_provider" placeholder="Nama Provider" class="flex-1 min-w-[140px]" required />
                                <label class="flex items-center gap-1 text-sm">
                                    <input type="checkbox" v-model="t.odp" /> ODP ada
                                </label>
                                <select v-model="t.status_izin"
                                    class="h-9 rounded-md border border-input bg-background text-foreground px-2 text-sm [&>option]:bg-background [&>option]:text-foreground">
                                    <option value="">Izin: -</option>
                                    <option value="berizin">Berizin</option>
                                    <option value="tidak_berizin">Tidak berizin</option>
                                </select>
                                <Button type="button" variant="outline" size="sm" :disabled="form.tiang.length === 1"
                                    @click="removeTiang(i)">Hapus</Button>
                            </div>
                            <Button type="button" variant="outline" size="sm" @click="addTiang">+ Tambah tiang</Button>
                        </div>

                        <!-- Sijali -->
                        <div class="space-y-2">
                            <Label for="edit-sijali">Sijali</Label>
                            <Input id="edit-sijali" v-model="form.sijali" placeholder="Sijali" />
                        </div>

                        <!-- X (Longitude) -->
                        <div class="space-y-2">
                            <Label for="edit-x">X (Longitude)</Label>
                            <Input id="edit-x" v-model="form.x" type="number" step="any" placeholder="Longitude" />
                        </div>

                        <!-- Y (Latitude) -->
                        <div class="space-y-2">
                            <Label for="edit-y">Y (Latitude)</Label>
                            <Input id="edit-y" v-model="form.y" type="number" step="any" placeholder="Latitude" />
                        </div>

                        <!-- Tanggal Survey -->
                        <div class="space-y-2">
                            <Label for="edit-tgl_survey">Tanggal Survey</Label>
                            <Input id="edit-tgl_survey" v-model="form.tgl_survey" type="date" />
                        </div>

                        <!-- Foto -->
                        <div class="space-y-2 md:col-span-2">
                            <Label for="edit-foto">Foto</Label>
                            <div v-if="selectedProvider?.foto_url" class="mb-2">
                                <img :src="selectedProvider.foto_url" alt="Current photo"
                                    class="h-24 w-auto rounded-lg object-cover" />
                            </div>
                            <Input id="edit-foto" type="file" accept="image/*" @change="handleFileChange" />
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

        <!-- Import Dialog -->
        <Dialog v-model:open="showImportDialog">
            <DialogContent class="max-w-3xl max-h-[90vh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle>Import Data Provider</DialogTitle>
                    <DialogDescription>
                        Unduh template, isi, lalu upload. File diperiksa dulu sebelum disimpan.
                    </DialogDescription>
                </DialogHeader>

                <div class="space-y-4">
                    <!-- 1. Template -->
                    <div class="flex items-center justify-between rounded-lg border p-3">
                        <div class="text-sm">
                            <p class="font-medium">1. Unduh template</p>
                            <p class="text-gray-500">Berisi kolom yang benar dan petunjuk pengisian.</p>
                        </div>
                        <Button as="a" :href="route('admin.provider.import.template')" variant="outline" size="sm">
                            <Download class="w-4 h-4 mr-2" /> Template
                        </Button>
                    </div>

                    <!-- 2. Upload -->
                    <div class="space-y-2 rounded-lg border p-3">
                        <p class="text-sm font-medium">2. Upload file</p>
                        <div class="flex items-center gap-2">
                            <Input type="file" accept=".xlsx,.xls" @change="handleImportFile" />
                            <Button size="sm" :disabled="!importFile || importLoading" @click="checkImportFile">
                                {{ importLoading && !importPreview ? 'Memeriksa...' : 'Cek File' }}
                            </Button>
                        </div>
                        <p v-if="importError" class="text-sm text-red-600">{{ importError }}</p>
                    </div>

                    <!-- 3. Hasil pengecekan -->
                    <div v-if="importPreview" class="space-y-3 rounded-lg border p-3">
                        <p class="text-sm font-medium">3. Hasil pengecekan</p>
                        <div class="flex flex-wrap gap-2 text-xs">
                            <span class="rounded bg-gray-100 px-2 py-1 dark:bg-gray-800">Total {{ importPreview.summary.total }}</span>
                            <span v-for="(meta, key) in importStatusLabel" :key="key"
                                :class="['rounded px-2 py-1', meta.class]">
                                {{ meta.text }} {{ importPreview.summary[key] }}
                            </span>
                        </div>

                        <label v-if="importPreview.summary.timpa > 0" class="flex items-center gap-2 text-sm">
                            <input type="checkbox" v-model="allowOverwrite" />
                            Izinkan menimpa {{ importPreview.summary.timpa }} titik yang sudah ada
                            (tiang lama tidak dihapus, status izin lama dipertahankan)
                        </label>

                        <div class="max-h-64 overflow-y-auto rounded border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead class="w-16">Baris</TableHead>
                                        <TableHead class="w-24">Status</TableHead>
                                        <TableHead>Titik</TableHead>
                                        <TableHead>Keterangan</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="r in importPreview.rows" :key="r.row">
                                        <TableCell>{{ r.row }}</TableCell>
                                        <TableCell>
                                            <span :class="['rounded px-2 py-0.5 text-xs', importStatusLabel[r.status].class]">
                                                {{ importStatusLabel[r.status].text }}
                                            </span>
                                        </TableCell>
                                        <TableCell class="text-xs">{{ r.label }}</TableCell>
                                        <TableCell class="text-xs">
                                            <p v-for="(m, i) in r.messages" :key="i">{{ m }}</p>
                                            <span v-if="!r.messages.length" class="text-gray-400">-</span>
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </div>
                        <p v-if="importPreview.truncated" class="text-xs text-gray-500">
                            Hanya {{ importPreview.rows.length }} baris pertama yang ditampilkan (error ditampilkan lebih dulu).
                        </p>
                    </div>
                </div>

                <DialogFooter class="gap-2">
                    <Button v-if="importPreview && (importPreview.summary.error + importPreview.summary.duplikat) > 0"
                        as="a" :href="route('admin.provider.import.errors', importPreview.token)" variant="outline">
                        Unduh baris bermasalah
                    </Button>
                    <Button variant="outline" @click="showImportDialog = false">Batal</Button>
                    <Button :disabled="!importPreview || importWillSave === 0 || importLoading" @click="commitImport">
                        {{ importLoading && importPreview ? 'Menyimpan...' : `Import ${importWillSave} baris` }}
                    </Button>
                </DialogFooter>
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
                        <img :src="detailProvider.foto_url" alt="Provider photo"
                            class="max-h-48 rounded-lg object-cover" />
                    </div>

                    <!-- Info Grid -->
                    <div class="grid grid-cols-2 gap-4 text-sm">
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
                            <p class="text-gray-500">Sijali</p>
                            <p class="font-medium">{{ detailProvider.sijali || '-' }}</p>
                        </div>
                        <div v-if="detailProvider.sijali_link">
                            <p class="text-gray-500">Link Sijali</p>
                            <a :href="detailProvider.sijali_link" target="_blank" rel="noopener noreferrer"
                                class="font-medium text-blue-600 hover:underline dark:text-blue-300">
                                Buka detail jalan
                            </a>
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
                        <a :href="`https://www.google.com/maps?q=${detailProvider.y},${detailProvider.x}`"
                            target="_blank" class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-800">
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
