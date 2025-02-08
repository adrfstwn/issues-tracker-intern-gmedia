<script setup>
import DefaultLayout from "@/layout/DefaultLayout.vue";
import PaginationPage from "@/components/pagination/PaginationPage.vue";

import { useIssuesStore } from "@/stores/issues";

import { storeToRefs } from "pinia";
import { useRoute } from "vue-router";
import { ref, onMounted, computed } from "vue";

const store = useIssuesStore();
const {
  paginationEventsIssue,
  detailEventsIssue,
  error,
  isLoading,
  detailIssues,
} = storeToRefs(store);
const { fetchEventsIssueApi, fetchDetailIssueApi } = store;
const route = useRoute();
const currentPage = ref(1);
const localError = ref(null);

// compute total pages
const totalPage = computed(() => {
  const total = paginationEventsIssue.value?.total || 0;
  const perPage = paginationEventsIssue.value?.per_page || 15;
  return Math.max(1, Math.ceil(total / perPage));
});

// next and back control
const hasNextPage = computed(() => {
  return !!paginationEventsIssue.value?.next_page_url;
});
const hasPrevPage = computed(() => {
  return !!paginationEventsIssue.value?.prev_page_url;
});

// fetch issue details
const fetchIssueDetails = async (id) => {
  try {
    await fetchDetailIssueApi(parseInt(id));
    localError.value = null;
  } catch (err) {
    console.error("failed to fetch issue ", err);
    localError.value = error.value || "failed to load issue";
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

// get value tag
const getTagValue = (tags, key) => {
  if (!tags) return "-";
  const tag = tags.find((t) => t.key === key);
  return tag?.value || "-";
};

// handle page change
const handlePageChange = (page) => {
  fetchEventsIssue(route.params.id, page);
};

onMounted(() => {
  fetchEventsIssue(route.params.id, 1);
  fetchDetailIssueApi(route.params.id);
});
</script>

<template>
  <DefaultLayout class="bg-whiteBgPrimary-100">
    <div
      class="rounded-xl border border-wildsand-200 bg-white shadow-lg shadow-wildsand-100 break-words"
    >
      <!-- Empty state -->

      <div class="flex flex-col py-6 px-4 md:px-6 xl:px-7 gap-6 break-words">
        <div class="flex gap-4 justify-between break-words">
          <div v-if="isLoading" class="py-6 text-center">
            <div
              class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-blue-500 border-t-transparent"
              role="status"
              aria-label="Loading"
            ></div>
            <p class="mt-2 text-gray-600">Loading Issue Details...</p>
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
          <div
            v-else-if="!detailIssues"
            class="py-6 text-center text-wildsand-500"
            role="status"
          >
            <span>No issue found.</span>
          </div>
          <div
            v-else
            class=" flex flex-col gap-2 font-medium break-words whitespace-normal max-w-lg"
          >
            <h4
              class="md:text-base text-sm font-bold text-cobalt-900 uppercase"
            >
              {{ detailIssues.shortId }} >
            </h4>

            <p
              class="md:text-base text-sm text-codgray-600 break-words whitespace-normal line-clamp-3 break-all"
            >
              {{ detailIssues.title }}
            </p>
          </div>
          <div class="flex flex-col gap-1">
            <h4
              class="text-base md:text-xl text-codgray-900 font-medium leading-none"
            >
              Total events
            </h4>
            <p class="md:text-2xl text-base font-bold text-cobalt-900">
              {{ paginationEventsIssue.total }}
            </p>
          </div>
        </div>
      </div>

      <!-- Projects Table -->
      <div
        class="overflow-x-auto scrollbar-hide rounded-2xl border mx-4 mb-6 border-wildsand-200"
      >
        <table
          class="w-full table-auto rounded-t-2xl border border-wildsand-200"
        >
          <thead class="bg-wildsand-50 rounded-t-2xl w-full">
            <tr
              class="bg-wildsand-100 w-full text-codgray-950 capitalize text-sm leading-normal rounded-t-2xl"
            >
              <th
                class="px-4 py-2 text-left font-medium uppercase rounded-t-xl"
              >
                ID
              </th>
              <th class="px-4 py-2 text-left font-medium uppercase">Title</th>
              <th class="px-4 py-2 text-left font-medium uppercase">Browser</th>
              <th class="px-4 py-2 text-left font-medium uppercase">
                Browser Name
              </th>
              <th class="px-4 py-2 text-left font-medium uppercase">
                Client OS
              </th>
              <th class="px-4 py-2 text-left font-medium uppercase">
                Client OS.Name
              </th>
              <th class="px-4 py-2 text-left font-medium uppercase">Device</th>
              <th class="px-4 py-2 text-left font-medium uppercase">
                Device Family
              </th>
              <th class="px-4 py-2 text-left font-medium uppercase">
                Environtment
              </th>
              <th class="px-4 py-2 text-left font-medium uppercase">Handled</th>
              <th class="px-4 py-2 text-left font-medium uppercase">Level</th>
              <th class="px-4 py-2 text-left font-medium uppercase">
                Mechanism
              </th>
              <th class="px-4 py-2 text-left font-medium uppercase">OS</th>
              <th class="px-4 py-2 text-left font-medium uppercase">OS Name</th>
              <th class="px-4 py-2 text-left font-medium uppercase">Runtime</th>
              <th class="px-4 py-2 text-left font-medium uppercase">
                Runtime Name
              </th>
              <th class="px-4 py-2 text-left font-medium uppercase">
                Server Name
              </th>
              <th class="px-4 py-2 text-left font-medium uppercase">
                Transaction
              </th>
              <th
                class="px-4 py-2 text-left font-medium uppercase rounded-t-xl"
              >
                Url
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="event in detailEventsIssue"
              :key="event?.id"
              class="border-t border-wildsand-200"
            >
              <td class="p-3 text-sm font-medium text-codgray-900">
                {{ event?.id }}
              </td>
              <td class="p-3 text-sm font-medium text-codgray-900">
                {{ event?.title || "-" }}
              </td>
              <td class="p-3 text-sm font-medium text-codgray-900">
                {{ getTagValue(event.tags, "browser" || "-") }}
              </td>
              <td class="p-3 text-sm font-medium text-codgray-900">
                {{ getTagValue(event.tags, "browser.name" || "-") }}
              </td>
              <td class="p-3 text-sm font-medium text-codgray-900">
                {{ getTagValue(event.tags, "client_os" || "-") }}
              </td>
              <td class="p-3 text-sm font-medium text-codgray-900">
                {{ getTagValue(event.tags, "client_os.name" || "-") }}
              </td>
              <td class="p-3 text-sm font-medium text-codgray-900">
                {{ getTagValue(event.tags, "device" || "-") }}
              </td>
              <td class="p-3 text-sm font-medium text-codgray-900">
                {{ getTagValue(event.tags, "device.family" || "-") }}
              </td>
              <td class="p-3 text-sm font-medium text-codgray-900">
                {{ getTagValue(event.tags, "environment" || "-") }}
              </td>
              <td class="p-3 text-sm font-medium text-codgray-900">
                {{ getTagValue(event.tags, "handled" || "-") }}
              </td>
              <td class="p-3 text-sm font-medium text-codgray-900">
                {{ getTagValue(event.tags, "level" || "-") }}
              </td>
              <td class="p-3 text-sm font-medium text-codgray-900">
                {{ getTagValue(event.tags, "mechanism" || "-") }}
              </td>
              <td class="p-3 text-sm font-medium text-codgray-900">
                {{ getTagValue(event.tags, "os") }}
              </td>
              <td class="p-3 text-sm font-medium text-codgray-900">
                {{ getTagValue(event.tags, "os.name") }}
              </td>
              <td class="p-3 text-sm font-medium text-codgray-900">
                {{ getTagValue(event.tags, "runtime") }}
              </td>
              <td class="p-3 text-sm font-medium text-codgray-900">
                {{ getTagValue(event.tags, "runtime.name") }}
              </td>
              <td class="p-3 text-sm font-medium text-codgray-900">
                {{ getTagValue(event.tags, "server_name") }}
              </td>
              <td class="p-3 text-sm font-medium text-codgray-900">
                {{ getTagValue(event.tags, "transaction") }}
              </td>
              <td class="p-3 text-sm font-medium text-codgray-900">
                {{ getTagValue(event.tags, "url") }}
              </td>
            </tr>
          </tbody>
          <div v-if="detailEventsIssue.length === 0" class="p-8">
            <p class="text-codgray-700 font-semibold">No events found</p>
          </div>
        </table>
      </div>
      <div
        class="mt-4 px-4 pb-4"
        v-if="!isLoading && detailEventsIssue.length > 0"
      >
        <PaginationPage
          v-if="totalPage > 1"
          :current-page="currentPage"
          :total-pages="totalPage"
          :has-prev-page="hasPrevPage"
          :has-next-page="hasNextPage"
          @page-change="handlePageChange"
        />
      </div>
    </div>
  </DefaultLayout>
</template>
