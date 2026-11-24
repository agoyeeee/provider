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
import { Link } from "@inertiajs/vue3";

defineProps({
  breadcrumbs: {
    type: Array,
    default: () => [],
  },
});
</script>

<template>
  <SidebarProvider>
    <!-- Sidebar -->
    <AppSidebar />

    <!-- Main Content Area -->
    <SidebarInset class="flex flex-col bg-slate-50/70">
      <!-- Top Header / Navbar -->
      <header
        class="sticky top-0 z-40 flex h-16 shrink-0 items-center border-b border-slate-200/80 bg-white/85 px-6 backdrop-blur-md"
      >
        <div class="flex items-center gap-3">
          <!-- Sidebar Toggle -->
          <SidebarTrigger
            class="-ml-1 h-9 w-9 rounded-xl text-slate-600 hover:bg-slate-100 hover:text-slate-900"
          />
          <Separator orientation="vertical" class="h-5 bg-slate-200" />
        </div>

        <!-- Header Content Area -->
        <div class="flex flex-1 items-center justify-between gap-4 pl-4">
          <div class="flex-1">
            <Breadcrumb v-if="breadcrumbs.length > 0">
              <BreadcrumbList>
                <template
                  v-for="(breadcrumb, index) in breadcrumbs"
                  :key="breadcrumb.title"
                >
                  <BreadcrumbItem>
                    <BreadcrumbLink
                      v-if="breadcrumb.href && index < breadcrumbs.length - 1"
                      as-child
                    >
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
      <main class="flex-1 overflow-auto pt-0">
        <slot />
      </main>
    </SidebarInset>
  </SidebarProvider>
</template>
