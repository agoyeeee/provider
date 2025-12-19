<template>
  <Head title="Users" />

  <DashboardLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 space-y-6">
      <!-- Header -->
      <div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Pengguna</h1>
        <p class="mt-1 text-gray-600 dark:text-gray-400">
          Kelola data pengguna sistem
        </p>
      </div>

      <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700">
        <div class="p-6 space-y-6">
          <!-- Top Bar -->
          <div class="flex justify-between items-center">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
              Data Pengguna
            </h2>

            <!-- Tombol Tambah User -->
            <Dialog v-model:open="createDialogOpen">
              <DialogTrigger as-child>
                <Button
                  class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg shadow-sm transition"
                >
                  + Tambah User
                </Button>
              </DialogTrigger>
              <DialogContent class="sm:max-w-lg max-h-[90vh] overflow-y-auto">
                <DialogHeader>
                  <DialogTitle class="text-xl font-bold">Tambah User Baru</DialogTitle>
                  <DialogDescription class="text-sm text-gray-500">
                    Isi detail berikut untuk menambahkan user baru
                  </DialogDescription>
                </DialogHeader>

                <!-- Form Create -->
                <div class="max-h-[60vh] overflow-y-auto pr-2">
                  <form @submit.prevent="createUser" class="grid gap-4">
                  <!-- NIK -->
                  <div>
                    <Label for="create-nik">NIK</Label>
                    <Input
                      id="create-nik"
                      v-model="createForm.nik"
                      type="text"
                      placeholder="Masukkan NIK (16 digit)"
                      maxlength="16"
                      required
                      class="mt-1 w-full"
                    />
                    <p v-if="createForm.errors.nik" class="text-sm text-red-600 mt-1">
                      {{ createForm.errors.nik }}
                    </p>
                  </div>

                  <!-- Nama -->
                  <div>
                    <Label for="create-name">Nama</Label>
                    <Input
                      id="create-name"
                      v-model="createForm.name"
                      type="text"
                      placeholder="Masukkan nama lengkap"
                      required
                      class="mt-1 w-full"
                    />
                    <p v-if="createForm.errors.name" class="text-sm text-red-600 mt-1">
                      {{ createForm.errors.name }}
                    </p>
                  </div>

                  <!-- Email -->
                  <div>
                    <Label for="create-email">Email</Label>
                    <Input
                      id="create-email"
                      v-model="createForm.email"
                      type="email"
                      placeholder="Masukkan email"
                      required
                      class="mt-1 w-full"
                    />
                    <p v-if="createForm.errors.email" class="text-sm text-red-600 mt-1">
                      {{ createForm.errors.email }}
                    </p>
                  </div>

                  <!-- Kontak -->
                  <div>
                    <Label for="create-kontak">Kontak</Label>
                    <Input
                      id="create-kontak"
                      v-model="createForm.kontak"
                      type="text"
                      placeholder="Masukkan nomor kontak"
                      required
                      class="mt-1 w-full"
                    />
                    <p v-if="createForm.errors.kontak" class="text-sm text-red-600 mt-1">
                      {{ createForm.errors.kontak }}
                    </p>
                  </div>

                  <!-- Role -->
                  <div>
                    <Label for="create-role">Role</Label>
                    <Select v-model="createForm.role" required>
                      <SelectTrigger class="mt-1 w-full">
                        <SelectValue placeholder="Pilih Role" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem
                          v-for="option in createRoleOptions"
                          :key="option.value"
                          :value="option.value"
                        >
                          {{ option.label }}
                        </SelectItem>
                      </SelectContent>
                    </Select>
                    <p v-if="createForm.errors.role" class="text-sm text-red-600 mt-1">
                      {{ createForm.errors.role }}
                    </p>
                  </div>

                  <!-- Password -->
                  <div>
                    <Label for="create-password">Password</Label>
                    <Input
                      id="create-password"
                      v-model="createForm.password"
                      type="password"
                      placeholder="Masukkan password (min. 8 karakter)"
                      required
                      class="mt-1 w-full"
                    />
                    <p v-if="createForm.errors.password" class="text-sm text-red-600 mt-1">
                      {{ createForm.errors.password }}
                    </p>
                  </div>

                  <!-- Action -->
                  <DialogFooter class="pt-2">
                    <DialogClose as-child>
                      <Button type="button" variant="outline">Batal</Button>
                    </DialogClose>
                    <Button type="submit" :disabled="createForm.processing" class="bg-blue-600 hover:bg-blue-700">
                      <span v-if="createForm.processing">Menyimpan...</span>
                      <span v-else>Simpan</span>
                    </Button>
                  </DialogFooter>
                </form>
                </div>
              </DialogContent>
            </Dialog>

            <!-- Dialog Edit User -->
            <Dialog v-model:open="editDialogOpen">
              <DialogContent class="sm:max-w-lg max-h-[90vh] overflow-y-auto">
                <DialogHeader>
                  <DialogTitle class="text-xl font-bold">Edit User</DialogTitle>
                  <DialogDescription class="text-sm text-gray-500">
                    Perbarui informasi user
                  </DialogDescription>
                </DialogHeader>

                <!-- Form Edit -->
                <div class="max-h-[60vh] overflow-y-auto pr-2">
                  <form @submit.prevent="updateUser" class="grid gap-4">
                  <!-- NIK -->
                  <div>
                    <Label for="edit-nik">NIK</Label>
                    <Input
                      id="edit-nik"
                      v-model="editForm.nik"
                      type="text"
                      placeholder="Masukkan NIK (16 digit)"
                      maxlength="16"
                      required
                      class="mt-1 w-full"
                    />
                    <p v-if="editForm.errors.nik" class="text-sm text-red-600 mt-1">
                      {{ editForm.errors.nik }}
                    </p>
                  </div>

                  <!-- Nama -->
                  <div>
                    <Label for="edit-name">Nama</Label>
                    <Input
                      id="edit-name"
                      v-model="editForm.name"
                      type="text"
                      placeholder="Masukkan nama lengkap"
                      required
                      class="mt-1 w-full"
                    />
                    <p v-if="editForm.errors.name" class="text-sm text-red-600 mt-1">
                      {{ editForm.errors.name }}
                    </p>
                  </div>

                  <!-- Email -->
                  <div>
                    <Label for="edit-email">Email</Label>
                    <Input
                      id="edit-email"
                      v-model="editForm.email"
                      type="email"
                      placeholder="Masukkan email"
                      required
                      class="mt-1 w-full"
                    />
                    <p v-if="editForm.errors.email" class="text-sm text-red-600 mt-1">
                      {{ editForm.errors.email }}
                    </p>
                  </div>

                  <!-- Kontak -->
                  <div>
                    <Label for="edit-kontak">Kontak</Label>
                    <Input
                      id="edit-kontak"
                      v-model="editForm.kontak"
                      type="text"
                      placeholder="Masukkan nomor kontak"
                      required
                      class="mt-1 w-full"
                    />
                    <p v-if="editForm.errors.kontak" class="text-sm text-red-600 mt-1">
                      {{ editForm.errors.kontak }}
                    </p>
                  </div>

                  <!-- Role -->
                  <div>
                    <Label for="edit-role">Role</Label>
                    <Select v-model="editForm.role" required>
                      <SelectTrigger class="mt-1 w-full">
                        <SelectValue placeholder="Pilih Role" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem
                          v-for="option in editRoleOptions"
                          :key="option.value"
                          :value="option.value"
                        >
                          {{ option.label }}
                        </SelectItem>
                      </SelectContent>
                    </Select>
                    <p v-if="editForm.errors.role" class="text-sm text-red-600 mt-1">
                      {{ editForm.errors.role }}
                    </p>
                  </div>

                  <!-- Password -->
                  <div>
                    <Label for="edit-password">Password</Label>
                    <Input
                      id="edit-password"
                      v-model="editForm.password"
                      type="password"
                      placeholder="Kosongkan jika tidak ingin mengubah password"
                      class="mt-1 w-full"
                    />
                    <p class="text-xs text-gray-500 mt-1">
                      Kosongkan jika tidak ingin mengubah password
                    </p>
                    <p v-if="editForm.errors.password" class="text-sm text-red-600 mt-1">
                      {{ editForm.errors.password }}
                    </p>
                  </div>

                  <!-- Action -->
                  <DialogFooter class="pt-2">
                    <DialogClose as-child>
                      <Button type="button" variant="outline">Batal</Button>
                    </DialogClose>
                    <Button type="submit" :disabled="editForm.processing" class="bg-blue-600 hover:bg-blue-700">
                      <span v-if="editForm.processing">Memperbarui...</span>
                      <span v-else>Perbarui</span>
                    </Button>
                  </DialogFooter>
                </form>
                </div>
              </DialogContent>
            </Dialog>
          </div>

          <!-- Users Table -->
          <div class="overflow-x-auto">
            <Table class="w-full text-sm">
              <TableHeader>
                <TableRow class="bg-gray-50 dark:bg-gray-700">
                  <TableHead>NIK</TableHead>
                  <TableHead>Nama</TableHead>
                  <TableHead>Email</TableHead>
                  <TableHead>Kontak</TableHead>
                  <TableHead>Role</TableHead>
                  <TableHead>UMKM</TableHead>
                  <TableHead>Aksi</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-if="users.length === 0">
                  <TableCell colspan="7" class="text-center text-gray-500 py-4">
                    Belum ada data user
                  </TableCell>
                </TableRow>
                <TableRow
                  v-for="user in users"
                  :key="user.id"
                  class="hover:bg-gray-50 dark:hover:bg-gray-700"
                >
                  <TableCell class="font-medium">{{ user.nik }}</TableCell>
                  <TableCell>{{ user.name }}</TableCell>
                  <TableCell>{{ user.email }}</TableCell>
                  <TableCell>{{ user.kontak }}</TableCell>
                  <TableCell>
                    <span :class="roleBadgeClasses[user.role] || roleBadgeClasses.default">
                      {{ roleLabels[user.role] || user.role }}
                    </span>
                  </TableCell>
                  <TableCell>
                    <span v-if="user.umkm && user.umkm.length > 0">
                      {{ user.umkm.length }} UMKM
                    </span>
                    <span v-else>-</span>
                  </TableCell>
                  <TableCell class="flex gap-2">
                    <Button
                      @click="openEditDialog(user)"
                      size="sm"
                      class="bg-blue-600 hover:bg-blue-700 text-white rounded-md px-3 py-1"
                    >
                      Edit
                    </Button>
                    <Button
                      @click="deleteUser(user)"
                      size="sm"
                      class="bg-red-600 hover:bg-red-700 text-white rounded-md px-3 py-1"
                    >
                      Hapus
                    </Button>
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { Head, useForm, router, usePage } from "@inertiajs/vue3";
import DashboardLayout from "@/Layouts/DashboardLayout.vue";
import { ref, computed } from "vue";
import Swal from 'sweetalert2';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
  DialogClose,
} from "@/components/ui/dialog";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/Components/ui/table";

