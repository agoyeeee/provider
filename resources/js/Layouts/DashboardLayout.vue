<script setup>
import { SidebarProvider, SidebarTrigger, SidebarInset } from "@/Components/ui/sidebar";
import AppSidebar from "@/Components/AppSidebar.vue";
import {
  Breadcrumb,
  BreadcrumbItem,
  BreadcrumbLink,
  BreadcrumbList,
  BreadcrumbPage,
  BreadcrumbSeparator,
} from "@/Components/ui/breadcrumb";
import { Separator } from "@/Components/ui/separator";
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();

defineProps({
  breadcrumbs: {
    type: Array,
    default: () => []
  }
});

// Get current user
const user = computed(() => page.props.auth?.user);
</script>

<template>
  <SidebarProvider>
    <!-- Sidebar -->
    <AppSidebar />

    <!-- Main Content Area -->
    <SidebarInset class="flex flex-col">
      <!-- Top Header / Navbar -->
      <header class="sticky top-0 z-50 flex h-14 shrink-0 items-center border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60 px-4">
        <div class="flex items-center gap-2">
          <!-- Sidebar Toggle -->
          <SidebarTrigger class="-ml-1" />
          <Separator orientation="vertical" class="h-4" />
        </div>

        <!-- Header Content Area -->
        <div class="flex flex-1 items-center justify-between gap-4 pl-4">
          <!-- Breadcrumb or Custom Header -->
          <div class="flex-1">
            <Breadcrumb v-if="breadcrumbs.length > 0">
              <BreadcrumbList>
                <template v-for="(breadcrumb, index) in breadcrumbs" :key="breadcrumb.title">
                  <BreadcrumbItem>
                    <BreadcrumbLink v-if="breadcrumb.href && index < breadcrumbs.length - 1" as-child>
                      <Link :href="breadcrumb.href">{{ breadcrumb.title }}</Link>
                    </BreadcrumbLink>
                    <BreadcrumbPage v-else>{{ breadcrumb.title }}</BreadcrumbPage>
                  </BreadcrumbItem>
                  <BreadcrumbSeparator v-if="index < breadcrumbs.length - 1" />
                </template>
              </BreadcrumbList>
            </Breadcrumb>

            <!-- Custom Header Slot -->
            <slot v-else name="header" />
          </div>
        </div>
      </header>

      <!-- Page Content -->
      <main class="flex-1 overflow-auto bg-muted/30 pt-0">
        <slot />
      </main>
    </SidebarInset>
  </SidebarProvider>
</template>
