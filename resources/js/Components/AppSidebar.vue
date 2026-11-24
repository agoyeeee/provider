<script setup>
import {
  User,
  LogOut,
  ChevronUp,
  Wifi,
  LayoutDashboard,
  Database,
  Globe,
  Map as MapIcon,
  ArrowUpRight,
} from "lucide-vue-next";
import {
  Sidebar,
  SidebarContent,
  SidebarFooter,
  SidebarGroup,
  SidebarGroupContent,
  SidebarGroupLabel,
  SidebarHeader,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
  SidebarSeparator,
  SidebarRail,
} from "@/Components/ui/sidebar";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from "@/Components/ui/dropdown-menu";
import { Avatar, AvatarFallback, AvatarImage } from "@/Components/ui/avatar";
import { Badge } from "@/Components/ui/badge";
import { Link, usePage } from "@inertiajs/vue3";
import { computed, onMounted } from "vue";

const page = usePage();

onMounted(() => {
  document.documentElement.classList.remove("dark");
});

// Get current user
const user = computed(() => page.props.auth.user);
const isAdminUser = computed(() =>
  ["admin", "super_admin"].includes(user.value?.role)
);

// Navigation items for Admin
const mainNavItems = [
  {
    title: "Dashboard",
    url: "admin.dashboard",
    icon: LayoutDashboard,
    description: "Overview & statistik",
  },
  {
    title: "Data Provider",
    url: "admin.provider.index",
    icon: Database,
    description: "Kelola data provider",
  },
];

const settingsItems = [
  {
    title: "Users",
    url: "admin.users.index",
    icon: User,
    description: "Manajemen pengguna",
    requiresSuperAdmin: true,
  },
];

const items = computed(() => {
  if (isAdminUser.value) {
    return mainNavItems;
  }
  return [];
});

const settingsNavItems = computed(() => {
  if (isAdminUser.value) {
    return settingsItems.filter((item) => {
      if (item.requiresSuperAdmin && user.value?.role !== "super_admin") {
        return false;
      }
      return true;
    });
  }
  return [];
});

const isRouteActive = (routeName) => {
  return route().current(routeName) || route().current(routeName + ".*");
};

// Get user initials for avatar fallback
const getUserInitials = (name) => {
  return (
    name
      ?.split(" ")
      .map((word) => word[0])
      .join("")
      .toUpperCase()
      .slice(0, 2) || "U"
  );
};

// Get role display name
const getRoleDisplayName = computed(() => {
  if (user.value?.role === "super_admin") return "Super Admin";
  if (user.value?.role === "admin") return "Admin";
  return "User";
});
</script>

