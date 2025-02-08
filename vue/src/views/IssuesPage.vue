<script setup>
import DefaultLayout from "@/layout/DefaultLayout.vue";
import PaginationPage from "@/components/pagination/PaginationPage.vue";
import ChartIssue from "@/components/chart/ChartIssue.vue";
import AlertStatus from "@/components/alert/AlertStatus.vue";
import SkeltonLoader from "@/components/loading-skelton/SkeltonLoader.vue";

import { useProjectsStore } from "@/stores/projects";
import { useIssuesStore } from "@/stores/issues";

import { RouterLink } from "vue-router";
import { onMounted, ref, computed } from "vue";
import { storeToRefs } from "pinia";
import moment from "moment";

const storeProjects = useProjectsStore();
const storeIssues = useIssuesStore();
const { allIssues, isLoading, error, paginationAllIssues, isAssigned } =
  storeToRefs(storeIssues);
const {
  fetchAllIssueApi,
  fetchEditStatusApi,
  fetchCreateWorkPackagesApi,
  fetchUserAssigneeApi,
  fetchDataAssigneeApi,
  //handleAssignIssueApi,
  fetchUpdateWorkPackagesApi,
} = storeIssues;
const { projectsList } = storeToRefs(storeProjects);
const { fetchAllProjectsListApi } = storeProjects;

const currentPage = ref();
const selectedProject = ref(" ");
const selectedStatus = ref(" ");
const searchQuery = ref("");
const selectedUser = ref({});

// filter by projects
const allProjects = computed(() => {
  if (!projectsList.value) return [];
  const projectProjectsList = projectsList.value.map((project) => project.name);
  const projectIssues = allIssues.value
    ? allIssues.value.map((issue) => issue.project?.name)
    : [];
  const uniqueProjects = [
    ...new Set([...projectProjectsList, ...projectIssues]),
  ];
  return [" ", ...uniqueProjects];
});

// Filter data berdasarkan project, status, dan search query
const filteredIssues = computed(() => {
  if (!allIssues.value) return [];

  let filtered = [...allIssues.value];
  if (selectedProject.value !== " ") {
    filtered = filtered.filter(
      (issue) => issue.project?.name === selectedProject.value
    );
  }
  if (selectedStatus.value !== " ") {
    filtered = filtered.filter(
      (issue) => issue.status?.current === selectedStatus.value
    );
  }
  return filtered;
});

// Pagination
const totalPages = computed(() => {
  const total = paginationAllIssues.value?.total;
  const perPage = paginationAllIssues.value?.per_page;
  return Math.max(1, Math.ceil(total / perPage));
});
// next and prev control
const hasNextPage = computed(() => {
  return !!paginationAllIssues.value?.next_page_url;
});

const hasPrevPage = computed(() => {
  return !!paginationAllIssues.value?.prev_page_url;
});

// Fetch issues
const fetchIssue = async (page = 1) => {
  try {
    await fetchAllIssueApi(page);
    currentPage.value = page;
  } catch (err) {
    console.error("Error fetching issues:", err);
  }
};
// fetch projects
const fetchProjects = async () => {
  try {
    await fetchAllProjectsListApi();
  } catch (err) {
    console.error("failed to fetch data projects", err);
  }
};

// update status
const updateStatus = async (id, status) => {
  try {
    await fetchEditStatusApi(id, status);
    fetchIssue(currentPage.value);
  } catch (error) {
    console.error("Error updating status:", error);
  }
};
// data assigned
const fetchDataAssigneed = async () => {
  try {
    await fetchDataAssigneeApi();
  } catch (err) {
    console.error("failed to fetch all data assigned", err);
  }
};

// send issue to work packages open project
const sendToOpenProject = async (id) => {
  try {
    await fetchCreateWorkPackagesApi(id, {
      assignee_id: selectedUser.value || null,
    });
  } catch (err) {
    console.error("Error sending to open project:", err);
  }
};
const updateToOpenProject = async (id) => {
  try {
    await fetchUpdateWorkPackagesApi(id, {
      assignee_id: selectedUser.value || null,
    });
  } catch (err) {
    console.error("Error sending to open project:", err);
  }
};

