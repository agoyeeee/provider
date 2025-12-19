<script setup lang="ts">
import type { Table } from "@tanstack/vue-table";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/Components/ui/select";
import Button from "@/Components/ui/button/Button.vue";
import {
  ChevronRightIcon,
  ArrowRightIcon,
  ChevronLeftIcon,
  ArrowLeftIcon,
} from "lucide-vue-next";

interface DataTablePaginationProps {
  table: Table<any>;
  isServerSide: boolean;
  currentPage?: number;
  lastPage?: number;
  perPage?: number;
  total?: number;
}

const props = defineProps<DataTablePaginationProps>();

const emit = defineEmits<{
  (e: "update:page", value: number): void;
  (e: "update:perPage", value: number): void;
}>();
</script>

<template>
  <div class="flex flex-col gap-y-4 px-2 lg:flex-row lg:items-center lg:justify-between">
    <div class="text-sm text-muted-foreground">
      {{ table.getFilteredRowModel().rows.length }} baris
    </div>

    <div class="flex flex-col gap-y-4 lg:flex-row lg:items-center lg:space-x-6">
      <div class="flex items-center space-x-2">
        <p class="text-sm font-medium">Baris per halaman</p>
        <Select
          :model-value="
            props.isServerSide
              ? props.perPage?.toString()
              : table.getState().pagination.pageSize?.toString()
          "
          @update:model-value="
            (v) =>
              props.isServerSide
                ? emit('update:perPage', parseInt(v))
                : table.setPageSize(parseInt(v))
          "
        >
          <SelectTrigger class="h-8 w-[70px]">
            <SelectValue placeholder="Rows" />
          </SelectTrigger>
          <SelectContent side="top">
            <SelectItem
              v-for="pageSize in [10, 20, 30, 40, 50]"
              :key="pageSize"
              :value="`${pageSize}`"
            >
              {{ pageSize }}
            </SelectItem>
          </SelectContent>
        </Select>
      </div>

      <!-- Page info -->
      <div v-if="props.isServerSide" class="text-sm font-medium text-center lg:w-[100px]">
        Hal {{ currentPage }} dari
        {{ lastPage }}
      </div>

      <div v-else class="text-sm font-medium text-center lg:w-[100px]">
        Hal {{ table.getState().pagination.pageIndex + 1 }} dari
        {{ table.getPageCount() }}
      </div>

      <!-- Pagination buttons -->
      <div class="flex items-center justify-center space-x-2">
        <Button
          variant="outline"
          class="hidden h-8 w-8 p-0 lg:flex"
          :disabled="
            props.isServerSide ? props.currentPage === 1 : !table.getCanPreviousPage()
          "
          @click="props.isServerSide ? emit('update:page', 1) : table.setPageIndex(0)"
        >
          <span class="sr-only">Go to first page</span>
          <ArrowLeftIcon class="h-4 w-4" />
        </Button>
        <Button
          variant="outline"
          class="h-8 w-8 p-0"
          :disabled="
            props.isServerSide ? props.currentPage === 1 : !table.getCanPreviousPage()
          "
          @click="
            props.isServerSide
              ? emit('update:page', props.currentPage - 1)
              : table.previousPage()
          "
        >
          <span class="sr-only">Go to previous page</span>
          <ChevronLeftIcon class="h-4 w-4" />
        </Button>
        <Button
          variant="outline"
          class="h-8 w-8 p-0"
          :disabled="
            props.isServerSide
              ? props.currentPage === props.lastPage
              : !table.getCanNextPage()
          "
          @click="
            props.isServerSide
              ? emit('update:page', props.currentPage + 1)
              : table.nextPage()
          "
        >
          <span class="sr-only">Go to next page</span>
          <ChevronRightIcon class="h-4 w-4" />
        </Button>
        <Button
          variant="outline"
          class="hidden h-8 w-8 p-0 lg:flex"
          :disabled="
            props.isServerSide
              ? props.currentPage === props.lastPage
              : !table.getCanNextPage()
          "
          @click="
            props.isServerSide
              ? emit('update:page', lastPage)
              : table.setPageIndex(table.getPageCount() - 1)
          "
        >
          <span class="sr-only">Go to last page</span>
          <ArrowRightIcon class="h-4 w-4" />
        </Button>
      </div>
    </div>
  </div>
</template>
