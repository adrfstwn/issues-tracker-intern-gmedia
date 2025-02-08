<script setup>
import { ref, onMounted, watch, onUnmounted, nextTick } from "vue";
import * as echarts from "echarts/core";
import { LineChart } from "echarts/charts";
import {
  TitleComponent,
  TooltipComponent,
  GridComponent,
} from "echarts/components";
import { CanvasRenderer } from "echarts/renderers";

echarts.use([
  LineChart,
  TitleComponent,
  TooltipComponent,
  GridComponent,
  CanvasRenderer,
]);

const props = defineProps({
  data: {
    type: Array,
    default: () => [],
  },
});

const chartContainer = ref(null);
let chart = null;

const formatTime = (timestamp) => {
  const date = new Date(timestamp * 1000);
  return date.toLocaleTimeString("id-ID", {
    month: "short",
    hour: "2-digit",
    minute: "2-digit",
  });
};

const initChart = () => {
  if (!props.data || !chartContainer.value) return;

  const chartData = props.data.map((item) => ({
    value: [formatTime(item[0]), item[1]],
  }));

  const option = {
    tooltip: {
      trigger: "axis",
      formatter: function (params) {
        return `Time: ${params[0].value[0]}<br/>Events: ${params[0].value[1]}`;
      },
    },
    grid: {
      top: "15%",
      left: "0%",
      right: "0%",
      bottom: "3%",
      containLabel: true,
    },
    xAxis: {
      type: "category",
      show: false,
    },
    yAxis: {
      type: "value",
      show: false,
    },
    series: [
      {
        type: "line",
        smooth: true,
        symbol: "none",
        data: chartData,
        areaStyle: {
          opacity: 0.3,
        },
        itemStyle: {
          color: "#4a90e2",
        },
      },
    ],
  };

  if (chart) {
    chart.dispose();
  }

  chart = echarts.init(chartContainer.value);
  chart.setOption(option);

  // Add window resize listener
  window.addEventListener("resize", () => {
    chart?.resize();
  });
};

onMounted(() => {
  if (props.data) {
    initChart();
  }
});

watch(
  () => props.data,
  (newVal) => {
    if (newVal) {
      nextTick(() => {
        initChart();
      });
    }
  },
  { deep: true }
);

onUnmounted(() => {
  if (chart) {
    chart.dispose();
    window.removeEventListener("resize", chart.resize);
  }
});
</script>

<template>
  <div ref="chartContainer" class="h-20">
    <div v-if="!data" class="h-full flex items-center justify-center">
      <span class="text-sm text-gray-400">No data available</span>
    </div>
  </div>
</template>
