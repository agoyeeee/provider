<script setup>
import {
  Home,
  User,
  LogOut,
  ChevronUp,
  Wifi
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
} from "@/Components/ui/sidebar";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "@/Components/ui/dropdown-menu";
import { Avatar, AvatarFallback, AvatarImage } from "@/Components/ui/avatar";
import ThemeToggle from "@/Components/ThemeToggle.vue";
import { Link, usePage } from "@inertiajs/vue3";
import { computed } from "vue";

const page = usePage();

// Get current user
const user = computed(() => page.props.auth.user);
const isAdminUser = computed(() => ['admin', 'super_admin'].includes(user.value?.role));

// Navigation items for Admin
const adminItems = [
    {
        title: "Dashboard",
        url: "admin.dashboard",
        icon: Home,
    },
    {
        title: "Data Provider",
        url: "admin.provider.index",
        icon: Wifi,
    },
    {
        title: "Users",
        url: "admin.users.index",
        icon: User,
        requiresSuperAdmin: true,
    },
];

// Get navigation items based on user role
const items = computed(() => {
    if (isAdminUser.value) {
        return adminItems.filter(item => {
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
      .toUpperCase() || "U"
  );
};

// Get dashboard title based on role
const dashboardTitle = computed(() => {
    if (user.value?.role === 'super_admin') {
        return 'Super Admin';
    } else if (user.value?.role === 'admin') {
        return 'Admin Panel';
    }
    return 'Provider Dashboard';
});
</script>

<template>
  <Sidebar collapsible="icon">
    <SidebarHeader class="border-b border-sidebar-border">
      <Link :href="route('admin.dashboard')" class="block">
        <div class="flex items-center gap-2 px-2 py-2">
          <!-- Logo placeholder -->
          <div
            class="flex w-12 h-12 items-center justify-center rounded-lg bg-primary text-primary-foreground"
          >
            <Wifi class="w-6 h-6" />
          </div>

          <!-- Text only shows when sidebar is not collapsed -->
          <div class="flex flex-col group-data-[collapsible=icon]:hidden">
            <span class="text-sm font-semibold">{{ dashboardTitle }}</span>
            <span class="text-xs text-sidebar-foreground/70">Provider Data</span>
          </div>
        </div>
      </Link>
    </SidebarHeader>

    <SidebarContent>
      <SidebarGroup>
        <SidebarGroupLabel>Navigation</SidebarGroupLabel>
        <SidebarGroupContent>
          <SidebarMenu>
            <SidebarMenuItem v-for="item in items" :key="item.title">
              <SidebarMenuButton
                :as-child="true"
                :is-active="route().current(item.url) || route().current(item.url + '.*')"
              >
                <Link :href="route(item.url)" class="flex items-center gap-2">
                  <component :is="item.icon" class="h-4 w-4" />
                  <span>{{ item.title }}</span>
                </Link>
              </SidebarMenuButton>
            </SidebarMenuItem>
          </SidebarMenu>
        </SidebarGroupContent>
      </SidebarGroup>

      <SidebarSeparator />

      <SidebarGroup>
        <SidebarGroupLabel>Preferences</SidebarGroupLabel>
        <SidebarGroupContent>
          <SidebarMenu>
            <SidebarMenuItem>
              <SidebarMenuButton as-child class="w-full">
                <ThemeToggle />
              </SidebarMenuButton>
            </SidebarMenuItem>
          </SidebarMenu>
        </SidebarGroupContent>
      </SidebarGroup>
    </SidebarContent>

    <SidebarFooter>
      <SidebarMenu>
        <SidebarMenuItem>
          <DropdownMenu>
            <DropdownMenuTrigger as-child>
              <SidebarMenuButton
                size="lg"
                class="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground"
              >
                <Avatar class="h-8 w-8 rounded-lg">
                  <AvatarImage
                    :src="page.props.auth.user.avatar"
                    :alt="page.props.auth.user.name"
                  />
                  <AvatarFallback class="rounded-lg">
                    {{ getUserInitials(page.props.auth.user.name) }}
                  </AvatarFallback>
                </Avatar>
                <div class="grid flex-1 text-left text-sm leading-tight">
                  <span class="truncate font-semibold">{{
                    page.props.auth.user.name
                  }}</span>
                  <span class="truncate text-xs">{{ page.props.auth.user.email }}</span>
                </div>
                <ChevronUp class="ml-auto size-4" />
              </SidebarMenuButton>
            </DropdownMenuTrigger>
            <DropdownMenuContent
              class="w-[--radix-dropdown-menu-trigger-width] min-w-56 rounded-lg"
              side="bottom"
              align="end"
              :side-offset="4"
            >
              <DropdownMenuItem as-child>
                <Link
                  :href="route('logout')"
                  method="post"
                  as="button"
                  class="flex items-center gap-2 w-full"
                >
                  <LogOut class="h-4 w-4" />
                  <span>Log out</span>
                </Link>
              </DropdownMenuItem>
            </DropdownMenuContent>
          </DropdownMenu>
        </SidebarMenuItem>
      </SidebarMenu>
    </SidebarFooter>
  </Sidebar>
</template>
