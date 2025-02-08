<script setup>
import DefaultLayout from "@/layout/DefaultLayout.vue";
import PaginationPage from "@/components/pagination/PaginationPage.vue";

import { useProjectsStore } from "@/stores/projects";

import { storeToRefs } from "pinia";
import { useRoute } from "vue-router";
import { onMounted, ref, computed } from "vue";
import { RouterLink } from "vue-router";
import moment from "moment";

const route = useRoute();
const store = useProjectsStore();
const { projectsListIssue, issuePaginationMeta, isLoading, error } = storeToRefs(store);
const { fetchAllProjectsIssueApi } = store;
const currentPage = ref(1);
const selectedStatus = ref("unresolved");

const totalPages = computed(() => {
  const total = issuePaginationMeta.value?.total || 0;
  const perPage = issuePaginationMeta.value?.per_page || 15;
  return Math.max(1, Math.ceil(total / perPage));
});

const hasNextPage = computed(() => issuePaginationMeta.value?.next_page_url);
const hasPrevPage = computed(() => issuePaginationMeta.value?.prev_page_url);

const filteredProjects = computed(() => {
  if (!projectsListIssue.value) return [];
  return projectsListIssue.value.filter((issue) => issue.status === selectedStatus.value);
});

const fetchIssueProject = async (slug, page = 1) => {
  try {
    const pageNumber = Number(page);
    if (isNaN(pageNumber) || pageNumber < 1) {
      console.warn(`Invalid page number: ${page}`);
      return;
    }
    error.value = null;
    await fetchAllProjectsIssueApi(slug, pageNumber);
    currentPage.value = pageNumber;
  } catch (err) {
    console.error("Failed to fetch issues project:", err);
    error.value = "Failed to load issues. Please try again later.";
  }
};

const handlePageChange = (page) => {
  fetchIssueProject(route.params.slug, page);
};

onMounted(() => {
  fetchIssueProject(route.params.slug, 1);
});
</script>

<template>
  <DefaultLayout class="bg-whiteBgPrimary-100">
    <div class="rounded-xl border border-wildsand-200 bg-white shadow-lg shadow-wildsand-100">
      <!-- Header -->
      <div class="py-6 px-4 md:px-6 flex flex-col sm:flex-row justify-between gap-4">
        <h4 class="text-base md:text-xl font-bold text-cobalt-950 capitalize line-clamp-1">
          {{ route.params.slug }}
        </h4>

        <select
          v-model="selectedStatus"
          class="px-4 py-2 border md:border-2 border-wildsand-200 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          <option value="unresolved">Unresolved</option>
          <option value="ignored">Ignored</option>
          <option value="resolved">Resolved</option>
        </select>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="py-8 text-center">
        <div
          class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-blue-500 border-t-transparent"
          role="status"
          aria-label="Loading"
        ></div>
        <p class="mt-2 text-gray-600">Loading Issues...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="py-8 text-center" role="alert">
        <div class="text-red-500 mb-4">{{ error }}</div>
        <button
          @click="() => fetchIssueProject(route.params.slug, currentPage)"
          class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
        >
          Retry
        </button>
      </div>

      <!-- Empty State -->
      <div
        v-else-if="!filteredProjects.length"
        class="py-8 text-center text-gray-500"
        role="status"
      >
        <span>No issues found.</span>
      </div>

      <!-- Projects Table -->
      <div
        v-else
        class="overflow-x-auto mx-4 rounded-2xl border border-wildsand-200 my-6"
      >
        <table class="w-full table-auto border-collapse">
          <thead class="bg-wildsand-50">
            <tr>
              <th class="px-4 py-2 text-left font-medium uppercase">ID</th>
              <th class="px-4 py-2 text-left font-medium uppercase">Short ID</th>
              <th class="px-4 py-2 text-left font-medium uppercase">Title</th>
              <th class="px-4 py-2 text-left font-medium uppercase">Status</th>
              <th class="px-4 py-2 text-left font-medium uppercase">Platform</th>
              <th class="px-4 py-2 text-left font-medium uppercase">Last Seen</th>
              <th class="px-4 py-2 text-left font-medium uppercase">Total</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="issuesProject in filteredProjects"
              :key="issuesProject?.id"
              class="border-t border-wildsand-200 hover:bg-gray-50"
            >
              <td class="p-3 text-sm font-medium text-codgray-900">
                <RouterLink
                  :to="{
                    name: 'DetailIssue',
                    params: { id: issuesProject?.id },
                  }"
                  class="hover:text-blue-600"
                >
                  {{ issuesProject?.id }}
                </RouterLink>
              </td>
              <td class="p-3 text-sm font-medium text-codgray-900">
                {{ issuesProject?.shortId }}
              </td>
              <td class="p-3">
                <div class="flex flex-col gap-1">
                  <span
                    class="px-2 py-1 rounded-full text-xs max-w-fit"
                    :class="{
                      'bg-red-100 text-red-800 border border-red-800': issuesProject.type === 'error',
                      'bg-yellow-100 text-yellow-800 border border-yellow-800': issuesProject.type === 'ignored',
                      'bg-green-100 text-green-800 border border-green-800': issuesProject.type === 'resolved'
                    }"
                  >
                    {{ issuesProject?.level }}
                  </span>
                  <div class="flex flex-col">
                    <RouterLink
                      :to="{ name: 'DetailIssue', params: { id: issuesProject?.id } }"
                      class="text-base font-medium text-codgray-900 line-clamp-1 hover:text-blue-600"
                    >
                      {{ issuesProject?.title }}
                    </RouterLink>
                    <span class="text-sm font-medium text-codgray-700">
                      {{ issuesProject?.permalink }}
                    </span>
                  </div>
                </div>
              </td>
              <td class="p-3">
                <span
                  class="px-2 py-1 rounded-full text-sm inline-block"
                  :class="{
                    'bg-red-100 text-red-600 border border-red-600': issuesProject.status === 'unresolved',
                    'bg-wildsand-200 text-wildsand-700 border border-wildsand-700': issuesProject.status === 'ignored',
                    'bg-green-100 text-green-600 border border-green-600': issuesProject.status === 'resolved'
                  }"
                >
                  {{ issuesProject?.status }}
                </span>
              </td>
              <td class="p-3 text-sm font-medium text-codgray-900">
                {{ issuesProject?.platform }}
              </td>
              <td class="p-3 text-sm font-medium text-codgray-900">
                {{ moment(issuesProject?.lastSeen).startOf("hour").fromNow() }}
              </td>
              <td class="p-3 text-sm text-center font-medium text-codgray-900">
                {{ issuesProject?.userCount }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <PaginationPage
        v-if="totalPages > 1"
        :current-page="currentPage"
        :total-pages="totalPages"
        :has-next-page="hasNextPage"
        :has-prev-page="hasPrevPage"
        @page-change="handlePageChange"
        class="px-4 pb-4"
      />
    </div>
  </DefaultLayout>
</template>