<template>
  <Sidebar collapsible="icon" class="border-r border-slate-200/80 bg-white">
    <!-- Header with SIPJALIN Brand -->
    <SidebarHeader class="border-b border-slate-100 bg-white px-3 py-3">
      <Link :href="route('admin.dashboard')" class="block" preserve-scroll>
        <div
          class="flex items-center gap-3 rounded-xl px-2 py-2 transition-all duration-200 hover:bg-slate-50"
        >
          <!-- Logo Icon -->
          <div
            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-600 to-teal-600 text-white shadow-md shadow-emerald-600/20"
          >
            <Wifi class="h-5 w-5" />
          </div>

          <!-- Text only shows when sidebar is not collapsed -->
          <div class="flex flex-col leading-tight group-data-[collapsible=icon]:hidden">
            <span class="text-sm font-extrabold tracking-wide text-slate-900">
              SIPJALIN
            </span>
            <span class="text-[11px] font-medium text-slate-400">
              Panel Admin Surakarta
            </span>
          </div>
        </div>
      </Link>
    </SidebarHeader>

    <SidebarContent class="bg-white px-2 py-2">
      <!-- Main Navigation -->
      <SidebarGroup>
        <SidebarGroupLabel
          class="px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 group-data-[collapsible=icon]:hidden"
        >
          Menu Utama
        </SidebarGroupLabel>
        <SidebarGroupContent class="mt-1">
          <SidebarMenu class="space-y-1">
            <SidebarMenuItem v-for="item in items" :key="item.title">
              <SidebarMenuButton as-child :tooltip="item.title" class="h-10 rounded-xl">
                <Link
                  :href="route(item.url)"
                  class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition-all duration-200"
                  :class="
                    isRouteActive(item.url)
                      ? 'bg-emerald-50/90 text-emerald-700 font-semibold ring-1 ring-emerald-500/20 shadow-sm shadow-emerald-500/5'
                      : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900 font-medium'
                  "
                >
                  <component
                    :is="item.icon"
                    class="h-4 w-4 shrink-0 transition-colors"
                    :class="
                      isRouteActive(item.url) ? 'text-emerald-600' : 'text-slate-400'
                    "
                  />
                  <span class="truncate group-data-[collapsible=icon]:hidden">
                    {{ item.title }}
                  </span>
                </Link>
              </SidebarMenuButton>
            </SidebarMenuItem>
          </SidebarMenu>
        </SidebarGroupContent>
      </SidebarGroup>

      <SidebarSeparator class="my-2 bg-slate-100 group-data-[collapsible=icon]:hidden" />

      <!-- Settings Navigation -->
      <SidebarGroup v-if="settingsNavItems.length > 0">
        <SidebarGroupLabel
          class="px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 group-data-[collapsible=icon]:hidden"
        >
          Pengaturan
        </SidebarGroupLabel>
        <SidebarGroupContent class="mt-1">
          <SidebarMenu class="space-y-1">
            <SidebarMenuItem v-for="item in settingsNavItems" :key="item.title">
              <SidebarMenuButton as-child :tooltip="item.title" class="h-10 rounded-xl">
                <Link
                  :href="route(item.url)"
                  class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition-all duration-200"
                  :class="
                    isRouteActive(item.url)
                      ? 'bg-emerald-50/90 text-emerald-700 font-semibold ring-1 ring-emerald-500/20 shadow-sm shadow-emerald-500/5'
                      : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900 font-medium'
                  "
                >
                  <component
                    :is="item.icon"
                    class="h-4 w-4 shrink-0 transition-colors"
                    :class="
                      isRouteActive(item.url) ? 'text-emerald-600' : 'text-slate-400'
                    "
                  />
                  <span class="truncate group-data-[collapsible=icon]:hidden">
                    {{ item.title }}
                  </span>
                </Link>
              </SidebarMenuButton>
            </SidebarMenuItem>
          </SidebarMenu>
        </SidebarGroupContent>
      </SidebarGroup>

      <SidebarSeparator
        v-if="settingsNavItems.length > 0"
        class="my-2 bg-slate-100 group-data-[collapsible=icon]:hidden"
      />

      <!-- Pintasan Halaman Publik -->
      <SidebarGroup>
        <SidebarGroupLabel
          class="px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 group-data-[collapsible=icon]:hidden"
        >
          Pintasan Publik
        </SidebarGroupLabel>
        <SidebarGroupContent class="mt-1">
          <SidebarMenu class="space-y-1">
            <SidebarMenuItem>
              <SidebarMenuButton as-child tooltip="Lihat Beranda" class="h-10 rounded-xl">
                <Link
                  :href="route('landing')"
                  class="flex items-center justify-between gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-600 transition-all duration-200 hover:bg-slate-100/80 hover:text-slate-900"
                >
                  <span class="flex items-center gap-3 truncate">
                    <Globe class="h-4 w-4 shrink-0 text-slate-400" />
                    <span class="truncate group-data-[collapsible=icon]:hidden">
                      Halaman Beranda
                    </span>
                  </span>
                  <ArrowUpRight
                    class="h-3.5 w-3.5 shrink-0 text-slate-400 group-data-[collapsible=icon]:hidden"
                  />
                </Link>
              </SidebarMenuButton>
            </SidebarMenuItem>
            <SidebarMenuItem>
              <SidebarMenuButton as-child tooltip="Peta Interaktif" class="h-10 rounded-xl">
                <Link
                  :href="route('landing.map')"
                  class="flex items-center justify-between gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-600 transition-all duration-200 hover:bg-slate-100/80 hover:text-slate-900"
                >
                  <span class="flex items-center gap-3 truncate">
                    <MapIcon class="h-4 w-4 shrink-0 text-slate-400" />
                    <span class="truncate group-data-[collapsible=icon]:hidden">
                      Peta Sebaran
                    </span>
                  </span>
                  <ArrowUpRight
                    class="h-3.5 w-3.5 shrink-0 text-slate-400 group-data-[collapsible=icon]:hidden"
                  />
                </Link>
              </SidebarMenuButton>
            </SidebarMenuItem>
          </SidebarMenu>
        </SidebarGroupContent>
      </SidebarGroup>
    </SidebarContent>

    <!-- Footer with User Profile -->
    <SidebarFooter class="border-t border-slate-100 bg-slate-50/60 p-2">
      <SidebarMenu>
        <SidebarMenuItem>
          <DropdownMenu>
            <DropdownMenuTrigger as-child>
              <SidebarMenuButton
                size="lg"
                class="h-14 rounded-xl hover:bg-white data-[state=open]:bg-white data-[state=open]:shadow-sm"
              >
                <Avatar class="h-9 w-9 rounded-xl ring-2 ring-emerald-500/20">
                  <AvatarImage
                    :src="page.props.auth.user.avatar"
                    :alt="page.props.auth.user.name"
                  />
                  <AvatarFallback
                    class="rounded-xl bg-emerald-500/10 text-emerald-700 font-bold text-xs"
                  >
                    {{ getUserInitials(page.props.auth.user.name) }}
                  </AvatarFallback>
                </Avatar>
                <div class="grid flex-1 text-left text-sm leading-tight">
                  <span class="truncate font-bold text-slate-900">
                    {{ page.props.auth.user.name }}
                  </span>
                  <span class="truncate text-xs text-slate-500">
                    {{ page.props.auth.user.email }}
                  </span>
                </div>
                <Badge
                  variant="secondary"
                  class="ml-auto group-data-[collapsible=icon]:hidden rounded-md bg-emerald-500/10 text-emerald-700 border-0 text-[10px] font-semibold px-2 py-0.5"
                >
                  {{ getRoleDisplayName }}
                </Badge>
                <ChevronUp class="ml-1 size-4 shrink-0 text-slate-400" />
              </SidebarMenuButton>
            </DropdownMenuTrigger>
            <DropdownMenuContent
              class="w-[--radix-dropdown-menu-trigger-width] min-w-56 rounded-xl border-slate-200 shadow-xl"
              side="top"
              align="start"
              :side-offset="8"
            >
              <DropdownMenuLabel class="font-normal">
                <div class="flex flex-col space-y-1">
                  <p class="text-sm font-semibold text-slate-900">
                    {{ page.props.auth.user.name }}
                  </p>
                  <p class="text-xs text-slate-500">{{ page.props.auth.user.email }}</p>
                </div>
              </DropdownMenuLabel>
              <DropdownMenuSeparator />
              <DropdownMenuItem as-child>
                <Link
                  :href="route('logout')"
                  method="post"
                  as="button"
                  class="flex items-center gap-2 w-full text-red-600 focus:text-red-700 focus:bg-red-50 cursor-pointer"
                >
                  <LogOut class="h-4 w-4" />
                  <span>Keluar</span>
                </Link>
              </DropdownMenuItem>
            </DropdownMenuContent>
          </DropdownMenu>
        </SidebarMenuItem>
      </SidebarMenu>
    </SidebarFooter>

    <!-- Rail for collapsed state -->
    <SidebarRail />
  </Sidebar>
</template>