// Breadcrumbs
const breadcrumbs = [
  { nama: "Dashboard", href: route("admin.dashboard") },
  { nama: "Pengguna", current: true },
];

// Props from controller
const props = defineProps({
    users: Array,
});

// Form untuk create user
const createForm = useForm({
  nik: "",
  name: "",
  email: "",
  kontak: "",
  role: "",
  password: "",
});

// Form untuk edit user
const editForm = useForm({
  nik: "",
  name: "",
  email: "",
  kontak: "",
  role: "",
  password: "",
});

// Dialog state
const createDialogOpen = ref(false);
const editDialogOpen = ref(false);
const editingUser = ref(null);

const page = usePage();
const authUser = computed(() => page.props.auth.user);
const isSuperAdmin = computed(() => authUser.value?.role === 'super_admin');

const roleLabels = {
  super_admin: 'Super Admin',
  admin: 'Admin',
  pemilik_umkm: 'Pemilik UMKM',
};

const roleBadgeClasses = {
  super_admin: 'bg-red-100 text-red-800 px-2 py-1 rounded-full text-xs',
  admin: 'bg-purple-100 text-purple-800 px-2 py-1 rounded-full text-xs',
  pemilik_umkm: 'bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs',
  default: 'bg-gray-100 text-gray-600 px-2 py-1 rounded-full text-xs',
};

