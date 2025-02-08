// DetailIssue.vue
<script setup>
import DefaultLayout from "@/layout/DefaultLayout.vue";
import ChartIssue from "@/components/chart/ChartIssue.vue";
import AlertStatus from "@/components/alert/AlertStatus.vue";
import DetailIssueSkeltonLoader from "@/components/loading-skelton/DetailIssueSkeltonLoader.vue";

import { storeToRefs } from "pinia";
import { useIssuesStore } from "@/stores/issues";

import { onMounted, ref, watch, computed } from "vue";
import { useRoute } from "vue-router";
import { RouterLink } from "vue-router";
import moment from "moment";
import hljs from "highlight.js";
import "highlight.js/styles/atom-one-dark.css";

const route = useRoute();
const store = useIssuesStore();
const {
  dataAssignee,
  userAssignee,
  detailIssues,
  paginationEventsIssue,
  notification,
  traceIssue,
  isLoading,
  error,
} = storeToRefs(store);
const {
  fetchDataAssigneeApi,
  fetchUserAssigneeApi,
  fetchCreateWorkPackagesApi,
  fetchDetailIssueApi,
  fetchEventsIssueApi,
  fetchTraceIssue,
  fetchEditStatusApi,
} = store;

const localError = ref(null);
const currentPage = ref(1);
const dropdownStates = ref([]);
const selectedUser = ref({});

// fetch users assignee
const fetchUserAssignee = async () => {
  try {
    await fetchUserAssigneeApi();
  } catch (err) {
    console.error("failed to fetch data api assignee", err);
  }
};
// send to open proejct
const sendToOpenProject = async (id) => {
  try {
    await fetchCreateWorkPackagesApi(id, { assignee_id: selectedUser.value });
  } catch (err) {
    console.error(
      "failed to fetch data assignee and id issue to send to open project",
      err
    );
  }
};
// fetch data assigned
const fetchDataAssigneed = async () => {
  try {
    await fetchDataAssigneeApi();
  } catch (err) {
    console.error("failed to fetch all data assigned", err);
  }
};
// handle change assignee
const handleAssigneeChange = (event, id) => {
  const selectedValue = event.target.value;
  selectedUser.value = selectedValue;
  sendToOpenProject(id);
  fetchIssueDetails(id);
};

const updateStatus = async (id, status) => {
  try {
    await fetchEditStatusApi(id, status);
  } catch (error) {
    console.error("Error updating status:", error);
  }
};

const highlightCode = (code) => {
  return hljs.highlightAuto(code).value;
};

const toggleDropdown = (index) => {
  dropdownStates.value[index] = !dropdownStates.value[index];
};

const isDropdownOpen = (index) => {
  return dropdownStates.value[index] ?? false;
};

const totalPage = computed(() => {
  const total = paginationEventsIssue.value?.total || 0;
  const perPage = paginationEventsIssue.value?.per_page || 15;
  return Math.max(1, Math.ceil(total / perPage));
});

const fetchIssueDetails = async (id) => {
  try {
    await fetchDetailIssueApi(parseInt(id));
    localError.value = null;
  } catch (err) {
    console.error("Failed to fetch issue details:", err);
    localError.value =
      error.value || "Failed to load issue details. Please try again.";
  }
};

//fetch events
const fetchEventsIssue = async (id, page = 1) => {
  try {
    const pageNumber = Number(page);
    if (isNaN(pageNumber) || pageNumber < 1) {
      console.warn(`invalid page number: ${page}`);
      return;
    }
    error.value = null;
    await fetchEventsIssueApi(id, pageNumber);
    currentPage.value = pageNumber;
  } catch (err) {
    console.error("failed to fetch event", err);
    error.value = "failed to load issue. please try again";
  }
};

// fetch trace
const fetchTraceIssues = async (id) => {
  try {
    await fetchTraceIssue(id);
  } catch (err) {
    console.error("failed to fetch issue trace");
    error.value = "failed to load trace, try again";
  }
};