// assign issue to user assignee
const assigneeUser = async () => {
  try {
    await fetchUserAssigneeApi();
  } catch (err) {
    console.error("failed to send user assignee open project", err);
  }
};
// handle assignees user
const handleAssigneeChange = async (event, issueId) => {
  const selectedValue = event.target.value;
  selectedUser.value = selectedValue;

  try {
    const issue = allIssues.value.find((issue) => issue.id === issueId);

    if (issue?.assignees?.assignee_id) {
      await updateToOpenProject(issueId);
    } else {
      await sendToOpenProject(issueId);
    }
  } catch (err) {
    console.error("Error handling assignment:", err);
  }

  fetchIssue(); // Refr
};

// Event Handlers
const handleProjectChange = (event) => {
  selectedProject.value = event.target.value;
  currentPage.value = 1;
  fetchIssue(1);
};
const handleStatusChange = (event) => {
  selectedStatus.value = event.target.value;
  fetchIssue(1);
};

const handlePageChange = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page;
    fetchIssue(page);
  }
};

const handleSearchChange = (event) => {
  searchQuery.value = event.target.value;
};

// Retry on error
const retryFetch = () => {
  fetchIssue(currentPage.value);
};

// Initialize data
onMounted(() => {
  fetchIssue();
  fetchProjects();
  assigneeUser();
  fetchDataAssigneed();
});
</script>