const baseRoleOptions = [
  { value: 'pemilik_umkm', label: 'Pemilik UMKM' },
];

const adminRoleOption = { value: 'admin', label: 'Admin' };
const superAdminRoleOption = { value: 'super_admin', label: 'Super Admin' };

const uniqueRoleOptions = (options) => {
  const seen = new Set();
  return options.filter((option) => {
    if (seen.has(option.value)) {
      return false;
    }
    seen.add(option.value);
    return true;
  });
};

const getRoleOptions = (contextRole = null) => {
  const options = [...baseRoleOptions];

  if (isSuperAdmin.value || contextRole === 'admin') {
    options.unshift(adminRoleOption);
  }

  if (contextRole === 'super_admin') {
    options.unshift(superAdminRoleOption);
  }

  return uniqueRoleOptions(options);
};

const createRoleOptions = computed(() => getRoleOptions());
const editRoleOptions = computed(() => getRoleOptions(editingUser.value?.role ?? null));

// Methods
const createUser = () => {
  createForm.post(route("admin.users.store"), {
    onSuccess: () => {
      createForm.reset();
      createDialogOpen.value = false;

      Swal.fire({
        title: 'Berhasil!',
        text: 'User berhasil ditambahkan.',
        icon: 'success',
        timer: 2000,
        showConfirmButton: false
      });
    },
    onError: () => {
      Swal.fire({
        title: 'Error!',
        text: 'Terjadi kesalahan saat menambahkan user.',
        icon: 'error'
      });
    },
  });
};

