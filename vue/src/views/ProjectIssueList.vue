<script setup>
import DefaultLayout from "@/layout/DefaultLayout.vue";
import PaginationPage from "@/components/pagination/PaginationPage.vue";
import SkeltonLoader from "@/components/loading-skelton/SkeltonLoader.vue";
import AlertStatus from "@/components/alert/AlertStatus.vue";

import { useProjectsStore } from "@/stores/projects";
import { useIssuesStore } from "@/stores/issues";

import { onMounted, ref, computed } from "vue";
import { storeToRefs } from "pinia";

const storeProjects = useProjectsStore();
const issuesStores = useIssuesStore();
const { dataRootProejcts } = storeToRefs(issuesStores);
const { projectsList, paginationMeta, isLoading, error } =
  storeToRefs(storeProjects);
const { fetchAllProjectsListApi, fetchRootProjectApi } = storeProjects;
const { fetchRootProjectsApi } = issuesStores;

const currentPage = ref(1);
const selectedPlatform = ref("all");
const searchQuery = ref("");

const selectedRootProjects = ref({
  openproject_id: null,
  openproject_name: null,
});

const notification = ref({
  message: "",
  type: "",
  show: false,
});

// get unique platforms
const platformList = computed(() => {
  if (!projectsList.value) return [];
  const uniquePlatforms = [
    ...new Set(projectsList.value.map((project) => project.platform)),
  ];
  return ["all", ...uniquePlatforms.filter((platform) => platform)];
});

// Data proyek yang difilter berdasarkan platform dan pencarian
const searchedAndFilteredProjects = computed(() => {
  let filtered = projectsList.value || [];

  if (selectedPlatform.value !== "all") {
    filtered = filtered.filter(
      (project) => project.platform === selectedPlatform.value
    );
  }

  if (searchQuery.value.trim() !== "") {
    const lowerSearch = searchQuery.value.toLowerCase();
    filtered = filtered.filter((project) =>
      project.name?.toLowerCase().includes(lowerSearch)
    );
  }

  return filtered;
});

// Pagination
const totalPages = computed(() => {
  const total = paginationMeta.value?.total || 0;
  const perPage = paginationMeta.value?.per_page || 15;
  return Math.max(1, Math.ceil(total / perPage));
});

// Pagination handlers
const hasNextPage = computed(() => !!paginationMeta.value?.next_page_url);
const hasPrevPage = computed(() => !!paginationMeta.value?.prev_page_url);

// fetch page
const fetchPage = async (page) => {
  try {
    const pageNumber = Number(page);
    if (isNaN(pageNumber) || pageNumber < 1) {
      console.warn(`Invalid page number: ${page}`);
      return;
    }
    await fetchAllProjectsListApi(pageNumber);
    currentPage.value = pageNumber;
  } catch (err) {
    console.error("Failed to fetch projects:", err);
  }
};
// fetch root projects
const fetchRootProjects = async () => {
  try {
    await fetchRootProjectsApi();
  } catch (err) {
    console.error("Failed to fetch root projects:", err);
  }
};

// fetch root project api
const sendRootProjects = async (slug) => {
  try {
    await fetchRootProjectApi(slug, selectedRootProjects.value);
  } catch (err) {
    console.error("Failed to send root projects data:", err);
  }
};

// handle root projects change
const handleRootProjectsChange = (event, slug) => {
  const selectedOption = event.target.selectedOptions[0];
  selectedRootProjects.value = {
    openproject_id: Number(selectedOption.value),
    openproject_name: selectedOption.textContent.trim(),
  };
  sendRootProjects(slug);
  fetchPage();
};

// Handle changes page
const handlePageChange = (page) => {
  fetchPage(page);
};

// Retry fetching issues on error
const retryFetch = () => {
  error.value = null;
  fetchPage(currentPage.value);
};

// On mounted
onMounted(async () => {
  await fetchPage(1);
  await fetchRootProjects();
});
</script>