<template>
  <DefaultLayout class="bg-whiteBgPrimary-100">
    <AlertStatus
      :message="storeIssues.notification.message"
      :type="storeIssues.notification.type"
      :is-visible="storeIssues.notification.show"
      @close="storeIssues.notification.show = false"
    />
    <div class="min-h-screen bg-white rounded-2xl">
      <!-- Header with search and filter -->
      <div
        class="p-4 bg-white flex flex-col gap-4 items-start md:flex-row justify-between rounded-t-2xl"
      >
        <div class="w-full md:w-2/3">
          <!-- Search Bar -->
          <input
            v-model="searchQuery"
            @input="handleSearchChange"
            type="text"
            placeholder="Search by Project Name"
            class="w-full px-4 py-2 border md:border-2 border-wildsand-200 rounded-lg text-codgray-900 font-medium cursor-pointer focus:outline-none focus:ring-2 focus:ring-offset-2 focus:text-cobalt-700 focus:ring-cobalt-600 hover:border-cobalt-500 transition-colors duration-200 text-sm md:text-base"
          />
        </div>

        <!-- Project Filter -->
        <div class="w-full md:w-1/3">
          <select
            v-model="selectedProject"
            @change="handleProjectChange"
            class="w-full px-4 py-2 border md:border-2 border-wildsand-200 rounded-lg text-codgray-900 font-medium cursor-pointer focus:outline-none focus:ring-2 focus:ring-offset-2 focus:text-cobalt-700 focus:ring-cobalt-600 hover:border-cobalt-500 transition-colors duration-200 text-sm md:text-base"
          >
            <option
              v-for="project in allProjects"
              :key="project"
              :value="project"
            >
              {{ project === " " ? "All Projects" : project }}
            </option>
          </select>
        </div>

        <!-- Status Filter -->
        <div class="w-full md:w-1/3">
          <select
            v-model="selectedStatus"
            @change="handleStatusChange"
            class="w-full px-4 py-2 border md:border-2 border-wildsand-200 rounded-lg text-codgray-900 font-medium cursor-pointer focus:outline-none focus:ring-2 focus:ring-offset-2 focus:text-cobalt-700 focus:ring-cobalt-600 hover:border-cobalt-500 transition-colors duration-200 text-sm md:text-base"
          >
            <option value=" ">All Statuses</option>

            <option value="unresolved">Unresolved</option>
            <option value="ignored">Ignored</option>
            <option value="resolved">Resolved</option>
          </select>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="text-center py-8">
        <SkeltonLoader type="table" size="medium" :rows="15" :columns="7" />
      </div>
      <!-- Error State -->
      <div v-else-if="error" class="text-center py-8">
        <div class="text-red-500 mb-4">{{ error }}</div>
        <button
          @click="retryFetch"
          class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition-colors"
        >
          Retry
        </button>
      </div>
      <!-- Table -->
      <div
        v-else
        class="overflow-x-auto scrollbar-hide rounded-2xl border mx-4 border-wildsand-200"
      >
        <table class="w-full table-auto md:table-fixed bg-white rounded-t-2xl">
          <thead class="w-full">
            <tr
              class="bg-wildsand-100 w-full text-codgray-950 capitalize text-sm leading-normal rounded-t-2xl"
            >
              <th
                class="py-3 px-6 text-left font-semibold font-Poppins w-1/6 md:w-1/3 border-b border-wildsand-200 uppercase"
              >
                Open Error
              </th>
              <th
                class="py-3 pl-6 text-left font-semibold w-1/5 md:w-1/5 font-Poppins border-b border-wildsand-200 uppercase"
              >
                Timeline
              </th>
              <th
                class="py-3 px-6 text-center font-semibold font-Poppins border-b border-wildsand-200 uppercase"
              >
                Users
              </th>
              <th
                class="py-3 text-center font-semibold w-fit max-w-xs font-Poppins border-b border-wildsand-200 uppercase"
              >
                Assignee
              </th>
              <th
                class="py-3 text-center font-semibold font-Poppins border-b border-wildsand-200 uppercase"
              >
                Last Seen
              </th>
              <th
                class="py-3 px-6 text-center font-semibold font-Poppins border-b border-wildsand-200 uppercase"
              >
                Action
              </th>
            </tr>
          </thead>
          <tbody class="text-codgray-800 text-base">
            <tr
              v-for="issues in filteredIssues"
              :key="issues?.id"
              class="border-b bg-white border-wildsand-200 hover:bg-wildsand-50/70 mx-2"
            >
              <td
                class="py-3 px-3 md:px-6 w-full flex justify-center flex-col gap-3 max-w-2xl"
              >
                <RouterLink
                  :to="{
                    name: 'DetailIssue',
                    params: { id: issues?.id },
                  }"
                >
                  <div class="flex gap-2 items-center">
                    <p
                      class="font-medium text-base md:text-xl text-codgray-900 hover:text-danger line-clamp-1"
                    >
                      {{ issues.metadata.type ?? "Error" }}
                    </p>
                    <p class="text-sm text-cobalt-700 font-medium line-clamp-1">
                      {{ issues.culprit }}
                    </p>
                  </div>
                  <p
                    class="text-base text-codgray-900 hover:text-danger line-clamp-1"
                  >
                    {{ issues.metadata.value }}
                  </p>
                  <div class="flex mt-3 gap-3 items-center">
                    <span
                      :class="{
                        'px-2 py-1 rounded-full text-sm': true,
                        'bg-red-100 text-red-600 border border-red-600':
                          issues.status.current === 'unresolved',
                        'bg-wildsand-200 text-wildsand-700 border border-wildsand-700':
                          issues.status.current === 'ignored',
                        'bg-green-100 text-green-600 border border-green-600':
                          issues.status.current === 'resolved',
                      }"
                    >
                      {{ issues.status.current }}
                    </span>
                    <p
                      class="text-sm text-codgray-950 font-medium line-clamp-1"
                    >
                      ID: {{ issues.id }}
                    </p>
                    <p
                      class="text-sm text-codgray-950 font-medium line-clamp-1"
                    >
                      {{ issues.shortId }}
                    </p>
                  </div>
                </RouterLink>
              </td>
              <td class="py-4">
                <ChartIssue :data="issues?.stats?.['24h']" />
              </td>
              <td class="py-3 px-6 text-center text-sm w-fit">
                {{ issues.userCount }}
              </td>
              <td class="py-3 px-2 text-center text-sm">
                <select
                  @change="(event) => handleAssigneeChange(event, issues.id)"
                  :value="issues.assignees?.assignee_name || ''"
                  :disabled="isAssigned"
                  class="w-full max-w-40 overflow-y-auto rounded-md border border-wildsand-200 focus:outline-none focus:ring-2 focus:ring-blue-500 py-1"
                >
                  <option
                    class="px-2 py-1 line-clamp-1"
                    v-if="issues.assignees?.assignee_name"
                    :value="issues.assignees.assignee_name"
                  >
                    {{ issues.assignees.assignee_name }}
                  </option>
                  <template
                    v-if="issues.data_user && issues.data_user.length > 0"
                  >
                    <option
                      v-for="user in issues.data_user"
                      :key="user.openProjectUserId"
                      :value="user.openProjectUserHref"
                      :selected="
                        user.openProjectUserHref ===
                        issues.assignees?.assignee_id
                      "
                      class="px-2 py-1 line-clamp-1"
                    >
                      {{ user.openProjectUserName }}
                    </option>
                  </template>
                </select>
              </td>
              <td class="py-3 px-2 text-center text-sm">
                {{ moment(issues.lastSeen).fromNow() }}
              </td>
              <td class="py-3 px-6 text-center">
                <div class="flex items-center justify-center gap-1">
                  <button
                    @click="updateStatus(issues.id, 'resolved')"
                    title="Resolved"
                    class="bg-white text-codgray-800 py-1 px-3 rounded-md border-[1.5px] border-wildsand-300 hover:bg-wildsand-100"
                  >
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      width="24px"
                      height="24px"
                      viewBox="0 0 50 50"
                    >
                      <path
                        fill="currentColor"
                        d="M25 42c-9.4 0-17-7.6-17-17S15.6 8 25 8s17 7.6 17 17s-7.6 17-17 17m0-32c-8.3 0-15 6.7-15 15s6.7 15 15 15s15-6.7 15-15s-6.7-15-15-15"
                      />
                      <path
                        fill="currentColor"
                        d="m23 32.4l-8.7-8.7l1.4-1.4l7.3 7.3l11.3-11.3l1.4 1.4z"
                      />
                    </svg>
                  </button>
                  <button
                    @click="updateStatus(issues.id, 'ignored')"
                    title="Ignored"
                    class="bg-white text-codgray-800 py-1 px-3 rounded-md border-[1.5px] border-wildsand-300 hover:bg-wildsand-100"
                  >
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      viewBox="0 0 24 24"
                      class="size-6"
                    >
                      <g
                        fill="none"
                        stroke="currentColor"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="1.5"
                      >
                        <path
                          d="M5.45 16.92a10.8 10.8 0 0 1-2.55-3.71a1.85 1.85 0 0 1 0-1.46A10.6 10.6 0 0 1 6.62 7.1A9 9 0 0 1 12 5.48a8.8 8.8 0 0 1 4 .85m2.56 1.72a10.85 10.85 0 0 1 2.54 3.7a1.85 1.85 0 0 1 0 1.46a10.6 10.6 0 0 1-3.72 4.65A9 9 0 0 1 12 19.48a8.8 8.8 0 0 1-4-.85"
                        />
                        <path
                          d="M8.71 13.65a3.3 3.3 0 0 1-.21-1.17a3.5 3.5 0 0 1 3.5-3.5c.4-.002.796.07 1.17.21m2.12 2.12c.14.374.212.77.21 1.17a3.5 3.5 0 0 1-3.5 3.5a3.3 3.3 0 0 1-1.17-.21M3 20L19 4"
                        />
                      </g>
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
          <div v-if="filteredIssues.length === 0" class="p-8">
            <p class="text-codgray-700 font-semibold">No issues found</p>
          </div>
        </table>
      </div>
      <PaginationPage
        :current-page="currentPage"
        :total-pages="totalPages"
        :has-next-page="hasNextPage"
        :has-prev-page="hasPrevPage"
        @page-change="handlePageChange"
      />
    </div>
  </DefaultLayout>
</template>

<style>
.scrollbar-hide {
  -ms-overflow-style: none;
  scrollbar-width: none;
}

.scrollbar-hide::-webkit-scrollbar {
  display: none;
}
select::-webkit-scrollbar {
  width: 8px;
}

select::-webkit-scrollbar-track {
  background: #f1f1f1;
}

select::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 4px;
}

select::-webkit-scrollbar-thumb:hover {
  background: #555;
}
</style>
