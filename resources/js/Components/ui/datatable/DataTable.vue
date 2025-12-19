<script setup lang="ts">
import type {
  ColumnDef,
  ColumnFiltersState,
  SortingState,
  VisibilityState,
} from "@tanstack/vue-table";
import {
  FlexRender,
  getCoreRowModel,
  getFacetedRowModel,
  getFacetedUniqueValues,
  getFilteredRowModel,
  getPaginationRowModel,
  getSortedRowModel,
  useVueTable,
} from "@tanstack/vue-table";
import { ref, computed, h } from "vue";
import { Checkbox } from "@/components/ui/checkbox";
import { valueUpdater } from "@/lib/utils";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/Components/ui/table";
import DataTablePagination from "./DataTablePagination.vue";
import DataTableToolbar from "./DataTableToolbar.vue";
import DataTableSkeleton from "./DataTableSkeleton.vue";

interface DataTableProps {
  columns: ColumnDef<any, any>[];
  data: any[];
  hideToolbar?: boolean;
  isLoading?: boolean;
  currentPage?: number;
  lastPage?: number;
  perPage?: number;
  total?: number;
  isServerSide?: boolean;
  enableBulkActions?: boolean;
}

const props = withDefaults(defineProps<DataTableProps>(), {
  isServerSide: false,
  enableBulkActions: false,
});

const emit = defineEmits<{
  (e: "update:page", value: number): void;
  (e: "update:perPage", value: number): void;
}>();

const sorting = ref<SortingState>([]);
const columnFilters = ref<ColumnFiltersState>([]);
const columnVisibility = ref<VisibilityState>({});
const rowSelection = ref<Record<string, boolean>>({});

// Computed to get selected data
const selectedRows = computed(() => {
  const selected = Object.keys(rowSelection.value).map(
    (key) => props.data[parseInt(key)]
  );
  return selected;
});

// Computed to check if there are selected rows
const hasSelectedRows = computed(() => {
  const count = Object.keys(rowSelection.value).length;
  return count > 0;
});

// Computed columns that will add select column if enableBulkActions = true
const computedColumns = computed(() => {
  if (!props.enableBulkActions) {
    return props.columns;
  }

  // Checkbox column for bulk selection
  const selectColumn: ColumnDef<any, any> = {
    id: "select",
    header: ({ table }) =>
      h("div", { class: "px-1" }, [
        h(Checkbox, {
          modelValue: table.getIsAllPageRowsSelected(),
          "onUpdate:modelValue": (value: boolean) => {
            table.toggleAllPageRowsSelected(!!value);
          },
          "aria-label": "Select all",
          indeterminate: table.getIsSomePageRowsSelected() && !table.getIsAllPageRowsSelected(),
        }),
      ]),
    cell: ({ row }) =>
      h("div", { class: "px-1" }, [
        h(Checkbox, {
          modelValue: row.getIsSelected(),
          "onUpdate:modelValue": (value: boolean) => {
            row.toggleSelected(!!value);
          },
          "aria-label": "Select row",
        }),
      ]),
    enableSorting: false,
    enableHiding: false,
    size: 50,
  };

  return [selectColumn, ...props.columns];
});

const table = useVueTable({
  get data() {
    return props.data;
  },
  get columns() {
    return computedColumns.value;
  },
  state: {
    get sorting() {
      return sorting.value;
    },
    get columnFilters() {
      return columnFilters.value;
    },
    get columnVisibility() {
      return columnVisibility.value;
    },
    get rowSelection() {
      return rowSelection.value;
    },
  },
  enableRowSelection: props.enableBulkActions,
  manualPagination: props.isServerSide,
  onSortingChange: (updaterOrValue) => valueUpdater(updaterOrValue, sorting),
  onColumnFiltersChange: (updaterOrValue) =>
    valueUpdater(updaterOrValue, columnFilters),
  onColumnVisibilityChange: (updaterOrValue) =>
    valueUpdater(updaterOrValue, columnVisibility),
  onRowSelectionChange: (updaterOrValue) => {
    console.log("Row Selection Changed:", updaterOrValue); // Debugging
    valueUpdater(updaterOrValue, rowSelection);
  },
  getCoreRowModel: getCoreRowModel(),
  getFilteredRowModel: getFilteredRowModel(),
  getSortedRowModel: getSortedRowModel(),
  getFacetedRowModel: getFacetedRowModel(),
  getFacetedUniqueValues: getFacetedUniqueValues(),
  ...(props.isServerSide
    ? {}
    : {
        getPaginationRowModel: getPaginationRowModel(),
      }),
});

