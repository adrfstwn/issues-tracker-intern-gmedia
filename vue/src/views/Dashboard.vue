<script setup>
import DefaultLayout from "@/layout/DefaultLayout.vue";
import SkeltonLoader from "@/components/loading-skelton/SkeltonLoader.vue";

import { useAllStatusStore } from "@/stores/dashboard";
import { useProjectsStore } from "@/stores/projects";

import { storeToRefs } from "pinia";
import { onMounted, ref, watch, nextTick, onUnmounted } from "vue";
import { useRoute } from "vue-router";
import * as echarts from "echarts/core";
import { BarChart } from "echarts/charts";
import {
  TitleComponent,
  TooltipComponent,
  GridComponent,
  LegendComponent,
} from "echarts/components";
import { CanvasRenderer } from "echarts/renderers";

echarts.use([
  TitleComponent,
  TooltipComponent,
  GridComponent,
  LegendComponent,
  BarChart,
  CanvasRenderer,
]);

const statusStore = useAllStatusStore();
const projectStore = useProjectsStore();
const { projectsList } = storeToRefs(projectStore);
const {
  isLoading,
  totalIssues,
  unresolvedIssues,
  resolvedIssues,
  ignoredIssues,
} = storeToRefs(statusStore);
const { fetchAllStatusApi } = statusStore;
const { fetchAllProjectsListApi } = projectStore;

const route = useRoute();
const chartContainer = ref(null);
let chartInstance = null;
const selectedProject = ref("");

// Fetch status based on route
const fetchStatus = async () => {
  const slug = route.params.slug || " ";
  await fetchAllStatusApi(slug);
};

// Fetch data when project changes
const handleProjectChange = async () => {
  // isLoading.value = true;
  try {
    const projectSlug = selectedProject.value || " ";
    await fetchAllStatusApi(projectSlug);
    await nextTick();
    if (chartInstance && chartContainer.value) {
      chartInstance.clear();
      initializeChart();
    }
  } catch (err) {
    console.error("error to fetch data", err);
  } finally {
    // isLoading.value = false;
  }
};

// Initialize chart
const initializeChart = () => {
  if (!chartContainer.value) {
    console.warn("Chart container is not yet initialized.");
    return;
  }
  if (chartInstance) {
    chartInstance.dispose();
  }
  chartInstance = echarts.init(chartContainer.value);
  const options = {
    title: {
      text: "",
      textStyle: { fontSize: 16, fontWeight: "normal" },
      left: "center",
    },
    tooltip: {
      trigger: "axis",
      axisPointer: { type: "shadow" },
    },
    grid: {
      top: "15%",
      left: "0%",
      right: "0%",
      bottom: "10%",
      containLabel: true,
    },
    xAxis: {
      type: "category",
      data: ["Current Status"],
      splitLine: { show: false },
    },
    yAxis: {
      type: "value",
      axisLabel: { formatter: "{value}" },
      splitLine: { show: false },
    },
    series: [
      {
        name: "Total Issues",
        type: "bar",
        smooth: true,
        data: [totalIssues.value],
        itemStyle: {
          color: {
            type: "linear",
            x: 0,
            y: 0,
            x2: 0,
            y2: 1,
            colorStops: [
              { offset: 0, color: "rgba(238, 35, 35, 1)" },
              { offset: 1, color: "rgba(238, 35, 35, 0.3)" },
            ],
          },
          barBorderRadius: [12, 12, 0, 0],
        },
        label: { show: true, position: "top" },
      },
      {
        name: "Unresolved Issues",
        type: "bar",
        smooth: true,
        data: [unresolvedIssues.value],
        itemStyle: {
          color: {
            type: "linear",
            x: 0,
            y: 0,
            x2: 0,
            y2: 1,
            colorStops: [
              { offset: 0, color: "rgba(255, 167, 11, 1)" },
              { offset: 1, color: "rgba(255, 167, 11, 0.3)" },
            ],
          },
          barBorderRadius: [12, 12, 0, 0],
        },
        label: { show: true, position: "top" },
      },
      {
        name: "Resolved Issues",
        type: "bar",
        smooth: true,
        data: [resolvedIssues.value],
        itemStyle: {
          color: {
            type: "linear",
            x: 0,
            y: 0,
            x2: 0,
            y2: 1,
            colorStops: [
              { offset: 0, color: "rgba(31, 194, 64, 1)" },
              { offset: 1, color: "rgba(31, 194, 64, 0.3)" },
            ],
          },
          barBorderRadius: [12, 12, 0, 0],
        },
        label: { show: true, position: "top" },
      },
      {
        name: "Ignored Issues",
        type: "bar",
        smooth: true,
        data: [ignoredIssues.value],
        itemStyle: {
          color: {
            type: "linear",
            x: 0,
            y: 0,
            x2: 0,
            y2: 1,
            colorStops: [
              { offset: 0, color: "rgba(104, 96, 105, 1)" },
              { offset: 1, color: "rgba(104, 96, 105, 0.3)" },
            ],
          },
          barBorderRadius: [12, 12, 0, 0],
        },
        label: { show: true, position: "top" },
      },
    ],
    legend: {
      data: [
        "Total Issues",
        "Unresolved Issues",
        "Resolved Issues",
        "Ignored Issues",
      ],
      bottom: 0,
    },
  };

  chartInstance.setOption(options);
};