const retryFetch = () => {
  const issueId = route.params.id;
  if (issueId) {
    fetchIssueDetails(issueId);
    fetchEventsIssue(issueId);
    fetchTraceIssues(issueId);
  }
};

onMounted(() => {
  const issueId = route.params.id;
  if (issueId) {
    fetchIssueDetails(issueId);
    fetchEventsIssue(issueId, 1);
    fetchTraceIssues(issueId);
  } else {
    localError.value = "Invalid or missing Issue ID.";
  }

  fetchUserAssignee();
  fetchDataAssigneed();
});

watch(
  () => route.params.id,
  (newId) => {
    if (newId) {
      fetchIssueDetails(newId);
      fetchEventsIssue(newId);
      fetchTraceIssues(newId);
    } else {
      localError.value = "Invalid or missing Issue ID.";
    }
  }
);
</script>


<template>
  <DefaultLayout class="bg-whiteBgPrimary-100">
    <AlertStatus
      :message="notification.message"
      :type="notification.type"
      :is-visible="notification.show"
      @close="notification.show = false"
    />
    <div v-if="isLoading" class="py-6 text-center">
      <DetailIssueSkeltonLoader />
    </div>

    <!-- Error state -->
    <div
      v-else-if="localError"
      class="py-6 text-center text-red-500"
      role="alert"
    >
      <p>{{ localError }}</p>
      <button
        @click="retryFetch"
        class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
      >
        Retry
      </button>
    </div>

    <!-- Empty state -->
    <div
      v-else-if="!detailIssues"
      class="py-6 text-center text-wildsand-500"
      role="status"
    >
      <span>No issue found.</span>
    </div>
    <!-- Content state -->
    <div v-else class="min-h-screen bg-white rounded-xl">
      <div class="flex flex-col gap-4 md:gap-8 w-full">
        <!-- Header Section -->
        <div
          class="md:mx-4 mt-6 bg-white items-center flex flex-col sm:flex-row gap-4 justify-between"
        >
          <div class="px-3 w-fit break-words">
            <span
              class="text-codgray-700 text-sm md:text-base block break-words"
            >
              Reported
              {{ moment(detailIssues.firstSeen).startOf("hour").fromNow() }} by
              <div class="flex flex-col gap-1 break-words">
                <p
                  class="text-codgray-950 text-base md:text-lg font-medium inline sm:inline mt-1 sm:mt-0 break-all"
                >
                  {{ detailIssues.permalink }}
                </p>
                <p class="text-codgray-600 text-sm md:text-base break-all">
                  {{ detailIssues.culprit }}
                </p>
              </div>
            </span>
          </div>

          <div
            class="flex items-center justify-center sm:justify-end gap-3 px-4 w-full sm:w-auto"
          >
            <button
              @click="updateStatus(detailIssues.id, 'ignored')"
              class="w-fit flex-1 sm:flex-none px-3 py-[6px] md:px-4 md:py-2 text-sm md:text-base text-codgray-900 border-2 rounded-md border-codgray-600 bg-white hover:bg-wildsand-100 transition-colors"
            >
              Ignored
            </button>
            <button
              @click="updateStatus(detailIssues.id, 'resolved')"
              class="w-fit flex-1 sm:flex-none px-3 py-[6px] md:px-4 md:py-2 text-sm md:text-base text-green-800 border-2 rounded-md border-green-800 bg-white hover:bg-green-100 transition-colors"
            >
              Resolve
            </button>
            <select
              @change="(event) => handleAssigneeChange(event, detailIssues.id)"
              :v-model="selectedUser"
              class="w-full max-w-40 overflow-y-auto rounded-md border-2 border-cobalt-800 focus:outline-none focus:ring-2 focus:ring-blue-800 x-3 py-[6px] md:px-4 md:py-2"
            >
              <option class="px-2 py-1 line-clamp-1">
                {{ detailIssues.assignees.assignee_name }}
              </option>
              <template
                v-if="
                  detailIssues.data_user && detailIssues.data_user.length > 0
                "
              >
              </template>
              <option
                v-for="user in detailIssues.data_user"
                :key="user.openProjectUserId"
                :value="user.openProjectUserHref"
                class="px-2 py-1 line-clamp-1"
              >
                {{ user.openProjectUserName }}
              </option>
            </select>
          </div>
        </div>
        <hr class="border border-wildsand-200" />
        <div class="mx-4 md:mx-6 flex flex-col gap-20">
          <!-- Error Stats Section -->
          <div class="flex flex-col justify-between gap-4 md:gap-6">
            <div class="flex flex-col gap-1">
              <div class="flex flex-col gap-1">
                <h2
                  class="text-base md:text-xl font-semibold text-codgray-900 line-clamp-3"
                >
                  {{ detailIssues.title }}
                </h2>
                <p class="text-sm md:text-base text-codgray-700 break-all">
                  File name: {{ detailIssues.metadata.filename }}
                </p>
              </div>
              <div class="flex flex-row gap-2 items-center">
                <span
                  :class="{
                    'px-2 py-1 rounded-full text-sm font-medium max-w-fit': true,
                    'bg-red-100 text-red-600 border border-red-600':
                      detailIssues.status === 'unresolved',
                    'bg-wildsand-200 text-wildsand-700 border border-wildsand-700':
                      detailIssues.status === 'ignored',
                  }"
                  >{{ detailIssues.status }}</span
                >
                <span
                  :class="{
                    'px-2 py-1 rounded-full text-sm font-medium max-w-fit': true,
                    'bg-red-100 text-red-600 border border-red-600':
                      detailIssues.type === 'error',
                    'bg-yellow-100 text-yellow-800 border border-yellow-800':
                      detailIssues.type === 'ignored',
                    'bg-green-100 text-green-800 border border-green-800':
                      detailIssues.type === 'resolved',
                  }"
                >
                  {{ detailIssues.type }}
                </span>
              </div>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 w-full">
              <!-- Stats Cards -->
              <div
                class="border w-full border-wildsand-200 rounded-lg flex flex-col"
              >
                <div
                  class="text-codgray-950 text-center text-sm font-semibold px-4 py-2 rounded-t-lg w-full border-b-[1px] bg-wildsand-50 border-wildsand-200"
                >
                  <p>Events</p>
                </div>
                <div
                  class="text-codgray-950 font-medium text-center text-base p-4"
                >
                  <RouterLink
                    :to="{
                      name: 'DetailEventsIssue',
                      params: { id: detailIssues?.id },
                    }"
                  >
                    <p>{{ paginationEventsIssue.total }}</p>
                  </RouterLink>
                </div>
              </div>

              <div
                class="border w-full border-wildsand-200 rounded-lg flex flex-col"
              >
                <div
                  class="text-codgray-950 text-center text-sm font-semibold px-4 py-2 w-full 2 rounded-t-lg border-b-[1px] bg-wildsand-50 border-wildsand-200"
                >
                  <p>Users</p>
                </div>
                <div
                  class="text-codgray-950 font-medium text-center text-base p-4"
                >
                  <p>{{ detailIssues.userCount }}</p>
                </div>
              </div>

              <div
                class="border w-full border-wildsand-200 rounded-lg flex flex-col"
              >
                <div
                  class="text-codgray-950 text-center text-sm font-semibold px-4 py-2 w-full 2 rounded-t-lg border-b-[1px] bg-wildsand-50 border-wildsand-200"
                >
                  <p>First Seen</p>
                </div>
                <div
                  class="text-codgray-950 font-medium text-center text-base p-4"
                >
                  <p>
                    {{
                      moment(detailIssues.firstSeen).startOf("day").fromNow()
                    }}
                  </p>
                </div>
              </div>

              <div
                class="border w-full border-wildsand-200 rounded-lg flex flex-col"
              >
                <div
                  class="text-codgray-950 text-center text-sm font-semibold px-4 py-2 w-full rounded-t-lg border-b-[1px] bg-wildsand-50 border-wildsand-200"
                >
                  <p>Last Seen</p>
                </div>
                <div
                  class="text-codgray-950 font-medium text-center text-base p-4"
                >
                  <p>
                    {{ moment(detailIssues.lastSeen).startOf("day").fromNow() }}
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Chart Section -->
          <div class="flex flex-col gap-4 md:gap-6 md:max-h-screen">
            <div class="flex flex-col gap-3">
              <h2 class="font-semibold text-xl md:text-2xl text-codgray-950">
                Last 24 Hours
              </h2>
            </div>
            <div class="p-4 w-full">
              <div class="w-full">
                <ChartIssue :data="detailIssues?.stats?.['24h']" />
              </div>
            </div>
          </div>

          <!-- Stack Trace Section -->
          <div>
            <!-- Check if traceIssue is available -->
            <div
              v-if="!traceIssue || traceIssue.length === 0"
              class="w-full text-center py-4 text-lg text-red-600"
            >
              State Not Available
            </div>

            <!-- If traceIssue is available, iterate over it -->
            <div v-for="trace in traceIssue" :key="trace">
              <h2
                class="text-xl md:text-2xl text-codgray-950 font-semibold mb-4"
              >
                Stack trace
              </h2>

              <!-- Check if entries are available -->
              <div
                v-if="
                  !trace.latestEvent ||
                  !trace.latestEvent.entries ||
                  trace.latestEvent.entries.length === 0
                "
                class="w-full text-center py-4 text-lg text-red-600"
              >
                State Not Available
              </div>

              <!-- If entries are available, display them -->
              <div
                v-for="(value, index) in trace.latestEvent.entries[0].data
                  .values"
                :key="index"
                class="flex flex-col gap-6 mb-6 break-words"
              >
                <div class="flex flex-col gap-1 break-words">
                  <h3
                    class="font-medium text-base md:text-2xl text-codgray-900 max-w-fit"
                  >
                    {{ value.type }}
                  </h3>
                  <p class="text-sm md:text-lg text-codgray-600">
                    {{ value.value }}
                  </p>
                </div>

                <!-- Responsive Grid Section -->
                <div
                  class="grid grid-cols-1 sm:grid-cols-2 md:flex md:flex-row gap-3"
                >
                  <!-- Mechanism Field -->
                  <div
                    class="flex items-center border border-wildsand-400 bg-white rounded-lg divide-x divide-wildsand-300 max-w-fit"
                  >
                    <span
                      class="text-sm sm:text-base text-codgray-600 px-4 py-2"
                    >
                      mechanism
                    </span>
                    <span
                      class="text-sm sm:text-base text-codgray-600 px-4 py-2"
                    >
                      {{ value.mechanism?.type || "Not available" }}
                    </span>
                  </div>

                  <!-- Handled Field -->
                  <div
                    class="flex items-center border border-wildsand-400 bg-white rounded-lg divide-x divide-wildsand-300 max-w-fit"
                  >
                    <span
                      class="text-sm sm:text-base text-codgray-600 px-4 py-2"
                    >
                      handled
                    </span>
                    <span
                      class="text-sm sm:text-base text-codgray-600 px-4 py-2"
                      :class="{
                        'px-4 py-2 rounded-r-lg': true,
                        'bg-red-100 text-red-800 rounded-lg':
                          !value.mechanism?.handled,
                        'bg-green-100 text-green-800': value.mechanism?.handled,
                      }"
                    >
                      {{ value.mechanism?.handled ? "Yes" : "No" }}
                    </span>
                  </div>

                  <!-- Code Field -->
                  <div
                    class="flex items-center border border-wildsand-400 bg-white rounded-lg divide-x divide-wildsand-300 max-w-fit break-words"
                  >
                    <span
                      class="text-sm sm:text-base text-codgray-600 px-4 py-2"
                    >
                      code
                    </span>
                    <span
                      class="text-sm sm:text-base text-codgray-600 px-4 py-2"
                    >
                      {{ value.mechanism?.data?.code || "Not available" }}
                    </span>
                  </div>
                </div>

                <!-- Iterasi pada stacktrace frames -->
                <div
                  v-for="(frame, frameIndex) in trace.latestEvent.entries[0]
                    .data.values[0].stacktrace.frames"
                  :key="frameIndex"
                  class="overflow-auto max-w-full w-full"
                >
                  <div
                    class="flex flex-col border border-cobalt-100 rounded-t-xl mb-4"
                    @click="toggleDropdown(frameIndex)"
                  >
                    <div
                      class="mb-4 border-b border-cobalt-100 py-3 bg-cobalt-50 flex justify-between items-center rounded-t-xl max-w-full break-words overflow-auto whitespace-normal"
                      @click="toggleDropdown(frameIndex)"
                    >
                      <p
                        class="text-base text-codgray-600 px-3 font-medium"
                        @click="toggleDropdown(frameIndex)"
                      >
                        {{ frame.filename }}
                        <span>at line {{ frame.lineNo }}</span>
                      </p>

                      <!-- Dropdown Toggle Button -->
                      <button
                        class="p-2 bg-white rounded-md mx-6"
                        @click="toggleDropdown(frameIndex)"
                      >
                        <svg
                          width="16"
                          height="16"
                          viewBox="0 0 16 16"
                          fill="none"
                          xmlns="http://www.w3.org/2000/svg"
                          :class="{ 'rotate-90': isDropdownOpen(frameIndex) }"
                        >
                          <path
                            d="M5.15674 13.069L9.76774 8.384C9.86886 8.28178 9.92559 8.14379 9.92559 8C9.92559 7.85621 9.86886 7.71822 9.76774 7.616L5.15774 2.93C5.05666 2.82715 5.00002 2.68871 5.00002 2.5445C5.00002 2.40029 5.05666 2.26186 5.15774 2.159C5.20711 2.10835 5.26613 2.0681 5.33131 2.04062C5.39648 2.01313 5.4665 1.99897 5.53724 1.99897C5.60797 1.99897 5.67799 2.01313 5.74317 2.04062C5.80834 2.0681 5.86736 2.10835 5.91674 2.159L10.5267 6.843C10.8297 7.15152 10.9994 7.56662 10.9994 7.999C10.9994 8.43138 10.8297 8.84648 10.5267 9.155L5.91674 13.839C5.86734 13.8898 5.80827 13.9302 5.743 13.9578C5.67773 13.9853 5.60759 13.9995 5.53674 13.9995C5.46588 13.9995 5.39574 13.9853 5.33048 13.9578C5.26521 13.9302 5.20613 13.8898 5.15674 13.839C5.05566 13.7361 4.99902 13.5977 4.99902 13.4535C4.99902 13.3093 5.05566 13.1709 5.15674 13.068"
                            fill="#206EF0"
                          />
                        </svg>
                      </button>
                    </div>

                    <!-- Dropdown Content -->
                    <div v-if="isDropdownOpen(frameIndex)" class="px-6">
                      <!-- Check if context is available -->
                      <div
                        v-if="!frame.context || frame.context.length === 0"
                        class="w-full text-center py-4 text-lg text-red-600"
                      >
                        Code not available
                      </div>

                      <div
                        v-for="(contextItem, contextIndex) in frame.context"
                        :key="contextIndex"
                      >
                        <p
                          class="font-CourierPrime text-sm md:text-base"
                          :class="{
                            'bg-red-200 text-yellow-800 text-sm md:text-base':
                              contextItem[0] === frame.lineNo,
                            'text-codgray-900 text-sm md:text-base':
                              contextItem[0] !== frame.lineNo,
                          }"
                        >
                          {{ contextItem[0] }}.
                          <span
                            class="text-sm md:text-base"
                            v-html="highlightCode(contextItem[1])"
                          ></span>
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </DefaultLayout>
</template>