// Function to clear selection
const clearSelection = () => {
  rowSelection.value = {};
};

// Function to execute bulk action
const executeBulkAction = async (action: (selectedRows: any[]) => Promise<void>) => {
  try {
    await action(selectedRows.value)
    clearSelection()
  } catch (error) {
    console.error('Bulk action failed:', error)
  }
};

// Expose functions to parent component
defineExpose({
  selectedRows,
  hasSelectedRows,
  executeBulkAction,
});
</script>

<template>
  <div class="space-y-4">
    <DataTableToolbar v-if="!hideToolbar && !isLoading" :table="table">
      <template #actions>

        <!-- Bulk Actions -->
        <div v-if="enableBulkActions && hasSelectedRows" class="lg:flex justify-center items-center gap-4">
          <span class="text-sm text-muted-foreground block mb-2 lg:mb-0">
            {{ Object.keys(rowSelection).length }} item terpilih
          </span>
          <div class="flex flex-wrap sm:flex-row sm:items-center gap-2 mr-4">
            <slot
              name="bulk-actions"
              :selectedRows="selectedRows"
              :executeBulkAction="executeBulkAction"
            >
            </slot>
          </div>
        </div>

        <!-- Regular toolbar actions -->
        <div v-else class="flex flex-wrap sm:flex-row sm:items-start justify-start items-center gap-2">
          <slot name="toolbar-actions"></slot>
        </div>
      </template>
    </DataTableToolbar>

    <DataTableSkeleton v-if="isLoading" :columns="computedColumns.length" :rows="5" />

    <div v-else class="rounded-md border">
      <Table>
        <TableHeader>
          <TableRow
            v-for="headerGroup in table.getHeaderGroups()"
            :key="headerGroup.id"
          >
            <TableHead v-for="header in headerGroup.headers" :key="header.id">
              <FlexRender
                v-if="!header.isPlaceholder"
                :render="header.column.columnDef.header"
                :props="header.getContext()"
              />
            </TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <template v-if="table.getRowModel().rows?.length">
            <TableRow
              v-for="row in table.getRowModel().rows"
              :key="row.id"
              :data-state="row.getIsSelected() && 'selected'"
            >
              <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id">
                <FlexRender
                  :render="cell.column.columnDef.cell"
                  :props="cell.getContext()"
                />
              </TableCell>
            </TableRow>
          </template>

          <TableRow v-else>
            <TableCell :colspan="computedColumns.length" class="h-24 text-center">
              Tidak ada data
            </TableCell>
          </TableRow>
        </TableBody>
        <TableHeader>
          <TableRow
            v-for="headerGroup in table.getHeaderGroups()"
            :key="`bottom-${headerGroup.id}`"
          >
            <TableHead v-for="header in headerGroup.headers" :key="`bottom-${header.id}`">
              <FlexRender
                v-if="!header.isPlaceholder"
                :render="header.column.columnDef.header"
                :props="header.getContext()"
              />
            </TableHead>
          </TableRow>
        </TableHeader>
      </Table>
    </div>

    <!-- Conditional pagination component -->
    <DataTablePagination
      :table="table"
      :is-server-side="props.isServerSide"
      v-bind="props.isServerSide
        ? {
            currentPage: props.currentPage,
            lastPage: props.lastPage,
            perPage: props.perPage,
            total: props.total,
          }
        : {}"
      @update:page="emit('update:page', $event)"
      @update:perPage="emit('update:perPage', $event)"
    />
  </div>
</template>