watch([totalIssues, unresolvedIssues, resolvedIssues, ignoredIssues], () => {
  nextTick(() => {
    if (chartInstance && chartContainer.value) {
      chartInstance.setOption({
        series: [
          { data: [totalIssues.value] },
          { data: [unresolvedIssues.value] },
          { data: [resolvedIssues.value] },
          { data: [ignoredIssues.value] },
        ],
      });
    }
  });
});

watch(
  () => route.params.slug,
  async (newSlug) => {
    if (newSlug) {
      await fetchStatus();
      await projectStore.fetchAllProjectsListApi();
    }
  }
);

const handleResize = () => {
  if (chartInstance) {
    chartInstance.resize();
  }
};

onMounted(async () => {
  await fetchStatus();
  await projectStore.fetchAllProjectsListApi();
  nextTick(() => {
    if (chartContainer.value) {
      initializeChart();
    }
  });
  window.addEventListener("resize", handleResize);
});

onUnmounted(() => {
  if (chartInstance) {
    chartInstance.dispose();
    chartInstance = null;
  }
  window.removeEventListener("resize", handleResize);
});
</script>

<template>
  <DefaultLayout class="bg-whiteBgPrimary-100">
    <div class="bg-white min-h-screen flex flex-col gap-6 p-6 rounded-2xl">
      <div v-if="isLoading" class="flex items-center justify-center h-full">
        <SkeltonLoader type="card" :cards="4" :size="large" />
      </div>
      <div
        v-else
        class="card grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-2 md:gap-4"
      >
        <div
          class="card p-4 md:p-6 rounded-xl bg-gradient-to-r from-red-900 to-red-600 relative flex flex-col"
        >
          <h3 class="text-sm md:text-base font-medium text-white">
            Total Issues
          </h3>
          <p class="text-2xl md:text-4xl font-bold text-white">
            {{ totalIssues }}
          </p>
          <div class="absolute -bottom-4 -right-4 md:-bottom-12 md:-right-9">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              viewBox="0 0 24 24"
              class="text-white opacity-40 size-24 md:size-40"
            >
              <path
                fill="none"
                stroke="currentColor"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-miterlimit="10"
                stroke-width="1.5"
                d="m15 15l-3-3m0 0L9 9m3 3l3-3m-3 3l-3 3M3.23 7.913L7.91 3.23c.15-.15.35-.23.57-.23h7.05c.21 0 .42.08.57.23l4.67 4.673c.15.15.23.35.23.57v7.054c0 .21-.08.42-.23.57L16.1 20.77c-.15.15-.35.23-.57.23H8.47a.8.8 0 0 1-.57-.23l-4.67-4.673a.8.8 0 0 1-.23-.57V8.473c0-.21.08-.42.23-.57z"
              />
            </svg>
          </div>
        </div>
        <div
          class="p-4 md:p-6 rounded-xl bg-gradient-to-r from-yellow-900 to-yellow-600 relative flex flex-col"
        >
          <h3 class="text-sm md:text-base font-medium text-white">
            Unresolved Issues
          </h3>
          <p class="text-2xl md:text-4xl font-bold text-white">
            {{ unresolvedIssues }}
          </p>
          <div class="absolute -bottom-4 -right-4 md:-bottom-10 md:-right-10">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              viewBox="0 0 24 24"
              class="text-white opacity-40 size-24 md:size-40"
            >
              <path
                fill="currentColor"
                fill-rule="evenodd"
                d="m11.998 4.4l-8.92 15.454l17.843-.001zM2.732 21.054a1 1 0 0 1-.866-1.5L11.132 3.5a1 1 0 0 1 1.732 0l9.27 16.053a1 1 0 0 1-.866 1.5zm8.64-11.1h1.255l-.097 4.722h-1.06l-.097-4.722zm.626 7.144a.696.696 0 0 1-.708-.694c0-.385.312-.688.708-.688c.4 0 .712.303.712.688a.697.697 0 0 1-.712.694"
              />
            </svg>
          </div>
        </div>

        <div
          class="p-4 md:p-6 rounded-xl bg-gradient-to-r from-green-900 to-green-600 relative flex flex-col"
        >
          <h3 class="text-sm md:text-base font-medium text-white">
            Resolved Issues
          </h3>
          <p class="text-2xl md:text-4xl font-bold text-white">
            {{ resolvedIssues }}
          </p>
          <div class="absolute -bottom-4 -right-4 md:-bottom-11 md:-right-8">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              viewBox="0 0 1024 1024"
              class="text-white opacity-40 size-24 md:size-36"
            >
              <path
                fill="currentColor"
                d="M512 0C229.232 0 0 229.232 0 512c0 282.784 229.232 512 512 512c282.784 0 512-229.216 512-512C1024 229.232 794.784 0 512 0m0 961.008c-247.024 0-448-201.984-448-449.01c0-247.024 200.976-448 448-448s448 200.977 448 448s-200.976 449.01-448 449.01m204.336-636.352L415.935 626.944l-135.28-135.28c-12.496-12.496-32.752-12.496-45.264 0c-12.496 12.496-12.496 32.752 0 45.248l158.384 158.4c12.496 12.48 32.752 12.48 45.264 0c1.44-1.44 2.673-3.009 3.793-4.64l318.784-320.753c12.48-12.496 12.48-32.752 0-45.263c-12.512-12.496-32.768-12.496-45.28 0"
              />
            </svg>
          </div>
        </div>
        <div
          class="p-4 md:p-6 rounded-xl bg-gradient-to-r from-codgray-900 to-codgray-600 relative flex flex-col overflow-hidden"
        >
          <h3 class="text-sm md:text-base font-medium text-white">
            Ignored Issues
          </h3>
          <p class="text-2xl md:text-4xl font-bold text-white">
            {{ ignoredIssues }}
          </p>
          <div class="absolute -bottom-4 -right-4 md:-bottom-14 md:-right-9">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              viewBox="0 0 16 16"
              class="text-white opacity-40 size-24 md:size-40"
            >
              <path
                fill="currentColor"
                d="M8 .5A7.77 7.77 0 0 0 0 8a7.77 7.77 0 0 0 8 7.5A7.77 7.77 0 0 0 16 8A7.77 7.77 0 0 0 8 .5M1.25 8A6 6 0 0 1 3 3.85L12.09 13A7.1 7.1 0 0 1 8 14.25A6.52 6.52 0 0 1 1.25 8M13 12.15L3.91 3A7.1 7.1 0 0 1 8 1.75A6.52 6.52 0 0 1 14.75 8A6 6 0 0 1 13 12.15"
              />
            </svg>
          </div>
        </div>
      </div>
      <div class="flex justify-end">
        <div class="relative w-full md:w-64">
          <select
            v-model="selectedProject"
            @change="handleProjectChange"
            class="appearance-none block w-full p-2 pl-3 pr-8 border md:border-2 border-wildsand-200 rounded-lg text-codgray-900 font-medium cursor-pointer focus:outline-none focus:ring-2 focus:ring-offset-2 focus:text-cobalt-700 focus:ring-cobalt-600 hover:border-cobalt-500 transition-colors duration-200"
          >
            <option
              class="text-sm md:text-base font-medium text-cobalt-600"
              value=""
              :disabled="!projectsList.length"
            >
              All Projects
            </option>
            <option
              class="rounded-lg cursor-pointer"
              v-for="project in projectsList"
              :key="project.id"
              :value="project.slug"
              :selected="selectedProject === project.slug"
            >
              {{ project.name }}
            </option>
          </select>
          <div
            class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none"
          >
            <svg
              class="w-4 h-4 text-gray-500"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M19 9l-7 7-7-7"
              ></path>
            </svg>
          </div>
        </div>
      </div>
      <div v-show="!isLoading" ref="chartContainer" class="h-[500px]">
        <div v-if="isLoading" class="flex items-center justify-center h-full">
          <SkeltonLoader type="card" :cards="4" :size="large" />
        </div>
        <div
          v-if="
            !isLoading &&
            !totalIssues &&
            !unresolvedIssues &&
            !ignoredIssues &&
            resolvedIssues
          "
          class="flex items-center justify-center h-full"
        >
          <span class="text-gray-400">No data available</span>
        </div>
      </div>
    </div>
  </DefaultLayout>
</template>