const openEditDialog = (user) => {
  if (user.role === 'super_admin' && !isSuperAdmin.value) {
    Swal.fire({
      title: 'Akses Ditolak',
      text: 'Hanya Super Admin yang dapat mengubah data Super Admin.',
      icon: 'warning',
    });
    return;
  }

  editingUser.value = user;
  editForm.nik = user.nik;
  editForm.name = user.name;
  editForm.email = user.email;
  editForm.kontak = user.kontak;
  editForm.role = user.role;
  editForm.password = ""; // Reset password field
  editDialogOpen.value = true;
};

const updateUser = () => {
  if (!editingUser.value) return;

  editForm.put(route("admin.users.update", editingUser.value.id), {
    onSuccess: () => {
      editForm.reset();
      editDialogOpen.value = false;
      editingUser.value = null;

      Swal.fire({
        title: 'Berhasil!',
        text: 'User berhasil diperbarui.',
        icon: 'success',
        timer: 2000,
        showConfirmButton: false
      });
    },
    onError: () => {
      Swal.fire({
        title: 'Error!',
        text: 'Terjadi kesalahan saat memperbarui user.',
        icon: 'error'
      });
    },
  });
};

const deleteUser = async (user) => {
  if (user.role === 'super_admin') {
    Swal.fire({
      title: 'Tidak Dapat Dihapus',
      text: 'Super Admin tidak dapat dihapus.',
      icon: 'info'
    });
    return;
  }

  const result = await Swal.fire({
    title: 'Hapus User?',
    text: 'Data user yang dihapus tidak dapat dikembalikan!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#ef4444',
    cancelButtonColor: '#6b7280',
    confirmButtonText: 'Ya, Hapus!',
    cancelButtonText: 'Batal',
    reverseButtons: true
  });

  if (result.isConfirmed) {
    router.delete(route("admin.users.destroy", user.id), {
      onSuccess: () => {
        Swal.fire({
          title: 'Berhasil!',
          text: 'User berhasil dihapus.',
          icon: 'success',
          timer: 2000,
          showConfirmButton: false
        });
      },
      onError: () => {
        Swal.fire({
          title: 'Error!',
          text: 'Terjadi kesalahan saat menghapus user.',
          icon: 'error'
        });
      }
    });
  }
};
</script>

<style scoped>
/* Custom scrollbar untuk dialog */
.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

/* Untuk dark mode */
.dark .overflow-y-auto::-webkit-scrollbar-track {
  background: #374151;
}

.dark .overflow-y-auto::-webkit-scrollbar-thumb {
  background: #6b7280;
}

.dark .overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: #9ca3af;
}

/* Smooth scroll behavior */
.overflow-y-auto {
  scroll-behavior: smooth;
}

/* Dialog content improvements */
.max-h-\[90vh\] {
  max-height: 90vh;
}

.max-h-\[60vh\] {
  max-height: 60vh;
}
</style>
