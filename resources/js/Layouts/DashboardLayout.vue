<script setup>
import { SidebarProvider, SidebarTrigger } from "@/Components/ui/sidebar";
import AppSidebar from "@/Components/AppSidebar.vue";
import {
  Breadcrumb,
  BreadcrumbItem,
  BreadcrumbLink,
  BreadcrumbList,
  BreadcrumbPage,
  BreadcrumbSeparator,
} from "@/Components/ui/breadcrumb";
import { Link } from '@inertiajs/vue3';

defineProps({
  breadcrumbs: {
    type: Array,
    default: () => []
  }
});
</script>

<template>
  <SidebarProvider>
    <!-- Sidebar -->
    <AppSidebar />

    <!-- Main Content -->
    <div class="flex-1">
      <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        <!-- Simple Header with Toggle and Breadcrumb -->
        <header class="border-b bg-white dark:bg-gray-800">
          <div class="flex h-12 items-center gap-4 px-6">
            <!-- Trigger untuk toggle sidebar -->
            <SidebarTrigger />

            <!-- Breadcrumb -->
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

            <!-- Fallback to slot header if no breadcrumbs -->
            <div v-else-if="$slots.header" class="text-xl font-semibold text-gray-800 dark:text-gray-200">
              <slot name="header" />
            </div>
          </div>
        </header>

        <!-- Page Content -->
        <main>
          <slot />
        </main>
      </div>
    </div>
  </SidebarProvider>
</template>
