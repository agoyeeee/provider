<script setup>
import {
  Home,
  User,
  LogOut,
  ChevronUp,
  Wifi,
  Settings,
  Bell,
  LayoutDashboard,
  Database,
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
import ThemeToggle from "@/Components/ThemeToggle.vue";
import { Link, usePage } from "@inertiajs/vue3";
import { computed } from "vue";

const page = usePage();

// Get current user
const user = computed(() => page.props.auth.user);
const isAdminUser = computed(() => ['admin', 'super_admin'].includes(user.value?.role));

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

// Get navigation items based on user role
const items = computed(() => {
  if (isAdminUser.value) {
    return mainNavItems;
  }
  return [];
});

const settingsNavItems = computed(() => {
  if (isAdminUser.value) {
    return settingsItems.filter(item => {
      if (item.requiresSuperAdmin && user.value?.role !== 'super_admin') {
        return false;
      }
      return true;
    });
  }
  return [];
});

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

// Get role badge color
const getRoleBadgeVariant = computed(() => {
  if (user.value?.role === 'super_admin') return 'destructive';
  if (user.value?.role === 'admin') return 'default';
  return 'secondary';
});

// Get role display name
const getRoleDisplayName = computed(() => {
  if (user.value?.role === 'super_admin') return 'Super Admin';
  if (user.value?.role === 'admin') return 'Admin';
  return 'User';
});
</script>

<template>
  <Sidebar collapsible="icon" class="border-r">
    <!-- Header with Logo -->
    <SidebarHeader class="border-b border-sidebar-border bg-sidebar">
      <Link :href="route('admin.dashboard')" class="block" preserve-scroll>
        <div
          class="flex items-center gap-3 px-2 py-3 hover:bg-sidebar-accent/50 transition-all duration-200 rounded-lg">
          <!-- Logo Icon -->
          <div
            class="flex w-10 h-10 items-center justify-center rounded-xl bg-gradient-to-br from-primary to-primary/80 text-primary-foreground shadow-lg shadow-primary/25">
            <Wifi class="w-5 h-5" />
          </div>

          <!-- Text only shows when sidebar is not collapsed -->
          <div class="flex flex-col group-data-[collapsible=icon]:hidden">
            <span class="text-base font-bold tracking-tight">Provider</span>
            <span class="text-xs text-sidebar-foreground/60">Data Management</span>
          </div>
        </div>
      </Link>
    </SidebarHeader>

    <SidebarContent class="bg-sidebar">
      <!-- Main Navigation -->
      <SidebarGroup>
        <SidebarGroupLabel class="text-xs font-semibold uppercase tracking-wider text-sidebar-foreground/50 group-data-[collapsible=icon]:hidden">
          Menu Utama
        </SidebarGroupLabel>
        <SidebarGroupContent>
          <SidebarMenu>
            <SidebarMenuItem v-for="item in items" :key="item.title">
              <SidebarMenuButton as-child :tooltip="item.title" class="h-10">
                <Link :href="route(item.url)" class="flex items-center gap-3" :class="{
                  'bg-sidebar-accent text-sidebar-accent-foreground font-medium': route().current(item.url) || route().current(item.url + '.*')
                }">
                  <component :is="item.icon" class="h-4 w-4 shrink-0" :class="{
                    'text-primary': route().current(item.url) || route().current(item.url + '.*')
                  }" />
                  <span class="truncate group-data-[collapsible=icon]:hidden">{{ item.title }}</span>
                </Link>
              </SidebarMenuButton>
            </SidebarMenuItem>
          </SidebarMenu>
        </SidebarGroupContent>
      </SidebarGroup>

      <SidebarSeparator class="group-data-[collapsible=icon]:hidden" />

      <!-- Settings Navigation -->
      <SidebarGroup v-if="settingsNavItems.length > 0">
        <SidebarGroupLabel class="text-xs font-semibold uppercase tracking-wider text-sidebar-foreground/50 group-data-[collapsible=icon]:hidden">
          Pengaturan
        </SidebarGroupLabel>
        <SidebarGroupContent>
          <SidebarMenu>
            <SidebarMenuItem v-for="item in settingsNavItems" :key="item.title">
              <SidebarMenuButton as-child :tooltip="item.title" class="h-10">
                <Link :href="route(item.url)" class="flex items-center gap-3" :class="{
                  'bg-sidebar-accent text-sidebar-accent-foreground font-medium': route().current(item.url) || route().current(item.url + '.*')
                }">
                  <component :is="item.icon" class="h-4 w-4 shrink-0" :class="{
                    'text-primary': route().current(item.url) || route().current(item.url + '.*')
                  }" />
                  <span class="truncate group-data-[collapsible=icon]:hidden">{{ item.title }}</span>
                </Link>
              </SidebarMenuButton>
            </SidebarMenuItem>
          </SidebarMenu>
        </SidebarGroupContent>
      </SidebarGroup>

      <SidebarSeparator class="group-data-[collapsible=icon]:hidden" />

      <!-- Preferences -->
      <SidebarGroup>
        <SidebarGroupLabel class="text-xs font-semibold uppercase tracking-wider text-sidebar-foreground/50 group-data-[collapsible=icon]:hidden">
          Preferensi
        </SidebarGroupLabel>
        <SidebarGroupContent>
          <SidebarMenu>
            <SidebarMenuItem>
              <SidebarMenuButton tooltip="Theme" class="w-full h-10">
                <ThemeToggle />
              </SidebarMenuButton>
            </SidebarMenuItem>
          </SidebarMenu>
        </SidebarGroupContent>
      </SidebarGroup>
    </SidebarContent>

    <!-- Footer with User Profile -->
    <SidebarFooter class="border-t border-sidebar-border bg-sidebar">
      <SidebarMenu>
        <SidebarMenuItem>
          <DropdownMenu>
            <DropdownMenuTrigger as-child>
              <SidebarMenuButton size="lg"
                class="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground h-14">
                <Avatar class="h-9 w-9 rounded-lg ring-2 ring-sidebar-border">
                  <AvatarImage :src="page.props.auth.user.avatar" :alt="page.props.auth.user.name" />
                  <AvatarFallback class="rounded-lg bg-primary/10 text-primary font-semibold">
                    {{ getUserInitials(page.props.auth.user.name) }}
                  </AvatarFallback>
                </Avatar>
                <div class="grid flex-1 text-left text-sm leading-tight">
                  <span class="truncate font-semibold">{{
                    page.props.auth.user.name
                    }}</span>
                  <span class="truncate text-xs text-sidebar-foreground/60">{{ page.props.auth.user.email }}</span>
                </div>
                <Badge :variant="getRoleBadgeVariant"
                  class="ml-auto group-data-[collapsible=icon]:hidden text-[10px] px-1.5">
                  {{ getRoleDisplayName }}
                </Badge>
                <ChevronUp class="ml-1 size-4 shrink-0 text-sidebar-foreground/50" />
              </SidebarMenuButton>
            </DropdownMenuTrigger>
            <DropdownMenuContent class="w-[--radix-dropdown-menu-trigger-width] min-w-56 rounded-xl" side="top"
              align="start" :side-offset="8">
              <DropdownMenuLabel class="font-normal">
                <div class="flex flex-col space-y-1">
                  <p class="text-sm font-medium">{{ page.props.auth.user.name }}</p>
                  <p class="text-xs text-muted-foreground">{{ page.props.auth.user.email }}</p>
                </div>
              </DropdownMenuLabel>
              <DropdownMenuSeparator />
              <DropdownMenuItem as-child>
                <Link :href="route('logout')" method="post" as="button"
                  class="flex items-center gap-2 w-full text-destructive focus:text-destructive">
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