<template>
  <DefaultLayout class="bg-whiteBgPrimary-100">
    <AlertStatus
      :message="storeProjects.notification.message"
      :type="storeProjects.notification.type"
      :is-visible="storeProjects.notification.show"
      @close="storeProjects.notification.show = false"
    />
    <div class="rounded-2xl bg-white shadow-lg shadow-wildsand-100">
      <!-- Header dengan Filter dan Search -->
      <div
        class="p-4 bg-white flex flex-col gap-2 items-center md:flex-row justify-between rounded-t-2xl"
      >
        <!-- Search Bar -->
        <div class="w-full md:w-100">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search by project name"
            class="w-full px-4 py-2 border md:border-2 border-wildsand-200 rounded-lg text-codgray-900 font-medium cursor-pointer focus:outline-none focus:ring-2 focus:ring-offset-2 focus:text-cobalt-700 focus:ring-cobalt-600 hover:border-cobalt-500 transition-colors duration-200 text-sm md:text-base ease-in-out"
          />
        </div>
        <!-- Filter Platform -->
        <div class="w-full md:w-64">
          <select
            v-model="selectedPlatform"
            class="w-full px-4 py-2 border md:border-2 border-wildsand-200 rounded-lg text-codgray-900 font-medium cursor-pointer focus:outline-none focus:ring-2 focus:ring-offset-2 focus:text-cobalt-700 focus:ring-cobalt-600 hover:border-cobalt-500 transition-colors duration-200 ease-in-out text-sm md:text-base"
          >
            <option
              v-for="platform in platformList"
              :key="platform"
              :value="platform"
            >
              {{ platform === "all" ? "All Platforms" : platform }}
            </option>
          </select>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="py-8 text-center">
        <SkeltonLoader type="table" size="medium" :rows="14" :columns="5" />
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="py-8 text-center" role="alert">
        <div class="text-red-500 mb-4">{{ error }}</div>
        <button
          @click="retryFetch"
          class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
        >
          Retry
        </button>
      </div>

      <!-- Empty State -->
      <div
        v-else-if="!searchedAndFilteredProjects?.length"
        class="py-8 text-center text-gray-500"
        role="status"
      >
        <span>No projects found.</span>
      </div>

      <!-- Tabel Data Proyek -->
      <div
        v-else
        class="overflow-x-auto mx-4 rounded-2xl border border-wildsand-200 mb-6"
      >
        <table
          class="w-full table-auto border-collapse border-t border-b rounded-t-2xl border-wildsand-200"
          aria-label="Projects list"
        >
          <thead>
            <tr class="bg-wildsand-50">
              <th class="px-4 py-2 text-left font-medium uppercase">Projects</th>
              <th class="px-4 py-2 text-left font-medium uppercase hidden sm:table-cell">
                Root Project
              </th>
              <th class="px-4 py-2 text-left font-medium uppercase">Status</th>
              <th class="px-4 py-2 text-left font-medium uppercase">Platform</th>
              <th class="px-4 py-2 text-left font-medium uppercase">Total Issue</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="project in searchedAndFilteredProjects"
              :key="project?.slug"
              class="border-t border-wildsand-200 hover:bg-gray-50"
            >
              <td class="p-4">
                <router-link
                  :to="{
                    name: 'Detail Project Issue',
                    params: { slug: project?.slug },
                  }"
                  class="text-blue-600 hover:text-blue-800 hover:underline"
                >
                  <span class="text-sm">{{ project?.slug || "N/A" }}</span>
                </router-link>
              </td>
              <td class="p-4 hidden text-sm sm:table-cell">
                <span class="text-sm font-medium text-codgray-900">
                  <select
                    @change="
                      (event) => handleRootProjectsChange(event, project.slug)
                    "
                    :v-model="selectedRootProjects"
                    class="w-full max-w-48 overflow-y-auto rounded-md border border-wildsand-200 focus:outline-none focus:ring-2 focus:ring-blue-500 py-1"
                  >
                    <option class="px-2 py-1 line-clamp-1">
                      {{ project.openproject_name ?? "Select Root Project" }}
                    </option>
                    <option
                      v-for="rootProjects in dataRootProejcts"
                      :key="rootProjects.id"
                      :value="rootProjects.id"
                      class="px-2 py-1 line-clamp-1"
                    >
                      {{ rootProjects.name }}
                    </option>
                  </select>
                </span>
              </td>
              <td class="p-4">
                <span
                  :class="{
                    'px-2 py-1 rounded-full text-sm': true,
                    'bg-green-100 text-green-600 border border-green-600':
                      project?.status === 'active',
                  }"
                >
                  {{ project?.status || "N/A" }}
                </span>
              </td>
              <td class="p-4">
                <span class="text-sm font-medium text-codgray-900">
                  {{ project?.platform || "N/A" }}
                </span>
              </td>
              <td class="p-4 text-center">
                <span class="text-sm font-medium text-codgray-900">
                  {{ project?.totalIssue ?? 0 }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Pagination -->
        <div
          v-if="!isLoading && searchedAndFilteredProjects.length > 0"
          class="mt-4 px-4 pb-4"
        >
          <PaginationPage
            v-if="totalPages > 1"
            :current-page="currentPage"
            :total-pages="totalPages"
            :has-next-page="hasNextPage"
            :has-prev-page="hasPrevPage"
            @page-change="handlePageChange"
          />
        </div>
      </div>
    </div>
  </DefaultLayout>
</template>
