<script setup>
import { Donut } from "@unovis/ts";
import { VisDonut, VisSingleContainer } from "@unovis/vue";
import { useMounted } from "@vueuse/core";
import { computed, ref } from "vue";
import { cn } from "@/lib/utils";
import {
  ChartSingleTooltip,
  defaultColors,
} from '@/components/ui/chart';

const props = defineProps({
  data: {
    type: Array,
    required: true,
    default: () => []
  },
  title: {
    type: String,
    default: 'Chart'
  },
  colors: {
    type: Array,
    required: false
  },
  showLegend: {
    type: Boolean,
    required: false,
    default: true
  },
  showTooltip: {
    type: Boolean,
    required: false,
    default: true
  },
  filterOpacity: {
    type: Number,
    required: false,
    default: 0.2
  },
  type: {
    type: String,
    required: false,
    default: "donut"
  },
  sortFunction: {
    type: Function,
    required: false,
    default: () => undefined
  },
  valueFormatter: {
    type: Function,
    required: false
  },
  customTooltip: {
    type: null,
    required: false
  },
});

// Transform data to match shadcn-vue chart format
const chartData = computed(() => {
  return props.data.map(item => ({
    name: item.name,
    value: parseInt(item.value) || 0,
    fill: item.color
  }));
});

const valueFormatter = props.valueFormatter ?? ((tick) => `${tick}`);
const category = 'value';
const index = 'name';

const isMounted = useMounted();
const activeSegmentKey = ref();

const colors = computed(() => {
  if (props.colors?.length) {
    return props.colors;
  }

  // Use colors from data if available, otherwise use default colors
  const dataColors = chartData.value.map(item => item.fill).filter(Boolean);
  if (dataColors.length > 0) {
    return dataColors;
  }

  return defaultColors(chartData.value.length);
});

const legendItems = computed(() =>
  chartData.value.map((item, i) => ({
    name: item.name,
    color: colors.value[i] || item.fill,
    inactive: false,
  }))
);

const totalValue = computed(() =>
  chartData.value.reduce((prev, curr) => {
    return prev + (curr.value || 0);
  }, 0)
);

// Calculate percentages for legend
const dataWithPercentages = computed(() =>
  chartData.value.map(item => ({
    ...item,
    percentage: totalValue.value > 0 ? ((item.value / totalValue.value) * 100).toFixed(1) : 0
  }))
);

// Custom tooltip formatter
const customTooltipFormatter = (d) => {
  if (!d || !d.data) return '';

  const data = d.data;
  const percentage = totalValue.value > 0 ? ((data.value / totalValue.value) * 100).toFixed(1) : 0;

  return `
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg p-3">
      <div class="flex items-center space-x-2">
        <div class="w-3 h-3 rounded-full" style="background-color: ${data.fill || '#6B7280'}"></div>
        <span class="font-medium text-gray-900 dark:text-white">${data.name}</span>
      </div>
      <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">
        <div>Jumlah: <span class="font-medium">${data.value}</span></div>
        <div>Persentase: <span class="font-medium">${percentage}%</span></div>
      </div>
    </div>
  `;
};
</script>

<template>
  <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ title }}</h3>

    <div v-if="chartData && chartData.length > 0" class="space-y-4">
      <!-- Chart Container -->
      <div :class="cn('w-full h-64 flex flex-col items-center', $attrs.class ?? '')">
        <VisSingleContainer
          :style="{ height: isMounted ? '100%' : 'auto', width: '100%' }"
          :margin="{ top: 20, bottom: 20, left: 20, right: 20 }"
          :data="chartData"
        >
          <!-- Remove or customize the tooltip -->
          <VisDonut
            :value="(d) => d[category]"
            :sort-function="sortFunction"
            :color="colors"
            :arc-width="type === 'donut' ? 20 : 0"
            :show-background="false"
            :central-label="type === 'donut' ? valueFormatter(totalValue) : ''"
            :events="{
              [Donut.selectors.segment]: {
                click: (d, ev, i, elements) => {
                  if (d?.data?.[index] === activeSegmentKey) {
                    activeSegmentKey = undefined;
                    elements.forEach((el) => (el.style.opacity = '1'));
                  } else {
                    activeSegmentKey = d?.data?.[index];
                    elements.forEach(
                      (el) => (el.style.opacity = `${filterOpacity}`),
                    );
                    elements[i].style.opacity = '1';
                  }
                },
                // Remove hover tooltip
                mouseover: () => {},
                mouseout: () => {}
              },
            }"
          />
        </VisSingleContainer>
      </div>

      <!-- Custom Legend -->
      <div v-if="showLegend" class="space-y-2">
        <div
          v-for="(item, index) in dataWithPercentages"
          :key="item.name"
          class="flex items-center justify-between text-sm"
        >
          <div class="flex items-center space-x-2">
            <div
              class="w-3 h-3 rounded-full"
              :style="{ backgroundColor: item.fill || colors[index] }"
            ></div>
            <span class="text-gray-700 dark:text-gray-300">{{ item.name }}</span>
          </div>
          <div class="flex items-center space-x-2 text-gray-600 dark:text-gray-400">
            <span class="font-medium">{{ item.value }}</span>
            <span class="text-xs">({{ item.percentage }}%)</span>
          </div>
        </div>
      </div>

      <!-- Summary -->
      <div class="pt-2 border-t border-gray-200 dark:border-gray-700">
        <div class="flex justify-between items-center text-sm font-medium">
          <span class="text-gray-700 dark:text-gray-300">Total</span>
          <span class="text-gray-900 dark:text-white">{{ totalValue }}</span>
        </div>
      </div>
    </div>

    <!-- No Data State -->
    <div v-else class="flex items-center justify-center h-64">
      <div class="text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
        </svg>
        <p class="mt-2 text-gray-500 dark:text-gray-400">Tidak ada data untuk ditampilkan</p>
      </div>
    </div>
  </div>
</template>
